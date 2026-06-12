<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {
    return view('login');
})->name('login')->middleware('guest');

Route::post('/login', [\App\Http\Controllers\LoginController::class, 'authenticate'])->name('login.post');

Route::post('/logout', [\App\Http\Controllers\LoginController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::prefix('admin')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Api\Admin\DashboardController::class, 'index'])->name('admin.dashboard');
        Route::get('/categories', function () {
            return view('admin.categories');
        });
        // Rute khusus (digantikan oleh apiResource product-recipes)

        // Obsolete inventory route removed

        Route::get('/stok', function () {
            return view('admin.ingredients');
        })->name('admin.stok');

        Route::get('/recipes', function () {
            return view('admin.recipes');
        })->name('admin.recipes');

        Route::get('/products', function () {
            return view('admin.products');
        });

        Route::get('/users', function () {
            return view('admin.users');
        })->name('admin.users');

        Route::get('/incomes', function () {
            return view('admin.incomes');
        })->name('admin.incomes');

        Route::get('/expenses', function () {
            return view('admin.expenses');
        })->name('admin.expenses');

        Route::get('/reports', [\App\Http\Controllers\Api\Admin\ReportController::class, 'index'])->name('admin.reports');
        Route::post('/reports/preview', [\App\Http\Controllers\Api\Admin\ReportController::class, 'preview'])->name('admin.reports.preview');
        Route::post('/reports/export', [\App\Http\Controllers\Api\Admin\ReportController::class, 'export'])->name('admin.reports.export');

        // JSON API endpoints for Datatables/Fetch (Session Authenticated)
        Route::apiResource('api/categories', \App\Http\Controllers\Api\Admin\CategoryController::class);
        
        Route::get('api/product-recipes', [\App\Http\Controllers\Api\Admin\ProductAndRecipeController::class, 'index']);
        Route::post('api/product-recipes', [\App\Http\Controllers\Api\Admin\ProductAndRecipeController::class, 'store']);
        Route::post('api/product-recipes/{id}', [\App\Http\Controllers\Api\Admin\ProductAndRecipeController::class, 'update']);
        Route::delete('api/product-recipes/{id}', [\App\Http\Controllers\Api\Admin\ProductAndRecipeController::class, 'destroy']);
        
        Route::apiResource('api/users', \App\Http\Controllers\Api\Admin\UserController::class);
        Route::apiResource('api/expenses', \App\Http\Controllers\Api\Admin\ExpenseController::class);
        Route::apiResource('api/ingredients', \App\Http\Controllers\Api\Admin\IngredientController::class);

    });

    Route::get('/cashier', function () {
        return view('cashier.dashboard');
    });

    // Cashier/Web API Routes (Session Authenticated)
    Route::prefix('cashier-api')->group(function () {
        Route::get('/user', function (Illuminate\Http\Request $request) {
            return $request->user();
        });

        // Read-only access for cashier interface
        Route::get('/categories', [\App\Http\Controllers\Api\Admin\CategoryController::class, 'index']);
        Route::get('/products', [\App\Http\Controllers\Api\Admin\ProductAndRecipeController::class, 'index']);
        Route::post('/transactions', [\App\Http\Controllers\Api\Cashier\TransactionController::class, 'store']);
        Route::get('/transactions', [\App\Http\Controllers\Api\Cashier\TransactionController::class, 'index']);
        Route::put('/transactions/{id}/update', [\App\Http\Controllers\Api\Cashier\TransactionController::class, 'update']);
        Route::post('/transactions/{id}/pay', [\App\Http\Controllers\Api\Cashier\TransactionController::class, 'pay']);
        Route::post('/transactions/{id}/cancel', [\App\Http\Controllers\Api\Cashier\TransactionController::class, 'cancel']);
        // Note: Using Admin Controllers for read access is fine if logic is standard
    });
    // Route Struk & WhatsApp
    Route::get('/cashier/struk/{id}', [\App\Http\Controllers\Api\Cashier\StrukController::class, 'index'])->name('cashier.struk');
    Route::post('/cashier/send-whatsapp', [\App\Http\Controllers\Api\Cashier\StrukController::class, 'sendWhatsapp']);
    Route::get('/cashier/struk/{id}/download', [\App\Http\Controllers\Api\Cashier\StrukController::class, 'downloadPdf'])->name('cashier.struk.download');
});
