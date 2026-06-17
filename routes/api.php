<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Admin\CategoryController;
use App\Http\Controllers\Api\Admin\ProductAndRecipeController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Cashier\TransactionController;
use App\Http\Middleware\IsAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Authentication Routes
|--------------------------------------------------------------------------
*/

// Login API untuk mendapatkan token Sanctum.
Route::post('/login', [AuthController::class, 'login'])
    ->name('api.login');

/*
|--------------------------------------------------------------------------
| Protected API Routes
|--------------------------------------------------------------------------
*/

// Semua route API di dalam group ini wajib membawa token Sanctum yang valid.
Route::middleware(['auth:sanctum'])->group(function () {
    // Mengambil profil user pemilik token yang sedang digunakan.
    Route::get('/user', function (Request $request) {
        return $request->user();
    })->name('api.user');

    // Menghapus token aktif saat logout API.
    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('api.logout');

    /*
    |--------------------------------------------------------------------------
    | Public/Auth Read Access
    |--------------------------------------------------------------------------
    */

    // Mengambil daftar kategori untuk user yang sudah login.
    Route::get('categories', [CategoryController::class, 'index'])
        ->name('api.categories.index');

    // Mengambil detail satu kategori.
    Route::get('categories/{category}', [CategoryController::class, 'show'])
        ->name('api.categories.show');

    // Mengambil daftar produk beserta resepnya.
    Route::get('product-recipes', [ProductAndRecipeController::class, 'index'])
        ->name('api.product-recipes.index');

    // Mengambil detail satu produk dan resepnya.
    Route::get('product-recipes/{product}', [ProductAndRecipeController::class, 'show'])
        ->name('api.product-recipes.show');

    /*
    |--------------------------------------------------------------------------
    | Admin Write Access
    |--------------------------------------------------------------------------
    */

    // Route di dalam group ini hanya untuk user dengan role admin.
    Route::middleware([IsAdmin::class])->group(function () {
        // Menyimpan kategori baru.
        Route::post('categories', [CategoryController::class, 'store'])
            ->name('api.categories.store');

        // Memperbarui kategori berdasarkan ID.
        Route::put('categories/{category}', [CategoryController::class, 'update'])
            ->name('api.categories.update');

        // Menghapus kategori jika tidak punya relasi produk.
        Route::delete('categories/{category}', [CategoryController::class, 'destroy'])
            ->name('api.categories.destroy');

        // Menyimpan produk baru beserta resepnya.
        Route::post('product-recipes', [ProductAndRecipeController::class, 'store'])
            ->name('api.product-recipes.store');

        // Memperbarui produk dan resep berdasarkan ID produk.
        Route::post('product-recipes/{product}', [ProductAndRecipeController::class, 'update'])
            ->name('api.product-recipes.update');

        // Menghapus produk/resep jika tidak punya relasi transaksi.
        Route::delete('product-recipes/{product}', [ProductAndRecipeController::class, 'destroy'])
            ->name('api.product-recipes.destroy');

        // Endpoint CRUD akun pengguna khusus admin.
        Route::apiResource('users', UserController::class)
            ->names([
                'index' => 'api.users.index',
                'store' => 'api.users.store',
                'show' => 'api.users.show',
                'update' => 'api.users.update',
                'destroy' => 'api.users.destroy',
            ]);
    });

    /*
    |--------------------------------------------------------------------------
    | Transaction Routes
    |--------------------------------------------------------------------------
    */

    // Endpoint transaksi API untuk melihat, membuat, dan membuka detail transaksi.
    Route::apiResource('transactions', TransactionController::class)
        ->only(['index', 'store', 'show'])
        ->names([
            'index' => 'api.transactions.index',
            'store' => 'api.transactions.store',
            'show' => 'api.transactions.show',
        ]);
});
