<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Court;
use App\Models\Equipment;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Midtrans\Config;
use Midtrans\Snap;

class BookingAdminController extends Controller {
    public function index(Request $request) {
        Booking::cancelExpiredBookings();

        $query = Booking::with(['user', 'court', 'approver'])->latest();
        if ($request->filled('q')) {
            $kw = trim($request->q);
            $cleanId = preg_replace('/[^0-9]/', '', $kw);
            $query->where(function ($sub) use ($kw, $cleanId) {
                $sub->where('customer_name', 'like', "%{$kw}%")
                    ->orWhere('customer_phone', 'like', "%{$kw}%")
                    ->orWhere('customer_email', 'like', "%{$kw}%")
                    ->orWhere('customer_nik', 'like', "%{$kw}%");
                if ($cleanId !== '') $sub->orWhere('id', (int)$cleanId);
            });
        }
        if ($request->status === 'unpaid') {
            $query->where('status', '!=', 'cancelled')->whereColumn('paid_amount', '<', 'total_price');
        } elseif ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('court')) $query->where('court_id', $request->court);
        if ($request->filled('date')) $query->whereDate('start_dt', $request->date);

        $bookings = $query->get();
        $courts = Court::where('is_active', true)->get();
        $prefix = 'admin';

        if ($request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return view('shared.bookings-table-partial', compact('bookings', 'courts', 'prefix'));
        }

        return view('admin.bookings.index', compact('bookings', 'courts'));
    }

    // Export Data Booking & Approval ke Format Microsoft Excel (.xlsx)
    public function export(Request $request) {
        Booking::cancelExpiredBookings();

        $query = Booking::with(['user', 'court', 'approver'])->latest();
        if ($request->filled('q')) {
            $kw = trim($request->q);
            $cleanId = preg_replace('/[^0-9]/', '', $kw);
            $query->where(function ($sub) use ($kw, $cleanId) {
                $sub->where('customer_name', 'like', "%{$kw}%")
                    ->orWhere('customer_phone', 'like', "%{$kw}%")
                    ->orWhere('customer_email', 'like', "%{$kw}%")
                    ->orWhere('customer_nik', 'like', "%{$kw}%");
                if ($cleanId !== '') $sub->orWhere('id', (int)$cleanId);
            });
        }
        if ($request->status === 'unpaid') {
            $query->where('status', '!=', 'cancelled')->whereColumn('paid_amount', '<', 'total_price');
        } elseif ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('court')) $query->where('court_id', $request->court);
        if ($request->filled('date')) $query->whereDate('start_dt', $request->date);

        $bookings = $query->get();

        $headers = [
            'No',
            'No. Invoice',
            'ID Booking',
            'Waktu Booking',
            'Nama Pemesan',
            'No. WhatsApp / HP',
            'Email',
            'Lapangan',
            'Cabang Olahraga',
            'Tanggal Main',
            'Jam Mulai',
            'Jam Selesai',
            'Durasi (Jam)',
            'Total Biaya (Rp)',
            'Sudah Dibayar (Rp)',
            'Sisa Tagihan (Rp)',
            'Metode Bayar',
            'Status Approval',
            'Status Pelunasan',
            'Bukti Transfer QRIS',
            'Di-Approve Oleh',
            'Waktu Approval',
        ];

        $statusLabels = [
            'pending'   => 'Menunggu Approve',
            'confirmed' => 'Sudah Di-Approve',
            'cancelled' => 'Dibatalkan',
        ];

        $rows = [];
        $no = 1;
        foreach ($bookings as $b) {
            $start = Carbon::parse($b->start_dt);
            $end = Carbon::parse($b->end_dt);
            $duration = max(1, (int)round(($end->timestamp - $start->timestamp) / 3600));
            $sisa = max(0, (int)$b->total_price - (int)$b->paid_amount);
            $isPaid = (int)$b->paid_amount >= (int)$b->total_price;

            $rows[] = [
                $no++,
                '#' . $b->formatted_invoice_no,
                $b->id,
                $b->created_at ? $b->created_at->format('d/m/Y H:i') : '-',
                $b->display_name,
                $b->display_phone ?: '-',
                $b->display_email ?: '-',
                $b->court->name ?? '-',
                ucfirst($b->court->sport ?? '-'),
                $start->format('d/m/Y'),
                $start->format('H:i'),
                $end->format('H:i'),
                $duration,
                (int)$b->total_price,
                (int)$b->paid_amount,
                $sisa,
                strtoupper($b->payment_method ?? 'LUNAS'),
                $statusLabels[$b->status] ?? ucfirst($b->status),
                $isPaid ? 'Lunas' : 'Belum Lunas',
                !empty($b->payment_proof) ? 'Ada (' . asset($b->payment_proof) . ')' : 'Tidak Ada',
                $b->approver->name ?? '-',
                $b->approved_at ? Carbon::parse($b->approved_at)->format('d/m/Y H:i') : '-',
            ];
        }

        $filterTag = '';
        if ($request->filled('status')) {
            $filterTag .= '_' . $request->status;
        }
        if ($request->filled('date')) {
            $filterTag .= '_' . str_replace('-', '', $request->date);
        }
        $filename = 'data_booking' . $filterTag . '_' . date('Ymd_His') . '.xlsx';

        return \App\Services\SimpleXlsxService::download($filename, $headers, $rows, 'Data Booking');
    }

    // Endpoint API untuk Lonceng Notifikasi Realtime & Audio Chime di Panel Admin
    public function pendingAlerts(Request $request) {
        Booking::cancelExpiredBookings();

        $pendingQuery = Booking::with(['court', 'approver'])
            ->where('status', 'pending')
            ->latest('updated_at');

        $pendingCount = (clone $pendingQuery)->count();
        $latestPending = (clone $pendingQuery)->first();

        $recent = $pendingQuery->limit(6)->get()->map(function ($b) {
            $nominal = $b->payment_method === 'dp' ? $b->dp_amount : $b->total_price;
            $start = Carbon::parse($b->start_dt);
            return [
                'id'             => $b->id,
                'invoice'        => '#' . $b->formatted_invoice_no,
                'customer_name'  => $b->display_name,
                'customer_phone' => $b->display_phone,
                'court_name'     => $b->court->name ?? 'Lapangan',
                'sport'          => ucfirst($b->court->sport ?? ''),
                'schedule_text'  => $start->translatedFormat('d M') . ' · ' . $start->format('H:i'),
                'total_price'    => 'Rp' . number_format($b->total_price, 0, ',', '.'),
                'nominal'        => 'Rp' . number_format($nominal, 0, ',', '.'),
                'payment_method' => strtoupper($b->payment_method ?? 'LUNAS'),
                'has_proof'      => !empty($b->payment_proof),
                'proof_url'      => $b->payment_proof_url,
                'time_ago'       => $b->updated_at ? $b->updated_at->diffForHumans() : 'Baru saja',
                'detail_url'     => route('admin.bookings.show', $b),
            ];
        });

        return response()->json([
            'status'            => 'ok',
            'pending_count'     => $pendingCount,
            'latest_id'         => $latestPending ? $latestPending->id : 0,
            'latest_updated_at' => $latestPending && $latestPending->updated_at ? $latestPending->updated_at->timestamp : 0,
            'has_new_proof'     => $latestPending ? !empty($latestPending->payment_proof) : false,
            'latest_booking'    => $recent->first() ?: null,
            'items'             => $recent,
            'server_time'       => Carbon::now()->format('H:i:s'),
        ]);
    }

    // Detail Booking, Tagihan & Manajemen Pelunasan
    public function show(Booking $booking) {
        $booking->load(['user', 'court', 'approver']);
        $equipmentList = [];
        $clientKey = null;
        return view('admin.bookings.show', compact('booking', 'equipmentList', 'clientKey'));
    }

    // Halaman Cetak Struk / Invoice (Thermal / A4)
    public function receipt(Booking $booking) {
        $booking->load(['user', 'court', 'approver']);
        return view('booking.receipt', compact('booking'));
    }

    // Halaman jadwal visual untuk admin
    public function schedule(Request $request) {
        Booking::cancelExpiredBookings();

        $courts = Court::where('is_active', true)->get();
        $date = $request->date ?? Carbon::now()->format('Y-m-d');
        
        $carbonDate = Carbon::parse($date);
        $prevDate = $carbonDate->copy()->subDay()->format('Y-m-d');
        $nextDate = $carbonDate->copy()->addDay()->format('Y-m-d');
        $todayDate = Carbon::now()->format('Y-m-d');
        $isToday = ($date === $todayDate);
        $currentHour = Carbon::now()->format('H:00');
        $now = Carbon::now();

        // Ambil semua booking untuk semua lapangan di tanggal ini
        $bookings = Booking::with(['user', 'court', 'approver'])
            ->where('status', '!=', 'cancelled')
            ->whereDate('start_dt', $date)
            ->get();

        // Siapkan grid jam 08:00 s/d 23:00 untuk setiap lapangan
        $hours = [];
        for ($h = 8; $h < 23; $h++) {
            $hours[] = sprintf('%02d:00', $h);
        }

        // Hitung statistik slot
        $totalSlots = count($courts) * count($hours);
        $confirmedSlots = 0;
        $pendingSlots = 0;

        foreach ($courts as $court) {
            foreach ($hours as $hour) {
                $slotStart = Carbon::parse($date . ' ' . $hour);
                $slotEnd   = Carbon::parse($date . ' ' . $hour)->addHour();

                $match = $bookings->first(function($b) use ($court, $slotStart, $slotEnd) {
                    if ($b->court_id != $court->id) return false;
                    $bStart = Carbon::parse($b->start_dt);
                    $bEnd   = Carbon::parse($b->end_dt);
                    return $bStart < $slotEnd && $bEnd > $slotStart;
                });

                if ($match) {
                    if ($match->status === 'confirmed') $confirmedSlots++;
                    elseif ($match->status === 'pending') $pendingSlots++;
                }
            }
        }

        $readySlots = max(0, $totalSlots - $confirmedSlots - $pendingSlots);
        $occupancyRate = $totalSlots > 0 ? round((($confirmedSlots + $pendingSlots) / $totalSlots) * 100) : 0;

        return view('admin.bookings.schedule', compact(
            'courts', 'date', 'carbonDate', 'prevDate', 'nextDate', 'todayDate', 'isToday', 'currentHour', 'now',
            'bookings', 'hours', 'totalSlots', 'confirmedSlots', 'pendingSlots', 'readySlots', 'occupancyRate'
        ));
    }

    public function confirm(Booking $booking) {
        $initialPaid = $booking->payment_method === 'dp' ? (int)$booking->dp_amount : (int)$booking->total_price;
        $newPaidAmount = max((int)$booking->paid_amount, $initialPaid);
        $isFullyPaid = $newPaidAmount >= (int)$booking->total_price;

        $booking->update([
            'status'            => 'confirmed',
            'payment_status'    => 'paid',
            'paid_amount'       => $newPaidAmount,
            'settlement_status' => $isFullyPaid ? 'paid' : 'unpaid',
            'approved_by'       => auth()->id(),
            'approved_at'       => Carbon::now(),
        ]);

        $adminName = auth()->user()?->name ?? 'Admin';
        return back()->with('success', "Booking #{$booking->id} berhasil di-APPROVE oleh {$adminName}.");
    }

    public function cancel(Booking $booking) {
        $booking->update(['status' => 'cancelled']);
        return back()->with('success', 'Booking #' . $booking->id . ' telah dibatalkan.');
    }

    // 1. Tambah Sewa Alat / Tagihan Tambahan
    public function addEquipment(Request $request, Booking $booking) {
        $request->validate([
            'equipment_id' => 'nullable',
            'custom_name'  => 'nullable|string|max:100',
            'price'        => 'required|numeric|min:0',
            'qty'          => 'required|integer|min:1',
            'duration'     => 'nullable|integer|min:1',
        ]);

        $name = 'Item Tambahan';
        if ($request->filled('equipment_id') && $request->equipment_id !== 'custom') {
            $eq = Equipment::find($request->equipment_id);
            if ($eq) $name = $eq->name;
        } elseif ($request->filled('custom_name')) {
            $name = $request->custom_name;
        }

        $isHourly = Equipment::isHourlyByName($name);
        $duration = $isHourly ? max(1, (int)($request->duration ?: 1)) : 1;
        $qty = max(1, (int)$request->qty);
        $unitPrice = (int)$request->price;
        $subtotal = $unitPrice * $qty * $duration;

        $isPaidNow = $request->filled('pay_cash') && $request->pay_cash == '1';

        $equipmentList = $booking->equipment_json ?? [];
        $equipmentList[] = [
            'equip_id'   => $request->equipment_id ?? null,
            'name'       => $name,
            'hours'      => $duration,
            'qty'        => $qty,
            'unit_price' => $unitPrice,
            'price'      => $subtotal,
            'is_paid'    => $isPaidNow,
            'added_at'   => Carbon::now()->format('d/m/Y H:i'),
            'by_admin'   => true,
        ];

        $newTotalPrice = $booking->total_price + $subtotal;
        $newPaidAmount = $booking->paid_amount;
        $settlementStatus = 'unpaid';

        // Jika dipilih opsi bayar tunai langsung di kasir
        if ($isPaidNow) {
            $newPaidAmount += $subtotal;
            $settlementStatus = ($newTotalPrice <= $newPaidAmount) ? 'paid' : 'unpaid';
            $msg = "Tagihan Alat ({$name}) sebesar Rp " . number_format($subtotal, 0, ',', '.') . " berhasil ditambahkan & LUNAS dibayar tunai.";
        } else {
            $msg = "Tagihan Alat ({$name}) sebesar Rp " . number_format($subtotal, 0, ',', '.') . " berhasil ditambahkan ke daftar Tagihan Sewa Alat.";
        }

        $booking->update([
            'equipment_json'    => $equipmentList,
            'total_price'       => $newTotalPrice,
            'paid_amount'       => $newPaidAmount,
            'settlement_status' => $settlementStatus,
            'settlement_token'  => null,   // Reset token agar perlu re-generate
        ]);

        $response = back()->with('success', $msg);
        if ($isPaidNow) {
            $response->with('print_receipt_id', $booking->id)
                     ->with('cash_received', $newTotalPrice)
                     ->with('cash_change', 0);
        }

        return $response;
    }

    // 2. Hapus Item Tambahan Dari Tagihan
    public function removeEquipment(Booking $booking, $index) {
        $equipmentList = $booking->equipment_json ?? [];
        $index = (int)$index;

        if (isset($equipmentList[$index])) {
            $removed = $equipmentList[$index];
            if (empty($removed['by_admin'])) {
                return back()->with('error', 'Hanya item tambahan yang ditambahkan oleh admin di tempat yang dapat dihapus.');
            }
            $deductPrice = (int)($removed['price'] ?? 0);
            $wasPaid = !empty($removed['is_paid']);
            unset($equipmentList[$index]);
            $equipmentList = array_values($equipmentList);

            $newPaidAmount = $wasPaid ? max(0, $booking->paid_amount - $deductPrice) : $booking->paid_amount;
            $newTotalPrice = max($newPaidAmount, $booking->total_price - $deductPrice);

            $booking->update([
                'equipment_json'    => $equipmentList,
                'total_price'       => $newTotalPrice,
                'paid_amount'       => $newPaidAmount,
                'settlement_token'  => null,
                'settlement_status' => ($newTotalPrice <= $newPaidAmount) ? 'paid' : 'unpaid',
            ]);

            return back()->with('success', "Item {$removed['name']} berhasil dihapus dari tagihan.");
        }

        return back()->with('error', 'Item tidak ditemukan.');
    }

    // 3. Generate Pembayaran Pelunasan via Midtrans (QRIS / VA)
    public function generateSettlement(Booking $booking) {
        $remaining = $booking->remaining_amount;
        if ($remaining <= 0) {
            return back()->with('error', 'Tagihan booking ini sudah lunas, tidak ada sisa pembayaran.');
        }

        try {
            Config::$serverKey = config('midtrans.server_key');
            Config::$isProduction = config('midtrans.is_production');
            Config::$isSanitized = config('midtrans.is_sanitized');
            Config::$is3ds = config('midtrans.is_3ds');

            $params = [
                'transaction_details' => [
                    'order_id' => 'SETTLE-' . $booking->id . '-' . time(),
                    'gross_amount' => (int)$remaining,
                ],
                'customer_details' => [
                    'first_name' => $booking->customer_name,
                    'email' => $booking->customer_email ?: 'pelanggan@linosport.com',
                    'phone' => $booking->customer_phone,
                ],
                'item_details' => [
                    [
                        'id' => 'PELUNASAN-' . $booking->id,
                        'price' => (int)$remaining,
                        'quantity' => 1,
                        'name' => substr('Pelunasan #' . $booking->id . ' - ' . $booking->court->name, 0, 50),
                    ]
                ],
                'expiry' => [
                    'start_time' => date("Y-m-d H:i:s O"),
                    'unit' => 'minute',
                    'duration' => 60
                ]
            ];

            $token = Snap::getSnapToken($params);
            $booking->update([
                'settlement_token' => $token,
                'settlement_status' => 'pending'
            ]);

            return back()->with('success', 'Kode pembayaran QRIS / Virtual Account pelunasan sebesar Rp ' . number_format($remaining, 0, ',', '.') . ' berhasil digenerate!');
        } catch (\Exception $e) {
            \Log::warning('Midtrans settlement token error: ' . $e->getMessage());
            return back()->with('warning', 'Gagal memanggil Midtrans API (' . $e->getMessage() . '). Jika kunci Midtrans di .env masih demo, Anda tetap bisa menggunakan opsi "Pelunasan Tunai (Kasir)" atau simulasi.');
        }
    }

    // 4. Pelunasan Sisa Tagihan Lapangan (DP -> Lunas)
    public function cashSettlement(Request $request, Booking $booking) {
        $amountToPay = $booking->remaining_amount;

        if ($amountToPay <= 0) {
            return back()->with('info', 'Tagihan booking lapangan ini sudah lunas.');
        }

        $cashReceived = (int)$request->input('cash_received', $amountToPay);
        if ($cashReceived < $amountToPay) {
            return back()->with('error', 'Nominal pembayaran kurang dari sisa tagihan (Rp ' . number_format($amountToPay, 0, ',', '.') . ').');
        }
        $change = max(0, $cashReceived - $amountToPay);

        $updateData = [
            'paid_amount'       => $booking->total_price,
            'settlement_status' => 'paid',
            'payment_status'    => 'paid',
            'status'            => 'confirmed',
        ];

        if (!$booking->approved_by) {
            $updateData['approved_by'] = auth()->id();
            $updateData['approved_at'] = Carbon::now();
        }

        $booking->update($updateData);

        $changeText = $cashReceived > $amountToPay 
            ? " | Diterima: Rp " . number_format($cashReceived, 0, ',', '.') . " | KEMBALIAN: Rp " . number_format($change, 0, ',', '.')
            : " (Uang Pas)";

        return back()
            ->with('success', "Pelunasan sisa tagihan lapangan sebesar Rp " . number_format($amountToPay, 0, ',', '.') . " berhasil diverifikasi LUNAS{$changeText}.")
            ->with('print_receipt_id', $booking->id)
            ->with('cash_received', $booking->total_price + $change)
            ->with('cash_change', $change);
    }
}
