<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Court;
use App\Models\Equipment;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Midtrans\Config;
use Midtrans\Snap;

class CashierBookingController extends Controller
{
    /**
     * Daftar Seluruh Booking untuk Kasir
     */
    public function index(Request $request)
    {
        Booking::cancelExpiredBookings();

        $query = Booking::with(['user', 'court'])->latest();
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
        return view('cashier.bookings.index', compact('bookings', 'courts'));
    }

    /**
     * Detail Booking, Rincian Tagihan & Pelunasan Kasir
     */
    public function show(Booking $booking)
    {
        $booking->load(['user', 'court']);
        $equipmentList = Equipment::all();
        $clientKey = config('midtrans.client_key');
        return view('cashier.bookings.show', compact('booking', 'equipmentList', 'clientKey'));
    }

    /**
     * Jadwal Visual Ketersediaan Jam Lapangan Realtime untuk Kasir
     */
    public function schedule(Request $request)
    {
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
        $bookings = Booking::with(['user', 'court'])
            ->where('status', '!=', 'cancelled')
            ->whereDate('start_dt', $date)
            ->get();

        // Siapkan grid jam 07:00 s/d 23:00 untuk setiap lapangan
        $hours = [];
        for ($h = 7; $h < 23; $h++) {
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

        return view('cashier.bookings.schedule', compact(
            'courts', 'date', 'carbonDate', 'prevDate', 'nextDate', 'todayDate', 'isToday', 'currentHour', 'now',
            'bookings', 'hours', 'totalSlots', 'confirmedSlots', 'pendingSlots', 'readySlots', 'occupancyRate'
        ));
    }

    /**
     * Konfirmasi Booking Manual oleh Kasir
     */
    public function confirm(Booking $booking)
    {
        $booking->update(['status' => 'confirmed']);
        return back()->with('success', 'Booking #' . $booking->id . ' berhasil dikonfirmasi.');
    }

    /**
     * Batalkan Booking oleh Kasir
     */
    public function cancel(Booking $booking)
    {
        $booking->update(['status' => 'cancelled']);
        return back()->with('success', 'Booking #' . $booking->id . ' telah dibatalkan.');
    }

    /**
     * Kasir Tambah Sewa Alat / Biaya Tambahan di Tempat
     */
    public function addEquipment(Request $request, Booking $booking)
    {
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
            'settlement_token'  => null,
        ]);

        $response = back()->with('success', $msg);
        if ($isPaidNow) {
            $response->with('print_receipt_id', $booking->id)
                     ->with('cash_received', $newTotalPrice)
                     ->with('cash_change', 0);
        }

        return $response;
    }

    /**
     * Hapus Item Tambahan
     */
    public function removeEquipment(Booking $booking, $index)
    {
        $equipmentList = $booking->equipment_json ?? [];
        $index = (int)$index;

        if (isset($equipmentList[$index])) {
            $removed = $equipmentList[$index];
            if (empty($removed['by_admin'])) {
                return back()->with('error', 'Hanya item tambahan yang ditambahkan oleh kasir/admin di tempat yang dapat dihapus.');
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

    /**
     * Generate Pembayaran QRIS Pelunasan Kasir
     */
    public function generateSettlement(Booking $booking)
    {
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

            return back()->with('success', 'Kode pembayaran QRIS pelunasan sebesar Rp ' . number_format($remaining, 0, ',', '.') . ' berhasil digenerate!');
        } catch (\Exception $e) {
            \Log::warning('Midtrans settlement token error: ' . $e->getMessage());
            return back()->with('warning', 'Gagal memanggil Midtrans API (' . $e->getMessage() . '). Anda dapat menggunakan opsi "Terima Pembayaran Tunai (Kasir)".');
        }
    }

    /**
     * Kasir Terima Pelunasan Tunai (Cash) - Bisa pilih Lapangan saja, Alat saja, atau Semua
     */
    public function cashSettlement(Request $request, Booking $booking)
    {
        $target = $request->input('settle_target', 'all'); // 'court', 'equipment', 'all'
        if ($target === 'court') {
            $amountToPay = $booking->court_remaining;
            $labelTarget = 'Tagihan Lapangan';
        } elseif ($target === 'equipment') {
            $amountToPay = $booking->equipment_remaining;
            $labelTarget = 'Tagihan Sewa Alat';
        } else {
            $amountToPay = $booking->remaining_amount;
            $labelTarget = 'Seluruh Tagihan (Lapangan & Alat)';
        }

        if ($amountToPay <= 0) {
            return back()->with('info', "{$labelTarget} sudah lunas.");
        }

        $cashReceived = (int)$request->input('cash_received', $amountToPay);
        if ($cashReceived < $amountToPay) {
            return back()->with('error', 'Uang tunai yang diterima kurang dari tagihan (Rp ' . number_format($amountToPay, 0, ',', '.') . ').');
        }
        $change = max(0, $cashReceived - $amountToPay);

        $equipmentList = $booking->equipment_json ?? [];
        if ($target === 'equipment' || $target === 'all') {
            foreach ($equipmentList as &$eq) {
                $eq['is_paid'] = true;
            }
            unset($eq);
        }

        $newPaidAmount = min($booking->total_price, $booking->paid_amount + $amountToPay);
        $isAllPaid = $newPaidAmount >= $booking->total_price;

        $booking->update([
            'equipment_json'    => $equipmentList,
            'paid_amount'       => $newPaidAmount,
            'settlement_status' => $isAllPaid ? 'paid' : 'unpaid',
            'payment_status'    => $isAllPaid ? 'paid' : $booking->payment_status,
            'status'            => 'confirmed',
        ]);

        $changeText = $cashReceived > $amountToPay 
            ? " | Uang Diterima: Rp " . number_format($cashReceived, 0, ',', '.') . " | KEMBALIAN: Rp " . number_format($change, 0, ',', '.')
            : " (Uang Pas)";

        return back()
            ->with('success', "Pelunasan {$labelTarget} sebesar Rp " . number_format($amountToPay, 0, ',', '.') . " berhasil diterima secara TUNAI{$changeText}.")
            ->with('print_receipt_id', $booking->id)
            ->with('cash_received', $booking->total_price + $change)
            ->with('cash_change', $change);
    }

    /**
     * Cetak Struk Bukti Bayar / Tagihan
     */
    public function receipt(Booking $booking)
    {
        $booking->load(['user', 'court']);
        return view('booking.receipt', compact('booking'));
    }
}
