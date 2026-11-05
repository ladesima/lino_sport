<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Court;
use App\Models\Equipment;
use Carbon\Carbon;

class CashierDashboardController extends Controller
{
    public function index()
    {
        Booking::cancelExpiredBookings();

        $today = Carbon::now()->format('Y-m-d');

        $todayBookings = Booking::with(['court', 'user'])
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

        $needsAction = Booking::with(['court', 'user'])
            ->where('status', '!=', 'cancelled')
            ->where(function ($q) {
                $q->where('status', 'pending')
                  ->orWhereColumn('paid_amount', '<', 'total_price');
            })
            ->orderBy('start_dt')
            ->take(8)
            ->get();

        $recentBookings = Booking::with(['court', 'user'])
            ->latest()
            ->take(8)
            ->get();

        $equipmentCount = Equipment::count();
        $courts = Court::where('is_active', true)->get();

        return view('cashier.dashboard', compact(
            'todayBookings',
            'totalTodayCount',
            'confirmedCount',
            'pendingCount',
            'todayIncome',
            'todayUnpaidRemaining',
            'needsAction',
            'recentBookings',
            'equipmentCount',
            'courts'
        ));
    }
}
