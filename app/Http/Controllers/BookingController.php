<?php
namespace App\Http\Controllers;
use App\Models\Booking;
use App\Models\Court;
use App\Models\Rate;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BookingController extends Controller {
    // Formulir booking terbuka untuk publik (tanpa wajib login)
    public function create(Request $request) {
        $courts = Court::where('is_active', true)->get();
        $rates = Rate::all();
        $selectedCourtId = $request->court_id;
        $selectedDate = $request->date ?? date('Y-m-d');

        return view('booking.create', compact('courts', 'rates', 'selectedCourtId', 'selectedDate'));
    }

    // Endpoint API visualisasi ketersediaan jam
    public function courtSchedule(Request $request) {
        $courtId = $request->court_id;
        $date = $request->date ?? date('Y-m-d');

        if (!$courtId) {
            return response()->json(['error' => 'Court ID required'], 400);
        }

        $court = Court::findOrFail($courtId);

        $bookings = Booking::where('court_id', $courtId)
            ->where('status', '!=', 'cancelled')
            ->whereDate('start_dt', $date)
            ->get();

        $slots = [];
        $now = Carbon::now();

        for ($hour = 8; $hour < 23; $hour++) {
            $slotStart = Carbon::parse($date . ' ' . sprintf('%02d:00:00', $hour));
            $slotEnd   = Carbon::parse($date . ' ' . sprintf('%02d:00:00', $hour + 1));
            $slotPrice = Rate::getPrice($court->sport, $slotStart);
            $sessionType = ($hour >= 16) ? 'Pukul 16.00 - 23.00' : 'Pukul 08.00 - 16.00';
            
            // 1. Jam sudah lewat dari waktu realtime sekarang
            $isPast = $slotEnd <= $now || $slotStart <= $now;

            // 2. Jam belum lewat tapi kurang dari 1 jam sebelum waktu mulai
            $isTooSoon = !$isPast && ($slotStart < $now->copy()->addHours(1));

            $bookingMatch = $bookings->first(function ($b) use ($slotStart, $slotEnd) {
                $bStart = Carbon::parse($b->start_dt);
                $bEnd   = Carbon::parse($b->end_dt);
                return $bStart < $slotEnd && $bEnd > $slotStart;
            });

            $status = 'available';
            $badge = 'Ready';
            $bookingInfo = null;

            if ($bookingMatch) {
                $status = ($bookingMatch->status === 'confirmed') ? 'booked' : 'pending';
                $badge  = ($bookingMatch->status === 'confirmed') ? 'Approved' : 'Menunggu Approve';
                $bookingInfo = [
                    'id' => $bookingMatch->id,
                    'user_name' => $bookingMatch->display_name,
                    'status' => $bookingMatch->status,
                ];
            } elseif ($isPast) {
                $status = 'disabled';
                $badge  = 'Lewat';
            } elseif ($isTooSoon) {
                $status = 'disabled';
                $badge  = '< 1 Jam';
            }

            $slots[] = [
                'hour' => sprintf('%02d:00', $hour),
                'next_hour' => sprintf('%02d:00', $hour + 1),
                'label' => sprintf('%02d:00 - %02d:00', $hour, $hour + 1),
                'price' => $slotPrice,
                'session' => $sessionType,
                'status' => $status,
                'badge' => $badge,
                'booking' => $bookingInfo,
            ];
        }

        return response()->json([
            'court' => [
                'id' => $court->id,
                'name' => $court->name,
                'sport' => $court->sport,
            ],
            'date' => $date,
            'server_time' => $now->format('H:i:s'),
            'slots' => $slots,
        ]);
    }

    public function store(Request $request) {
        $request->validate([
            'court_id' => 'required|exists:courts,id',
            'date' => 'required|date_format:Y-m-d',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'payment_method' => 'required|in:dp,lunas',

            // Validasi Biodata Pemesan (Hanya Nama & Nomor WhatsApp)
            'customer_name'  => 'required|string|max:100',
            'customer_phone' => 'required|string|min:9|max:20',
            'customer_email' => 'nullable|email|max:100',
            'customer_nik'   => 'nullable|string|max:30',
        ], [
            'court_id.required' => 'Silakan pilih lapangan terlebih dahulu.',
            'court_id.exists'   => 'Lapangan tidak ditemukan.',
            'date.required'     => 'Tanggal bermain wajib diisi.',
            'start_time.required' => 'Jam mulai bermain wajib dipilih.',
            'end_time.required'   => 'Jam selesai bermain wajib dipilih.',
            'payment_method.required' => 'Pilih salah satu skema pembayaran QRIS (DP 50% atau Lunas 100%).',
            'customer_name.required'  => 'Nama pemesan wajib diisi.',
            'customer_name.max'       => 'Nama pemesan maksimal 100 karakter.',
            'customer_phone.required' => 'Nomor WhatsApp / HP aktif wajib diisi.',
            'customer_phone.min'      => 'Nomor WhatsApp / HP minimal 9 digit angka (contoh: 08123456789).',
            'customer_phone.max'      => 'Nomor WhatsApp / HP maksimal 20 digit.',
        ]);

        $start = Carbon::parse($request->date . ' ' . $request->start_time);
        $end   = Carbon::parse($request->date . ' ' . $request->end_time);
        $now   = Carbon::now();

        // 1. Validasi jam sudah lewat atau kurang dari 1 jam sebelum waktu mulai
        if ($start <= $now) {
            return back()->withErrors(['start_time' => 'Jam bermain yang dipilih sudah lewat dari waktu saat ini.'])->withInput();
        }

        if ($start < $now->copy()->addHours(1)) {
            return back()->withErrors(['start_time' => 'Booking harus dibuat minimal 1 jam sebelum waktu bermain.'])->withInput();
        }

        // 2. Validasi urutan waktu
        if ($end <= $start) {
            return back()->withErrors(['end_time' => 'Waktu selesai harus setelah waktu mulai.'])->withInput();
        }

        // 3. Validasi jam operasional (08:00 - 23:00)
        if ($start->format('H:i') < '08:00' || $end->format('H:i') > '23:00') {
            return back()->withErrors(['start_time' => 'Jam harus berada dalam rentang operasional 08:00 - 23:00.'])->withInput();
        }

        // Transaksi Database dengan Pessimistic Locking
        try {
            $booking = DB::transaction(function () use ($request, $start, $end) {
                $court = Court::where('id', $request->court_id)->lockForUpdate()->first();

                if (!$court->isSlotAvailable($start->toDateTime(), $end->toDateTime())) {
                    throw new \Exception('Slot pada rentang jam ini tidak tersedia atau sudah dibooking orang lain! Silakan pilih jam atau lapangan lain.');
                }

                // Hitung harga lapangan per jam berdasarkan sesi waktu (07.00-17.00 vs 17.00-22.00)
                $sport = $court->sport;
                $total = Rate::calculateCourtPrice($sport, $start->toDateTime(), $end->toDateTime());
                $dp = $request->payment_method === 'dp' ? (int)round($total * 0.5) : 0;

                return Booking::create([
                    'user_id'        => auth()->id(),
                    'court_id'       => $court->id,
                    'customer_name'  => trim($request->customer_name),
                    'customer_phone' => trim($request->customer_phone),
                    'customer_email' => trim($request->customer_email ?? ''),
                    'customer_nik'   => null,
                    'start_dt'       => $start,
                    'end_dt'         => $end,
                    'equipment_json' => [],
                    'total_price'    => (int)round($total),
                    'dp_amount'      => $dp,
                    'paid_amount'    => 0,
                    'payment_status' => 'unpaid',
                    'status'         => 'pending', // Menunggu Approve dari Admin
                    'payment_method' => $request->payment_method,
                ]);
            });
        } catch (\Exception $e) {
            return back()->withErrors(['court_id' => $e->getMessage()])->withInput();
        }

        return redirect()
            ->route('booking.confirm', $booking->id)
            ->with('success', 'Pesanan berhasil dibuat! Silakan scan QRIS Statis di bawah lalu upload bukti pembayaran QRIS untuk divalidasi dan di-approve oleh Admin.');
    }

    // Halaman konfirmasi QRIS Statis & Upload Bukti Pembayaran
    public function confirm(Booking $booking) {
        $booking->load(['court', 'user', 'approver']);
        return view('booking.confirm', compact('booking'));
    }

    // Upload Bukti Pembayaran QRIS langsung di halaman Konfirmasi Booking
    public function uploadProof(Request $request, Booking $booking) {
        if (!$request->hasFile('payment_proof')) {
            return back()->withErrors([
                'payment_proof' => 'Silakan pilih foto / screenshot bukti pembayaran QRIS terlebih dahulu.',
            ]);
        }

        $file = $request->file('payment_proof');
        if (!$file || !$file->isValid()) {
            return back()->withErrors([
                'payment_proof' => 'File bukti pembayaran gagal diupload. Silakan coba lagi.',
            ]);
        }

        $filePath = $file->getPathname();
        $fileSize = @filesize($filePath);
        if ($fileSize !== false && $fileSize > 5 * 1024 * 1024) {
            return back()->withErrors([
                'payment_proof' => 'Ukuran gambar bukti pembayaran maksimal 5 MB.',
            ]);
        }

        $imageInfo = @getimagesize($filePath);
        $allowedTypes = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP];
        if ($imageInfo === false || !in_array($imageInfo[2] ?? 0, $allowedTypes, true)) {
            return back()->withErrors([
                'payment_proof' => 'File yang diupload harus berupa gambar valid dengan format JPG, JPEG, PNG, atau WEBP.',
            ]);
        }

        $uploadDir = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, public_path('uploads/payment_proofs'));
        if (!is_dir($uploadDir)) {
            $parentDir = dirname($uploadDir);
            if (!is_dir($parentDir)) {
                @mkdir($parentDir, 0777);
            }
            @mkdir($uploadDir, 0777);
        }

        // Hapus file bukti lama jika sebelumnya sudah pernah upload
        if ($booking->payment_proof) {
            $oldFile = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, public_path($booking->payment_proof));
            if (is_file($oldFile)) {
                @unlink($oldFile);
            }
        }

        $extMap = [
            IMAGETYPE_JPEG => 'jpg',
            IMAGETYPE_PNG  => 'png',
            IMAGETYPE_WEBP => 'webp',
        ];
        $ext = $extMap[$imageInfo[2]] ?? 'jpg';
        $filename = 'qris_booking_' . $booking->id . '_' . time() . '_' . \Illuminate\Support\Str::random(6) . '.' . $ext;
        $targetPath = $uploadDir . DIRECTORY_SEPARATOR . $filename;

        if (!@move_uploaded_file($filePath, $targetPath)) {
            if (!@rename($filePath, $targetPath)) {
                @copy($filePath, $targetPath);
                @unlink($filePath);
            }
        }

        $booking->update([
            'payment_proof'             => 'uploads/payment_proofs/' . $filename,
            'payment_proof_uploaded_at' => Carbon::now(),
        ]);

        // Otomatis kirim notifikasi WhatsApp ke Admin (+62 823-4379-7911)
        try {
            WhatsAppService::notifyAdminNewPaymentProof($booking);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('WhatsApp admin notification failed: ' . $e->getMessage());
        }

        return redirect()
            ->route('booking.confirm', $booking->id)
            ->with('success', 'Bukti pembayaran QRIS berhasil diupload! Notifikasi telah dikirimkan ke WhatsApp Admin dan pesanan Anda sedang menunggu approval.');
    }

    // Halaman Cetak Struk / Bukti Tagihan & Pelunasan
    public function receipt(Booking $booking) {
        $booking->load(['court', 'user', 'approver']);
        return view('booking.receipt', compact('booking'));
    }

    // Cek booking berdasarkan No. WhatsApp (harus persis) atau ID Booking
    public function check(Request $request) {
        $bookings = collect();
        $searched = false;

        if ($request->filled('keyword')) {
            $searched = true;
            $kw = trim($request->keyword);
            $digits = preg_replace('/[^0-9]/', '', $kw);
            $idOnly = (int)preg_replace('/[^0-9]/', '', preg_replace('/^INV-?/i', '', $kw));

            // Nomor WA dinormalisasi (08xx / 628xx / +628xx) dan dicocokkan persis, bukan LIKE,
            // agar data pemesan lain tidak bisa ditebak/dicari secara massal.
            $local = preg_replace('/^62/', '0', $digits);
            $intl  = preg_replace('/^0/', '62', $digits);

            $bookings = Booking::with(['court', 'approver'])
                ->where(function($q) use ($digits, $local, $intl, $idOnly, $kw) {
                    if (strlen($digits) >= 9) {
                        $q->whereIn('customer_phone', array_unique([$kw, $digits, $local, $intl, '+' . $intl]));
                    }
                    if ($idOnly > 0 && strlen($digits) < 9) {
                        $q->orWhere('id', $idOnly);
                    }
                })
                ->when(strlen($digits) === 0, fn($q) => $q->whereRaw('1 = 0'))
                ->latest()
                ->limit(20)
                ->get();
        }

        return view('booking.check', compact('bookings', 'searched'));
    }

    public function myBookings() {
        $bookings = auth()->user()->bookings()->with('court')->latest()->get();
        return view('booking.index', compact('bookings'));
    }
}
