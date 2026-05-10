@extends('frontend.layout')

@section('title', 'Clients')

@section('content')
    <div class="card">
        <h1>Clients</h1>
        <p class="muted">Uses authenticated API: <code>GET/POST /api/clients</code>, <code>GET/PUT/DELETE /api/clients/{id}</code>, and <code>POST /api/clients/{id}</code> when uploading a new logo (multipart).</p>
    </div>

    <div class="card">
        <h2 id="form-title">Create client</h2>
        <form id="client-form">
            <input type="hidden" id="client-id">
            <div class="row">
                <div>
                    <label for="name">Name</label>
                    <input id="name" name="name" required>
                </div>
                <div>
                    <label for="page_slug">Page slug</label>
                    <select id="page_slug" name="page_slug" required>
                        <option value="">Loading…</option>
                    </select>
                </div>
                <div>
                    <label for="sort_order">Sort order</label>
                    <input id="sort_order" name="sort_order" type="number" min="0" value="0">
                </div>
            </div>
            <div class="row">
                <div>
                    <label for="link">Website link</label>
                    <input id="link" name="link" placeholder="https://…">
                </div>
                <div>
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>
            <label for="logo">Logo (optional for update)</label>
            <input id="logo" name="logo" type="file" accept="image/*">
            <div class="actions" style="margin-top:12px;">
                <button type="submit" id="save-btn">Save</button>
                <button type="button" class="secondary" id="reset-btn">Reset</button>
            </div>
            <div id="client-message" class="message"></div>
        </form>
    </div>

    <div class="card">
        <h2>All Client</h2>
        <div style="overflow:auto;">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Page</th>
                        <th>Logo</th>
                        <th>Link</th>
                        <th>Sort</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="client-table">
                    <tr><td colspan="8" class="muted">Loading…</td></tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const tableEl  = document.getElementById('client-table');
            const msgEl    = document.getElementById('client-message');
            const formEl   = document.getElementById('client-form');
            const saveBtn  = document.getElementById('save-btn');
            const formTitle = document.getElementById('form-title');

            function setMessage(text, isError) {
                msgEl.textContent = text || '';
                msgEl.className = 'message ' + (isError ? 'err' : 'ok');
            }

            function imageUrl(path) {
                if (!path) return '';
                if (path.indexOf('http') === 0) return path;
                return @json(asset('storage')) + '/' + path.replace(/^\/+/, '');
            }

            function resetForm() {
                formEl.reset();
                document.getElementById('client-id').value = '';
                // document.getElementById('page_slug').value = 'home';
                document.getElementById('sort_order').value = '0';
                document.getElementById('status').value = '1';
                saveBtn.textContent = 'Save';
                formTitle.textContent = 'Create client';
                setMessage('');
            }

            function fillForm(c) {
                document.getElementById('client-id').value   = c.id;
                document.getElementById('name').value        = c.name || '';
                document.getElementById('page_slug').value   = c.page_slug || 'home';
                document.getElementById('sort_order').value  = String(c.sort_order ?? 0);
                document.getElementById('link').value        = c.link || '';
                document.getElementById('status').value      = String(c.status ?? 1);
                saveBtn.textContent  = 'Update';
                formTitle.textContent = 'Edit client #' + c.id;
                setMessage('Edit mode — change fields and save. Leave logo empty to keep the current file.');
            }

             async function loadPageSlug() {
                try {
                    const res = await window.FrontendApi.apiFetch('{{ url('/api/pageslug') }}', {
                        method: 'GET',
                        headers: window.FrontendApi.authHeadersJson()  // ← auth header fix
                    });
                    const items = (res && res.data) ? res.data : [];
                    const sel = document.getElementById('page_slug');
                    if (!sel) return;
                    if (!items.length) {
                        sel.innerHTML = '<option value="home">home</option>';
                        return;
                    }
                    sel.innerHTML = items.map(function (p) {
                        return `<option value="${p.slug}">${p.name} → ${p.slug}</option>`;
                    }).join('');
                } catch (err) {
                    console.error('loadPageSlug error:', err);
                    const sel = document.getElementById('page_slug');
                    if (sel) sel.innerHTML = '<option value="home">home</option>';
                }
            }

            async function loadTable() {
                const res  = await window.FrontendApi.apiFetch('{{ url('/api/clients') }}', {
                    method: 'GET',
                    headers: window.FrontendApi.authHeadersJson()
                });
                const rows = (res && res.data) ? res.data : [];
                if (!rows.length) {
                    tableEl.innerHTML = '<tr><td colspan="8" class="muted">No Client yet.</td></tr>';
                    return;
                }
                tableEl.innerHTML = rows.map(function (c) {
                    const img = c.logo
                        ? '<img class="thumb" src="' + imageUrl(c.logo) + '" alt="">'
                        : '<span class="muted">—</span>';
                    const statusBadge = c.status == 1
                        ? '<span style="color:green;">Active</span>'
                        : '<span style="color:red;">Inactive</span>';
                    return '<tr>' +
                        '<td>' + c.id + '</td>' +
                        '<td>' + (c.name || '') + '</td>' +
                        '<td>' + (c.page_slug || '') + '</td>' +
                        '<td>' + img + '</td>' +
                        '<td>' + (c.link ? '<a href="' + c.link + '" target="_blank">Link</a>' : '—') + '</td>' +
                        '<td>' + (c.sort_order ?? '') + '</td>' +
                        '<td>' + statusBadge + '</td>' +
                        '<td class="actions">' +
                        '<button type="button" data-action="edit"   data-id="' + c.id + '">Edit</button> ' +
                        '<button type="button" class="danger" data-action="delete" data-id="' + c.id + '">Delete</button>' +
                        '</td>' +
                        '</tr>';
                }).join('');
            }

            formEl.addEventListener('submit', async function (e) {
                e.preventDefault();
                setMessage('');
                const id        = document.getElementById('client-id').value;
                const fileInput = document.getElementById('logo');
                const hasFile   = fileInput.files && fileInput.files[0];

                try {
                    if (id && hasFile) {
                        // UPDATE with new logo — multipart POST
                        const fd = new FormData();
                        fd.append('name',       document.getElementById('name').value);
                        fd.append('page_slug',  document.getElementById('page_slug').value);
                        fd.append('sort_order', document.getElementById('sort_order').value || '0');
                        fd.append('link',       document.getElementById('link').value);
                        fd.append('status',     document.getElementById('status').value);
                        fd.append('logo',       fileInput.files[0]);
                        await window.FrontendApi.apiFetch('{{ url('/api/clients') }}/' + id, {
                            method: 'POST',
                            headers: window.FrontendApi.authHeadersMultipart(),
                            body: fd
                        });
                        setMessage('Client updated (logo replaced).');

                    } else if (id) {
                        // UPDATE without new logo — JSON PUT
                        const payload = {
                            name:       document.getElementById('name').value,
                            page_slug:  document.getElementById('page_slug').value,
                            sort_order: Number(document.getElementById('sort_order').value) || 0,
                            link:       document.getElementById('link').value || null,
                            status:     Number(document.getElementById('status').value)
                        };
                        await window.FrontendApi.apiFetch('{{ url('/api/clients') }}/' + id, {
                            method: 'PUT',
                            headers: window.FrontendApi.authHeadersJson(),
                            body: JSON.stringify(payload)
                        });
                        setMessage('Client updated.');

                    } else {
                        // CREATE — multipart POST
                        const fd = new FormData();
                        fd.append('name',       document.getElementById('name').value);
                        fd.append('page_slug',  document.getElementById('page_slug').value);
                        fd.append('sort_order', document.getElementById('sort_order').value || '0');
                        fd.append('link',       document.getElementById('link').value);
                        fd.append('status',     document.getElementById('status').value);
                        if (hasFile) fd.append('logo', fileInput.files[0]);
                        await window.FrontendApi.apiFetch('{{ url('/api/clients') }}', {
                            method: 'POST',
                            headers: window.FrontendApi.authHeadersMultipart(),
                            body: fd
                        });
                        setMessage('Client created.');
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
                const cid    = btn.getAttribute('data-id');
                if (!cid) return;

                try {
                    if (action === 'edit') {
                        const data = await window.FrontendApi.apiFetch('{{ url('/api/clients') }}/' + cid, {
                            method: 'GET',
                            headers: window.FrontendApi.authHeadersJson()
                        });
                        fillForm(data.data);
                    }
                    if (action === 'delete') {
                        if (!window.confirm('Delete client #' + cid + '?')) return;
                        await window.FrontendApi.apiFetch('{{ url('/api/clients') }}/' + cid, {
                            method: 'DELETE',
                            headers: window.FrontendApi.authHeadersJson()
                        });
                        setMessage('Client deleted.');
                        await loadTable();
                    }
                } catch (err) {
                    setMessage(err.message || 'Action failed.', true);
                }
            });

            // REPLACE with this:
            loadPageSlug().then(function () {
                loadTable().catch(function (err) {
                    tableEl.innerHTML = '<tr><td colspan="7" class="err">' + (err.message || 'Failed to load sliders.') + '</td></tr>';
                });
            }).catch(function () {
                loadTable().catch(function (err) {
                    tableEl.innerHTML = '<tr><td colspan="7" class="err">' + (err.message || 'Failed to load sliders.') + '</td></tr>';
                });
            });
        })();
    </script>
@endpush