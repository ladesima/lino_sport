<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Court;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CourtController extends Controller {
    public function index() {
        $courts = Court::all();
        return view('admin.courts.index', compact('courts'));
    }

    public function create() {
        return view('admin.courts.form', ['court' => new Court()]);
    }

    public function store(Request $request) {
        $request->validate([
            'name' => 'required|string|max:100',
            'sport' => 'required|in:padel,bulutangkis,pickleball',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $data = $request->only(['name', 'sport']);
        $data['is_active'] = $request->has('is_active');

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $uploadDir = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, public_path('uploads/courts'));
            if (!is_dir($uploadDir)) {
                $parent = dirname($uploadDir);
                if (!is_dir($parent)) @mkdir($parent, 0777, true);
                @mkdir($uploadDir, 0777, true);
            }
            $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) $ext = 'jpg';
            $filename = 'court_' . time() . '_' . \Illuminate\Support\Str::random(6) . '.' . $ext;

            $file->move($uploadDir, $filename);
            $data['image'] = 'uploads/courts/' . $filename;
        }

        Court::create($data);
        return redirect()->route('admin.courts.index')->with('success', 'Lapangan berhasil ditambahkan.');
    }

    public function edit(Court $court) {
        return view('admin.courts.form', compact('court'));
    }

    public function update(Request $request, Court $court) {
        $request->validate([
            'name' => 'required|string|max:100',
            'sport' => 'required|in:padel,bulutangkis,pickleball',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $data = $request->only(['name', 'sport']);
        $data['is_active'] = $request->has('is_active');

        if ($request->hasFile('image')) {
            if ($court->image) {
                $oldPath = public_path($court->image);
                if (is_file($oldPath)) {
                    @unlink($oldPath);
                } elseif (Storage::disk('public')->exists($court->image)) {
                    @Storage::disk('public')->delete($court->image);
                }
            }

            $file = $request->file('image');
            $uploadDir = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, public_path('uploads/courts'));
            if (!is_dir($uploadDir)) {
                $parent = dirname($uploadDir);
                if (!is_dir($parent)) @mkdir($parent, 0777, true);
                @mkdir($uploadDir, 0777, true);
            }
            $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) $ext = 'jpg';
            $filename = 'court_' . time() . '_' . \Illuminate\Support\Str::random(6) . '.' . $ext;

            $file->move($uploadDir, $filename);
            $data['image'] = 'uploads/courts/' . $filename;
        }

        $court->update($data);
        return redirect()->route('admin.courts.index')->with('success', 'Lapangan berhasil diupdate.');
    }

    public function destroy(Court $court) {
        if ($court->image) {
            $oldPath = public_path($court->image);
            if (is_file($oldPath)) {
                @unlink($oldPath);
            } elseif (Storage::disk('public')->exists($court->image)) {
                @Storage::disk('public')->delete($court->image);
            }
        }
        $court->delete();
        return redirect()->route('admin.courts.index')->with('success', 'Lapangan berhasil dihapus.');
    }
}
