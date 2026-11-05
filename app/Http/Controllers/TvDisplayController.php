<?php
namespace App\Http\Controllers;
use App\Models\Court;
use App\Models\Booking;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TvDisplayController extends Controller {
    public function index(Request $request) {
        $now = Carbon::now();
        $today = $now->toDateString();
        $currentTime = $now->format('H:i');

        $courts = Court::where('is_active', true)->get();

        // Ambil semua booking terkonfirmasi hari ini
        $confirmedBookings = Booking::where('status', 'confirmed')
            ->whereDate('start_dt', $today)
            ->get();

        // Petakan status tiap lapangan sekarang
        $courtStatus = [];
        foreach ($courts as $court) {
            // Cari booking yang sedang berjalan saat ini
            $activeBooking = $confirmedBookings->first(function ($b) use ($court, $now) {
                return $b->court_id == $court->id
                    && Carbon::parse($b->start_dt) <= $now
                    && Carbon::parse($b->end_dt) > $now;
            });

            // Cari booking berikutnya setelah jam sekarang
            $nextBooking = $confirmedBookings->filter(function ($b) use ($court, $now) {
                return $b->court_id == $court->id && Carbon::parse($b->start_dt) > $now;
            })->sortBy('start_dt')->first();

            $courtStatus[] = [
                'court' => $court,
                'is_in_use' => (bool)$activeBooking,
                'active_booking' => $activeBooking ? [
                    'customer_name' => $activeBooking->display_name,
                    'start_time' => Carbon::parse($activeBooking->start_dt)->format('H:i'),
                    'end_time' => Carbon::parse($activeBooking->end_dt)->format('H:i'),
                    'remaining_minutes' => (int)max(0, ceil($now->diffInMinutes(Carbon::parse($activeBooking->end_dt), false))),
                ] : null,
                'next_booking' => $nextBooking ? [
                    'customer_name' => $nextBooking->display_name,
                    'start_time' => Carbon::parse($nextBooking->start_dt)->format('H:i'),
                    'end_time' => Carbon::parse($nextBooking->end_dt)->format('H:i'),
                ] : null,
            ];
        }

        // Ambil jadwal lengkap hari ini
        $todaySchedule = $confirmedBookings->sortBy('start_dt');

        return view('tv.display', compact('courtStatus', 'todaySchedule', 'now'));
    }

    // Endpoint API untuk Auto-Refresh Data di Layar TV tanpa Reload Kasar
    public function apiStatus() {
        $now = Carbon::now();
        $today = $now->toDateString();

        $courts = Court::where('is_active', true)->get();
        $confirmedBookings = Booking::where('status', 'confirmed')
            ->whereDate('start_dt', $today)
            ->get();

        $data = [];
        foreach ($courts as $court) {
            $activeBooking = $confirmedBookings->first(function ($b) use ($court, $now) {
                return $b->court_id == $court->id
                    && Carbon::parse($b->start_dt) <= $now
                    && Carbon::parse($b->end_dt) > $now;
            });

            $nextBooking = $confirmedBookings->filter(function ($b) use ($court, $now) {
                return $b->court_id == $court->id && Carbon::parse($b->start_dt) > $now;
            })->sortBy('start_dt')->first();

            $data[] = [
                'court_id' => $court->id,
                'court_name' => $court->name,
                'court_sport' => ucfirst($court->sport),
                'is_in_use' => (bool)$activeBooking,
                'active_user' => $activeBooking ? $activeBooking->display_name : null,
                'active_time' => $activeBooking ? Carbon::parse($activeBooking->start_dt)->format('H:i') . ' - ' . Carbon::parse($activeBooking->end_dt)->format('H:i') : null,
                'remaining_min' => $activeBooking ? (int)max(0, ceil($now->diffInMinutes(Carbon::parse($activeBooking->end_dt), false))) : 0,
                'next_user' => $nextBooking ? $nextBooking->display_name : null,
                'next_time' => $nextBooking ? Carbon::parse($nextBooking->start_dt)->format('H:i') . ' - ' . Carbon::parse($nextBooking->end_dt)->format('H:i') : null,
            ];
        }

        return response()->json([
            'time' => $now->format('H:i:s'),
            'date' => $now->translatedFormat('l, d F Y'),
            'courts' => $data,
        ]);
    }
}
