<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - RealKasir</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css'])
</head>

<body
    class="relative flex min-h-screen items-center justify-center overflow-x-hidden bg-[radial-gradient(circle_at_82%_16%,rgba(201,20,47,0.10)_0,rgba(201,20,47,0)_28%),radial-gradient(circle_at_16%_86%,rgba(8,8,11,0.07)_0,rgba(8,8,11,0)_30%),linear-gradient(135deg,#ffffff_0%,#ffffff_52%,#fff6f7_100%)] px-[18px] py-8 font-sans text-[#08080b] after:pointer-events-none after:fixed after:inset-0 after:bg-[linear-gradient(90deg,rgba(255,255,255,0.64),rgba(255,255,255,0)_48%,rgba(201,20,47,0.06))] after:mix-blend-soft-light max-[520px]:px-4 max-[520px]:py-6">
    <main class="relative z-10 w-full max-w-[430px]" aria-label="Halaman login RealKasir">
        <section
            class="w-full rounded-[26px] border border-white/70 bg-[linear-gradient(145deg,rgba(255,255,255,0.84),rgba(255,255,255,0.34)),linear-gradient(135deg,rgba(201,20,47,0.10),rgba(8,8,11,0.08))] p-[clamp(26px,6vw,40px)] shadow-[0_34px_90px_rgba(8,8,11,0.24),inset_0_1px_0_rgba(255,255,255,0.88)] backdrop-blur-[22px] max-[520px]:rounded-3xl">
            <header class="mb-[30px] text-center">
                <h1 class="text-[clamp(2rem,7vw,2.75rem)] font-extrabold leading-[0.98] tracking-normal">
                    Warung Bakso
                    <span class="mt-2 block whitespace-nowrap text-[#c9142f]">Panjang Rezeki</span>
                </h1>
            </header>

            <div id="alertMessage" class="hidden"></div>

            <form id="loginForm">
                <div class="mb-4">
                    <label for="email"
                        class="mb-[9px] ml-0.5 block text-[0.84rem] font-extrabold uppercase tracking-[0.04em] text-[#08080b]/80">
                        Email
                    </label>
                    <input type="email" id="email"
                        class="h-[54px] w-full rounded-[15px] border border-[#08080b]/10 bg-white/75 px-[17px] text-[0.98rem] font-semibold text-[#08080b] shadow-[inset_0_1px_0_rgba(255,255,255,0.9),0_10px_24px_rgba(8,8,11,0.06)] outline-none transition duration-200 placeholder:font-medium placeholder:text-[#08080b]/40 focus:-translate-y-px focus:border-[#c9142f]/60 focus:bg-white/95 focus:ring-4 focus:ring-[#c9142f]/10 focus:shadow-[0_14px_32px_rgba(8,8,11,0.12)]"
                        placeholder="Masukkan email" autocomplete="email" required>
                </div>

                <div class="mb-4">
                    <label for="password"
                        class="mb-[9px] ml-0.5 block text-[0.84rem] font-extrabold uppercase tracking-[0.04em] text-[#08080b]/80">
                        Password
                    </label>
                    <input type="password" id="password"
                        class="h-[54px] w-full rounded-[15px] border border-[#08080b]/10 bg-white/75 px-[17px] text-[0.98rem] font-semibold text-[#08080b] shadow-[inset_0_1px_0_rgba(255,255,255,0.9),0_10px_24px_rgba(8,8,11,0.06)] outline-none transition duration-200 placeholder:font-medium placeholder:text-[#08080b]/40 focus:-translate-y-px focus:border-[#c9142f]/60 focus:bg-white/95 focus:ring-4 focus:ring-[#c9142f]/10 focus:shadow-[0_14px_32px_rgba(8,8,11,0.12)]"
                        placeholder="Masukkan password" autocomplete="current-password" required>
                </div>

                <button type="submit" id="loginBtn"
                    class="mt-2 flex h-[54px] w-full cursor-pointer items-center justify-center overflow-hidden rounded-[15px] border-0 bg-[#08080b] text-[0.98rem] font-extrabold text-white shadow-[0_18px_36px_rgba(8,8,11,0.28)] transition duration-200 hover:-translate-y-px hover:bg-[#171014] hover:shadow-[0_22px_42px_rgba(8,8,11,0.34)] active:translate-y-0 disabled:cursor-wait disabled:opacity-80">
                    <span class="btn-text">Masuk</span>
                    <div id="spinner"
                        class="hidden size-5 rounded-full border-2 border-white/35 border-t-white animate-spin"></div>
                </button>
            </form>
        </section>
    </main>

    <script>
        const loginForm = document.getElementById('loginForm');
        const loginBtn = document.getElementById('loginBtn');
        const btnText = document.querySelector('.btn-text');
        const spinner = document.getElementById('spinner');
        const alertBox = document.getElementById('alertMessage');

        const alertBaseClasses = [
            'mb-5',
            'flex',
            'w-full',
            'items-center',
            'rounded-[14px]',
            'px-3.5',
            'py-[13px]',
            'text-[0.88rem]',
            'font-bold',
            'leading-[1.45]',
        ].join(' ');

        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            loginBtn.disabled = true;
            btnText.classList.add('hidden');
            spinner.classList.remove('hidden');
            alertBox.className = 'hidden';

            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;

            try {
                const response = await fetch('/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ email, password })
                });

                const data = await response.json();

                if (response.ok) {
                    const redirectUrl = data.redirect_url || (data.user.role === 'admin' ? '/admin/dashboard' : '/cashier');
                    showAlert('Login berhasil. Mengalihkan halaman...', 'success');

                    setTimeout(() => {
                        window.location.href = redirectUrl;
                    }, 800);
                } else {
                    showAlert(data.message || 'Login gagal. Periksa kembali email dan kata sandi Anda.', 'error');
                }
            } catch (error) {
                showAlert('Tidak dapat terhubung ke server. Coba lagi beberapa saat.', 'error');
                console.error('Login error:', error);
            } finally {
                loginBtn.disabled = false;
                btnText.classList.remove('hidden');
                spinner.classList.add('hidden');
            }
        });

        function showAlert(message, type) {
            const stateClasses = type === 'success'
                ? 'border border-white/75 bg-white/60 text-[#0b5a2a]'
                : 'border border-[#c9142f]/20 bg-[#c9142f]/10 text-[#7e0718]';

            alertBox.textContent = message;
            alertBox.className = `${alertBaseClasses} ${stateClasses}`;
        }
    </script>
</body>

</html>
