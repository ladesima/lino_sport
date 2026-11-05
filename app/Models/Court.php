<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Court extends Model {
    protected $fillable = ['name', 'sport', 'image', 'opening_time', 'closing_time', 'is_active'];

    public function bookings(): HasMany {
        return $this->hasMany(Booking::class);
    }

    public function getImageUrlAttribute(): ?string {
        if (empty($this->image)) {
            return null;
        }
        if (str_starts_with($this->image, 'http')) {
            return $this->image;
        }
        if (str_starts_with($this->image, 'uploads/')) {
            return asset($this->image);
        }
        return asset('storage/' . $this->image);
    }

    /**
     * Memeriksa apakah slot tersedia.
     * Aturan:
     * Semua booking yang aktif (status != 'cancelled', baik 'pending' menunggu approve admin maupun 'confirmed')
     * mengunci slot jadwal agar tidak terjadi bentrok / double-booking.
     */
    public function isSlotAvailable(\DateTime $start, \DateTime $end): bool {
        return !$this->bookings()
            ->where('status', '!=', 'cancelled')
            ->where('start_dt', '<', $end->format('Y-m-d H:i:s'))
            ->where('end_dt', '>', $start->format('Y-m-d H:i:s'))
            ->exists();
    }
}
