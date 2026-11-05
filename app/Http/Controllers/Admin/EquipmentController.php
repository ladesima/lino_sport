<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Equipment;
use Illuminate\Http\Request;

class EquipmentController extends Controller {
    public function index() {
        $equipment = Equipment::all();
        return view('admin.equipment.index', compact('equipment'));
    }
    public function create() { return view('admin.equipment.form', ['equipment' => new Equipment()]); }
    public function store(Request $request) {
        $request->validate(['name' => 'required', 'price' => 'required|integer|min:1']);
        Equipment::create($request->only(['name', 'price']));
        return redirect()->route('admin.equipment.index')->with('success', 'Peralatan berhasil ditambahkan.');
    }
    public function edit(Equipment $equipment) { return view('admin.equipment.form', compact('equipment')); }
    public function update(Request $request, Equipment $equipment) {
        $request->validate(['name' => 'required', 'price' => 'required|integer|min:1']);
        $equipment->update($request->only(['name', 'price']));
        return redirect()->route('admin.equipment.index')->with('success', 'Peralatan berhasil diupdate.');
    }
    public function destroy(Equipment $equipment) {
        $equipment->delete();
        return redirect()->route('admin.equipment.index')->with('success', 'Peralatan berhasil dihapus.');
    }
}
