<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Court;
use Carbon\Carbon;

class AdminController extends Controller {
    public function index() {
        Booking::cancelExpiredBookings();

        $today = Carbon::now()->format('Y-m-d');

        $total = Booking::count();
        $pending = Booking::where('status', 'pending')->count();
        $confirmed = Booking::where('status', 'confirmed')->count();
        $courts = Court::count();

        $todayBookings = Booking::with(['court', 'user', 'approver'])
            ->whereDate('start_dt', $today)
            ->where('status', '!=', 'cancelled')
            ->orderBy('start_dt')
            ->get();

        $totalTodayCount = $todayBookings->count();
        $confirmedCount  = $todayBookings->where('status', 'confirmed')->count();
        $pendingCount    = $todayBookings->where('status', 'pending')->count();

        $todayIncome = Booking::whereDate('created_at', $today)
            ->where('status', '!=', 'cancelled')
            ->sum('paid_amount');

        $todayUnpaidRemaining = $todayBookings->sum(fn($b) => $b->remaining_amount);

        // Antrean perlu tindakan: masih pending ATAU belum lunas (masih ada sisa pelunasan)
        $needsAction = Booking::with(['court', 'user', 'approver'])
            ->where('status', '!=', 'cancelled')
            ->where(function ($q) {
                $q->where('status', 'pending')
                  ->orWhereColumn('paid_amount', '<', 'total_price');
            })
            ->orderBy('start_dt')
            ->take(8)
            ->get();

        $recentBookings = Booking::with(['court', 'user', 'approver'])->latest()->take(8)->get();

        return view('admin.dashboard', compact(
            'total', 'pending', 'confirmed', 'courts',
            'todayBookings', 'totalTodayCount', 'confirmedCount', 'pendingCount',
            'todayIncome', 'todayUnpaidRemaining', 'needsAction', 'recentBookings'
        ));
    }
}
