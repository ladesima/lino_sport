<?php

namespace App\Services;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * Format dan bersihkan nomor telepon menjadi format internasional WhatsApp (62xxx).
     */
    public static function sanitizePhoneNumber(?string $phone): string
    {
        if (empty($phone)) {
            return '';
        }

        // Hapus karakter non-digit selain '+'
        $clean = preg_replace('/[^0-9]/', '', $phone);

        // Jika diawali 08, ubah menjadi 628
        if (str_starts_with($clean, '0')) {
            $clean = '62' . substr($clean, 1);
        }

        return $clean;
    }

    /**
     * Kirim pesan WhatsApp melalui Gateway API (Fonnte) dengan fallback logging.
     *
     * @param string $target Nomor tujuan WhatsApp (contoh: 6282343797911)
     * @param string $message Teks pesan WhatsApp (support format WhatsApp *bold*, _italic_, dll)
     * @param string|null $fileUrl URL gambar/file bukti (opsional)
     * @return array
     */
    public static function send(string $target, string $message, ?string $fileUrl = null): array
    {
        $sanitizedTarget = self::sanitizePhoneNumber($target);
        if (empty($sanitizedTarget)) {
            return [
                'success' => false,
                'message' => 'Nomor tujuan WhatsApp tidak valid.',
            ];
        }

        $token = config('services.whatsapp.token');
        $provider = config('services.whatsapp.provider', 'fonnte');

        // Jika token belum diisi di .env, catat ke log sistem agar tetap bisa ditinjau
        if (empty($token)) {
            Log::info("[WhatsAppService] Token belum dikonfigurasi. Simulasi kirim pesan ke {$sanitizedTarget}:\n{$message}" . ($fileUrl ? "\nLampiran: {$fileUrl}" : ""));
            return [
                'success' => true,
                'simulated' => true,
                'message' => 'Token WA belum diisi di .env, pesan dicatat di log sistem.',
            ];
        }

        try {
            if ($provider === 'fonnte') {
                $payload = [
                    'target'      => $sanitizedTarget,
                    'message'     => $message,
                    'countryCode' => '62',
                ];

                if (!empty($fileUrl)) {
                    $payload['url'] = $fileUrl;
                }

                $response = Http::timeout(12)
                    ->withHeaders([
                        'Authorization' => $token,
                    ])
                    ->asForm()
                    ->post('https://api.fonnte.com/send', $payload);

                $resBody = $response->json();
                $isOk = $response->successful() && ($resBody['status'] ?? false);

                if (!$isOk) {
                    Log::warning("[WhatsAppService] Gagal kirim WA ke {$sanitizedTarget}: " . $response->body());
                } else {
                    Log::info("[WhatsAppService] Berhasil kirim WA ke {$sanitizedTarget} via Fonnte.");
                }

                return [
                    'success' => $isOk,
                    'response' => $resBody,
                ];
            }

            return [
                'success' => false,
                'message' => "Provider {$provider} belum didukung.",
            ];
        } catch (\Throwable $e) {
            Log::error("[WhatsAppService] Exception saat kirim WA ke {$sanitizedTarget}: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Kirim notifikasi bukti transfer QRIS baru ke WhatsApp Admin (+62 823-4379-7911).
     */
    public static function notifyAdminNewPaymentProof(Booking $booking): array
    {
        $adminNumber = config('services.whatsapp.admin_number', '6282343797911');
        if (empty($adminNumber)) {
            $adminNumber = '6282343797911';
        }

        $booking->loadMissing(['court', 'user']);

        $courtName = $booking->court->name ?? 'Lapangan Lino Sport';
        $sport = ucfirst($booking->court->sport ?? 'Olahraga');
        if (strtolower($sport) === 'bulutangkis') {
            $sport = 'Badminton';
        }

        $startDt = Carbon::parse($booking->start_dt);
        $endDt = Carbon::parse($booking->end_dt);
        $dateFormatted = $startDt->translatedFormat('d M Y');
        $timeFormatted = $startDt->format('H:i') . ' - ' . $endDt->format('H:i') . ' WITA';

        $totalFormatted = 'Rp' . number_format($booking->total_price, 0, ',', '.');
        $nominalToPay = $booking->payment_method === 'dp' ? (int) $booking->dp_amount : (int) $booking->total_price;
        $nominalFormatted = 'Rp' . number_format($nominalToPay, 0, ',', '.');
        $schemeLabel = $booking->payment_method === 'dp' ? 'DP 50%' : 'LUNAS 100%';

        $proofUrl = $booking->payment_proof_url ?: url($booking->payment_proof);
        $adminApprovalUrl = url('/admin/bookings/' . $booking->id);

        $message = "🔔 *NOTIFIKASI BUKTI BAYAR QRIS BARU!*\n"
            . "*LINO SPORT CENTER*\n"
            . "────────────────────────────\n"
            . "📋 *No. Invoice* : {$booking->formatted_invoice_no}\n"
            . "👤 *Pemesan*     : {$booking->display_name}\n"
            . "📞 *WhatsApp*    : {$booking->display_phone}\n"
            . "🏟️ *Lapangan*    : {$courtName} ({$sport})\n"
            . "📅 *Jadwal*      : {$dateFormatted}\n"
            . "⏰ *Waktu*       : {$timeFormatted}\n"
            . "💰 *Total Sewa*  : {$totalFormatted}\n"
            . "💵 *Skema Bayar* : {$schemeLabel} ({$nominalFormatted})\n"
            . "────────────────────────────\n"
            . "📸 *Lihat Bukti Transfer QRIS*:\n"
            . "{$proofUrl}\n\n"
            . "👉 *Validasi & Approve Sekarang di Admin Panel*:\n"
            . "{$adminApprovalUrl}\n"
            . "────────────────────────────\n"
            . "_Pesan otomatis dari Sistem Pemesanan Lino Sport Center_";

        return self::send($adminNumber, $message, $proofUrl);
    }
}
