{{-- Overlay modal alert dan konfirmasi --}}
<div id="alertModal"
    class="fixed inset-0 z-[2000] hidden items-center justify-center bg-slate-900/60 p-4 opacity-0 backdrop-blur-sm transition-opacity duration-200">
    {{-- Container isi pesan alert --}}
    <div id="alertModalContent"
        class="w-[350px] max-w-[90%] scale-95 rounded-2xl bg-white p-8 text-center transition-transform duration-200">
        <h3 id="alertMessage" class="mb-8 text-[1.1rem] font-medium text-slate-900"></h3>

        {{-- Area tombol alert dan konfirmasi yang ditampilkan sesuai mode oleh JavaScript --}}
        <div id="alertButtons" class="flex justify-center gap-4">
            {{-- Tombol untuk mode konfirmasi Ya/Tidak --}}
            <div id="alertConfirmActions" class="hidden w-full gap-4">
                <button type="button" id="modalBtnTidak"
                    class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg bg-red-500 px-5 py-2.5 text-sm font-medium text-white transition hover:opacity-80 active:scale-95">
                    <x-icons.x-mark class="h-[18px] w-[18px]" />
                    Tidak
                </button>
                <button type="button" id="modalBtnYa"
                    class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg bg-green-500 px-5 py-2.5 text-sm font-medium text-white transition hover:opacity-80 active:scale-95">
                    <x-icons.check-circle class="h-[18px] w-[18px]" />
                    Ya
                </button>
            </div>

            {{-- Tombol untuk mode alert satu pesan --}}
            <div id="alertOkActions" class="hidden w-full">
                <button type="button" id="modalBtnOk"
                    class="inline-flex w-full items-center justify-center rounded-lg bg-[#007bff] px-8 py-2 text-sm font-semibold text-white transition hover:opacity-80 active:scale-95">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
