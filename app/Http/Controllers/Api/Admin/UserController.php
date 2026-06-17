<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // Mengambil seluruh akun pengguna terbaru.
    public function index()
    {
        // Return all users
        return User::latest()->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    // Membuat akun pengguna baru dengan role, status, dan password.
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'role' => ['required', 'in:admin,cashier'],
            'status' => ['required', 'in:active,inactive'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->role = $request->role;
        $user->status = $request->status;
        $user->save();

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    // Menampilkan detail satu akun pengguna berdasarkan ID.
    public function show(string $id)
    {
        return User::findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     */
    // Memperbarui akun pengguna dan password jika password baru dikirim.
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->getKey() . ',user_id'],
            'role' => ['required', 'in:admin,cashier'],
            'status' => ['required', 'in:active,inactive'],
            'password' => ['sometimes', 'nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        if (auth()->check() && $user->getKey() === auth()->id() && $request->status === 'inactive') {
            return response()->json([
                'message' => 'Anda tidak dapat menonaktifkan akun yang sedang digunakan.'
            ], 403);
        }

        if ($request->status === 'inactive' && $this->isOnlyActiveAdmin($user)) {
            return response()->json([
                'message' => 'Akun tidak dapat diinaktifkan karena merupakan satu-satunya admin aktif.'
            ], 403);
        }

        if ($request->status === 'inactive' && $this->hasPendingTransactions($user)) {
            return response()->json([
                'message' => 'Akun tidak dapat diinaktifkan karena masih memiliki proses aktif.'
            ], 422);
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;
        $user->status = $request->status;

        // Only update password if provided
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    // Menghapus akun jika bukan akun sendiri dan belum punya riwayat transaksi/pengeluaran.
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);

        // Prevent deleting self
        if (auth()->check() && $user->getKey() === auth()->id()) {
            return response()->json(['message' => 'Anda tidak dapat menghapus akun yang sedang Anda gunakan saat ini.'], 403);
        }

        $hasTransactions = Transaction::withTrashed()
            ->where('user_id', $user->getKey())
            ->exists();

        $hasExpenses = Expense::withTrashed()
            ->where('user_id', $user->getKey())
            ->exists();

        // Prevent deleting if the user still has transaction or expense history.
        if ($hasTransactions || $hasExpenses) {
            return response()->json(['message' => 'Akun ini tidak bisa dihapus, karena memiliki relasi dengan data lain.'], 403);
        }

        try {
            $user->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == '23000') {
                return response()->json(['message' => 'Akun ini tidak bisa dihapus, karena memiliki relasi dengan data lain.'], 403);
            }
            return response()->json(['message' => 'Terjadi kesalahan pada server saat menghapus akun.'], 500);
        }

        return response()->json(['message' => 'User deleted successfully']);
    }

    // Mengecek apakah akun adalah satu-satunya admin aktif di sistem.
    private function isOnlyActiveAdmin(User $user): bool
    {
        if ($user->role !== 'admin' || $user->status !== 'active') {
            return false;
        }

        return User::where('role', 'admin')
            ->where('status', 'active')
            ->where('user_id', '!=', $user->getKey())
            ->doesntExist();
    }

    // Mengecek apakah akun masih memiliki transaksi pending yang belum selesai.
    private function hasPendingTransactions(User $user): bool
    {
        return Transaction::where('user_id', $user->getKey())
            ->where('status', 'pending')
            ->exists();
    }
}
