@extends('frontend.layout')

@section('title', 'Testimonials')

@section('content')
    <div class="card">
        <h1>Testimonials</h1>
        <p class="muted">Uses authenticated API: <code>GET/POST /api/testimonials</code>, <code>GET/PUT/DELETE /api/testimonials/{id}</code>, and <code>POST /api/testimonials/{id}</code> when uploading a new image (multipart).</p>
    </div>

    <div class="card">
        <h2 id="form-title">Create testimonial</h2>
        <form id="testimonial-form">
            <input type="hidden" id="testimonial-id">
            <div class="row">
                <div>
                    <label for="name">Name</label>
                    <input id="name" name="name" required>
                </div>
                <div>
                    <label for="designation">Designation</label>
                    <input id="designation" name="designation" placeholder="e.g. CEO">
                </div>
                <div>
                    <label for="company">Company</label>
                    <input id="company" name="company">
                </div>
            </div>
            <div class="row">
                <div>
                    <label for="page_slug">Page slug</label>
                    <select id="page_slug" name="page_slug" required>
                        <option value="">Loading…</option>
                    </select>
                </div>
                <div>
                    <label for="rating">Rating (1–5)</label>
                    <input id="rating" name="rating" type="number" min="1" max="5" value="5">
                </div>
                <div>
                    <label for="sort_order">Sort order</label>
                    <input id="sort_order" name="sort_order" type="number" min="0" value="0">
                </div>
                <div>
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>
            <label for="message">Message</label>
            <textarea id="message" name="message" placeholder="Review / testimonial text"></textarea>
            <label for="image">Profile image (optional for update)</label>
            <input id="image" name="image" type="file" accept="image/*">
            <div class="actions" style="margin-top:12px;">
                <button type="submit" id="save-btn">Save</button>
                <button type="button" class="secondary" id="reset-btn">Reset</button>
            </div>
            <div id="testimonial-message" class="message"></div>
        </form>
    </div>

    <div class="card">
        <h2>All testimonials</h2>
        <div style="overflow:auto;">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Designation</th>
                        <th>Company</th>
                        <th>Rating</th>
                        <th>Image</th>
                        <th>Page</th>
                        <th>Sort</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="testimonial-table">
                    <tr><td colspan="10" class="muted">Loading…</td></tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const tableEl   = document.getElementById('testimonial-table');
            const msgEl     = document.getElementById('testimonial-message');
            const formEl    = document.getElementById('testimonial-form');
            const saveBtn   = document.getElementById('save-btn');
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

            function starsHtml(rating) {
                const n = parseInt(rating) || 0;
                return '★'.repeat(Math.min(n, 5)) + '☆'.repeat(Math.max(0, 5 - n));
            }

            function resetForm() {
                formEl.reset();
                document.getElementById('testimonial-id').value = '';
                // document.getElementById('page_slug').value = 'home';
                document.getElementById('rating').value = '5';
                document.getElementById('sort_order').value = '0';
                document.getElementById('status').value = '1';
                saveBtn.textContent = 'Save';
                formTitle.textContent = 'Create testimonial';
                setMessage('');
            }

            function fillForm(t) {
                document.getElementById('testimonial-id').value = t.id;
                document.getElementById('name').value          = t.name || '';
                document.getElementById('designation').value   = t.designation || '';
                document.getElementById('company').value       = t.company || '';
                document.getElementById('page_slug').value     = t.page_slug || '';
                document.getElementById('rating').value        = String(t.rating ?? 5);
                document.getElementById('sort_order').value    = String(t.sort_order ?? 0);
                document.getElementById('status').value        = String(t.status ?? 1);
                document.getElementById('message').value       = t.message || '';
                saveBtn.textContent  = 'Update';
                formTitle.textContent = 'Edit testimonial #' + t.id;
                setMessage('Edit mode — change fields and save. Leave image empty to keep the current file.');
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
                const res  = await window.FrontendApi.apiFetch('{{ url('/api/testimonials') }}', {
                    method: 'GET',
                    headers: window.FrontendApi.authHeadersJson()
                });
                const rows = (res && res.data) ? res.data : [];
                if (!rows.length) {
                    tableEl.innerHTML = '<tr><td colspan="10" class="muted">No testimonials yet.</td></tr>';
                    return;
                }
                tableEl.innerHTML = rows.map(function (t) {
                    const img = t.image
                        ? '<img class="thumb" src="' + imageUrl(t.image) + '" alt="">'
                        : '<span class="muted">—</span>';
                    const statusBadge = t.status == 1
                        ? '<span style="color:green;">Active</span>'
                        : '<span style="color:red;">Inactive</span>';
                    return '<tr>' +
                        '<td>' + t.id + '</td>' +
                        '<td>' + (t.name || '') + '</td>' +
                        '<td>' + (t.designation || '—') + '</td>' +
                        '<td>' + (t.company || '—') + '</td>' +
                        '<td style="color:#f5a623;">' + starsHtml(t.rating) + '</td>' +
                        '<td>' + img + '</td>' +
                        '<td>' + (t.page_slug || '') + '</td>' +
                        '<td>' + (t.sort_order ?? '') + '</td>' +
                        '<td>' + statusBadge + '</td>' +
                        '<td class="actions">' +
                        '<button type="button" data-action="edit"   data-id="' + t.id + '">Edit</button> ' +
                        '<button type="button" class="danger" data-action="delete" data-id="' + t.id + '">Delete</button>' +
                        '</td>' +
                        '</tr>';
                }).join('');
            }

            formEl.addEventListener('submit', async function (e) {
                e.preventDefault();
                setMessage('');
                const id        = document.getElementById('testimonial-id').value;
                const fileInput = document.getElementById('image');
                const hasFile   = fileInput.files && fileInput.files[0];

                function collectFd(fd) {
                    fd.append('name',        document.getElementById('name').value);
                    fd.append('designation', document.getElementById('designation').value);
                    fd.append('company',     document.getElementById('company').value);
                    fd.append('page_slug',   document.getElementById('page_slug').value);
                    fd.append('rating',      document.getElementById('rating').value || '5');
                    fd.append('sort_order',  document.getElementById('sort_order').value || '0');
                    fd.append('status',      document.getElementById('status').value);
                    fd.append('message',     document.getElementById('message').value);
                }

                try {
                    if (id && hasFile) {
                        // UPDATE with new image — multipart POST
                        const fd = new FormData();
                        collectFd(fd);
                        fd.append('image', fileInput.files[0]);
                        await window.FrontendApi.apiFetch('{{ url('/api/testimonials') }}/' + id, {
                            method: 'POST',
                            headers: window.FrontendApi.authHeadersMultipart(),
                            body: fd
                        });
                        setMessage('Testimonial updated (image replaced).');

                    } else if (id) {
                        // UPDATE without new image — JSON PUT
                        const payload = {
                            name:        document.getElementById('name').value,
                            designation: document.getElementById('designation').value || null,
                            company:     document.getElementById('company').value || null,
                            page_slug:   document.getElementById('page_slug').value,
                            rating:      Number(document.getElementById('rating').value) || 5,
                            sort_order:  Number(document.getElementById('sort_order').value) || 0,
                            status:      Number(document.getElementById('status').value),
                            message:     document.getElementById('message').value || null
                        };
                        await window.FrontendApi.apiFetch('{{ url('/api/testimonials') }}/' + id, {
                            method: 'PUT',
                            headers: window.FrontendApi.authHeadersJson(),
                            body: JSON.stringify(payload)
                        });
                        setMessage('Testimonial updated.');

                    } else {
                        // CREATE — multipart POST
                        const fd = new FormData();
                        collectFd(fd);
                        if (hasFile) fd.append('image', fileInput.files[0]);
                        await window.FrontendApi.apiFetch('{{ url('/api/testimonials') }}', {
                            method: 'POST',
                            headers: window.FrontendApi.authHeadersMultipart(),
                            body: fd
                        });
                        setMessage('Testimonial created.');
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
                const tid    = btn.getAttribute('data-id');
                if (!tid) return;

                try {
                    if (action === 'edit') {
                        const data = await window.FrontendApi.apiFetch('{{ url('/api/testimonials') }}/' + tid, {
                            method: 'GET',
                            headers: window.FrontendApi.authHeadersJson()
                        });
                        fillForm(data.data);
                    }
                    if (action === 'delete') {
                        if (!window.confirm('Delete testimonial #' + tid + '?')) return;
                        await window.FrontendApi.apiFetch('{{ url('/api/testimonials') }}/' + tid, {
                            method: 'DELETE',
                            headers: window.FrontendApi.authHeadersJson()
                        });
                        setMessage('Testimonial deleted.');
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