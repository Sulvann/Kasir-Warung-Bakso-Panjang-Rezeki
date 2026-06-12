@extends('layouts.admin')

@section('content')
    {{-- Header halaman manajemen produk --}}
    <div class="mb-6 flex items-center justify-between">
        {{-- Informasi judul dan keterangan halaman --}}
        <div>
            <h1 class="m-0 text-2xl font-bold tracking-tight text-slate-900">Manajemen Produk</h1>
            <p class="mt-1 text-sm font-medium text-slate-500">Produk dikelola melalui halaman Produk dan Resep.</p>
        </div>
    </div>

    {{-- Kartu utama tabel daftar produk --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        {{-- Area scroll horizontal untuk tabel produk pada layar kecil --}}
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-[#1e2e53] text-left text-xs font-bold uppercase tracking-wider text-white">
                        <th class="border-b border-slate-200 px-6 py-5">Gambar</th>
                        <th class="border-b border-slate-200 px-6 py-5">Nama Produk</th>
                        <th class="border-b border-slate-200 px-6 py-5">Kategori</th>
                        <th class="border-b border-slate-200 px-6 py-5">Harga</th>
                        <th class="border-b border-slate-200 px-6 py-5">Status</th>
                        <th class="border-b border-slate-200 px-6 py-5">Stok</th>
                    </tr>
                </thead>
                <tbody id="productsTable">
                    <tr>
                        <td colspan="6" class="px-4 py-4 text-center text-sm font-medium text-slate-500">Loading...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        // State utama halaman produk dan konfigurasi API.
        let products = [];
        const API_URL = '/admin/api/product-recipes';
        const fallbackProductImageIcon = @json(view('components.icons.photo', ['attributes' => new Illuminate\View\ComponentAttributeBag(['class' => 'h-6 w-6 text-slate-400'])])->render());

        // Menentukan status efektif produk berdasarkan status produk dan status kategorinya.
        function getEffectiveProductStatus(product) {
            const categoryInactive = product.category && product.category.status === 'inactive';

            return product.status === 'active' && !categoryInactive ? 'active' : 'inactive';
        }

        // Menentukan label status produk yang ditampilkan pada tabel.
        function getProductStatusLabel(product) {
            if (product.status === 'active' && product.category && product.category.status === 'inactive') {
                return 'Inaktif (Kategori)';
            }

            return getEffectiveProductStatus(product) === 'active' ? 'Aktif' : 'Inaktif';
        }

        // Membuat badge status produk dalam bentuk HTML.
        function statusBadge(status, label = null) {
            const isActive = status === 'active';
            const badgeClass = isActive
                ? 'bg-green-100 text-green-800'
                : 'bg-red-100 text-red-700';

            return `<span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-extrabold ${badgeClass}">${label || (isActive ? 'Aktif' : 'Inaktif')}</span>`;
        }

        // Mengurutkan produk aktif terlebih dahulu sebelum produk inaktif.
        function sortActiveFirst(items) {
            return [...items].sort((a, b) => (getEffectiveProductStatus(a) === 'inactive') - (getEffectiveProductStatus(b) === 'inactive'));
        }

        // Menjalankan proses awal setelah halaman selesai dimuat.
        document.addEventListener('DOMContentLoaded', () => {
            loadProducts();
        });

        // Mengubah angka harga menjadi format mata uang Rupiah.
        const formatRupiah = (number) => {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(number);
        }

        // Membuat tampilan gambar produk atau placeholder jika gambar belum tersedia.
        function productImagePreview(product) {
            if (product.image) {
                return `<img src="/storage/${product.image}" alt="${product.name}" class="h-12 w-12 rounded-lg bg-slate-100 object-cover">`;
            }

            return `
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-slate-100">
                    ${fallbackProductImageIcon}
                </div>
            `;
        }

        // Mengambil daftar produk dari API product-recipes.
        async function loadProducts() {
            try {
                const res = await fetch(API_URL, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();
                products = sortActiveFirst(data.data);
                renderTable();
            } catch (error) {
                console.error(error);
                document.getElementById('productsTable').innerHTML = `
                    <tr>
                        <td colspan="6" class="px-4 py-4 text-center text-sm font-medium text-red-500">Gagal memuat data</td>
                    </tr>
                `;
            }
        }

        // Merender data produk ke dalam tabel HTML.
        function renderTable() {
            const tbody = document.getElementById('productsTable');

            if (products.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="px-4 py-4 text-center text-sm font-medium text-slate-500">Tidak ada data produk</td>
                    </tr>
                `;
                return;
            }

            tbody.innerHTML = products.map((p, index) => {
                const rowClass = index % 2 === 0 ? 'bg-white' : 'bg-slate-50';
                const effectiveStatus = getEffectiveProductStatus(p);
                const catName = p.category ? p.category.name : '-';
                const stockValue = p.max_yield !== null
                    ? p.max_yield
                    : '<span class="text-xs font-bold text-slate-400">-</span>';

                return `
                    <tr class="${rowClass}">
                        <td class="border-b border-slate-100 px-6 py-5 align-middle">
                            ${productImagePreview(p)}
                        </td>
                        <td class="border-b border-slate-100 px-6 py-5 align-middle">
                            <!-- Nama produk pada baris tabel -->
                            <div class="text-sm font-bold text-black">${p.name}</div>
                        </td>
                        <td class="border-b border-slate-100 px-6 py-5 align-middle">
                            <span class="rounded bg-blue-50 px-2 py-0.5 text-xs font-semibold text-blue-800">${catName}</span>
                        </td>
                        <td class="border-b border-slate-100 px-6 py-5 align-middle text-sm font-medium text-slate-700">
                            ${formatRupiah(p.price)}
                        </td>
                        <td class="border-b border-slate-100 px-6 py-5 align-middle">
                            ${statusBadge(effectiveStatus, getProductStatusLabel(p))}
                        </td>
                        <td class="border-b border-slate-100 px-6 py-5 align-middle text-sm font-bold text-slate-700">
                            ${stockValue}
                        </td>
                    </tr>
                `;
            }).join('');
        }
    </script>
@endsection
