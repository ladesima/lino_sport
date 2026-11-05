<?php

namespace App\Http\Controllers;

use App\Models\Court;
use App\Models\Rate;
use App\Models\Equipment;
use App\Models\Slider;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Halaman Utama (Home) - Dapat dibuka bersamaan di tab lain meskipun sedang login sebagai Admin.
     */
    public function index(Request $request)
    {
        // Ambil slider banner aktif
        $sliders = Slider::where('is_active', true)->orderBy('order')->get();

        // Ambil semua lapangan aktif
        $allCourts = Court::where('is_active', true)->get();

        // Ambil data tarif harga lapangan
        $rates = Rate::all()->groupBy(function ($rate) {
            return $rate->sport . '_' . $rate->day_type;
        });

        // Ambil daftar peralatan olahraga
        $equipment = Equipment::all();

        return view('home', compact('sliders', 'allCourts', 'rates', 'equipment'));
    }
}
