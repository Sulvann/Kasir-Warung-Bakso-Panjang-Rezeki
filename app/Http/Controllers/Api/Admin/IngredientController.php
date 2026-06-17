<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use Illuminate\Http\Request;

class IngredientController extends Controller
{
    // Mengambil seluruh data bahan baku terbaru.
    public function index()
    {
        $ingredients = Ingredient::latest()->get();
        return response()->json([
            'status' => 'success',
            'data' => $ingredients
        ]);
    }

    // Menyimpan bahan baku baru setelah validasi stok, satuan, dan status.
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'stock' => 'required|numeric|min:0',
            'unit' => 'required|in:Gram,Kg,Pcs,Kantong',
            'status' => 'required|in:active,inactive',
        ]);

        $ingredient = Ingredient::create($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Bahan baku berhasil ditambahkan',
            'data' => $ingredient
        ], 201);
    }

    // Menampilkan detail satu bahan baku berdasarkan ID.
    public function show($id)
    {
        $ingredient = Ingredient::find($id);

        if (!$ingredient) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bahan baku tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $ingredient
        ]);
    }

    // Memperbarui data bahan baku berdasarkan ID.
    public function update(Request $request, $id)
    {
        $ingredient = Ingredient::find($id);

        if (!$ingredient) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bahan baku tidak ditemukan'
            ], 404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'stock' => 'required|numeric|min:0',
            'unit' => 'required|in:Gram,Kg,Pcs,Kantong',
            'status' => 'required|in:active,inactive',
        ]);

        if ($request->status === 'inactive' && $this->isUsedByActiveProducts($ingredient)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bahan setengah jadi tidak dapat diinaktifkan karena masih digunakan dalam komposisi produk aktif.'
            ], 422);
        }

        $ingredient->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Bahan baku berhasil diperbarui',
            'data' => $ingredient
        ]);
    }

    // Menghapus bahan baku jika belum dipakai dalam resep produk.
    public function destroy($id)
    {
        $ingredient = Ingredient::find($id);

        if (!$ingredient) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bahan baku tidak ditemukan'
            ], 404);
        }

        if ($ingredient->productIngredients()->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bahan Baku ini tidak dapat dihapus, karena memiliki relasi ke komposisi Resep Produk.'
            ], 400);
        }

        $ingredient->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Bahan baku berhasil dihapus'
        ]);
    }

    // Mengecek apakah bahan masih dipakai dalam komposisi produk yang aktif.
    private function isUsedByActiveProducts(Ingredient $ingredient): bool
    {
        return $ingredient->productIngredients()
            ->whereHas('product', fn ($query) => $query->where('status', 'active'))
            ->exists();
    }
}
