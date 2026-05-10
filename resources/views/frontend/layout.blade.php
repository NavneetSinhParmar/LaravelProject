<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard')</title>
    <style>
        :root { --bg: #f5f7fb; --card: #fff; --text: #0f172a; --muted: #64748b; --border: #e5e7eb; --primary: #2563eb; --danger: #dc2626; }
        * { box-sizing: border-box; }
        body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; margin: 0; background: var(--bg); color: var(--text); }
        .shell { margin: 0 auto; padding: 20px; }
        .top { display: flex; flex-wrap: wrap; gap: 12px; align-items: center; justify-content: space-between; margin-bottom: 20px; }
        .brand { font-weight: 700; font-size: 1.1rem; }
        nav { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
        nav a { color: var(--primary); text-decoration: none; padding: 8px 12px; border-radius: 8px; }
        nav a:hover { background: rgba(37, 99, 235, 0.08); }
        nav a.active { background: rgba(37, 99, 235, 0.14); font-weight: 600; }
        .card { background: var(--card); border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,.06); padding: 18px; margin-bottom: 16px; }
        h1, h2, h3 { margin: 0 0 12px; font-size: 1.25rem; }
        .muted { color: var(--muted); font-size: 0.9rem; }
        label { display: block; margin: 10px 0 6px; font-size: 0.875rem; }
        input, textarea, select { width: 100%; padding: 10px; border: 1px solid #d9d9d9; border-radius: 8px; }
        textarea { min-height: 88px; resize: vertical; }
        .row { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; }
        button { background: var(--primary); color: #fff; border: 0; border-radius: 8px; padding: 9px 14px; cursor: pointer; font-size: 0.9rem; }
        button.secondary { background: #475569; }
        button.danger { background: var(--danger); }
        a.btn { display: inline-block; background: var(--primary); color: #fff !important; text-decoration: none; padding: 9px 14px; border-radius: 8px; font-size: 0.9rem; }
        a.btn:hover { filter: brightness(0.95); }
        .actions { display: flex; flex-wrap: wrap; gap: 8px; }
        table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
        th, td { text-align: left; padding: 10px; border-bottom: 1px solid var(--border); vertical-align: top; }
        .message { min-height: 22px; font-size: 0.9rem; margin-top: 8px; }
        .ok { color: #166534; }
        .err { color: var(--danger); }
        .thumb { max-width: 72px; max-height: 48px; object-fit: cover; border-radius: 6px; }
        .modal-backdrop { position: fixed; inset: 0; background: rgba(15,23,42,.45); display: none; align-items: center; justify-content: center; padding: 16px; z-index: 50; }
        .modal-backdrop.open { display: flex; }
        .modal { background: #fff; border-radius: 12px; max-width: 640px; width: 100%; max-height: 90vh; overflow: auto; padding: 18px; }
        pre.detail { background: #f8fafc; padding: 12px; border-radius: 8px; overflow: auto; font-size: 0.8rem; }
    </style>
    @stack('head')
</head>
<body>
    <div class="shell">
        <div class="top">
            <div class="brand">Admin</div>
            <nav>
                @auth
                    @if (Route::has('frontend.dashboard'))
                        <a href="{{ route('frontend.dashboard') }}" class="{{ request()->routeIs('frontend.dashboard') ? 'active' : '' }}">Dashboard</a>
                    @endif

                    @if (Route::has('frontend.sliders'))
                        <a href="{{ route('frontend.sliders') }}" class="{{ request()->routeIs('frontend.sliders') ? 'active' : '' }}">Sliders</a>
                    @endif

                    @if (Route::has('frontend.portfolio'))
                        <a href="{{ route('frontend.portfolio') }}" class="{{ request()->routeIs('frontend.portfolio') ? 'active' : '' }}">Portfolio</a>
                    @endif

                    @if (Route::has('frontend.products'))
                        <a href="{{ route('frontend.products') }}" class="{{ request()->routeIs('frontend.products') ? 'active' : '' }}">Products</a>
                    @endif

                    @if (Route::has('frontend.categories'))
                        <a href="{{ route('frontend.categories') }}" class="{{ request()->routeIs('frontend.categories') ? 'active' : '' }}">Categories</a>
                    @endif

                    @if (Route::has('frontend.clients'))
                        <a href="{{ route('frontend.clients') }}" class="{{ request()->routeIs('frontend.clients') ? 'active' : '' }}">Clients</a>
                    @endif

                    @if (Route::has('frontend.testimonials'))
                        <a href="{{ route('frontend.testimonials') }}" class="{{ request()->routeIs('frontend.testimonials') ? 'active' : '' }}">Testimonials</a>
                    @endif

                    @if (Route::has('frontend.faq'))
                        <a href="{{ route('frontend.faq') }}" class="{{ request()->routeIs('frontend.faq') ? 'active' : '' }}">FAQ</a>
                    @endif

                    @if (Route::has('frontend.pageslug'))
                        <a href="{{ route('frontend.pageslug') }}" class="{{ request()->routeIs('frontend.pageslug') ? 'active' : '' }}">page slug</a>
                    @endif

                    <button type="button" class="danger" id="nav-logout">Logout</button>
                @endauth
            </nav>
        </div>

        @yield('content')
    </div>

    <script>
        (function () {
            const tokenKey = 'api_token';

            function getToken() {
                return localStorage.getItem(tokenKey);
            }

            function authHeadersJson() {
                const token = getToken();
                return {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': token ? 'Bearer ' + token : ''
                };
            }

            function authHeadersMultipart() {
                const token = getToken();
                return {
                    'Accept': 'application/json',
                    'Authorization': token ? 'Bearer ' + token : ''
                };
            }

            async function webLogout() {
                const csrf = document.querySelector('meta[name="csrf-token"]');
                const hdr = csrf ? csrf.getAttribute('content') : '';
                await fetch('{{ url('/web/logout') }}', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': hdr
                    }
                }).catch(function () {});
            }

            async function apiFetch(url, options = {}) {
                const response = await fetch(url, options);
                const text = await response.text();
                let data = null;
                if (text) {
                    try {
                        data = JSON.parse(text);
                    } catch (e) {
                        throw new Error('Server returned non-JSON. Check PHP error logs or run php artisan route:list.');
                    }
                }

                if (response.status === 401) {
                    localStorage.removeItem(tokenKey);
                    // Clear Laravel web session so /login does not fight a stale session (infinite refresh loop).
                    await webLogout();
                    const loginUrl = @json(route('login'));
                    window.location.replace(loginUrl + (loginUrl.indexOf('?') >= 0 ? '&' : '?') + 'reauth=1');
                    return null;
                }

                if (!response.ok) {
                    let msg = null;
                    if (data && data.errors && typeof data.errors === 'object') {
                        const first = Object.values(data.errors).flat()[0];
                        if (first) {
                            msg = first;
                        }
                    }
                    if (!msg && data && (data.message || data.error)) {
                        msg = data.message || data.error;
                    }
                    if (!msg) {
                        msg = 'Request failed (' + response.status + ').';
                    }
                    const err = new Error(typeof msg === 'string' ? msg : 'Request failed.');
                    err.payload = data;
                    throw err;
                }

                return data;
            }

            async function apiLogout() {
                const t = getToken();
                if (!t) {
                    return;
                }
                await fetch('{{ url('/api/logout') }}', {
                    method: 'POST',
                    headers: authHeadersJson()
                }).catch(function () {});
            }

            document.getElementById('nav-logout').addEventListener('click', async function () {
                await apiLogout();
                await webLogout();
                localStorage.removeItem(tokenKey);
                window.location.href = @json(route('login'));
            });

            window.FrontendApi = {
                getToken: getToken,
                authHeadersJson: authHeadersJson,
                authHeadersMultipart: authHeadersMultipart,
                apiFetch: apiFetch
            };
        })();
    </script>
    @stack('scripts')
</body>
</html>
