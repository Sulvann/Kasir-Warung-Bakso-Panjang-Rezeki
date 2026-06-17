<?php

namespace App\Http\Controllers\Api\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // Mengambil daftar transaksi terbaru beserta detail produk untuk kasir/admin.
    public function index()
    {
        $transactions = Transaction::with('details.product')->latest()->get();
        return response()->json([
            'status' => 'success',
            'data' => $transactions
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    // Membuat transaksi baru, menghitung total, dan mengurangi stok bahan sesuai resep.
    public function store(Request $request)
    {
        $request->validate([
            'status' => 'required|in:pending,completed',
            'items' => 'required|array',
            'items.*.id' => 'required|exists:products,product_id',
            'items.*.quantity' => 'required|integer|min:1',
            'cash_amount' => 'required_if:status,completed|integer|min:0|nullable',
            'payment_method' => 'required_if:status,completed|in:cash,qris|nullable',
            'phone_number' => 'nullable|string',
            'customer_name' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $totalAmount = 0;
            $transactionDetails = [];

            foreach ($request->items as $item) {
                $product = Product::with(['category', 'productIngredients.ingredient'])->find($item['id']);

                if (!$product) {
                    throw new \Exception('Produk tidak ditemukan.');
                }

                if ($product->status === 'inactive') {
                    throw new \Exception('Produk inaktif tidak bisa diproses dalam transaksi baru.');
                }

                if ($product->category && $product->category->status === 'inactive') {
                    throw new \Exception('Produk dengan kategori inaktif tidak bisa diproses dalam transaksi baru.');
                }

                $subtotal = $product->price * $item['quantity'];
                $totalAmount += $subtotal;

                // Kurangi stok bahan baku berdasarkan resep produk
                foreach ($product->productIngredients as $pi) {
                    $ingredient = \App\Models\Ingredient::lockForUpdate()->find($pi->ingredient_id);
                    $needed = $pi->quantity * $item['quantity'];

                    if ($ingredient->stock < $needed) {
                        throw new \Exception(
                            "Bahan '{$ingredient->name}' tidak cukup untuk {$product->name}. " .
                            "Dibutuhkan: {$needed} {$ingredient->unit}, Tersisa: {$ingredient->stock} {$ingredient->unit}"
                        );
                    }

                    $ingredient->stock -= $needed;
                    $ingredient->save();
                }

                $transactionDetails[] = [
                    'product_id' => $product->getKey(),
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                    'subtotal' => $subtotal,
                    'note' => $item['note'] ?? null,
                ];
            }

            // Create Transaction
            $cashAmount = $request->cash_amount ?? 0;

            $transaction = Transaction::create([
                'user_id' => auth()->id(),
                'total_amount' => $totalAmount,
                'cash_amount' => $cashAmount,
                'change_amount' => max(0, $cashAmount - $totalAmount),
                'payment_method' => $request->payment_method ?? 'cash',
                'phone_number' => $request->phone_number,
                'customer_name' => $request->customer_name,
                'status' => $request->status,
            ]);

            // Create Details
            foreach ($transactionDetails as $detail) {
                $detail['transaction_id'] = $transaction->getKey();
                TransactionDetail::create($detail);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Transaksi berhasil',
                'data' => $transaction->load('details.product')
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Display the specified resource.
     */
    // Menampilkan detail satu transaksi beserta produk yang dibeli.
    public function show($id)
    {
        $transaction = Transaction::with('details.product')->find($id);

        if (!$transaction) {
            return response()->json([
                'status' => 'error',
                'message' => 'Transaksi tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $transaction
        ]);
    }

    /**
     * Memodifikasi pesanan yang masih pending (Restock & Deduct Ulang)
     */
    // Mengubah pesanan pending dengan mengembalikan stok lama lalu memotong stok baru.
    public function update(Request $request, $id)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:products,product_id',
            'items.*.quantity' => 'required|integer|min:1',
            'customer_name' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:20',
        ]);

        try {
            DB::beginTransaction();

            $transaction = Transaction::with('details')->find($id);

            if (!$transaction || $transaction->status !== 'pending') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Hanya transaksi pending yang bisa diubah'
                ], 400);
            }

            $existingQuantities = $transaction->details
                ->groupBy('product_id')
                ->map(fn ($details) => $details->sum('quantity'));

            $requestedQuantities = collect($request->items)
                ->groupBy('id')
                ->map(fn ($items) => collect($items)->sum('quantity'));

            // 1. KEMBALIKAN STOK LAMA (Restock Sementara)
            foreach ($transaction->details as $oldDetail) {
                $product = Product::with('productIngredients')->find($oldDetail->product_id);
                foreach ($product->productIngredients as $pi) {
                    $ingredient = \App\Models\Ingredient::lockForUpdate()->find($pi->ingredient_id);
                    $needed = $pi->quantity * $oldDetail->quantity;
                    $ingredient->stock += $needed;
                    $ingredient->save();
                }
            }

            // Hapus detail lama
            TransactionDetail::where('transaction_id', $transaction->getKey())->delete();

            // 2. POTONG ULANG STOK BARU (Deduct Ulang)
            $totalAmount = 0;
            $transactionDetails = [];

            foreach ($request->items as $item) {
                $product = Product::with(['category', 'productIngredients'])->find($item['id']);
                $existingQuantity = (int) ($existingQuantities->get($item['id']) ?? 0);
                $requestedQuantity = (int) ($requestedQuantities->get($item['id']) ?? $item['quantity']);

                if (!$product) {
                    throw new \Exception('Produk tidak ditemukan.');
                }

                if ($product->status === 'inactive' && $requestedQuantity > $existingQuantity) {
                    throw new \Exception('Produk inaktif tidak bisa diproses dalam transaksi.');
                }

                if ($product->category && $product->category->status === 'inactive' && $requestedQuantity > $existingQuantity) {
                    throw new \Exception('Produk dengan kategori inaktif tidak bisa diproses dalam transaksi.');
                }
                
                $subtotal = $product->price * $item['quantity'];
                $totalAmount += $subtotal;

                foreach ($product->productIngredients as $pi) {
                    $ingredient = \App\Models\Ingredient::lockForUpdate()->find($pi->ingredient_id);
                    $needed = $pi->quantity * $item['quantity'];

                    if ($ingredient->stock < $needed) {
                        throw new \Exception(
                            "Bahan '{$ingredient->name}' tidak cukup untuk {$product->name}."
                        );
                    }

                    $ingredient->stock -= $needed;
                    $ingredient->save();
                }

                $transactionDetails[] = [
                    'transaction_id' => $transaction->getKey(),
                    'product_id' => $product->getKey(),
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                    'subtotal' => $subtotal,
                    'note' => $item['note'] ?? null,
                ];
            }

            // Update master transaksi
            $transaction->update([
                'total_amount' => $totalAmount,
                'customer_name' => $request->customer_name ?? $transaction->customer_name,
                'phone_number' => $request->phone_number ?? $transaction->phone_number,
            ]);

            // Insert detail baru
            TransactionDetail::insert($transactionDetails);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Pesanan berhasil diubah',
                'data' => $transaction->load('details.product')
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Melunasi pesanan pending
     */
    // Menyelesaikan pembayaran transaksi pending dan menghitung kembalian jika tunai.
    public function pay(Request $request, $id)
    {
        $request->validate([
            'cash_amount' => 'required|integer|min:0',
            'payment_method' => 'required|in:cash,qris',
        ]);

        try {
            DB::beginTransaction();
            $transaction = Transaction::find($id);

            if (!$transaction || $transaction->status !== 'pending') {
                return response()->json(['status' => 'error', 'message' => 'Transaksi tidak valid atau sudah lunas'], 400);
            }

            if ($request->payment_method === 'cash' && $request->cash_amount < $transaction->total_amount) {
                return response()->json(['status' => 'error', 'message' => 'Uang tunai kurang dari total transaksi'], 400);
            }

            $transaction->update([
                'status' => 'completed',
                'cash_amount' => $request->cash_amount,
                'change_amount' => max(0, $request->cash_amount - $transaction->total_amount),
                'payment_method' => $request->payment_method,
            ]);

            DB::commit();

            return response()->json(['status' => 'success', 'message' => 'Pembayaran berhasil', 'data' => $transaction]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * Membatalkan pesanan pending dan mengembalikan stok
     */
    // Membatalkan transaksi pending serta mengembalikan stok bahan baku ke sistem.
    public function cancel($id)
    {
        try {
            DB::beginTransaction();
            $transaction = Transaction::with('details')->find($id);

            if (!$transaction || $transaction->status !== 'pending') {
                return response()->json(['status' => 'error', 'message' => 'Hanya pesanan pending yang bisa dibatalkan'], 400);
            }

            // KEMBALIKAN STOK PERMANEN
            foreach ($transaction->details as $oldDetail) {
                $product = Product::with('productIngredients')->find($oldDetail->product_id);
                foreach ($product->productIngredients as $pi) {
                    $ingredient = \App\Models\Ingredient::lockForUpdate()->find($pi->ingredient_id);
                    $needed = $pi->quantity * $oldDetail->quantity;
                    $ingredient->stock += $needed;
                    $ingredient->save();
                }
            }

            $transaction->update(['status' => 'cancelled']);

            DB::commit();

            return response()->json(['status' => 'success', 'message' => 'Pesanan dibatalkan, bahan baku telah dikembalikan ke kulkas']);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }
}
