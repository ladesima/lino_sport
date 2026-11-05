<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Slider extends Model {
    protected $fillable = [
        'title', 'subtitle', 'badge', 'image',
        'button_text', 'button_url', 'order', 'is_active'
    ];

    public function getImageUrlAttribute(): string {
        if (empty($this->image)) {
            return '';
        }
        if (str_starts_with($this->image, 'http')) {
            return $this->image;
        }
        if (str_starts_with($this->image, 'uploads/')) {
            return asset($this->image);
        }
        return asset('storage/' . $this->image);
    }
}
