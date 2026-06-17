{{-- Modal alert dan konfirmasi untuk halaman kasir.
    Dipakai oleh JavaScript di cashier/dashboard.blade.php melalui fungsi:
    - showAlertDialog(message): menampilkan pesan informasi/error dengan satu tombol.
    - showConfirmDialog(message): meminta persetujuan kasir dengan tombol Ya/Tidak.

    Dipakai juga oleh cashier/struk.blade.php melalui showStrukAlertDialog(message)
    untuk menampilkan pesan hasil kirim struk WhatsApp.

    Contoh alert:
    - Produk atau kategori sedang inaktif.
    - Stok bahan kurang.
    - Keranjang masih kosong.
    - Metode pembayaran belum dipilih.
    - Uang tunai kurang.
    - Pesanan atau transaksi berhasil/gagal diproses.
    - Link struk WhatsApp berhasil/gagal dikirim.
    - Terjadi kesalahan koneksi/server.

    Contoh konfirmasi:
    - Hapus produk dari keranjang.
    - Timpa keranjang yang sedang aktif.
    - Batalkan pesanan dan kembalikan stok.
--}}
{{-- Overlay modal alert/konfirmasi kasir --}}
<div id="cashierAlertModal"
    class="fixed inset-0 bg-black/60 hidden items-center justify-center z-[130] backdrop-blur-sm p-4">
    {{-- Kotak utama modal alert/konfirmasi --}}
    <div
        class="w-full max-w-sm bg-white dark:bg-[#111] border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl overflow-hidden">
        {{-- Area teks pesan yang diisi dari parameter message pada JavaScript --}}
        <div class="p-7 text-center">
            <h3 id="cashierAlertMessage" class="text-base font-bold text-slate-900 dark:text-white leading-relaxed">
            </h3>
        </div>
        {{-- Area tombol alert/konfirmasi yang ditampilkan sesuai mode oleh JavaScript --}}
        <div id="cashierAlertButtons"
            class="px-5 py-4 bg-slate-50 dark:bg-[#0a0a0a] border-t border-slate-100 dark:border-slate-800 flex gap-3">
            {{-- Tombol untuk mode alert satu pesan --}}
            <div id="cashierAlertOkActions" class="hidden w-full">
                <button type="button" id="cashierAlertOk"
                    class="flex w-full items-center justify-center gap-2 rounded-xl bg-blue-500 py-3 font-bold text-white transition-transform duration-200 ease-in-out hover:bg-blue-600 active:scale-95">
                    <x-icons.check-circle class="h-5 w-5" />
                    Tutup
                </button>
            </div>

            {{-- Tombol untuk mode konfirmasi Ya/Tidak --}}
            <div id="cashierAlertConfirmActions" class="hidden w-full gap-3">
                <button type="button" id="cashierAlertNo"
                    class="flex w-full items-center justify-center gap-2 rounded-xl bg-red-500 py-3 font-bold text-white transition-transform duration-200 ease-in-out hover:bg-red-600 active:scale-95">
                    <x-icons.x-circle class="h-5 w-5" />
                    Batal
                </button>
                <button type="button" id="cashierAlertYes"
                    class="flex w-full items-center justify-center gap-2 rounded-xl bg-green-500 py-3 font-bold text-white transition-transform duration-200 ease-in-out hover:bg-green-600 active:scale-95">
                    <x-icons.check-circle class="h-5 w-5" />
                    Ya
                </button>
            </div>
        </div>
    </div>
</div>
