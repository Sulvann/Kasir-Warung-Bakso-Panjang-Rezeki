<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Ingredient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductAndRecipeController extends Controller
{
    /**
     * Display a listing of products with their ingredients and max_yield calculation.
     */
    public function index()
    {
        $products = Product::with(['category', 'ingredients'])->latest()->get();

        // Hitung max_yield (potensi porsi) dari stok bahan baku
        $products->each(function ($product) {
            $ingredients = $product->ingredients;
            if ($ingredients->isEmpty()) {
                // Tidak ada resep -> tidak ada batasan dari bahan (null = bebas)
                $product->max_yield = null;
            } else {
                $maxYield = PHP_INT_MAX;
                foreach ($ingredients as $ing) {
                    $qty = $ing->pivot->quantity;
                    if ($qty <= 0) continue;
                    $possible = floor($ing->stock / $qty);
                    if ($possible < $maxYield)
                        $maxYield = $possible;
                }
                $product->max_yield = ($maxYield === PHP_INT_MAX) ? 0 : $maxYield;
            }
        });

        return response()->json([
            'status' => 'success',
            'data' => $products
        ]);
    }

    /**
     * Store composite Product + Recipe.
     */
    public function store(Request $request)
    {
        // 1. Validasi Data dari layarnya (Produk + Array Resep)
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'category_id' => 'required|exists:categories,category_id',
            'status' => 'required|in:active,inactive',
            'image' => 'nullable|image',
            'ingredients' => 'required|array|min:1', // HARUS ada minimal 1 bahan baku
            'ingredients.*.id' => 'required|exists:ingredients,ingredient_id',
            'ingredients.*.quantity' => 'required|numeric|min:0.01'
        ]);

        // 2. Transaksi Database (Cegah error setengah jalan)
        DB::beginTransaction();

        try {
            $category = Category::findOrFail($request->category_id);
            if ($category->status === 'inactive') {
                DB::rollBack();

                return response()->json([
                    'status' => 'error',
                    'message' => 'Kategori inaktif tidak bisa digunakan untuk menu baru.'
                ], 422);
            }

            $inactiveIngredients = Ingredient::whereIn('ingredient_id', collect($request->ingredients)->pluck('id'))
                ->where('status', 'inactive')
                ->pluck('name');

            if ($inactiveIngredients->isNotEmpty()) {
                DB::rollBack();

                return response()->json([
                    'status' => 'error',
                    'message' => 'Bahan inaktif tidak bisa digunakan untuk resep baru: ' . $inactiveIngredients->join(', ')
                ], 422);
            }

            // 3. Simpan data Produk ke tabel products
            $productData = $request->only(['name', 'price', 'category_id', 'status']);
            if ($request->hasFile('image')) {
                $productData['image'] = $request->file('image')->store('products', 'public');
            }
            $product = Product::create($productData);

            // 4. Siapkan data Resep-nya ke tabel product_ingredients
            $recipeRows = [];
            foreach ($request->ingredients as $ing) {
                $recipeRows[] = [
                    'product_id' => $product->getKey(), // Pakai ID produk yang baru saja dibuat di atas
                    'ingredient_id' => $ing['id'],
                    'quantity' => $ing['quantity'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            \App\Models\ProductIngredient::insert($recipeRows); // Simpan semua baris resep sekaligus

            // 5. Berhasil semua? Commit!
            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Menu baru beserta resepnya berhasil dibuat!'
            ]);

        } catch (\Exception $e) {
            DB::rollback(); // Batalkan jika gagal
            Log::error('Resep Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membuat menu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update composite Product + Recipe.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'category_id' => 'required|exists:categories,category_id',
            'status' => 'required|in:active,inactive',
            'ingredients' => 'required|array|min:1',
            'ingredients.*.id' => 'required|exists:ingredients,ingredient_id',
            'ingredients.*.quantity' => 'required|numeric|min:0.01'
        ]);

        DB::beginTransaction();

        try {
            $product = Product::findOrFail($id);

            $category = Category::findOrFail($request->category_id);
            if ($category->status === 'inactive' && (int) $request->category_id !== (int) $product->category_id) {
                DB::rollBack();

                return response()->json([
                    'status' => 'error',
                    'message' => 'Kategori inaktif tidak bisa digunakan untuk menu.'
                ], 422);
            }

            $existingIngredientIds = $product->ingredients()
                ->pluck('ingredients.ingredient_id')
                ->all();

            $newIngredientIds = collect($request->ingredients)
                ->pluck('id')
                ->diff($existingIngredientIds);

            $inactiveNewIngredients = Ingredient::whereIn('ingredient_id', $newIngredientIds)
                ->where('status', 'inactive')
                ->pluck('name');

            if ($inactiveNewIngredients->isNotEmpty()) {
                DB::rollBack();

                return response()->json([
                    'status' => 'error',
                    'message' => 'Bahan inaktif tidak bisa ditambahkan ke resep: ' . $inactiveNewIngredients->join(', ')
                ], 422);
            }

            $productData = $request->only(['name', 'price', 'category_id', 'status']);
            if ($request->hasFile('image')) {
                if ($product->image && Storage::disk('public')->exists($product->image)) {
                    Storage::disk('public')->delete($product->image);
                }
                $productData['image'] = $request->file('image')->store('products', 'public');
            }
            $product->update($productData);

            \App\Models\ProductIngredient::where('product_id', $product->getKey())->delete();
            
            $recipeRows = [];
            foreach ($request->ingredients as $ing) {
                $recipeRows[] = [
                    'product_id' => $product->getKey(),
                    'ingredient_id' => $ing['id'],
                    'quantity' => $ing['quantity'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            \App\Models\ProductIngredient::insert($recipeRows);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Menu beserta resepnya berhasil diperbarui!'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Resep Update Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memperbarui menu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'status' => 'error',
                'message' => 'Produk tidak ditemukan'
            ], 404);
        }

        if (\App\Models\TransactionDetail::where('product_id', $id)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tidak bisa menghapus resep produk dikarenakan data ini memiliki relasi dengan riwayat transaksi.'
            ], 400);
        }

        if ($product->image && Storage::disk('public')->exists($product->image)) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Produk berhasil dihapus'
        ]);
    }
}
