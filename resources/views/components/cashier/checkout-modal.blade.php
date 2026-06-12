<!-- MODAL PEMBAYARAN -->
{{-- Overlay modal pembayaran --}}
<div id="checkoutModal"
    class="fixed inset-0 bg-black/60 hidden items-center justify-center z-[100] backdrop-blur-sm">
    {{-- Kotak utama modal pembayaran --}}
    <div class="bg-white dark:bg-[#111] rounded-3xl w-full max-w-[480px] overflow-hidden border border-slate-200 dark:border-slate-800 shadow-2xl"
        id="checkoutModalContent">

        {{-- Header modal: total tagihan dan metode pembayaran --}}
        <div class="px-8 py-6 text-center border-b border-slate-100 dark:border-slate-800">
            {{-- Label total pembayaran --}}
            <div class="text-slate-500 dark:text-slate-400 font-semibold mb-2">Total Pembayaran</div>
            {{-- Nominal total pembayaran, diisi melalui JavaScript --}}
            <div id="modalTotal" class="text-4xl font-black text-slate-900 dark:text-white tracking-tight">Rp 0
            </div>
            {{-- Informasi metode pembayaran terpilih --}}
            <div class="text-sm font-semibold text-slate-600 dark:text-slate-300 mt-2">Metode: <span
                    id="displayMethod" class="text-blue-600 dark:text-blue-400">-</span></div>
        </div>

        {{-- Body modal: tampilan tunai atau QRIS --}}
        <div class="p-8">
            {{-- View pembayaran tunai --}}
            <div id="viewCash" class="hidden">
                {{-- Input nominal uang tunai --}}
                <div class="relative mb-6">
                    <span
                        class="absolute left-5 top-1/2 -translate-y-1/2 text-xl font-bold text-slate-400">Rp</span>
                    <input type="number" id="cashAmount"
                        class="w-full py-4 pl-14 pr-4 text-2xl font-bold text-slate-900 dark:text-white bg-slate-50 dark:bg-[#1a1a1a] border-2 border-slate-200 dark:border-slate-700 rounded-2xl outline-none focus:border-blue-500 focus:bg-white dark:focus:bg-[#111] [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                        placeholder="0">
                </div>
                {{-- Tombol nominal cepat untuk uang tunai --}}
                <div class="grid grid-cols-3 gap-3 mb-6">
                    <button
                        class="py-3 bg-white dark:bg-[#111] border border-slate-200 dark:border-slate-700 rounded-xl font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-[#1a1a1a] hover:border-blue-500 hover:text-blue-600 transition-transform duration-200 ease-in-out"
                        onclick="setCash('exact')">Uang Pas</button>
                    <button
                        class="py-3 bg-white dark:bg-[#111] border border-slate-200 dark:border-slate-700 rounded-xl font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-[#1a1a1a] hover:border-blue-500 hover:text-blue-600 transition-transform duration-200 ease-in-out"
                        onclick="setCash(20000)">20K</button>
                    <button
                        class="py-3 bg-white dark:bg-[#111] border border-slate-200 dark:border-slate-700 rounded-xl font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-[#1a1a1a] hover:border-blue-500 hover:text-blue-600 transition-transform duration-200 ease-in-out"
                        onclick="setCash(50000)">50K</button>
                    <button
                        class="py-3 bg-white dark:bg-[#111] border border-slate-200 dark:border-slate-700 rounded-xl font-bold text-slate-600 dark:text-slate-300 col-span-3 hover:bg-slate-50 dark:hover:bg-[#1a1a1a] hover:border-blue-500 hover:text-blue-600 transition-transform duration-200 ease-in-out"
                        onclick="setCash(100000)">100.000</button>
                </div>
                {{-- Tampilan hasil kembalian tunai --}}
                <div
                    class="bg-slate-50 dark:bg-[#1a1a1a] border border-dashed border-slate-300 dark:border-slate-700 rounded-2xl p-5 text-center">
                    {{-- Label kembalian --}}
                    <div class="text-sm font-semibold text-slate-500 dark:text-slate-400 mb-1">Kembalian</div>
                    {{-- Nominal kembalian, diisi melalui JavaScript --}}
                    <div id="changeAmount" class="text-3xl font-bold text-slate-900 dark:text-white">Rp 0</div>
                </div>
            </div>

            {{-- View pembayaran QRIS --}}
            <div id="viewQris" class="hidden text-center py-4">
                {{-- Area gambar QRIS --}}
                <div
                    class="w-[200px] h-[200px] mx-auto mb-6 bg-slate-50 dark:bg-[#222] rounded-2xl flex items-center justify-center p-2">
                    <img src="/qris.png" alt="QRIS" class="max-w-full max-h-full rounded-xl">
                </div>
                <p class="text-slate-500 dark:text-slate-400 font-medium">Silahkan di Scan untuk Melakukan
                    Pembayaran</p>
            </div>
        </div>

        {{-- Footer modal: opsi cetak struk dan tombol aksi pembayaran --}}
        <div
            class="px-8 py-6 bg-slate-50 dark:bg-[#0a0a0a] border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" id="printReceipt"
                    class="w-5 h-5 accent-slate-900 dark:accent-blue-500 cursor-pointer" checked>
                <span class="font-medium text-slate-600 dark:text-slate-300">Cetak Struk</span>
            </label>
            {{-- Tombol batal dan selesai pembayaran --}}
            <div class="flex gap-3">
                <button
                    class="px-6 py-3 font-bold text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-transform duration-200 ease-in-out"
                    onclick="closeModal('checkoutModal')">Batal</button>
                <button
                    class="px-8 py-3 bg-slate-900 dark:bg-blue-600 text-white font-bold rounded-xl hover:bg-slate-800 dark:hover:bg-blue-700 transition-transform duration-200 ease-in-out"
                    onclick="processPayment()">Selesai</button>
            </div>
        </div>
    </div>
</div>
