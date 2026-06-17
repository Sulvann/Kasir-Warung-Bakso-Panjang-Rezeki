<?php

use Illuminate\Support\Facades\Route;

// Menampilkan halaman awal aplikasi.
Route::get('/', function () {
    return view('welcome');
});

// Menampilkan halaman login hanya untuk pengunjung yang belum login.
Route::get('/login', function () {
    return view('login');
})->name('login')->middleware('guest');

// Memproses login berbasis session web.
Route::post('/login', [\App\Http\Controllers\LoginController::class, 'authenticate'])->name('login.post');

// Memproses logout dan menghapus session pengguna.
Route::post('/logout', [\App\Http\Controllers\LoginController::class, 'logout'])->name('logout');

// Semua route di dalam group ini hanya bisa diakses setelah login.
Route::middleware('auth')->group(function () {
    // Kumpulan halaman dan endpoint admin dengan prefix /admin.
    Route::prefix('admin')->group(function () {
        // Menampilkan dashboard ringkasan keuangan admin.
        Route::get('/dashboard', [\App\Http\Controllers\Api\Admin\DashboardController::class, 'index'])->name('admin.dashboard');

        // Menampilkan halaman kelola kategori.
        Route::get('/categories', function () {
            return view('admin.categories');
        })->name('admin.categories');

        // Menampilkan halaman kelola stok/bahan baku.
        Route::get('/stok', function () {
            return view('admin.ingredients');
        })->name('admin.stok');

        // Menampilkan halaman kelola produk dan resep.
        Route::get('/recipes', function () {
            return view('admin.recipes');
        })->name('admin.recipes');

        // Menampilkan halaman daftar produk.
        Route::get('/products', function () {
            return view('admin.products');
        })->name('admin.products');

        // Menampilkan halaman kelola akun pengguna.
        Route::get('/users', function () {
            return view('admin.users');
        })->name('admin.users');

        // Menampilkan halaman daftar pemasukan/transaksi.
        Route::get('/incomes', function () {
            return view('admin.incomes');
        })->name('admin.incomes');

        // Menampilkan halaman kelola pengeluaran.
        Route::get('/expenses', function () {
            return view('admin.expenses');
        })->name('admin.expenses');

        // Menampilkan halaman filter laporan keuangan.
        Route::get('/reports', [\App\Http\Controllers\Api\Admin\ReportController::class, 'index'])->name('admin.reports');

        // Mengambil pratinjau laporan keuangan dalam bentuk HTML.
        Route::post('/reports/preview', [\App\Http\Controllers\Api\Admin\ReportController::class, 'preview'])->name('admin.reports.preview');

        // Mengunduh laporan keuangan dalam format Excel.
        Route::post('/reports/export', [\App\Http\Controllers\Api\Admin\ReportController::class, 'export'])->name('admin.reports.export');

        /*
        |--------------------------------------------------------------------------
        | JSON API endpoints for Datatables/Fetch
        |--------------------------------------------------------------------------
        | Route di bawah ini berada di dalam prefix /admin.
        | Jadi URI-nya menjadi:
        | /admin/api/categories
        | /admin/api/users
        | /admin/api/expenses
        | /admin/api/ingredients
        |
        | Nama route dibuat khusus dengan prefix admin.api.*
        | agar tidak bentrok dengan route api/users dari file api.php.
        |--------------------------------------------------------------------------
        */

        // Endpoint CRUD kategori untuk halaman admin.
        Route::apiResource('api/categories', \App\Http\Controllers\Api\Admin\CategoryController::class)
            ->names([
                'index' => 'admin.api.categories.index',
                'store' => 'admin.api.categories.store',
                'show' => 'admin.api.categories.show',
                'update' => 'admin.api.categories.update',
                'destroy' => 'admin.api.categories.destroy',
            ]);

        // Mengambil daftar produk beserta resep dan estimasi porsi.
        Route::get('api/product-recipes', [\App\Http\Controllers\Api\Admin\ProductAndRecipeController::class, 'index'])
            ->name('admin.api.product-recipes.index');

        // Menyimpan produk baru beserta komposisi resepnya.
        Route::post('api/product-recipes', [\App\Http\Controllers\Api\Admin\ProductAndRecipeController::class, 'store'])
            ->name('admin.api.product-recipes.store');

        // Memperbarui produk dan resep berdasarkan ID produk.
        Route::post('api/product-recipes/{id}', [\App\Http\Controllers\Api\Admin\ProductAndRecipeController::class, 'update'])
            ->name('admin.api.product-recipes.update');

        // Menghapus produk/resep jika tidak punya relasi transaksi.
        Route::delete('api/product-recipes/{id}', [\App\Http\Controllers\Api\Admin\ProductAndRecipeController::class, 'destroy'])
            ->name('admin.api.product-recipes.destroy');

        // Endpoint CRUD akun pengguna untuk halaman admin.
        Route::apiResource('api/users', \App\Http\Controllers\Api\Admin\UserController::class)
            ->names([
                'index' => 'admin.api.users.index',
                'store' => 'admin.api.users.store',
                'show' => 'admin.api.users.show',
                'update' => 'admin.api.users.update',
                'destroy' => 'admin.api.users.destroy',
            ]);

        // Endpoint CRUD pengeluaran untuk halaman admin.
        Route::apiResource('api/expenses', \App\Http\Controllers\Api\Admin\ExpenseController::class)
            ->names([
                'index' => 'admin.api.expenses.index',
                'store' => 'admin.api.expenses.store',
                'show' => 'admin.api.expenses.show',
                'update' => 'admin.api.expenses.update',
                'destroy' => 'admin.api.expenses.destroy',
            ]);

        // Endpoint CRUD bahan baku/stok untuk halaman admin.
        Route::apiResource('api/ingredients', \App\Http\Controllers\Api\Admin\IngredientController::class)
            ->names([
                'index' => 'admin.api.ingredients.index',
                'store' => 'admin.api.ingredients.store',
                'show' => 'admin.api.ingredients.show',
                'update' => 'admin.api.ingredients.update',
                'destroy' => 'admin.api.ingredients.destroy',
            ]);
    });

    // Menampilkan dashboard kasir/POS.
    Route::get('/cashier', function () {
        return view('cashier.dashboard');
    })->name('cashier.dashboard');

    /*
    |--------------------------------------------------------------------------
    | Cashier/Web API Routes
    |--------------------------------------------------------------------------
    */

    // Endpoint AJAX kasir berbasis session dengan prefix /cashier-api.
    Route::prefix('cashier-api')->group(function () {
        // Mengambil data user yang sedang login untuk tampilan kasir.
        Route::get('/user', function (Illuminate\Http\Request $request) {
            return $request->user();
        })->name('cashier.api.user');

        // Mengambil kategori aktif untuk filter produk kasir.
        Route::get('/categories', [\App\Http\Controllers\Api\Admin\CategoryController::class, 'index'])
            ->name('cashier.api.categories.index');

        // Mengambil produk aktif beserta resep dan ketersediaan porsinya.
        Route::get('/products', [\App\Http\Controllers\Api\Admin\ProductAndRecipeController::class, 'index'])
            ->name('cashier.api.products.index');

        // Membuat transaksi baru, baik pending maupun langsung selesai.
        Route::post('/transactions', [\App\Http\Controllers\Api\Cashier\TransactionController::class, 'store'])
            ->name('cashier.api.transactions.store');

        // Mengambil daftar transaksi untuk riwayat dan pesanan pending.
        Route::get('/transactions', [\App\Http\Controllers\Api\Cashier\TransactionController::class, 'index'])
            ->name('cashier.api.transactions.index');

        // Mengubah isi pesanan pending dan menghitung ulang stok.
        Route::put('/transactions/{id}/update', [\App\Http\Controllers\Api\Cashier\TransactionController::class, 'update'])
            ->name('cashier.api.transactions.update');

        // Melunasi pesanan pending menjadi transaksi completed.
        Route::post('/transactions/{id}/pay', [\App\Http\Controllers\Api\Cashier\TransactionController::class, 'pay'])
            ->name('cashier.api.transactions.pay');

        // Membatalkan pesanan pending dan mengembalikan stok bahan.
        Route::post('/transactions/{id}/cancel', [\App\Http\Controllers\Api\Cashier\TransactionController::class, 'cancel'])
            ->name('cashier.api.transactions.cancel');
    });

    // Menampilkan halaman struk transaksi.
    Route::get('/cashier/struk/{id}', [\App\Http\Controllers\Api\Cashier\StrukController::class, 'index'])
        ->name('cashier.struk');

    // Mengirim link struk ke WhatsApp pelanggan melalui Fonnte.
    Route::post('/cashier/send-whatsapp', [\App\Http\Controllers\Api\Cashier\StrukController::class, 'sendWhatsapp'])
        ->name('cashier.send-whatsapp');

    // Mengunduh struk transaksi dalam format PDF.
    Route::get('/cashier/struk/{id}/download', [\App\Http\Controllers\Api\Cashier\StrukController::class, 'downloadPdf'])
        ->name('cashier.struk.download');
});
