@extends('frontend.layout')

@section('title', 'Portfolio')

@section('content')
    <div class="card">
        <h1>Portfolio</h1>
        <p class="muted">CRUD via <code>/api/portfolio</code> (Bearer token). Edit loads all fields from <code>GET /api/portfolio/{id}</code>.</p>
    </div>

    <div class="card">
        <h2 id="form-title">Create portfolio item</h2>
        <form id="portfolio-form">
            <input type="hidden" id="portfolio-id">

            <div class="row">
                <div>
                    <label for="pf-title">Title</label>
                    <input id="pf-title" required maxlength="255" placeholder="Title">
                </div>
                <div>
                    <label for="pf-subtitle">Subtitle</label>
                    <input id="pf-subtitle" maxlength="255" placeholder="Subtitle">
                </div>
                <div>
                    <label for="pf-slug">Slug (optional)</label>
                    <input id="pf-slug" maxlength="255" placeholder="Auto from title if empty">
                </div>
            </div>

            <div class="row">
                <div>
                    <label for="pf-page-slug">Page slug</label>
                    <input id="pf-page-slug" maxlength="255" placeholder="e.g. home" value="home">
                </div>
                <div>
                    <label for="pf-category-id">Category</label>
                    <select id="pf-category-id">
                        <option value="">— None —</option>
                    </select>
                </div>
                <div>
                    <label for="pf-order">Order</label>
                    <input id="pf-order" type="number" min="0" value="0">
                </div>
            </div>

            <div class="row">
                <div>
                    <label for="pf-link">Link</label>
                    <input id="pf-link" maxlength="500" placeholder="https://…">
                </div>
                <div>
                    <label for="pf-meta-title">Meta title (SEO)</label>
                    <input id="pf-meta-title" maxlength="255" placeholder="Meta title">
                </div>
                <div>
                    <label for="pf-meta-description">Meta description</label>
                    <input id="pf-meta-description" maxlength="1000" placeholder="Meta description">
                </div>
            </div>

            <label for="pf-content">Content</label>
            <textarea id="pf-content" placeholder="Main content"></textarea>

            <label for="pf-json-data">JSON data (optional object)</label>
            <textarea id="pf-json-data" placeholder='e.g. {"key":"value"}' style="min-height:72px;font-family:monospace;font-size:12px;"></textarea>

            <label for="pf-image">Image</label>
            <input id="pf-image" type="file" accept="image/*">
            <p class="muted" id="pf-current-image-wrap" style="display:none;margin-top:8px;">
                Current: <span id="pf-current-image-label"></span><br>
                <img id="pf-current-image-thumb" class="thumb" alt="" style="margin-top:6px;max-width:120px;">
            </p>

            <div class="row" style="margin-top:12px;">
                <label><input type="checkbox" id="pf-is-featured"> Featured</label>
                <label><input type="checkbox" id="pf-status" checked> Active</label>
            </div>

            <div class="actions" style="margin-top:12px;">
                <button type="submit" id="pf-save-btn">Save</button>
                <button type="button" class="secondary" id="pf-reset-btn">Reset</button>
            </div>
            <div id="pf-msg" class="message"></div>
        </form>
    </div>

    <div class="card">
        <h2>All portfolio items</h2>
        <div style="overflow:auto;">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Page</th>
                        <th>Category</th>
                        <th>Image</th>
                        <th>Order</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="pf-table">
                    <tr><td colspan="8" class="muted">Loading…</td></tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const apiBase = @json(url('/api/portfolio'));
            const apiCategories = @json(url('/api/portfolio-categories'));
            const storageBase = @json(asset('storage'));

            const tableEl = document.getElementById('pf-table');
            const msgEl = document.getElementById('pf-msg');
            const formEl = document.getElementById('portfolio-form');
            const saveBtn = document.getElementById('pf-save-btn');
            const formTitle = document.getElementById('form-title');
            const categorySelect = document.getElementById('pf-category-id');

            function setMsg(text, isError) {
                msgEl.textContent = text || '';
                msgEl.className = 'message ' + (isError ? 'err' : 'ok');
            }

            function imageUrl(path) {
                if (!path) return '';
                if (String(path).indexOf('http') === 0) return path;
                return storageBase.replace(/\/$/, '') + '/' + String(path).replace(/^\/+/, '');
            }

            function resetForm() {
                formEl.reset();
                document.getElementById('portfolio-id').value = '';
                document.getElementById('pf-page-slug').value = 'home';
                document.getElementById('pf-order').value = '0';
                document.getElementById('pf-status').checked = true;
                document.getElementById('pf-is-featured').checked = false;
                document.getElementById('pf-image').value = '';
                document.getElementById('pf-current-image-wrap').style.display = 'none';
                saveBtn.textContent = 'Save';
                formTitle.textContent = 'Create portfolio item';
                categorySelect.value = '';
                setMsg('');
            }

            function fillForm(d) {
                document.getElementById('portfolio-id').value = d.id;
                document.getElementById('pf-title').value = d.title || '';
                document.getElementById('pf-subtitle').value = d.subtitle || '';
                document.getElementById('pf-slug').value = d.slug || '';
                document.getElementById('pf-page-slug').value = d.page_slug || '';
                document.getElementById('pf-order').value = String(d.order ?? 0);
                document.getElementById('pf-link').value = d.link || '';
                document.getElementById('pf-meta-title').value = d.meta_title || '';
                document.getElementById('pf-meta-description').value = d.meta_description || '';
                document.getElementById('pf-content').value = d.content || '';
                document.getElementById('pf-json-data').value = d.json_data
                    ? JSON.stringify(d.json_data, null, 2)
                    : '';
                document.getElementById('pf-is-featured').checked = Boolean(d.is_featured);
                document.getElementById('pf-status').checked = d.status !== false && d.status !== 0;

                if (d.category_id) {
                    categorySelect.value = String(d.category_id);
                } else {
                    categorySelect.value = '';
                }

                const wrap = document.getElementById('pf-current-image-wrap');
                if (d.image) {
                    wrap.style.display = 'block';
                    document.getElementById('pf-current-image-label').textContent = d.image;
                    document.getElementById('pf-current-image-thumb').src = imageUrl(d.image);
                } else {
                    wrap.style.display = 'none';
                }

                document.getElementById('pf-image').value = '';
                saveBtn.textContent = 'Update';
                formTitle.textContent = 'Edit portfolio #' + d.id;
                setMsg('Edit mode — change fields and save. Leave image empty to keep the current file.');
            }

            function buildPayloadFromForm() {
                const jsonRaw = document.getElementById('pf-json-data').value.trim();
                let jsonData = null;
                if (jsonRaw) {
                    try {
                        jsonData = JSON.parse(jsonRaw);
                        if (jsonData !== null && typeof jsonData !== 'object') {
                            throw new Error('JSON data must be an object.');
                        }
                    } catch (e) {
                        throw new Error('Invalid JSON in JSON data field: ' + (e.message || 'parse error'));
                    }
                }

                const cat = document.getElementById('pf-category-id').value;
                const payload = {
                    title: document.getElementById('pf-title').value.trim(),
                    subtitle: document.getElementById('pf-subtitle').value.trim() || null,
                    slug: document.getElementById('pf-slug').value.trim() || null,
                    page_slug: document.getElementById('pf-page-slug').value.trim() || null,
                    link: document.getElementById('pf-link').value.trim() || null,
                    meta_title: document.getElementById('pf-meta-title').value.trim() || null,
                    meta_description: document.getElementById('pf-meta-description').value.trim() || null,
                    content: document.getElementById('pf-content').value.trim() || null,
                    order: document.getElementById('pf-order').value ? parseInt(document.getElementById('pf-order').value, 10) : 0,
                    is_featured: document.getElementById('pf-is-featured').checked,
                    status: document.getElementById('pf-status').checked,
                    json_data: jsonData,
                    category_id: cat ? parseInt(cat, 10) : null,
                };
                return payload;
            }

            async function loadCategories() {
                try {
                    const res = await window.FrontendApi.apiFetch(apiCategories, {
                        method: 'GET',
                        headers: window.FrontendApi.authHeadersJson()
                    });
                    const rows = (res && res.data) ? res.data : [];
                    const keep = categorySelect.querySelector('option[value=""]');
                    categorySelect.innerHTML = '';
                    categorySelect.appendChild(keep);
                    rows.forEach(function (c) {
                        const opt = document.createElement('option');
                        opt.value = String(c.id);
                        opt.textContent = c.name + ' (#' + c.id + ')';
                        categorySelect.appendChild(opt);
                    });
                } catch (e) {
                    /* optional — table may be empty */
                }
            }

            async function loadTable() {
                const res = await window.FrontendApi.apiFetch(apiBase, {
                    method: 'GET',
                    headers: window.FrontendApi.authHeadersJson()
                });
                const rows = (res && res.data) ? res.data : [];
                if (!rows.length) {
                    tableEl.innerHTML = '<tr><td colspan="8" class="muted">No portfolio items yet.</td></tr>';
                    return;
                }
                tableEl.innerHTML = rows.map(function (d) {
                    const img = d.image
                        ? '<img class="thumb" src="' + imageUrl(d.image) + '" alt="">'
                        : '<span class="muted">—</span>';
                    const cat = (d.category && d.category.name) ? d.category.name : '—';
                    const st = (d.status !== false && d.status !== 0) ? 'Active' : 'Inactive';
                    return '<tr>' +
                        '<td>' + d.id + '</td>' +
                        '<td>' + (d.title || '') + '</td>' +
                        '<td>' + (d.page_slug || '') + '</td>' +
                        '<td>' + cat + '</td>' +
                        '<td>' + img + '</td>' +
                        '<td>' + (d.order ?? '') + '</td>' +
                        '<td>' + st + '</td>' +
                        '<td class="actions">' +
                        '<button type="button" class="pf-edit" data-id="' + d.id + '">Edit</button> ' +
                        '<button type="button" class="danger pf-del" data-id="' + d.id + '">Delete</button>' +
                        '</td></tr>';
                }).join('');
            }

            formEl.addEventListener('submit', async function (e) {
                e.preventDefault();
                setMsg('');
                const id = document.getElementById('portfolio-id').value;
                const fileInput = document.getElementById('pf-image');
                const hasFile = fileInput.files && fileInput.files[0];

                try {
                    const payload = buildPayloadFromForm();

                    if (id && hasFile) {
                        const fd = new FormData();
                        Object.keys(payload).forEach(function (key) {
                            const v = payload[key];
                            if (v === null || v === undefined) return;
                            if (key === 'json_data' && typeof v === 'object') {
                                fd.append(key, JSON.stringify(v));
                            } else if (key === 'is_featured' || key === 'status') {
                                fd.append(key, v ? '1' : '0');
                            } else {
                                fd.append(key, v);
                            }
                        });
                        fd.append('image', fileInput.files[0]);
                        await window.FrontendApi.apiFetch(apiBase + '/' + id, {
                            method: 'POST',
                            headers: window.FrontendApi.authHeadersMultipart(),
                            body: fd
                        });
                        setMsg('Updated (new image saved).');
                    } else if (id) {
                        await window.FrontendApi.apiFetch(apiBase + '/' + id, {
                            method: 'PUT',
                            headers: window.FrontendApi.authHeadersJson(),
                            body: JSON.stringify(payload)
                        });
                        setMsg('Updated.');
                    } else {
                        if (hasFile) {
                            const fd = new FormData();
                            Object.keys(payload).forEach(function (key) {
                                const v = payload[key];
                                if (v === null || v === undefined) return;
                                if (key === 'json_data' && typeof v === 'object') {
                                    fd.append(key, JSON.stringify(v));
                                } else if (key === 'is_featured' || key === 'status') {
                                    fd.append(key, v ? '1' : '0');
                                } else {
                                    fd.append(key, v);
                                }
                            });
                            fd.append('image', fileInput.files[0]);
                            await window.FrontendApi.apiFetch(apiBase, {
                                method: 'POST',
                                headers: window.FrontendApi.authHeadersMultipart(),
                                body: fd
                            });
                        } else {
                            await window.FrontendApi.apiFetch(apiBase, {
                                method: 'POST',
                                headers: window.FrontendApi.authHeadersJson(),
                                body: JSON.stringify(payload)
                            });
                        }
                        setMsg('Created.');
                    }

                    resetForm();
                    await loadTable();
                } catch (err) {
                    setMsg(err.message || 'Save failed.', true);
                }
            });

            document.getElementById('pf-reset-btn').addEventListener('click', resetForm);

            tableEl.addEventListener('click', async function (e) {
                const editBtn = e.target.closest('.pf-edit');
                const delBtn = e.target.closest('.pf-del');
                if (editBtn) {
                    const pid = editBtn.getAttribute('data-id');
                    try {
                        const data = await window.FrontendApi.apiFetch(apiBase + '/' + pid, {
                            method: 'GET',
                            headers: window.FrontendApi.authHeadersJson()
                        });
                        fillForm(data.data);
                    } catch (err) {
                        setMsg(err.message || 'Could not load item.', true);
                    }
                    return;
                }
                if (delBtn) {
                    const pid = delBtn.getAttribute('data-id');
                    if (!window.confirm('Delete portfolio #' + pid + '?')) return;
                    try {
                        await window.FrontendApi.apiFetch(apiBase + '/' + pid, {
                            method: 'DELETE',
                            headers: window.FrontendApi.authHeadersJson()
                        });
                        setMsg('Deleted.');
                        await loadTable();
                    } catch (err) {
                        setMsg(err.message || 'Delete failed.', true);
                    }
                }
            });

            (async function init() {
                try {
                    await loadCategories();
                    await loadTable();
                } catch (err) {
                    tableEl.innerHTML = '<tr><td colspan="8" class="err">' + (err.message || 'Failed to load.') + '</td></tr>';
                }
            })();
        })();
    </script>
@endpush
