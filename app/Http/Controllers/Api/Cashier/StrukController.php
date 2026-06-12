<?php

namespace App\Http\Controllers\Api\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class StrukController extends Controller
{
    public function index($id)
    {
        $transaction = Transaction::with(['items.product', 'user'])->findOrFail($id);

        return view('cashier.struk', [
            'transaction' => $transaction
        ]);
    }

    public function sendWhatsapp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'transaction_id' => 'required|exists:transactions,transaction_id'
        ]);

        $phone = $this->normalizePhone($request->phone);
        if (!$phone || !preg_match('/^62\d{8,15}$/', $phone)) {
            return response()->json([
                'message' => 'Nomor WhatsApp tidak valid. Gunakan format Indonesia, contoh 081234567890.'
            ], 422);
        }

        $token = config('services.fonnte.token');
        if (!$token) {
            return response()->json([
                'message' => 'Token Fonnte belum dikonfigurasi di .env.'
            ], 500);
        }

        $transaction = Transaction::with(['items.product', 'user'])->findOrFail($request->transaction_id);

        // -- TINGGALKAN LOGIKA GENERATE PDF & TEMP FILE --
        // Kita cukup membuat sebuah URL tautan lokal dari rute baru yang Anda ciptakan kemarin:
        $urlDownload = url('/cashier/struk/' . $transaction->getKey() . '/download');

        // -- SUSUN CAPTION CHAT YANG BERSIH --
        $customerName = $transaction->customer_name ?: 'Pelanggan';
        $caption1 = "Halo Kak " . $customerName . ", terima kasih telah belanja di Warung Bakso Panjang Rezeki.\n";
        $caption1 .= "Total Tagihan : Rp " . number_format($transaction->total_amount, 0, ',', '.') . "\n";
        $caption1 .= "Untuk melihat struk/kuitansi digital secara detail beserta semua pesanan Anda, silakan unduh via Tautan Resmi kami.\n\n";
        $caption1 .= "(Pastikan nomor ini sudah disimpan di Kontak Anda agar Link biru bisa diklik)";

        $caption2 = $urlDownload;

        $logPath = storage_path('logs/wa_debug.log');
        file_put_contents($logPath, "\n-----\n[" . date('Y-m-d H:i:s') . "] START Split WhatsApp for: " . $phone . "\n", FILE_APPEND);

        try {
            // Fungsi helper agar tidak mengulang kode CURL
            $sendFonnte = function($message) use ($phone, $token) {
                $verifySsl = (bool) config('services.fonnte.verify_ssl', true);
                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => 'https://api.fonnte.com/send',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 15,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_SSL_VERIFYPEER => $verifySsl,
                    CURLOPT_SSL_VERIFYHOST => $verifySsl ? 2 : 0,
                    CURLOPT_POSTFIELDS => array(
                        'target' => $phone,
                        'message' => $message,
                    ),
                    CURLOPT_HTTPHEADER => array(
                        'Authorization: ' . $token
                    ),
                ));
                $response = curl_exec($curl);
                $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                $error = curl_error($curl);
                curl_close($curl);
                
                return ['response' => $response, 'httpCode' => $httpCode, 'error' => $error];
            };

            $res1 = $sendFonnte($caption1);
            if ($res1['error']) throw new \Exception("CURL Error 1: " . $res1['error']);
            $this->ensureFonnteAccepted($res1, 'Pesan informasi');
            
            sleep(1);

            $res2 = $sendFonnte($caption2);
            if ($res2['error']) throw new \Exception("CURL Error 2: " . $res2['error']);
            $body = $this->ensureFonnteAccepted($res2, 'Link struk');

            file_put_contents($logPath, "Fonnte SUCCESS Response: " . substr((string) $res2['response'], 0, 200) . "...\n", FILE_APPEND);

            return response()->json([
                'message' => 'Link struk WhatsApp berhasil dikirim.',
                'debug' => $body
            ]);
        } catch (\Throwable $e) {
            file_put_contents($logPath, "EXCEPTION: " . $e->getMessage() . "\n", FILE_APPEND);
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    private function ensureFonnteAccepted(array $result, string $label): array
    {
        $body = json_decode((string) $result['response'], true);

        if ($result['httpCode'] < 200 || $result['httpCode'] >= 300) {
            throw new \Exception($label . ' ditolak Fonnte. HTTP ' . $result['httpCode']);
        }

        if (!is_array($body)) {
            throw new \Exception($label . ' ditolak Fonnte. Response tidak valid.');
        }

        if (($body['status'] ?? true) === false) {
            throw new \Exception($label . ' ditolak Fonnte: ' . ($body['reason'] ?? 'Tidak ada alasan dari Fonnte.'));
        }

        return $body;
    }

    private function normalizePhone(?string $phone): string
    {
        $phone = preg_replace('/[^\d+]/', '', trim((string) $phone));
        if (str_starts_with($phone, '+')) {
            $phone = substr($phone, 1);
        }
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        return $phone;
    }


    public function downloadPdf(Request $request, $id)
    {
        // 1. Cari data transaksi beserta rincian pesanan dan kasirnya
        $transaction = Transaction::with(['items.product', 'user'])->findOrFail($id);
        $paperWidth = $this->resolvePaperWidth($request->input('paper_width', 80));
        $paperPadding = $paperWidth <= 58 ? 3 : 4;

        // 2. Buat objek PDF memakai desain struk yang sama dengan preview.
        $pdf = Pdf::loadView('cashier.struk_pdf', [
            'transaction' => $transaction,
            'paperWidth' => $paperWidth,
            'paperPadding' => $paperPadding,
        ]);

        $paperSize = [
            0,
            0,
            $this->mmToPoints($paperWidth),
            $this->mmToPoints($this->estimateReceiptHeight($transaction)),
        ];

        $pdf->setPaper($paperSize, 'portrait');

        // 3. Beri nama file
        $filename = 'struk-pemesanan-#' . $transaction->getKey() . '.pdf';

        // 4. Lakukan pengunduhan paksa ke browser/HP
        return $pdf->download($filename);
    }

    private function resolvePaperWidth($paperWidth): int
    {
        $paperWidth = (int) $paperWidth;

        if ($paperWidth < 45 || $paperWidth > 120) {
            return 80;
        }

        return $paperWidth;
    }

    private function estimateReceiptHeight(Transaction $transaction): int
    {
        $height = 92;

        if ($transaction->customer_name) {
            $height += 6;
        }

        if ($transaction->payment_method === 'cash') {
            $height += 11;
        }

        foreach ($transaction->items as $item) {
            $height += 13;

            if ($item->note) {
                $height += 3 + (count(explode(' | ', $item->note)) * 5);
            }
        }

        return max(130, $height);
    }

    private function mmToPoints(int $millimeters): float
    {
        return $millimeters * 72 / 25.4;
    }

}
