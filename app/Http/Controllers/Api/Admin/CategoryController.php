<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // Mengambil daftar kategori; khusus kasir hanya menampilkan kategori aktif.
    public function index(Request $request)
    {
        $categories = Category::query()
            ->when($request->is('cashier-api/*'), fn ($query) => $query->where('status', 'active'))
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $categories
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    // Menyimpan kategori baru setelah validasi nama dan status.
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:categories,name|max:255',
            'status' => 'required|in:active,inactive',
        ], [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.unique' => 'Nama kategori sudah digunakan. Silakan gunakan nama lain.',
            'name.max' => 'Nama kategori maksimal 255 karakter.',
            'status.required' => 'Status kategori wajib dipilih.',
            'status.in' => 'Status kategori tidak valid.'
        ]);

        $category = Category::create([
            'name' => $request->name,
            'status' => $request->status,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Category created successfully',
            'data' => $category
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    // Menampilkan detail satu kategori berdasarkan route model binding.
    public function show(Category $category)
    {
        return response()->json([
            'status' => 'success',
            'data' => $category
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    // Memperbarui kategori dengan menjaga nama tetap unik.
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|unique:categories,name,' . $category->getKey() . ',category_id|max:255',
            'status' => 'required|in:active,inactive',
        ], [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.unique' => 'Nama kategori sudah digunakan. Silakan gunakan nama lain.',
            'name.max' => 'Nama kategori maksimal 255 karakter.',
            'status.required' => 'Status kategori wajib dipilih.',
            'status.in' => 'Status kategori tidak valid.'
        ]);

        if ($request->status === 'inactive' && $this->hasActiveProducts($category)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kategori produk tidak dapat diinaktifkan karena masih digunakan oleh produk aktif.'
            ], 422);
        }

        $category->update([
            'name' => $request->name,
            'status' => $request->status,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Category updated successfully',
            'data' => $category
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    // Menghapus kategori jika belum memiliki relasi produk.
    public function destroy(Category $category)
    {
        // Prevent deleting if the category has products
        if ($category->products()->exists()) {
            return response()->json(['message' => 'Kategori ini tidak bisa dihapus, karena memiliki relasi dengan data lain'], 403);
        }

        try {
            $category->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Category deleted successfully'
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == '23000') {
                return response()->json(['message' => 'Kategori ini tidak bisa dihapus, karena memiliki relasi dengan data lain'], 403);
            }
            return response()->json(['message' => 'Terjadi kesalahan pada server saat menghapus kategori.'], 500);
        }
    }

    // Mengecek apakah kategori masih dipakai oleh produk yang berstatus aktif.
    private function hasActiveProducts(Category $category): bool
    {
        return Product::where('category_id', $category->getKey())
            ->where('status', 'active')
            ->exists();
    }
}
