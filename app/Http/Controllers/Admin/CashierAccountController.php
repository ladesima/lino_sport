<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class CashierAccountController extends Controller
{
    /**
     * Tampilkan daftar seluruh akun Admin beserta jumlah booking yang telah di-approve
     */
    public function index()
    {
        $cashiers = User::where(function ($q) {
                $q->where('is_admin', true)
                  ->orWhereIn('role', ['super_admin', 'admin', 'staff_admin']);
            })
            ->withCount('approvedBookings')
            ->orderByRaw('CASE WHEN id = 1 THEN 0 ELSE 1 END')
            ->latest()
            ->get();

        return view('admin.cashiers.index', compact('cashiers'));
    }

    /**
     * Form tambah User Admin baru
     */
    public function create()
    {
        return view('admin.cashiers.create');
    }

    /**
     * Simpan akun User Admin baru (Akses: Dashboard, Jadwal Lapangan, Daftar Booking & Approval)
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|max:100|unique:users,email',
            'phone'    => 'required|string|min:9|max:20',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'name.required'      => 'Nama lengkap Admin wajib diisi.',
            'email.required'     => 'Email login Admin wajib diisi.',
            'email.unique'       => 'Email ini sudah terdaftar.',
            'phone.required'     => 'Nomor WhatsApp / HP Admin wajib diisi.',
            'phone.min'          => 'Nomor WhatsApp minimal 9 digit.',
            'password.required'  => 'Kata sandi wajib diisi.',
            'password.min'       => 'Kata sandi minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        User::create([
            'name'     => trim($request->name),
            'email'    => strtolower(trim($request->email)),
            'phone'    => trim($request->phone),
            'password' => Hash::make($request->password),
            'role'     => 'staff_admin',
            'is_admin' => true,
        ]);

        return redirect()->route('admin.cashiers.index')->with('success', "Akun User Admin '{$request->name}' berhasil dibuat! Akun ini dapat mengakses Dashboard, Jadwal Lapangan, serta Daftar Booking & Approval.");
    }

    /**
     * Form edit akun User Admin
     */
    public function edit(User $cashier)
    {
        return view('admin.cashiers.edit', compact('cashier'));
    }

    /**
     * Update akun User Admin
     */
    public function update(Request $request, User $cashier)
    {
        $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => ['required', 'email', 'max:100', Rule::unique('users')->ignore($cashier->id)],
            'phone'    => 'required|string|min:9|max:20',
            'password' => 'nullable|string|min:6|confirmed',
        ], [
            'name.required'      => 'Nama lengkap Admin wajib diisi.',
            'email.required'     => 'Email login Admin wajib diisi.',
            'email.unique'       => 'Email ini sudah digunakan oleh akun lain.',
            'phone.required'     => 'Nomor WhatsApp / HP Admin wajib diisi.',
            'password.min'       => 'Kata sandi minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        $data = [
            'name'     => trim($request->name),
            'email'    => strtolower(trim($request->email)),
            'phone'    => trim($request->phone),
            'is_admin' => true,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $cashier->update($data);

        return redirect()->route('admin.cashiers.index')->with('success', "Data User Admin '{$cashier->name}' berhasil diperbarui!");
    }

    /**
     * Hapus akun User Admin
     */
    public function destroy(User $cashier)
    {
        if ($cashier->id === auth()->id() || $cashier->isSuperAdmin()) {
            return redirect()->route('admin.cashiers.index')->with('error', 'Akun Admin Utama atau akun yang sedang login tidak dapat dihapus.');
        }

        $name = $cashier->name;
        $cashier->delete();

        return redirect()->route('admin.cashiers.index')->with('success', "Akun User Admin '{$name}' telah dihapus.");
    }
}
