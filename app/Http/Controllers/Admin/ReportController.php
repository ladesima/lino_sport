<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Court;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller {
    public function index(Request $request) {
        // Filter rentang tanggal default bulan ini
        $startDate = $request->start_date ?? Carbon::now()->startOfMonth()->toDateString();
        $endDate   = $request->end_date ?? Carbon::now()->endOfMonth()->toDateString();
        $courtId   = $request->court_id;
        $paymentMethod = $request->payment_method;

        // Query dasar booking terkonfirmasi / dibayar
        $query = Booking::with(['court', 'approver'])
            ->where('status', 'confirmed')
            ->whereDate('start_dt', '>=', $startDate)
            ->whereDate('start_dt', '<=', $endDate);

        if ($courtId) {
            $query->where('court_id', $courtId);
        }

        if ($paymentMethod) {
            $query->where('payment_method', $paymentMethod);
        }

        $bookings = $query->orderBy('start_dt', 'desc')->get();

        // 1. Ringkasan Finansial Utama
        $totalPendapatanMasuk = 0;
        $totalNilaiBooking = $bookings->sum('total_price');
        $totalSisaPelunasan = 0;

        foreach ($bookings as $b) {
            $received = $b->actual_paid_amount;
            $remaining = $b->remaining_amount;
            $totalPendapatanMasuk += $received;
            $totalSisaPelunasan += $remaining;
        }

        $totalTransaksi = $bookings->count();
        $totalDpCount = $bookings->where('payment_method', 'dp')->count();
        $totalLunasCount = $bookings->where('payment_method', 'lunas')->count();

        // 2. Pendapatan Berdasarkan Cabang Olahraga
        $incomeBySport = [
            'padel' => 0,
            'bulutangkis' => 0,
            'pickleball' => 0,
        ];
        foreach ($bookings as $b) {
            $sport = $b->court->sport;
            $received = $b->actual_paid_amount;
            if (isset($incomeBySport[$sport])) {
                $incomeBySport[$sport] += $received;
            }
        }

        // 3. Ringkasan Harian (Untuk visualisasi grafik mini & tren)
        $dailyIncome = [];
        foreach ($bookings as $b) {
            $day = Carbon::parse($b->start_dt)->format('d M');
            $received = $b->actual_paid_amount;
            if (!isset($dailyIncome[$day])) {
                $dailyIncome[$day] = 0;
            }
            $dailyIncome[$day] += $received;
        }

        $courts = Court::all();

        return view('admin.reports.index', compact(
            'bookings',
            'startDate',
            'endDate',
            'courtId',
            'paymentMethod',
            'totalPendapatanMasuk',
            'totalNilaiBooking',
            'totalSisaPelunasan',
            'totalTransaksi',
            'totalDpCount',
            'totalLunasCount',
            'incomeBySport',
            'dailyIncome',
            'courts'
        ));
    }
}
