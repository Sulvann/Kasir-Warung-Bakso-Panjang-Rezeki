<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kasir - RealKasir</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            background-color: #f1f5f9;
            min-height: 100vh;
        }

        /* Header */
        .header {
            background: #0f172a;
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 70px;
        }

        .brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
        }

        .brand span {
            color: #3b82f6;
        }

        .user-nav {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            background: #3b82f6;
            border-radius: 8px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-weight: 600;
        }

        .btn-logout {
            background: rgba(239, 68, 68, 0.2);
            color: #fca5a5;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: 0.2s;
        }

        .btn-logout:hover {
            background: rgba(239, 68, 68, 0.3);
        }

        /* Main Content */
        .main-content {
            padding: 1.5rem;
            height: calc(100vh - 70px);
        }

        /* Modal & Utilities from Admin Layout */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            padding: 2rem;
            border-radius: 16px;
            width: 100%;
            max-width: 500px;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            border: none;
        }

        .btn-primary {
            background: #3b82f6;
            color: white;
        }

        .btn-edit {
            background: #f1f5f9;
            color: #334155;
        }

        /* Loading Overlay */
        #loadingOverlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #f1f5f9;
            z-index: 9999;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #e2e8f0;
            border-top: 4px solid #0f172a;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
    <style>
        /* ... existing styles ... */
    </style>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @yield('styles')
</head>

<body>
    <div id="loadingOverlay">
        <div class="spinner"></div>
    </div>

    <div class="w-full shrink-0 z-50">
        @include('layouts.navigation')
    </div>

    <main class="main-content">
        @yield('content')
    </main>

    <script>
        // Session Based Auth: No Token Check Needed here because Middleware handles it.
        // But we handle UI loading state.

        // Safety Timeout
        setTimeout(() => {
            const overlay = document.getElementById('loadingOverlay');
            if (overlay && overlay.style.display !== 'none') {
                overlay.style.display = 'none';
            }
        }, 3000);

        // Fetch User Info using Session Cookie
        fetch('/cashier-api/user', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(res => {
                if (res.status === 401 || res.status === 419) {
                    // Session expired
                    window.location.href = '/login';
                    return;
                }
                if (!res.ok) throw new Error('Fetch failed');
                return res.json();
            })
            .then(user => {
                if (user) {
                    document.getElementById('userName').textContent = user.name;
                    document.getElementById('userEmail').textContent = user.email;
                    document.getElementById('userAvatar').textContent = user.name.charAt(0).toUpperCase();
                }
                document.getElementById('loadingOverlay').style.display = 'none';
            })
            .catch(err => {
                console.error('User info load error:', err);
                // Still hide overlay so app is usable (maybe partial load)
                document.getElementById('loadingOverlay').style.display = 'none';
            });

        function logout() {
            if (confirm('Keluar dari aplikasi?')) {
                fetch('/logout', { // Use web logout route which handles session invalidate
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                }).finally(() => {
                    window.location.href = '/login';
                });
            }
        }
    </script>
    @yield('scripts')
</body>

</html>