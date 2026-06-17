<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50 dark:bg-[#050505]">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>RealKasir - Dashboard Kasir</title>

    <!-- Tailwind CSS & Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>

<body
    class="h-full w-full flex flex-col overflow-hidden bg-slate-50 dark:bg-[#050505] font-sans antialiased text-slate-800 dark:text-slate-100 [&::-webkit-scrollbar]:w-1.5 [&::-webkit-scrollbar-track]:bg-transparent [&::-webkit-scrollbar-thumb]:bg-slate-300 dark:[&::-webkit-scrollbar-thumb]:bg-slate-700 [&::-webkit-scrollbar-thumb]:rounded-full [&_button]:transition-[background-color,border-color,color,box-shadow,transform,opacity] [&_button]:duration-[220ms] [&_button]:ease-in-out [&_button]:will-change-[transform,box-shadow] [&_button:not(:disabled):hover]:-translate-y-0.5 [&_button:not(:disabled):hover]:shadow-[0_10px_20px_-16px_rgba(15,23,42,0.45)] [&_button:not(:disabled):active]:translate-y-0 [&_button:not(:disabled):active]:scale-[0.98] [&_button:not(:disabled):active]:shadow-[0_6px_14px_-12px_rgba(15,23,42,0.45)] motion-reduce:[&_button]:duration-0 motion-reduce:[&_button:not(:disabled):hover]:transform-none motion-reduce:[&_button:not(:disabled):active]:transform-none">
    {{-- Navigasi utama kasir --}}
    <div class="w-full shrink-0 z-50">
        @include('layouts.navigation')
    </div>

    {{-- Layout utama POS: katalog produk, keranjang, dan pesanan tersimpan --}}
    <div class="grid grid-cols-[50%_30%_20%] flex-1 min-h-0">
        {{-- Panel kiri: pencarian, filter kategori, dan daftar produk --}}
        <div class="flex flex-col p-6 h-full overflow-hidden">
            {{-- Filter katalog produk --}}
            <div class="flex gap-4 mb-6 shrink-0">
                <input type="text" id="searchInput"
                    class="flex-1 py-3 px-4 border border-slate-200 dark:border-slate-800 rounded-xl text-base bg-white dark:bg-[#111] dark:text-white outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Cari nama produk...">
                <select id="categoryFilter"
                    class="py-3 px-4 border border-slate-200 dark:border-slate-800 rounded-xl text-base bg-white dark:bg-[#111] dark:text-white outline-none cursor-pointer min-w-[200px]">
                    <option value="all">Semua Kategori</option>
                </select>
            </div>

            {{-- Grid kartu produk, diisi melalui JavaScript --}}
            <div id="productGrid"
                class="grid grid-cols-[repeat(auto-fill,minmax(160px,1fr))] auto-rows-fr gap-5 overflow-y-auto pr-2 pb-8"></div>
        </div>

        {{-- Panel tengah: keranjang dan aksi pembayaran --}}
        <div
            class="bg-white dark:bg-[#0a0a0a] border-l border-slate-200 dark:border-slate-800 flex flex-col h-full overflow-hidden">
            {{-- Daftar item keranjang, diisi melalui JavaScript --}}
            <div id="cartItems" class="flex-1 overflow-y-auto p-5"></div>

            {{-- Footer keranjang: subtotal, data pelanggan, metode bayar, dan tombol aksi --}}
            <div class="p-6 bg-slate-50 dark:bg-[#000000] border-t border-slate-200 dark:border-slate-800 shrink-0">
                {{-- Ringkasan subtotal keranjang --}}
                <div class="flex justify-between text-2xl font-black text-slate-900 dark:text-white mb-6">
                    <span>Sub Total</span><span id="displayTotalReal">Rp 0</span>
                </div>

                {{-- Form data pelanggan --}}
                <div class="flex gap-3 mb-3">
                    <input type="text" id="customerName" placeholder="Ketik Nama Pelanggan..."
                        class="w-full py-3 px-4 border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#111] dark:text-white rounded-xl outline-none focus:ring-2 focus:ring-blue-500 text-sm placeholder:text-slate-400">

                    <input type="text" id="customerPhone" placeholder="No. Telepon / WhatsApp..."
                        class="w-full py-3 px-4 border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#111] dark:text-white rounded-xl outline-none focus:ring-2 focus:ring-blue-500 text-sm placeholder:text-slate-400">
                </div>

                {{-- Pilihan metode pembayaran --}}
                <select id="paymentMethod"
                    class="w-full py-3 px-4 border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#111] dark:text-white rounded-xl mb-4 outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="" disabled selected>Pilih Metode Pembayaran</option>
                    <option value="cash">Tunai (Cash)</option>
                    <option value="qris">QRIS</option>
                </select>

                {{-- Tombol simpan pesanan pending dan lanjut bayar --}}
                <div class="grid grid-cols-[1fr_2fr] gap-3">
                    <button id="btnSaveOrder"
                        class="py-3.5 px-4 rounded-xl font-bold bg-slate-200 text-slate-700 dark:bg-slate-800 dark:text-slate-200 hover:bg-slate-300 dark:hover:bg-slate-700 disabled:opacity-50 disabled:cursor-not-allowed transition-transform duration-200 ease-in-out"
                        onclick="saveOrder()">Simpan</button>
                    <button id="btnPay"
                        class="py-3.5 px-4 rounded-xl font-bold bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-transform duration-200 ease-in-out"
                        onclick="openCheckout()" disabled>Bayar Sekarang</button>
                </div>
            </div>
        </div>

        {{-- Panel kanan: daftar pesanan tersimpan atau pending --}}
        <div
            class="bg-white dark:bg-[#0a0a0a] border-l border-slate-200 dark:border-slate-800 flex flex-col h-full overflow-hidden">
            {{-- Pencarian pesanan tersimpan --}}
            <div class="p-4 pb-0">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <x-heroicon-o-magnifying-glass class="w-5 h-5 text-slate-400" />
                    </div>
                    <input type="text" id="searchSaved" onkeyup="renderSavedPanel()" placeholder="Cari pesanan..."
                        class="w-full pl-10 pr-4 py-3 border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-[#111] dark:text-white rounded-xl outline-none focus:ring-2 focus:ring-blue-500 text-sm placeholder:text-slate-400 font-medium">
                </div>
            </div>

            {{-- List pesanan pending, diisi melalui JavaScript --}}
            <div id="savedOrdersList" class="flex-1 overflow-y-auto p-4 space-y-3"></div>
        </div>
    </div>

    {{-- Modal pembayaran, catatan pesanan, dan alert kasir --}}
    <x-cashier.checkout-modal />
    <x-cashier.note-modal />
    <x-cashier.alert-modal />

    <script>
        // State utama halaman kasir.
        const API_URL = '/cashier-api'; let products = []; let cart = []; let categories = []; let currentTransactionId = null; let savedOrdersData = [];
        const els = { grid: document.getElementById('productGrid'), cart: document.getElementById('cartItems'), search: document.getElementById('searchInput'), catFilter: document.getElementById('categoryFilter'), totalReal: document.getElementById('displayTotalReal'), payBtn: document.getElementById('btnPay') };
        const productKey = p => p.product_id ?? p.id;
        const categoryKey = c => c.category_id ?? c.id;
        const ingredientKey = ing => ing.ingredient_id ?? ing.id;
        const transactionKey = trx => trx.transaction_id ?? trx.id;
        const fallbackProductImage = 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" width="150" height="150" viewBox="0 0 150 150"><rect width="150" height="150" fill="#f1f5f9"/><path d="M42 96l22-26 18 20 11-13 18 19H42z" fill="#cbd5e1"/><circle cx="96" cy="54" r="10" fill="#cbd5e1"/></svg>');

        // Modal alert sederhana untuk pesan sukses/error.
        function showAlertDialog(message) {
            return new Promise(resolve => {
                const modal = document.getElementById('cashierAlertModal');
                const messageEl = document.getElementById('cashierAlertMessage');
                const okActions = document.getElementById('cashierAlertOkActions');
                const confirmActions = document.getElementById('cashierAlertConfirmActions');

                messageEl.textContent = message;
                okActions.classList.remove('hidden');
                confirmActions.classList.add('hidden');
                confirmActions.classList.remove('flex');

                modal.classList.replace('hidden', 'flex');
                document.getElementById('cashierAlertOk').onclick = () => {
                    modal.classList.replace('flex', 'hidden');
                    resolve(true);
                };
            });
        }

        // Modal konfirmasi untuk aksi yang butuh persetujuan kasir.
        function showConfirmDialog(message) {
            return new Promise(resolve => {
                const modal = document.getElementById('cashierAlertModal');
                const messageEl = document.getElementById('cashierAlertMessage');
                const okActions = document.getElementById('cashierAlertOkActions');
                const confirmActions = document.getElementById('cashierAlertConfirmActions');

                messageEl.textContent = message;
                okActions.classList.add('hidden');
                confirmActions.classList.remove('hidden');
                confirmActions.classList.add('flex');

                const close = result => {
                    modal.classList.replace('flex', 'hidden');
                    resolve(result);
                };

                modal.classList.replace('hidden', 'flex');
                document.getElementById('cashierAlertNo').onclick = () => close(false);
                document.getElementById('cashierAlertYes').onclick = () => close(true);
            });
        }

        // Memuat data awal, keranjang, dan panel pesanan saat halaman siap.
        document.addEventListener('DOMContentLoaded', () => { loadInitialData(); renderCart(); renderSavedPanel(); });

        // Ambil kategori dan produk awal dari API kasir.
        async function loadInitialData() {
            try {
                const h = { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' };
                const resCats = await fetch('/cashier-api/categories', { headers: h });
                if (resCats.status === 401) return location.href = '/login';
                categories = (await resCats.json()).data || [];
                els.catFilter.innerHTML = '<option value="all">Semua Kategori</option>' + categories.map(c => `<option value="${categoryKey(c)}">${c.name}</option>`).join('');
                const resProd = await fetch('/cashier-api/products', { headers: h });
                products = (await resProd.json()).data || [];
                recalculateMaxYields();
                filterAndRender();
            } catch (e) { els.grid.innerHTML = '<div class="col-span-full text-center p-8 text-red-500 font-bold">Gagal memuat. <button onclick="location.reload()" class="underline hover:text-red-700 transition-transform duration-200 ease-in-out">Refresh</button></div>'; }
        }

        // Menyaring produk berdasarkan pencarian dan kategori.
        function filterAndRender() {
            const t = els.search.value.toLowerCase(), cId = els.catFilter.value;
            renderProducts(products.filter(p => p.name.toLowerCase().includes(t) && (cId === 'all' || p.category_id == cId)));
        }
        els.search.addEventListener('keyup', filterAndRender); els.catFilter.addEventListener('change', filterAndRender);

        // Mengubah angka menjadi format mata uang Rupiah.
        function formatRupiah(num) { return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(num); }

        // Render kartu produk pada panel kiri.
        function renderProducts(list) {
            if (list.length === 0) return els.grid.innerHTML = '<div class="col-span-full text-center p-8 text-slate-400 font-medium">Barang Kosong</div>';
            els.grid.innerHTML = list.map(p => {
                const img = p.image ? "/storage/" + p.image : fallbackProductImage;
                const isOOS = p.max_yield !== null && p.max_yield <= 0;
                const hasInactiveCategory = p.category && p.category.status === 'inactive';
                const isInactive = p.status === 'inactive' || hasInactiveCategory;
                const isDisabled = isOOS || isInactive;
                const overlayLabel = hasInactiveCategory ? 'Kategori Inaktif' : (isInactive ? 'Inaktif' : 'Habis');
                const cardBackground = isInactive ? 'bg-slate-700 dark:bg-slate-700' : 'bg-white dark:bg-[#111]';
                return `<div class="h-full ${cardBackground} rounded-2xl overflow-hidden border border-black/20 dark:border-slate-800 flex flex-col ${isDisabled ? 'opacity-55 grayscale cursor-not-allowed' : 'cursor-pointer'}" onclick="${!isDisabled ? `addToCart(${productKey(p)})` : ''}">
                        <div class="aspect-square w-full shrink-0 bg-slate-100 dark:bg-[#1a1a1a] relative overflow-hidden"><img src="${img}" class="absolute inset-0 block w-full h-full object-cover object-center">${isDisabled ? `<div class="absolute inset-0 bg-slate-900/55 flex items-center justify-center"><div class="${isInactive ? 'bg-slate-700 border border-slate-500' : 'bg-red-500'} text-white font-bold text-xs px-3 py-1 rounded-full uppercase">${overlayLabel}</div></div>` : ''}</div>
                        <div class="p-4 flex-1 min-h-[120px] flex flex-col items-center justify-between text-center"><div class="min-h-[2.75rem] flex items-center justify-center font-bold text-slate-900 dark:text-white mb-1 leading-snug line-clamp-2">${p.name}</div>
                        <div class="flex flex-col items-center justify-center gap-2 mt-3"><div class="text-blue-600 dark:text-blue-400 font-black tracking-tight">${formatRupiah(p.price)}</div><div class="shrink-0 whitespace-nowrap text-[10px] font-bold text-slate-500 bg-slate-100 dark:bg-[#222] px-2 py-1 rounded border dark:border-slate-700">${p.max_yield !== null ? p.max_yield + ' Porsi' : 'Tidak terbatas'}</div></div></div></div>`;
            }).join('');
        }

        // Hitung sisa porsi tiap produk berdasarkan stok bahan dan isi keranjang.
        function recalculateMaxYields() {
            const usage = {};
            for (const item of cart) { 
                const p = products.find(x => productKey(x) === item.id); 
                if (p && p.ingredients) p.ingredients.forEach(ing => usage[ingredientKey(ing)] = (usage[ingredientKey(ing)] || 0) + (parseFloat(ing.pivot.quantity) * item.quantity)); 
            }

            for (const p of products) {
                if (!p.ingredients || p.ingredients.length === 0) { p.max_yield = null; continue; }
                let min = Infinity;
                for (const ing of p.ingredients) { 
                    const qty = parseFloat(ing.pivot.quantity);
                    if (qty > 0) min = Math.min(min, Math.floor((ing.stock - (usage[ingredientKey(ing)] || 0)) / qty)); 
                }
                p.max_yield = min === Infinity ? 0 : Math.max(0, min);
            }
        }

        // Tambahkan produk ke keranjang jika produk aktif dan stok mencukupi.
        window.addToCart = (id) => {
            const p = products.find(x => productKey(x) === id);
            if (!p || p.status === 'inactive') return showAlertDialog('Produk inaktif tidak bisa ditambahkan ke keranjang');
            if (p.category && p.category.status === 'inactive') return showAlertDialog('Produk dengan kategori inaktif tidak bisa ditambahkan ke keranjang');
            const e = cart.find(x => x.id === id), l = p.max_yield;
            if (e) { if (l === null || l > 0) e.quantity++; else return showAlertDialog('Stok bahan kurang'); }
            else { if (l !== null && l <= 0) return showAlertDialog('Stok bahan kurang'); cart.push({ ...p, id: productKey(p), quantity: 1 }); }
            recalculateMaxYields(); filterAndRender(); renderCart();
        };

        // Render isi keranjang, tombol jumlah, catatan, dan total pembayaran.
        function renderCart() {
            if (cart.length === 0) {
                els.totalReal.textContent = 'Rp 0'; els.payBtn.disabled = true;
                els.cart.innerHTML = `<div class="h-full flex flex-col justify-center items-center text-center p-6 text-slate-400"><div class="w-20 h-20 bg-slate-100 dark:bg-[#111] rounded-full flex items-center justify-center mb-4"><x-heroicon-o-shopping-cart class="w-10 h-10" /></div><h3 class="font-bold text-slate-800 dark:text-white mb-1">Keranjang Kosong</h3></div>`;
                return;
            }
            let total = 0;
            els.cart.innerHTML = cart.map(item => {
                total += item.price * item.quantity;

                let groupHtml = '';
                if (item.notesArray && item.notesArray.length > 0) {
                    const noteCounts = {};
                    let emptyCount = 0;
                    for (let i = 0; i < item.quantity; i++) {
                        const n = (item.notesArray[i] || '').trim();
                        if (n === '') emptyCount++;
                        else { noteCounts[n] = (noteCounts[n] || 0) + 1; }
                    }
                    for (const [nText, nCount] of Object.entries(noteCounts)) {
                        groupHtml += `<div class="mb-2.5 bg-slate-50 dark:bg-[#1a1a1a] border border-slate-100 dark:border-slate-800 p-2.5 rounded-xl">
                            <div class="font-bold text-slate-800 dark:text-slate-300 text-sm">${item.name} (${nCount} porsi)</div>
                            <div class="italic text-blue-600 dark:text-blue-400 text-xs mt-0.5 font-semibold">Catatan "${nText}"</div>
                        </div>`;
                    }
                    if (emptyCount > 0) {
                        groupHtml += `<div class="mb-2.5 bg-slate-50 dark:bg-[#1a1a1a] border border-slate-100 dark:border-slate-800 p-2.5 rounded-xl">
                            <div class="font-bold text-slate-800 dark:text-slate-300 text-sm">${item.name} (${emptyCount} porsi) <span class="font-normal opacity-70">lengkap/normal</span></div>
                        </div>`;
                    }
                }

                return `<div class="flex flex-col p-4 border border-slate-200 dark:border-slate-800 rounded-2xl mb-3 bg-white dark:bg-[#111] shadow-sm">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <div class="font-bold text-slate-900 dark:text-white">${item.name}</div>
                            <div class="text-slate-500 text-sm font-medium">${formatRupiah(item.price)}</div>
                        </div>
                        <div class="font-black">${formatRupiah(item.price * item.quantity)}</div>
                    </div>
                    ${groupHtml}
                    <div class="flex flex-col gap-2 mt-1">
                        <div class="flex justify-end gap-1 bg-slate-100 dark:bg-[#1a1a1a] p-1 rounded-xl shrink-0 h-fit w-fit self-end">
                            <button onclick="updateQty(${item.id}, -1)" class="w-8 h-8 flex items-center justify-center bg-white dark:bg-[#222] border dark:border-slate-700 rounded-lg font-bold hover:bg-slate-100 dark:hover:bg-[#333] transition-colors duration-200 ease-in-out">-</button>
                            <input type="number" value="${item.quantity}" onchange="manualQty(${item.id}, this.value)" class="w-10 text-center bg-transparent font-bold outline-none [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                            <button onclick="updateQty(${item.id}, 1)" class="w-8 h-8 flex items-center justify-center bg-white dark:bg-[#222] border dark:border-slate-700 rounded-lg font-bold hover:bg-slate-100 dark:hover:bg-[#333] transition-colors duration-200 ease-in-out">+</button>
                        </div>
                        <div class="grid grid-cols-[1fr_auto] gap-2">
                            <button onclick="openNoteModal(${item.id})" class="w-full py-3 bg-[#1e3a8a] hover:bg-[#172f73] text-white font-bold rounded-xl shadow-sm transition-all duration-300 ease-in-out flex items-center justify-center gap-2 text-sm">
                               <x-heroicon-o-pencil-square class="w-5 h-5" /> Catatan (${item.quantity} porsi)
                            </button>
                            <button onclick="removeCartProduct(${item.id})" class="w-12 py-3 bg-red-50 hover:bg-red-100 text-red-600 hover:text-red-700 border border-red-100 font-bold rounded-xl shadow-sm transition-transform duration-300 ease-in-out flex items-center justify-center text-sm" title="Hapus produk">
                               <x-heroicon-o-trash class="w-5 h-5" />
                            </button>
                        </div>
                    </div>
                </div>`;
            }).join('');
            els.totalReal.textContent = formatRupiah(total);
            els.payBtn.disabled = false;
        }

        // Batalkan transaksi pending otomatis jika semua item dihapus dari keranjang.
        async function checkEmptyTransaction() {
            if (cart.length === 0 && currentTransactionId) {
                try {
                    const r = await fetch(`/cashier-api/transactions/${currentTransactionId}/cancel`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                    });
                    if (r.ok) {
                        currentTransactionId = null;
                        document.getElementById('customerName').value = '';
                        document.getElementById('customerPhone').value = '';
                        document.getElementById('paymentMethod').value = '';
                        await loadInitialData();
                        await renderSavedPanel();
                        showAlertDialog('Semua barang dihapus. Pesanan otomatis dibatalkan.');
                    }
                } catch(e) {}
            }
        }

        // Ubah jumlah item melalui tombol plus/minus.
        window.updateQty = async (id, d) => {
            const i = cart.find(c => c.id === id), p = products.find(x => productKey(x) === id), n = i.quantity + d, l = p.max_yield;
            if (n <= 0) { 
                if (await showConfirmDialog('Hapus produk ini dari keranjang?')) {
                    cart = cart.filter(c => c.id !== id); 
                    await checkEmptyTransaction();
                }
            }
            else if (d > 0 && l !== null && n > l + i.quantity) await showAlertDialog('Stok kurang'); else i.quantity = n;
            recalculateMaxYields(); filterAndRender(); renderCart();
        };

        // Hapus satu produk dari keranjang.
        window.removeCartProduct = async (id) => {
            if (!(await showConfirmDialog('Hapus produk ini dari keranjang?'))) return;
            cart = cart.filter(c => c.id !== id);
            await checkEmptyTransaction();
            recalculateMaxYields(); filterAndRender(); renderCart();
        };

        // Ubah jumlah item melalui input angka manual.
        window.manualQty = async (id, v) => {
            const q = parseInt(v), i = cart.find(c => c.id === id), l = products.find(x => productKey(x) === id).max_yield;
            if (isNaN(q) || q < 1) i.quantity = 1; else if (l !== null && q > l + i.quantity) { await showAlertDialog('Stok kurang'); i.quantity = l + i.quantity; } else i.quantity = q;
            recalculateMaxYields(); filterAndRender(); renderCart();
        };

        // Buka modal catatan per porsi untuk produk di keranjang.
        window.openNoteModal = (id) => {
            const item = cart.find(c => c.id === id);
            if (!item) return;
            let html = '';
            for (let i = 0; i < item.quantity; i++) {
                const savedNote = (item.notesArray && item.notesArray[i]) ? item.notesArray[i] : '';
                html += `
                   <div class="mb-5 last:mb-0">
                      <label class="block font-bold text-sm mb-2 text-slate-700 dark:text-slate-300 w-full bg-slate-100 dark:bg-[#222] px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700">
                          <x-heroicon-o-pencil-square class="w-4 h-4 inline-block mr-1 -mt-0.5" /> ${item.name} 
                          <span class="float-right text-xs bg-white dark:bg-slate-800 px-2 py-0.5 rounded shadow-sm text-slate-600 dark:text-slate-400 font-bold border border-slate-200 dark:border-slate-700">Porsi ${i + 1}</span>
                      </label>
                      <input type="text" id="tempNote_${id}_${i}" value="${savedNote}" class="w-full py-3 px-4 border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#111] dark:text-white rounded-xl outline-none focus:ring-2 focus:ring-blue-500 text-sm placeholder:text-slate-400" placeholder="Misal: Tanpa seledri, kuah pisah" />
                   </div>
                `;
            }
            document.getElementById('noteModalList').innerHTML = html;
            document.getElementById('noteModalBtnSave').setAttribute('onclick', `saveNoteModal(${id})`);
            document.getElementById('modalNotePerPorsi').classList.replace('hidden', 'flex');
        };

        // Menutup modal catatan per porsi.
        window.closeNoteModal = () => {
            document.getElementById('modalNotePerPorsi').classList.replace('flex', 'hidden');
        };

        // Simpan catatan per porsi ke item keranjang.
        window.saveNoteModal = (id) => {
            const item = cart.find(c => c.id === id);
            if (!item) return;
            item.notesArray = [];
            let combinedString = [];
            for (let i = 0; i < item.quantity; i++) {
                const inputEl = document.getElementById(`tempNote_${id}_${i}`);
                if (!inputEl) continue;
                const inputVal = inputEl.value;
                item.notesArray[i] = inputVal;
                if (inputVal.trim() !== '') {
                    combinedString.push(`P[${i + 1}]: ${inputVal}`);
                }
            }
            item.note = combinedString.join(" | ");
            closeNoteModal();
            renderCart();
        };

        // Simpan keranjang sebagai pesanan pending.
        window.saveOrder = async () => {
            if (cart.length === 0) return;
            
            const btnSave = document.getElementById('btnSaveOrder');
            const originalText = btnSave.innerHTML;
            btnSave.disabled = true;
            btnSave.innerHTML = `<svg class="animate-spin h-5 w-5 mx-auto text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>`;

            const custName = document.getElementById('customerName').value.trim();
            const custPhone = document.getElementById('customerPhone').value.trim();
            
            const payload = {
                status: 'pending',
                items: cart.map(x => ({ id: x.id, quantity: x.quantity, note: x.note || null })),
                customer_name: custName,
                phone_number: custPhone
            };

            try {
                let url = '/cashier-api/transactions';
                let method = 'POST';
                if (currentTransactionId) {
                    url = `/cashier-api/transactions/${currentTransactionId}/update`;
                    method = 'PUT';
                }

                const r = await fetch(url, {
                    method: method,
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify(payload)
                });
                const d = await r.json();
                if (r.ok) {
                    cart = [];
                    currentTransactionId = null;
                    document.getElementById('customerName').value = '';
                    document.getElementById('customerPhone').value = '';
                    document.getElementById('paymentMethod').value = '';
                    await loadInitialData(); 
                    await renderSavedPanel(); 
                    renderCart(); 
                    showAlertDialog('Pesanan berhasil disimpan di sistem');
                } else {
                    const message = d.message || (d.errors ? Object.values(d.errors).flat().join('\n') : 'Gagal menyimpan pesanan');
                    showAlertDialog(message);
                }
            } catch (e) { 
                showAlertDialog('Terjadi kesalahan koneksi server'); 
            } finally {
                btnSave.disabled = false;
                btnSave.innerHTML = originalText;
            }
        };

        // Render panel kanan berisi daftar pesanan pending yang bisa diambil lagi.
        window.renderSavedPanel = async () => {
            try {
                const r = await fetch('/cashier-api/transactions', { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                const d = await r.json();
                if (!r.ok) return;
                
                savedOrdersData = d.data.filter(x => x.status === 'pending');
                const searchQuery = (document.getElementById('searchSaved') ? document.getElementById('searchSaved').value.toLowerCase() : '');
                let s = savedOrdersData;
                
                if (searchQuery) s = s.filter(o => (o.customer_name || `Trans ${transactionKey(o)}`).toLowerCase().includes(searchQuery));
                
                if (currentTransactionId) {
                    s = s.filter(o => transactionKey(o) !== currentTransactionId);
                }
                
                const c = document.getElementById('savedOrdersList');
                if (s.length === 0) return c.innerHTML = `<div class="text-center py-10 px-4 text-slate-400"><div class="w-16 h-16 bg-slate-100 dark:bg-[#111] rounded-full mx-auto mb-3 flex items-center justify-center"><x-heroicon-o-bookmark class="w-8 h-8 text-slate-300" /></div><h4 class="font-bold">Belum ada pesanan tersimpan</h4></div>`;

                c.innerHTML = s.map(o => {
                    const id = transactionKey(o);
                    const dispName = o.customer_name || `Tamu #${id}`;
                    let itemsHtml = '';
                    const details = o.details || [];
                    details.forEach(item => {
                        itemsHtml += `<div class="mb-3 last:mb-0 border-b border-slate-200 dark:border-slate-700 pb-3 last:border-0 last:pb-0">
                            <div class="flex justify-between items-start"><div class="font-bold text-sm text-slate-900 dark:text-white truncate pr-2">${item.product?.name || 'Produk'}</div><div class="shrink-0 text-slate-500 font-bold bg-slate-200 dark:bg-slate-700 px-2 py-0.5 rounded text-xs">${item.quantity} x</div></div>
                        </div>`;
                    });

                    return `<div class="bg-white dark:bg-[#111] border border-slate-200 dark:border-slate-800 rounded-2xl p-4 mb-3 shadow-sm hover:border-blue-400 dark:hover:border-blue-500 transition-colors">
                        <div class="flex justify-between items-start mb-4 border-b border-slate-100 dark:border-slate-800 pb-3">
                            <div class="flex-1 min-w-0 pr-2">
                                <div class="font-bold text-sm text-slate-900 dark:text-white truncate">Pesanan ${dispName}</div>
                                <div class="text-[10px] text-slate-400 font-medium">${new Date(o.created_at).toLocaleString()}</div>
                            </div>
                        </div>
                        <div class="mb-5 bg-slate-50 dark:bg-[#1a1a1a] p-3 rounded-xl border border-slate-100 dark:border-slate-800">
                            ${itemsHtml}
                        </div>
                        <div class="flex gap-2">
                            <button onclick="restoreOrder(${id})" class="flex-1 bg-slate-900 hover:bg-slate-800 dark:bg-white dark:hover:bg-slate-100 text-white dark:text-slate-900 py-3 rounded-xl text-sm font-bold transition-transform duration-200 ease-in-out flex items-center justify-center gap-2"><x-heroicon-o-arrow-down-tray class="w-4 h-4"/> Ambil</button>
                            <button onclick="deleteSaved(${id})" class="w-12 flex-shrink-0 bg-red-50 hover:bg-red-100 text-red-600 hover:text-red-700 dark:bg-red-900/20 dark:hover:bg-red-900/50 py-3 rounded-xl border border-red-200 dark:border-red-900/30 flex justify-center items-center transition-transform duration-200 ease-in-out"><x-heroicon-o-trash class="w-4 h-4"/></button>
                        </div>
                    </div>`;
                }).join('');
            } catch(e) { console.error('Gagal memuat pesanan tersimpan'); }
        };

        // Ambil kembali pesanan pending ke keranjang aktif.
        window.restoreOrder = async (id) => { 
            const o = savedOrdersData.find(x => transactionKey(x) === id); 
            if (o) { 
                if (cart.length > 0 && !(await showConfirmDialog('Timpa keranjang yang sedang aktif?'))) return; 
                
                cart = o.details.map(d => ({
                    id: d.product_id,
                    name: d.product ? d.product.name : 'Unknown',
                    price: d.price,
                    quantity: d.quantity,
                    note: d.note
                }));
                
                currentTransactionId = id;
                document.getElementById('customerName').value = o.customer_name || ''; 
                document.getElementById('customerPhone').value = o.phone_number || ''; 
                
                filterAndRender(); 
                renderCart(); 
                renderSavedPanel();
            } 
        };
        
        // Batalkan pesanan pending dan kembalikan stoknya.
        window.deleteSaved = async (id) => { 
            if (await showConfirmDialog('Batal dan kembalikan stok pesanan ini?')) { 
                try {
                    const r = await fetch(`/cashier-api/transactions/${id}/cancel`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                    });
                    if (r.ok) {
                        if (currentTransactionId === id) { cart = []; currentTransactionId = null; renderCart(); }
                        await loadInitialData();
                        await renderSavedPanel();
                        showAlertDialog('Pesanan dibatalkan, stok dikembalikan.');
                    } else {
                        const d = await r.json(); showAlertDialog(d.message || 'Gagal menghapus');
                    }
                } catch(e) { showAlertDialog('Terjadi kesalahan koneksi'); }
            } 
        };

        // Buka modal pembayaran berdasarkan metode tunai atau QRIS.
        window.openCheckout = async () => {
            const m = document.getElementById('paymentMethod').value;
            if (!m) return showAlertDialog('Pilih metode pembayaran terlebih dahulu!');
            if (cart.length === 0) return showAlertDialog('Keranjang kasir masih kosong!');

            // Hitung sub-total langsung dari array keranjang
            const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);

            document.getElementById('modalTotal').textContent = formatRupiah(total);
            document.getElementById('modalTotal').dataset.value = total;
            document.getElementById('displayMethod').textContent = m === 'cash' ? 'Tunai' : 'QRIS';

            document.getElementById('viewCash').className = m === 'cash' ? 'block' : 'hidden';
            document.getElementById('viewQris').className = m === 'cash' ? 'hidden' : 'block text-center py-4';
            document.getElementById('changeAmount').textContent = 'Rp 0';
            document.getElementById('cashAmount').value = '';

            document.getElementById('checkoutModal').classList.replace('hidden', 'flex');

            if (m === 'cash') setTimeout(() => document.getElementById('cashAmount').focus(), 150);
        };

        // Menutup modal berdasarkan ID elemen modal.
        window.closeModal = (id) => document.getElementById(id).classList.replace('flex', 'hidden');

        // Isi cepat nominal tunai dan hitung kembalian.
        window.setCash = (v) => { const t = parseInt(document.getElementById('modalTotal').dataset.value); document.getElementById('cashAmount').value = v === 'exact' ? t : v; calcChange(); };
        document.getElementById('cashAmount').addEventListener('input', calcChange);

        // Menghitung kembalian atau kekurangan pembayaran tunai.
        function calcChange() { const c = parseInt(document.getElementById('cashAmount').value) || 0, t = parseInt(document.getElementById('modalTotal').dataset.value), d = c - t, el = document.getElementById('changeAmount'); if (d >= 0) { el.textContent = formatRupiah(d); el.className = `text-3xl font-bold text-green-500`; } else { el.textContent = `Kurang ${formatRupiah(Math.abs(d))}`; el.className = `text-3xl font-bold text-red-500`; } }

        // Proses pembayaran transaksi baru atau pelunasan pesanan pending.
        window.processPayment = async () => {
            const t = parseInt(document.getElementById('modalTotal').dataset.value),
                m = document.getElementById('paymentMethod').value,
                c = parseInt(document.getElementById('cashAmount').value) || 0;

            const custName = document.getElementById('customerName').value;
            const custPhone = document.getElementById('customerPhone').value;
            const shouldOpenReceipt = document.getElementById('printReceipt').checked;

            if (m === 'cash' && c < t) return showAlertDialog('Uang kurang!');

            try {
                let url = '/cashier-api/transactions';
                let payload = {};

                if (currentTransactionId) {
                    url = `/cashier-api/transactions/${currentTransactionId}/pay`;
                    payload = {
                        cash_amount: m === 'cash' ? c : t,
                        payment_method: m
                    };
                } else {
                    payload = {
                        status: 'completed',
                        items: cart.map(x => ({ id: x.id, quantity: x.quantity, note: x.note || null })),
                        cash_amount: m === 'cash' ? c : t,
                        payment_method: m,
                        total_amount: t,
                        customer_name: custName,
                        phone_number: custPhone
                    };
                }

                const r = await fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify(payload)
                });

                const d = await r.json();
                if (r.ok) {
                    const id = d.data ? transactionKey(d.data) : transactionKey(d);
                    if (shouldOpenReceipt && id) {
                        location.href = `/cashier/struk/${id}`;
                        return;
                    }

                    cart = [];
                    currentTransactionId = null;
                    document.getElementById('customerName').value = '';
                    document.getElementById('customerPhone').value = '';
                    document.getElementById('paymentMethod').value = '';
                    closeModal('checkoutModal');
                    await loadInitialData();
                    await renderSavedPanel();
                    renderCart();
                    await showAlertDialog('Transaksi berhasil diselesaikan.');
                } else {
                    await showAlertDialog(d.message || 'Error pembayaran');
                }
            } catch (e) { await showAlertDialog('Error sistem saat memproses transaksi'); }
        };
    </script>
</body>

</html>
