<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Slider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SliderController extends Controller {
    public function index() {
        $sliders = Slider::orderBy('order')->get();
        return view('admin.sliders.index', compact('sliders'));
    }

    public function create() {
        return view('admin.sliders.form', ['slider' => new Slider()]);
    }

    public function store(Request $request) {
        $request->validate([
            'title' => 'required|string|max:150',
            'subtitle' => 'nullable|string|max:255',
            'badge' => 'nullable|string|max:50',
            'image' => 'required_without:image_url|nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'image_url' => 'nullable|string|max:255',
            'button_text' => 'nullable|string|max:50',
            'button_url' => 'nullable|string|max:255',
            'order' => 'nullable|integer',
        ]);

        $data = $request->only(['title', 'subtitle', 'badge', 'button_text', 'button_url', 'order']);
        $data['is_active'] = $request->has('is_active');
        $data['order'] = $request->order ?? 0;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $uploadDir = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, public_path('uploads/sliders'));
            if (!is_dir($uploadDir)) {
                $parent = dirname($uploadDir);
                if (!is_dir($parent)) @mkdir($parent, 0777, true);
                @mkdir($uploadDir, 0777, true);
            }
            $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) $ext = 'jpg';
            $filename = 'slider_' . time() . '_' . \Illuminate\Support\Str::random(6) . '.' . $ext;

            $file->move($uploadDir, $filename);
            $data['image'] = 'uploads/sliders/' . $filename;
        } elseif ($request->filled('image_url')) {
            $data['image'] = $request->image_url;
        }

        Slider::create($data);
        return redirect()->route('admin.sliders.index')->with('success', 'Slider baru berhasil ditambahkan.');
    }

    public function edit(Slider $slider) {
        return view('admin.sliders.form', compact('slider'));
    }

    public function update(Request $request, Slider $slider) {
        $request->validate([
            'title' => 'required|string|max:150',
            'subtitle' => 'nullable|string|max:255',
            'badge' => 'nullable|string|max:50',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'image_url' => 'nullable|string|max:255',
            'button_text' => 'nullable|string|max:50',
            'button_url' => 'nullable|string|max:255',
            'order' => 'nullable|integer',
        ]);

        $data = $request->only(['title', 'subtitle', 'badge', 'button_text', 'button_url', 'order']);
        $data['is_active'] = $request->has('is_active');
        $data['order'] = $request->order ?? 0;

        if ($request->hasFile('image')) {
            if ($slider->image) {
                $oldPath = public_path($slider->image);
                if (is_file($oldPath)) {
                    @unlink($oldPath);
                } elseif (Storage::disk('public')->exists($slider->image)) {
                    @Storage::disk('public')->delete($slider->image);
                }
            }

            $file = $request->file('image');
            $uploadDir = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, public_path('uploads/sliders'));
            if (!is_dir($uploadDir)) {
                $parent = dirname($uploadDir);
                if (!is_dir($parent)) @mkdir($parent, 0777, true);
                @mkdir($uploadDir, 0777, true);
            }
            $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) $ext = 'jpg';
            $filename = 'slider_' . time() . '_' . \Illuminate\Support\Str::random(6) . '.' . $ext;

            $file->move($uploadDir, $filename);
            $data['image'] = 'uploads/sliders/' . $filename;
        } elseif ($request->filled('image_url')) {
            $data['image'] = $request->image_url;
        }

        $slider->update($data);
        return redirect()->route('admin.sliders.index')->with('success', 'Slider berhasil diperbarui.');
    }

    public function destroy(Slider $slider) {
        if ($slider->image) {
            $oldPath = public_path($slider->image);
            if (is_file($oldPath)) {
                @unlink($oldPath);
            } elseif (Storage::disk('public')->exists($slider->image)) {
                @Storage::disk('public')->delete($slider->image);
            }
        }
        $slider->delete();
        return redirect()->route('admin.sliders.index')->with('success', 'Slider berhasil dihapus.');
    }
}
