@extends('frontend.layout')

@section('title', 'Dashboard')

@section('content')
    <div class="card">
        <h1>Dashboard</h1>
        <p class="muted">You are signed in for both the browser session (Laravel <code>auth</code>) and API calls (Bearer token).</p>
        <div id="profile" class="muted" style="margin-top:8px;">Loading profile…</div>
    </div>

    <div class="row" style="margin-bottom: 8px;">
        <div class="card" style="margin-bottom:0;">
            <h2>Sliders</h2>
            <p class="muted">Create, edit (with image upload), list, and delete slider sections.</p>
            <div class="actions" style="margin-top:12px;">
                <a class="btn" href="{{ route('frontend.sliders') }}">Open Sliders</a>
            </div>
        </div>
        <div class="card" style="margin-bottom:0;">
            <h2>Products</h2>
            <p class="muted">Full CRUD plus detail view from <code>GET /api/products/{id}</code>.</p>
            <div class="actions" style="margin-top:12px;">
                <a class="btn" href="{{ route('frontend.products') }}">Open Products</a>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (async function () {
            const profileEl = document.getElementById('profile');
            try {
                const data = await window.FrontendApi.apiFetch('{{ url('/api/profile') }}', {
                    method: 'GET',
                    headers: window.FrontendApi.authHeadersJson()
                });
                if (data && data.user) {
                    profileEl.textContent = data.user.name + ' (' + data.user.email + ')';
                }
            } catch (e) {
                profileEl.textContent = e.message || 'Could not load profile.';
                profileEl.classList.add('err');
            }
        })();
    </script>
@endpush
