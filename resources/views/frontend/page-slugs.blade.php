@extends('frontend.layout')

@section('title', 'Page Slugs')

@section('content')
    <div class="card">
        <h1>Page Slugs</h1>
        <p class="muted">Manage reusable page slug values used across modules.</p>
    </div>

    <div class="card">
        <h2 id="form-title">Create page slug</h2>

        <form id="pageslug-form">
            <input type="hidden" id="pageslug-id">

            <div class="row">
                <div>
                    <label for="name">Name</label>
                    <input id="name" name="name" required>
                </div>

                <div>
                    <label for="slug">Slug (optional)</label>
                    <input id="slug" name="slug">
                </div>
            </div>

            <div class="actions" style="margin-top:12px;">
                <button type="submit" id="save-btn">Save</button>
                <button type="button" class="secondary" id="reset-btn">Reset</button>
            </div>

            <div id="pageslug-message" class="message"></div>
        </form>
    </div>

    <div class="card">
        <h2>All Page Slugs</h2>

        <div style="overflow:auto;">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody id="pageslug-table">
                    <tr>
                        <td colspan="4" class="muted">Loading…</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
<script>
(function () {

    const tableEl = document.getElementById('pageslug-table');
    const msgEl = document.getElementById('pageslug-message');
    const formEl = document.getElementById('pageslug-form');
    const saveBtn = document.getElementById('save-btn');
    const formTitle = document.getElementById('form-title');

    function setMessage(text, isError = false) {
        msgEl.textContent = text || '';
        msgEl.className = 'message ' + (isError ? 'err' : 'ok');
    }

    function resetForm() {
        formEl.reset();
        document.getElementById('pageslug-id').value = '';
        saveBtn.textContent = 'Save';
        formTitle.textContent = 'Create page slug';
        setMessage('');
    }

    function fillForm(p) {
        document.getElementById('pageslug-id').value = p.id;
        document.getElementById('name').value = p.name || '';
        document.getElementById('slug').value = p.slug || '';
        saveBtn.textContent = 'Update';
        formTitle.textContent = 'Edit page slug #' + p.id;
    }

    async function loadTable() {
        const res = await window.FrontendApi.apiFetch('{{ url('/api/page-slugs') }}', { method: 'GET', headers: window.FrontendApi.authHeadersJson() });
        const rows = (res && res.data) ? res.data : [];
        if (!rows.length) {
            tableEl.innerHTML = `<tr><td colspan="4" class="muted">No page slugs yet.</td></tr>`;
            return;
        }
        tableEl.innerHTML = rows.map(function (p) {
            return `
                <tr>
                    <td>${p.id}</td>
                    <td>${p.name}</td>
                    <td>${p.slug}</td>
                    <td class="actions">
                        <button type="button" data-action="edit" data-id="${p.id}">Edit</button>
                        <button type="button" class="danger" data-action="delete" data-id="${p.id}">Delete</button>
                    </td>
                </tr>
            `;
        }).join('');
    }

    formEl.addEventListener('submit', async function (e) {
        e.preventDefault();
        setMessage('');
        const id = document.getElementById('pageslug-id').value;
        try {
            if (id) {
                const payload = { name: document.getElementById('name').value, slug: document.getElementById('slug').value };
                await window.FrontendApi.apiFetch('{{ url('/api/page-slugs') }}/' + id, { method: 'PUT', headers: window.FrontendApi.authHeadersJson(), body: JSON.stringify(payload) });
                setMessage('Updated.');
            } else {
                const payload = { name: document.getElementById('name').value, slug: document.getElementById('slug').value };
                await window.FrontendApi.apiFetch('{{ url('/api/page-slugs') }}', { method: 'POST', headers: window.FrontendApi.authHeadersJson(), body: JSON.stringify(payload) });
                setMessage('Created.');
            }

            resetForm();
            await loadTable();

        } catch (err) {
            setMessage(err.message || 'Save failed.', true);
        }
    });

    document.getElementById('reset-btn').addEventListener('click', resetForm);

    tableEl.addEventListener('click', async function (e) {
        const btn = e.target.closest('button[data-action]');
        if (!btn) return;
        const action = btn.getAttribute('data-action');
        const id = btn.getAttribute('data-id');
        if (!id) return;
        try {
            if (action === 'edit') {
                const data = await window.FrontendApi.apiFetch('{{ url('/api/page-slugs') }}/' + id, { method: 'GET', headers: window.FrontendApi.authHeadersJson() });
                fillForm(data.data);
            }
            if (action === 'delete') {
                if (!window.confirm('Delete page slug #' + id + '?')) return;
                await window.FrontendApi.apiFetch('{{ url('/api/page-slugs') }}/' + id, { method: 'DELETE', headers: window.FrontendApi.authHeadersJson() });
                setMessage('Deleted.');
                await loadTable();
            }
        } catch (err) {
            setMessage(err.message || 'Action failed.', true);
        }
    });

    loadTable().catch(function (err) {
        tableEl.innerHTML = `<tr><td colspan="4" class="err">${err.message || 'Failed to load page slugs.'}</td></tr>`;
    });

})();
</script>
@endpush
