<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use Illuminate\Http\Request;

class IngredientController extends Controller
{
    public function index()
    {
        $ingredients = Ingredient::latest()->get();
        return response()->json([
            'status' => 'success',
            'data' => $ingredients
        ]);
    }

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

        $ingredient->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Bahan baku berhasil diperbarui',
            'data' => $ingredient
        ]);
    }

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
}
