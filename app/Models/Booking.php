<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model {
    protected $fillable = [
        'user_id', 'approved_by', 'court_id',
        'customer_name', 'customer_phone', 'customer_email', 'customer_nik',
        'start_dt', 'end_dt',
        'equipment_json', 'total_price', 'dp_amount', 'paid_amount',
        'snap_token', 'settlement_token',
        'payment_status', 'settlement_status',
        'status', 'approved_at', 'payment_method',
        'payment_proof', 'payment_proof_uploaded_at'
    ];

    protected $casts = [
        'equipment_json'            => 'array',
        'approved_at'               => 'datetime',
        'payment_proof_uploaded_at' => 'datetime',
    ];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function court(): BelongsTo {
        return $this->belongsTo(Court::class);
    }

    public function getApproverNameAttribute(): ?string {
        if ($this->relationLoaded('approver') && $this->approver) {
            return $this->approver->name;
        }
        if ($this->approved_by) {
            return $this->approver?->name;
        }
        return null;
    }

    public function getApprovedAtFormattedAttribute(): ?string {
        return $this->approved_at ? \Carbon\Carbon::parse($this->approved_at)->format('d M Y, H:i') : null;
    }

    public function getPaymentProofUrlAttribute(): ?string {
        if (!$this->payment_proof) {
            return null;
        }
        if (str_starts_with($this->payment_proof, 'http')) {
            return $this->payment_proof;
        }
        return asset($this->payment_proof);
    }

    public function getPaymentProofUploadedAtFormattedAttribute(): ?string {
        return $this->payment_proof_uploaded_at
            ? \Carbon\Carbon::parse($this->payment_proof_uploaded_at)->format('d M Y, H:i')
            : null;
    }

    public function getPayableAmountAttribute(): int {
        return $this->payment_method === 'dp' ? $this->dp_amount : $this->total_price;
    }

    // Sisa tagihan yang harus dilunasi
    public function getRemainingAmountAttribute(): int {
        return max(0, $this->total_price - $this->paid_amount);
    }

    // Cek apakah booking sudah lunas total
    public function getIsFullyPaidAttribute(): bool {
        return $this->total_price > 0 && $this->paid_amount >= $this->total_price;
    }

    // Cek apakah baru bayar DP
    public function getIsDpPaidAttribute(): bool {
        return $this->paid_amount > 0 && $this->paid_amount < $this->total_price;
    }

    // Accessor nama pemesan fleksibel
    public function getDisplayNameAttribute(): string {
        return $this->customer_name ?: ($this->user?->name ?? 'Tamu');
    }

    // Accessor nomor telepon fleksibel
    public function getDisplayPhoneAttribute(): string {
        return $this->customer_phone ?: ($this->user?->phone ?? '-');
    }

    // Nomor Invoice Berformat (contoh: INV-00001)
    public function getFormattedInvoiceNoAttribute(): string {
        return 'INV-' . str_pad($this->id, 5, '0', STR_PAD_LEFT);
    }

    // Durasi bermain dalam jam
    public function getDurationHoursAttribute(): float {
        $start = \Carbon\Carbon::parse($this->start_dt);
        $end   = \Carbon\Carbon::parse($this->end_dt);
        return max(1, round($start->diffInMinutes($end) / 60, 1));
    }

    // Total biaya sewa alat & tambahan
    public function getEquipmentTotalAttribute(): int {
        $total = 0;
        if (!empty($this->equipment_json)) {
            foreach ($this->equipment_json as $eq) {
                $total += (int)($eq['price'] ?? 0);
            }
        }
        return $total;
    }

    // Total biaya sewa alat yang SUDAH DIBAYAR
    public function getEquipmentPaidTotalAttribute(): int {
        $paid = 0;
        if (!empty($this->equipment_json)) {
            foreach ($this->equipment_json as $eq) {
                if (!empty($eq['is_paid'])) {
                    $paid += (int)($eq['price'] ?? 0);
                }
            }
        }
        if ($this->is_fully_paid) {
            return $this->equipment_total;
        }
        return min($this->equipment_total, $paid);
    }

    // Sisa tagihan khusus sewa alat
    public function getEquipmentRemainingAttribute(): int {
        if ($this->is_fully_paid) return 0;
        return max(0, $this->equipment_total - $this->equipment_paid_total);
    }

    // Biaya sewa lapangan murni
    public function getCourtPriceAttribute(): int {
        return max(0, $this->total_price - $this->equipment_total);
    }

    // Uang yang sudah masuk khusus untuk tagihan lapangan
    public function getCourtPaidAmountAttribute(): int {
        if ($this->is_fully_paid) return $this->court_price;
        $rawCourtPaid = max(0, (int)$this->paid_amount - $this->equipment_paid_total);
        return min($this->court_price, $rawCourtPaid);
    }

    // Sisa tagihan khusus sewa lapangan
    public function getCourtRemainingAttribute(): int {
        if ($this->is_fully_paid) return 0;
        return max(0, $this->court_price - $this->court_paid_amount);
    }

    // Uang aktual yang sudah diterima kasir/gateway
    public function getActualPaidAmountAttribute(): int {
        if ($this->paid_amount > 0) return (int)$this->paid_amount;
        if ($this->status === 'confirmed') {
            return $this->payment_method === 'dp' ? (int)$this->dp_amount : (int)$this->total_price;
        }
        return 0;
    }

    /**
     * Dengan sistem QRIS Statis & Approval Manual Admin, booking berstatus 'pending'
     * (Menunggu Approve Admin) tetap dipertahankan hingga disetujui atau ditolak oleh Admin.
     */
    public static function cancelExpiredBookings(): int {
        return 0;
    }
}
