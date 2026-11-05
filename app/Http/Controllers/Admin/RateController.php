<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Rate;
use Illuminate\Http\Request;

class RateController extends Controller {
    public function index() {
        $rates = Rate::orderBy('sport')->orderBy('day_type')->get();
        return view('admin.rates.index', compact('rates'));
    }
    public function create() { return view('admin.rates.form', ['rate' => new Rate()]); }
    public function store(Request $request) {
        $request->validate(['sport' => 'required', 'day_type' => 'required', 'price' => 'required|integer|min:1']);
        Rate::updateOrCreate(
            ['sport' => $request->sport, 'day_type' => $request->day_type],
            ['price' => $request->price]
        );
        return redirect()->route('admin.rates.index')->with('success', 'Tarif berhasil disimpan.');
    }
    public function edit(Rate $rate) { return view('admin.rates.form', compact('rate')); }
    public function update(Request $request, Rate $rate) {
        $request->validate(['sport' => 'required', 'day_type' => 'required', 'price' => 'required|integer|min:1']);
        $rate->update($request->only(['sport', 'day_type', 'price']));
        return redirect()->route('admin.rates.index')->with('success', 'Tarif berhasil diupdate.');
    }
    public function destroy(Rate $rate) {
        $rate->delete();
        return redirect()->route('admin.rates.index')->with('success', 'Tarif berhasil dihapus.');
    }
}
