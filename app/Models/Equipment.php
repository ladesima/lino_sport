<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Equipment extends Model {
    protected $fillable = ['name', 'price'];

    /**
     * Apakah item ini dihitung per jam (Raket/Paddle) atau per item (Bola/Shuttlecock)?
     */
    public function getIsHourlyAttribute(): bool {
        return self::isHourlyByName($this->name ?? '');
    }

    /**
     * Label satuan harga: '/jam' untuk raket, '/item' untuk bola & shuttlecock
     */
    public function getUnitLabelAttribute(): string {
        return $this->is_hourly ? '/jam' : '/item';
    }

    public static function isHourlyByName(string $name): bool {
        $lower = strtolower($name);
        return str_contains($lower, 'raket') || str_contains($lower, 'paddle');
    }
}
