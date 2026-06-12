<!-- MODAL ALERT / KONFIRMASI -->
{{-- Overlay modal alert/konfirmasi kasir --}}
<div id="cashierAlertModal"
    class="fixed inset-0 bg-black/60 hidden items-center justify-center z-[130] backdrop-blur-sm p-4">
    {{-- Kotak utama modal alert --}}
    <div
        class="w-full max-w-sm bg-white dark:bg-[#111] border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl overflow-hidden">
        {{-- Area pesan alert atau konfirmasi --}}
        <div class="p-7 text-center">
            <h3 id="cashierAlertMessage" class="text-base font-bold text-slate-900 dark:text-white leading-relaxed">
            </h3>
        </div>
        {{-- Area tombol alert, diisi melalui JavaScript --}}
        <div id="cashierAlertButtons"
            class="px-5 py-4 bg-slate-50 dark:bg-[#0a0a0a] border-t border-slate-100 dark:border-slate-800 flex gap-3">
        </div>
    </div>
</div>
