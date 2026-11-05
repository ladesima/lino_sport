<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable {
    use HasFactory, Notifiable;
    protected $fillable = ['name', 'email', 'password', 'phone', 'is_admin', 'role'];
    protected $hidden = ['password', 'remember_token'];
    protected function casts(): array {
        return ['email_verified_at' => 'datetime', 'password' => 'hashed', 'is_admin' => 'boolean'];
    }
    public function bookings(): HasMany { return $this->hasMany(Booking::class); }

    public function approvedBookings(): HasMany {
        return $this->hasMany(Booking::class, 'approved_by');
    }

    /**
     * Admin Utama (Super Admin) memiliki akses penuh ke seluruh menu
     * (Keuangan, Master Data, dan Kelola User Admin).
     */
    public function isSuperAdmin(): bool {
        return $this->role === 'super_admin'
            || strtolower((string)$this->email) === 'admin@lino.com'
            || (int)$this->id === 1;
    }

    /**
     * Semua Admin (baik Admin Utama maupun User Admin operasional)
     * dapat mengakses Dashboard, Jadwal Lapangan, serta Daftar Booking & Approval.
     */
    public function isAdmin(): bool {
        return $this->isSuperAdmin()
            || in_array($this->role, ['admin', 'staff_admin', 'cashier'], true)
            || (bool)$this->is_admin;
    }

    public function isCashier(): bool {
        return $this->role === 'cashier' && !$this->isAdmin();
    }

    public function getRoleNameAttribute(): string {
        return $this->isSuperAdmin() ? 'Admin Utama' : 'User Admin';
    }
}
