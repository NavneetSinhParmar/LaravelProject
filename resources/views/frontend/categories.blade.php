@extends('frontend.layout')

@section('title', 'Categories')

@section('content')
    <div class="card">
        <h1>Categories</h1>
        <p class="muted">
            Uses authenticated API:
            <code>GET/POST /api/categories</code>,
            <code>GET/PUT/DELETE /api/categories/{id}</code>,
            and <code>POST /api/categories/{id}</code> when uploading a new logo (multipart).
        </p>
    </div>

    <div class="card">
        <h2 id="form-title">Create category</h2>

        <form id="category-form">
            <input type="hidden" id="category-id">

            <div class="row">
                <div>
                    <label for="page_slug">Page slug</label>
                    <input id="page_slug" name="page_slug" value="home" required>
                </div>

                <div>
                    <label for="sort_order">Order</label>
                    <input
                        id="sort_order"
                        name="sort_order"
                        type="number"
                        min="0"
                        value="0"
                    >
                </div>

                <div>
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div>
                    <label for="name">Name</label>
                    <input id="name" name="name" required>
                </div>

                <!-- Category link removed from form -->
            </div>

            <label for="logo">Logo (optional for update)</label>
            <input
                id="logo"
                name="logo"
                type="file"
                accept="image/*"
            >

            <div class="actions" style="margin-top:12px;">
                <button type="submit" id="save-btn">Save</button>

                <button
                    type="button"
                    class="secondary"
                    id="reset-btn"
                >
                    Reset
                </button>
            </div>

            <div id="category-message" class="message"></div>
        </form>
    </div>

    <div class="card">
        <h2>All Categories</h2>

        <div style="overflow:auto;">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Page Slug</th>
                        <th>Name</th>
                        <th>Link</th>
                        <th>Status</th>
                        <th>Logo</th>
                        <th>Order</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody id="category-table">
                    <tr>
                        <td colspan="8" class="muted">
                            Loading…
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
<script>
(function () {

    const tableEl = document.getElementById('category-table');
    const msgEl = document.getElementById('category-message');
    const formEl = document.getElementById('category-form');
    const saveBtn = document.getElementById('save-btn');
    const formTitle = document.getElementById('form-title');

    function setMessage(text, isError = false) {
        msgEl.textContent = text || '';
        msgEl.className = 'message ' + (isError ? 'err' : 'ok');
    }

    function logoUrl(path) {
        if (!path) {
            return '';
        }

        if (path.indexOf('http') === 0) {
            return path;
        }

        return @json(asset('storage')) + '/' + path.replace(/^\/+/, '');
    }

    function resetForm() {
        formEl.reset();

        document.getElementById('category-id').value = '';
        document.getElementById('page_slug').value = 'home';
        document.getElementById('sort_order').value = '0';
        document.getElementById('status').value = '1';

        saveBtn.textContent = 'Save';
        formTitle.textContent = 'Create category';

        setMessage('');
    }

    function fillForm(c) {

        document.getElementById('category-id').value = c.id;
        document.getElementById('page_slug').value = c.page_slug || 'home';
        document.getElementById('sort_order').value = String(c.sort_order ?? 0);
        document.getElementById('status').value = String(c.status ?? 1);

        document.getElementById('name').value = c.name || '';

        saveBtn.textContent = 'Update';
        formTitle.textContent = 'Edit category #' + c.id;

        setMessage(
            'Edit mode — change fields and save. Leave logo empty to keep current file.'
        );
    }

    async function loadTable() {

        const res = await window.FrontendApi.apiFetch(
            '{{ url('/api/categories') }}',
            {
                method: 'GET',
                headers: window.FrontendApi.authHeadersJson()
            }
        );

        const rows = (res && res.data) ? res.data : [];

        if (!rows.length) {

            tableEl.innerHTML = `
                <tr>
                    <td colspan="8" class="muted">
                        No categories yet.
                    </td>
                </tr>
            `;

            return;
        }

        tableEl.innerHTML = rows.map(function (c) {

            const logo = c.logo
                ? '<img class="thumb" src="' + logoUrl(c.logo) + '" alt="">'
                : '<span class="muted">—</span>';

            return `
                <tr>
                    <td>${c.id}</td>
                    <td>${c.page_slug || ''}</td>
                    <td>${c.name || ''}</td>
                    <td>
                        ${
                            c.link
                                ? `<a href="${c.link}" target="_blank">${c.link}</a>`
                                : '<span class="muted">—</span>'
                        }
                    </td>
                    <td>
                        ${Number(c.status) === 1 ? 'Active' : 'Inactive'}
                    </td>
                    <td>${logo}</td>
                    <td>${c.sort_order ?? ''}</td>

                    <td class="actions">
                        <button
                            type="button"
                            data-action="edit"
                            data-id="${c.id}"
                        >
                            Edit
                        </button>

                        <button
                            type="button"
                            class="danger"
                            data-action="delete"
                            data-id="${c.id}"
                        >
                            Delete
                        </button>
                    </td>
                </tr>
            `;
        }).join('');
    }

    formEl.addEventListener('submit', async function (e) {

        e.preventDefault();

        setMessage('');

        const id = document.getElementById('category-id').value;

        const fileInput = document.getElementById('logo');

        const hasFile = fileInput.files && fileInput.files[0];

        try {

            if (id && hasFile) {

                const fd = new FormData();

                fd.append('page_slug', document.getElementById('page_slug').value);
                fd.append('name', document.getElementById('name').value);
                fd.append('status', document.getElementById('status').value);
                fd.append('sort_order', document.getElementById('sort_order').value || '0');

                fd.append('logo', fileInput.files[0]);

                await window.FrontendApi.apiFetch(
                    '{{ url('/api/categories') }}/' + id,
                    {
                        method: 'POST',
                        headers: window.FrontendApi.authHeadersMultipart(),
                        body: fd
                    }
                );

                setMessage('Category updated (logo replaced).');

            } else if (id) {

                const payload = {
                    page_slug: document.getElementById('page_slug').value,
                    name: document.getElementById('name').value,
                    status: Number(document.getElementById('status').value),
                    sort_order: document.getElementById('sort_order').value
                        ? Number(document.getElementById('sort_order').value)
                        : 0
                };

                await window.FrontendApi.apiFetch(
                    '{{ url('/api/categories') }}/' + id,
                    {
                        method: 'PUT',
                        headers: window.FrontendApi.authHeadersJson(),
                        body: JSON.stringify(payload)
                    }
                );

                setMessage('Category updated.');

            } else {

                const fd = new FormData();

                fd.append('page_slug', document.getElementById('page_slug').value);
                fd.append('name', document.getElementById('name').value);
                fd.append('status', document.getElementById('status').value);
                fd.append('sort_order', document.getElementById('sort_order').value || '0');

                if (hasFile) {
                    fd.append('logo', fileInput.files[0]);
                }

                await window.FrontendApi.apiFetch(
                    '{{ url('/api/categories') }}',
                    {
                        method: 'POST',
                        headers: window.FrontendApi.authHeadersMultipart(),
                        body: fd
                    }
                );

                setMessage('Category created.');
            }

            resetForm();

            await loadTable();

        } catch (err) {

            setMessage(err.message || 'Save failed.', true);
        }
    });

    document.getElementById('reset-btn')
        .addEventListener('click', resetForm);

    tableEl.addEventListener('click', async function (e) {

        const btn = e.target.closest('button[data-action]');

        if (!btn) {
            return;
        }

        const action = btn.getAttribute('data-action');
        const id = btn.getAttribute('data-id');

        if (!id) {
            return;
        }

        try {

            if (action === 'edit') {

                const data = await window.FrontendApi.apiFetch(
                    '{{ url('/api/categories') }}/' + id,
                    {
                        method: 'GET',
                        headers: window.FrontendApi.authHeadersJson()
                    }
                );

                fillForm(data.data);
            }

            if (action === 'delete') {

                if (!window.confirm('Delete category #' + id + '?')) {
                    return;
                }

                await window.FrontendApi.apiFetch(
                    '{{ url('/api/categories') }}/' + id,
                    {
                        method: 'DELETE',
                        headers: window.FrontendApi.authHeadersJson()
                    }
                );

                setMessage('Category deleted.');

                await loadTable();
            }

        } catch (err) {

            setMessage(err.message || 'Action failed.', true);
        }
    });

    loadTable().catch(function (err) {

        tableEl.innerHTML = `
            <tr>
                <td colspan="8" class="err">
                    ${err.message || 'Failed to load categories.'}
                </td>
            </tr>
        `;
    });

})();
</script>
@endpush
