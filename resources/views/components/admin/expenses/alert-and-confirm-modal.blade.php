{{-- Overlay modal alert dan konfirmasi pengeluaran --}}
<div id="alertModal"
    class="fixed inset-0 z-[2000] hidden items-center justify-center bg-slate-900/60 opacity-0 backdrop-blur-sm transition-opacity duration-200">
    {{-- Container isi pesan alert dan konfirmasi --}}
    <div id="alertModalContent"
        class="w-[350px] max-w-[90%] scale-95 rounded-2xl bg-white p-8 text-center transition-transform duration-200">
        <h3 id="alertMessage" class="mb-8 text-[1.1rem] font-medium text-slate-900"></h3>

        {{-- Area tombol modal yang diisi oleh JavaScript --}}
        <div id="alertButtons" class="flex justify-center gap-4">
            {{-- Buttons injected via JavaScript --}}
        </div>
    </div>
</div>
