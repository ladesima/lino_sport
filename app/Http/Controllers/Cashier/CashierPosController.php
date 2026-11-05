<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Equipment;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Midtrans\Config;
use Midtrans\Snap;

class CashierPosController extends Controller
{
    /**
     * Halaman Kasir POS Sewa Alat
     */
    public function index(Request $request)
    {
        $bookings = Booking::with(['court', 'user'])
            ->where('status', '!=', 'cancelled')
            ->latest('start_dt')
            ->get();

        $equipmentList = Equipment::all();
        $selectedBookingId = $request->booking_id;
        $selectedBooking = null;

        if ($selectedBookingId) {
            $selectedBooking = Booking::with(['court', 'user'])->find($selectedBookingId);
        }

        $clientKey = config('midtrans.client_key');

        return view('cashier.pos.index', compact('bookings', 'equipmentList', 'selectedBooking', 'selectedBookingId', 'clientKey'));
    }

    /**
     * Checkout Kasir POS (Bisa Bayar Sewa Alat, Sekaligus Lunasi Sisa Lapangan, atau Hanya Lunasi Lapangan)
     */
    public function checkout(Request $request)
    {
        $request->validate([
            'booking_id'          => 'required|exists:bookings,id',
            'payment_method'      => 'required|in:cash,qris',
            'include_unpaid_bill' => 'nullable|boolean',
            'items'               => 'nullable|array',
            'items.*.name'        => 'required_with:items|string',
            'items.*.price'       => 'required_with:items|numeric|min:0',
            'items.*.qty'         => 'required_with:items|integer|min:1',
            'items.*.hours'       => 'nullable|integer|min:1',
        ], [
            'booking_id.required' => 'Pilih pemesan / booking lapangan terlebih dahulu.',
        ]);

        $booking = Booking::with('court')->findOrFail($request->booking_id);
        $includeUnpaidBill = $request->boolean('include_unpaid_bill');
        $existingRemaining = $includeUnpaidBill ? $booking->remaining_amount : 0;

        $currentEquipment = $booking->equipment_json ?? [];
        $subtotalEquipment = 0;
        $newItemsSummary = [];

        if (!empty($request->items) && is_array($request->items)) {
            $catalog = Equipment::all()->keyBy('id');
            foreach ($request->items as $item) {
                // Harga katalog selalu diambil dari database (tidak dipercaya dari browser)
                $equipId = $item['equip_id'] ?? null;
                if ($equipId && $equipId !== 'custom' && isset($catalog[$equipId])) {
                    $item['name']  = $catalog[$equipId]->name;
                    $item['price'] = (int)$catalog[$equipId]->price;
                }
                $isHourly = Equipment::isHourlyByName($item['name'] ?? '');
                $hours = $isHourly ? max(1, (int)($item['hours'] ?? 1)) : 1;
                $qty = max(1, (int)($item['qty'] ?? 1));
                $unitPrice = (int)$item['price'];
                $itemTotal = $unitPrice * $qty * $hours;

                $subtotalEquipment += $itemTotal;

                $itemData = [
                    'equip_id'   => $item['equip_id'] ?? null,
                    'name'       => $item['name'],
                    'hours'      => $hours,
                    'qty'        => $qty,
                    'unit_price' => $unitPrice,
                    'price'      => $itemTotal,
                    'is_paid'    => true,
                    'added_at'   => Carbon::now()->format('d/m/Y H:i'),
                    'by_admin'   => true,
                    'pos_sale'   => true,
                ];

                $currentEquipment[] = $itemData;
                $newItemsSummary[] = "{$item['name']} (x{$qty})";
            }
        }

        $totalPayNow = $subtotalEquipment + $existingRemaining;

        if ($totalPayNow <= 0) {
            return back()->with('warning', 'Pilih minimal 1 peralatan atau centang pelunasan sisa tagihan untuk diproses.');
        }

        if ($includeUnpaidBill) {
            foreach ($currentEquipment as &$eq) {
                $eq['is_paid'] = true;
            }
            unset($eq);
        }

        $newTotalPrice = $booking->total_price + $subtotalEquipment;
        $newPaidAmount = min($newTotalPrice, $booking->paid_amount + $totalPayNow);
        $isFullyPaid = $newPaidAmount >= $newTotalPrice;

        $cashReceived = $request->payment_method === 'cash'
            ? (int)$request->input('cash_received', $totalPayNow)
            : $totalPayNow;
        if ($request->payment_method === 'cash' && $cashReceived < $totalPayNow) {
            return back()->withInput()->with('error', 'Uang tunai yang diterima kurang dari total pembayaran (Rp ' . number_format($totalPayNow, 0, ',', '.') . ').');
        }
        $change = max(0, $cashReceived - $totalPayNow);

        $booking->update([
            'equipment_json'    => $currentEquipment,
            'total_price'       => $newTotalPrice,
            'paid_amount'       => $newPaidAmount,
            'status'            => 'confirmed',
            'payment_status'    => $isFullyPaid ? 'paid' : $booking->payment_status,
            'settlement_status' => $isFullyPaid ? 'paid' : 'unpaid',
            'settlement_token'  => null,
        ]);

        $parts = [];
        if ($existingRemaining > 0) {
            $parts[] = "Pelunasan Tagihan Rp " . number_format($existingRemaining, 0, ',', '.');
        }
        if ($subtotalEquipment > 0) {
            $parts[] = "Sewa Alat (" . implode(', ', $newItemsSummary) . ") Rp " . number_format($subtotalEquipment, 0, ',', '.');
        }
        $summaryText = implode(' + ', $parts);
        $methodLabel = $request->payment_method === 'cash' ? 'TUNAI' : 'QRIS';
        $changeText = ($request->payment_method === 'cash' && $cashReceived > $totalPayNow)
            ? " | Uang Diterima: Rp " . number_format($cashReceived, 0, ',', '.') . " | KEMBALIAN: Rp " . number_format($change, 0, ',', '.')
            : "";

        return redirect()->route('kasir.pos.index', ['booking_id' => $booking->id])
            ->with('success', "Pembayaran {$methodLabel} Berhasil! {$summaryText} = Total Rp " . number_format($totalPayNow, 0, ',', '.') . " telah Lunas{$changeText}.")
            ->with('print_receipt_id', $booking->id)
            ->with('last_paid_total', $totalPayNow)
            ->with('last_paid_method', $methodLabel)
            ->with('last_paid_summary', $summaryText)
            ->with('cash_received', $cashReceived)
            ->with('cash_change', $change);
    }
}
