<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Notification;

class PaymentCallbackController extends Controller
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
    }

    public function handle(Request $request)
    {
        try {
            $notif = new Notification();

            $transaction = $notif->transaction_status;
            $type = $notif->payment_type;
            $orderId = $notif->order_id;
            $fraud = $notif->fraud_status;

            // Ekstrak ID booking dari format: BOOKING-{id}-{timestamp}
            $parts = explode('-', $orderId);
            $bookingId = $parts[1] ?? null;

            if (!$bookingId) {
                return response()->json(['message' => 'Invalid order ID'], 400);
            }

            $booking = Booking::find($bookingId);
            if (!$booking) {
                return response()->json(['message' => 'Booking not found'], 404);
            }

            $isSuccess = ($transaction == 'capture' && ($type != 'credit_card' || $fraud != 'challenge')) || $transaction == 'settlement';
            $isSettlementOrder = str_starts_with($orderId, 'SETTLE-');
            $alreadyPaid = (int)$booking->paid_amount > 0;

            if ($isSuccess) {
                if ($isSettlementOrder) {
                    // Pembayaran Pelunasan Sisa Tagihan (tandai semua item alat lunas)
                    $equipment = $booking->equipment_json ?? [];
                    foreach ($equipment as &$eq) { $eq['is_paid'] = true; }
                    unset($eq);
                    $booking->update([
                        'equipment_json' => $equipment,
                        'paid_amount' => $booking->total_price,
                        'settlement_status' => 'paid',
                        'payment_status' => 'paid',
                    ]);
                } elseif (!$alreadyPaid) {
                    // Pembayaran Awal (DP / Lunas) — idempoten, tidak menimpa pembayaran yang sudah masuk
                    $paid = ($booking->payment_method === 'dp') ? $booking->dp_amount : $booking->total_price;
                    $booking->update([
                        'paid_amount' => $paid,
                        'payment_status' => 'paid',
                        'status' => 'confirmed' // Otomatis lock dan konfirmasi
                    ]);
                }
            } elseif ($isSettlementOrder) {
                // Status gagal/expired pada QR pelunasan TIDAK boleh membatalkan booking yang sudah terkonfirmasi
                if (in_array($transaction, ['deny', 'expire', 'cancel'])) {
                    $booking->update(['settlement_status' => 'unpaid', 'settlement_token' => null]);
                } elseif ($transaction == 'pending') {
                    $booking->update(['settlement_status' => 'pending']);
                }
            } elseif ($alreadyPaid) {
                // Booking sudah dibayar (mis. lewat kasir) — abaikan notifikasi gagal dari token lama
            } elseif ($transaction == 'pending') {
                $booking->update(['payment_status' => 'pending']);
            } elseif ($transaction == 'deny') {
                $booking->update(['payment_status' => 'failed']);
            } elseif ($transaction == 'expire') {
                $booking->update([
                    'payment_status' => 'expired',
                    'status' => 'cancelled' // Lepas slot jika kedaluwarsa
                ]);
            } elseif ($transaction == 'cancel') {
                $booking->update([
                    'payment_status' => 'cancelled',
                    'status' => 'cancelled'
                ]);
            }

            return response()->json(['message' => 'Notification handled successfully']);
        } catch (\Exception $e) {
            \Log::warning('Midtrans callback error: ' . $e->getMessage());
            return response()->json(['error' => 'Notification could not be processed'], 500);
        }
    }
}
