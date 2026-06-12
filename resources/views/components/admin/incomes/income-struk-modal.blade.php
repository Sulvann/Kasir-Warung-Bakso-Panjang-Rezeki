{{-- Modal preview struk transaksi --}}
<div id="strukModal"
    class="fixed inset-0 z-[9999] hidden items-center justify-center bg-black/55 opacity-0 backdrop-blur-sm transition-opacity duration-200">
    {{-- Kotak isi modal struk --}}
    <div id="strukModalBox"
        class="flex max-h-[90vh] w-[460px] scale-95 flex-col overflow-hidden rounded-[20px] bg-white shadow-2xl transition-transform duration-200">
        {{-- Header modal struk --}}
        <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-6 py-4">
            <h3 class="text-base font-bold text-slate-900">Preview Struk Transaksi</h3>
            <button type="button" onclick="closeStrukModal()"
                class="flex h-8 w-8 items-center justify-center rounded-full text-slate-400 transition-colors hover:bg-slate-200 hover:text-slate-900">
                <x-icons.x-mark class="h-5 w-5" />
            </button>
        </div>

        {{-- Iframe untuk menampilkan halaman struk --}}
        <iframe id="strukIframe" class="min-h-[540px] w-full flex-1 border-0" src=""></iframe>

        {{-- Footer modal struk berisi form kirim WhatsApp --}}
        <div class="flex items-center gap-3 border-t border-slate-200 bg-slate-50 px-6 py-4">
            <input type="text" id="incomeWaNumber"
                class="h-[42px] min-w-0 flex-1 rounded-[10px] border border-slate-200 bg-white px-4 text-sm font-medium text-slate-900 outline-none transition-colors placeholder:text-slate-400 focus:border-green-400 focus:ring-2 focus:ring-green-100"
                placeholder="Nomor WhatsApp (08xxx)">

            <button type="button" onclick="sendIncomeWhatsapp()" id="btnIncomeWa"
                class="inline-flex h-[42px] shrink-0 items-center justify-center gap-1.5 rounded-[10px] bg-[#25D366] px-5 text-sm font-semibold text-white transition-colors hover:bg-[#128C7E] disabled:cursor-not-allowed disabled:opacity-70">
                <x-icons.chat-bubble-left-right class="h-4 w-4" />
                Kirim WhatsApp
            </button>
        </div>
    </div>
</div>
