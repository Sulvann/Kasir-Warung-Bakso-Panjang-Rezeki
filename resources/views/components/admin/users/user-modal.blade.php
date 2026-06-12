{{-- Overlay modal tambah dan edit akun --}}
<div id="userModal"
    class="fixed inset-0 z-[1000] hidden items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm">
    {{-- Container utama modal form akun --}}
    <div class="relative w-[400px] max-w-[90%] rounded-2xl bg-white p-8">
        <button type="button" onclick="closeModal()"
            class="absolute right-6 top-6 flex h-8 w-8 items-center justify-center rounded-full bg-red-500 text-white transition hover:bg-red-700">
            <x-icons.x-mark class="h-5 w-5" />
        </button>

        <h2 id="modalTitle" class="mb-6 text-xl font-black uppercase tracking-tight text-slate-900">
            Tambah Akun
        </h2>

        <form id="userForm">
            <input type="hidden" id="userId">

            {{-- Field nama akun --}}
            <div class="mb-5">
                <label for="name" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-900">
                    Nama
                </label>
                <input type="text" id="name" required
                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-medium outline-none transition-all focus:border-slate-900 focus:ring-2 focus:ring-slate-900">
            </div>

            {{-- Field email akun --}}
            <div class="mb-5">
                <label for="email" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-900">
                    Email
                </label>
                <input type="email" id="email" required
                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-medium outline-none transition-all focus:border-slate-900 focus:ring-2 focus:ring-slate-900">
            </div>

            {{-- Field role akun --}}
            <div class="mb-5">
                <label for="role" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-900">
                    Role
                </label>
                <select id="role" required
                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-medium outline-none transition-all focus:border-slate-900 focus:ring-2 focus:ring-slate-900">
                    <option value="cashier">Kasir</option>
                    <option value="admin">Admin</option>
                </select>
            </div>

            {{-- Field status akun --}}
            <div class="mb-5">
                <label for="status" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-900">
                    Status
                </label>
                <select id="status" required
                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-medium outline-none transition-all focus:border-slate-900 focus:ring-2 focus:ring-slate-900">
                    <option value="active">Aktif</option>
                    <option value="inactive">Inaktif</option>
                </select>
            </div>

            {{-- Field kata sandi akun --}}
            <div class="mb-5">
                <label for="password" class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-900">
                    Kata Sandi
                    <span class="font-normal normal-case tracking-normal text-slate-400">(Opsional)</span>
                </label>
                <input type="password" id="password" autocomplete="new-password"
                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-medium outline-none transition-all focus:border-slate-900 focus:ring-2 focus:ring-slate-900"
                    placeholder="Kosongkan jika tidak diubah">
            </div>

            {{-- Field konfirmasi kata sandi akun --}}
            <div class="mb-6">
                <label for="password_confirmation"
                    class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-900">
                    Konfirmasi Kata Sandi
                </label>
                <input type="password" id="password_confirmation" autocomplete="new-password"
                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-medium outline-none transition-all focus:border-slate-900 focus:ring-2 focus:ring-slate-900"
                    placeholder="Kosongkan jika tidak diubah">
            </div>

            {{-- Tombol aksi modal akun --}}
            <div class="mt-2 flex justify-end gap-3">
                <button type="button" onclick="closeModal()"
                    class="rounded-xl bg-red-500 px-6 py-2.5 text-sm font-bold text-white transition-colors hover:bg-red-600">
                    Batal
                </button>
                <button type="submit"
                    class="rounded-xl bg-green-500 px-6 py-2.5 text-sm font-bold text-white transition-colors hover:bg-green-600">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>
