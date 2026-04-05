@extends('frontend.layout')

@section('title', 'Sliders')

@section('content')
    <div class="card">
        <h1>Sliders</h1>
        <p class="muted">Uses authenticated API: <code>GET/POST /api/sliders</code>, <code>GET/PUT/DELETE /api/sliders/{id}</code>, and <code>POST /api/sliders/{id}</code> when uploading a new image (multipart).</p>
    </div>

    <div class="card">
        <h2 id="form-title">Create slider</h2>
        <form id="slider-form">
            <input type="hidden" id="slider-id">
            <div class="row">
                <div>
                    <label for="page_slug">Page slug</label>
                    <input id="page_slug" name="page_slug" value="home" required>
                </div>
                <div>
                    <label for="section_key">Section key</label>
                    <input id="section_key" name="section_key" value="slider" required>
                </div>
                <div>
                    <label for="sort_order">Sort order</label>
                    <input id="sort_order" name="sort_order" type="number" min="0" value="0">
                </div>
            </div>
            <div class="row">
                <div>
                    <label for="title">Title</label>
                    <input id="title" name="title">
                </div>
                <div>
                    <label for="subtitle">Subtitle</label>
                    <input id="subtitle" name="subtitle">
                </div>
                <div>
                    <label for="link">Link</label>
                    <input id="link" name="link" placeholder="https://…">
                </div>
            </div>
            <label for="html_content">HTML content</label>
            <textarea id="html_content" name="html_content" placeholder="Optional rich text / HTML"></textarea>
            <label for="image">Image (optional for update)</label>
            <input id="image" name="image" type="file" accept="image/*">
            <div class="actions" style="margin-top:12px;">
                <button type="submit" id="save-btn">Save</button>
                <button type="button" class="secondary" id="reset-btn">Reset</button>
            </div>
            <div id="slider-message" class="message"></div>
        </form>
    </div>

    <div class="card">
        <h2>All sliders</h2>
        <div style="overflow:auto;">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Page</th>
                        <th>Section</th>
                        <th>Title</th>
                        <th>Image</th>
                        <th>Sort</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="slider-table">
                    <tr><td colspan="7" class="muted">Loading…</td></tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const tableEl = document.getElementById('slider-table');
            const msgEl = document.getElementById('slider-message');
            const formEl = document.getElementById('slider-form');
            const saveBtn = document.getElementById('save-btn');
            const formTitle = document.getElementById('form-title');

            function setMessage(text, isError) {
                msgEl.textContent = text || '';
                msgEl.className = 'message ' + (isError ? 'err' : 'ok');
            }

            function imageUrl(path) {
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
                document.getElementById('slider-id').value = '';
                document.getElementById('page_slug').value = 'home';
                document.getElementById('section_key').value = 'slider';
                document.getElementById('sort_order').value = '0';
                saveBtn.textContent = 'Save';
                formTitle.textContent = 'Create slider';
                setMessage('');
            }

            function fillForm(s) {
                document.getElementById('slider-id').value = s.id;
                document.getElementById('page_slug').value = s.page_slug || 'home';
                document.getElementById('section_key').value = s.section_key || 'slider';
                document.getElementById('sort_order').value = String(s.sort_order ?? 0);
                document.getElementById('title').value = s.title || '';
                document.getElementById('subtitle').value = s.subtitle || '';
                document.getElementById('link').value = s.link || '';
                document.getElementById('html_content').value = s.html_content || s.description || '';
                saveBtn.textContent = 'Update';
                formTitle.textContent = 'Edit slider #' + s.id;
                setMessage('Edit mode — change fields and save. Leave image empty to keep the current file.');
            }

            async function loadTable() {
                const res = await window.FrontendApi.apiFetch('{{ url('/api/sliders') }}', {
                    method: 'GET',
                    headers: window.FrontendApi.authHeadersJson()
                });
                const rows = (res && res.data) ? res.data : [];
                if (!rows.length) {
                    tableEl.innerHTML = '<tr><td colspan="7" class="muted">No sliders yet.</td></tr>';
                    return;
                }
                tableEl.innerHTML = rows.map(function (s) {
                    const img = s.image
                        ? '<img class="thumb" src="' + imageUrl(s.image) + '" alt="">'
                        : '<span class="muted">—</span>';
                    return '<tr>' +
                        '<td>' + s.id + '</td>' +
                        '<td>' + (s.page_slug || '') + '</td>' +
                        '<td>' + (s.section_key || '') + '</td>' +
                        '<td>' + (s.title || '') + '</td>' +
                        '<td>' + img + '</td>' +
                        '<td>' + (s.sort_order ?? '') + '</td>' +
                        '<td class="actions">' +
                        '<button type="button" data-action="edit" data-id="' + s.id + '">Edit</button> ' +
                        '<button type="button" class="danger" data-action="delete" data-id="' + s.id + '">Delete</button>' +
                        '</td>' +
                        '</tr>';
                }).join('');
            }

            formEl.addEventListener('submit', async function (e) {
                e.preventDefault();
                setMessage('');
                const id = document.getElementById('slider-id').value;
                const fileInput = document.getElementById('image');
                const hasFile = fileInput.files && fileInput.files[0];

                try {
                    if (id && hasFile) {
                        const fd = new FormData();
                        fd.append('page_slug', document.getElementById('page_slug').value);
                        fd.append('section_key', document.getElementById('section_key').value);
                        fd.append('title', document.getElementById('title').value);
                        fd.append('subtitle', document.getElementById('subtitle').value);
                        fd.append('html_content', document.getElementById('html_content').value);
                        fd.append('link', document.getElementById('link').value);
                        fd.append('sort_order', document.getElementById('sort_order').value || '0');
                        fd.append('image', fileInput.files[0]);
                        await window.FrontendApi.apiFetch('{{ url('/api/sliders') }}/' + id, {
                            method: 'POST',
                            headers: window.FrontendApi.authHeadersMultipart(),
                            body: fd
                        });
                        setMessage('Slider updated (image replaced).');
                    } else if (id) {
                        const payload = {
                            page_slug: document.getElementById('page_slug').value,
                            section_key: document.getElementById('section_key').value,
                            title: document.getElementById('title').value || null,
                            subtitle: document.getElementById('subtitle').value || null,
                            html_content: document.getElementById('html_content').value || null,
                            link: document.getElementById('link').value || null,
                            sort_order: document.getElementById('sort_order').value ? Number(document.getElementById('sort_order').value) : 0
                        };
                        await window.FrontendApi.apiFetch('{{ url('/api/sliders') }}/' + id, {
                            method: 'PUT',
                            headers: window.FrontendApi.authHeadersJson(),
                            body: JSON.stringify(payload)
                        });
                        setMessage('Slider updated.');
                    } else {
                        const fd = new FormData();
                        fd.append('page_slug', document.getElementById('page_slug').value);
                        fd.append('section_key', document.getElementById('section_key').value);
                        fd.append('title', document.getElementById('title').value);
                        fd.append('subtitle', document.getElementById('subtitle').value);
                        fd.append('html_content', document.getElementById('html_content').value);
                        fd.append('link', document.getElementById('link').value);
                        fd.append('sort_order', document.getElementById('sort_order').value || '0');
                        if (hasFile) {
                            fd.append('image', fileInput.files[0]);
                        }
                        await window.FrontendApi.apiFetch('{{ url('/api/sliders') }}', {
                            method: 'POST',
                            headers: window.FrontendApi.authHeadersMultipart(),
                            body: fd
                        });
                        setMessage('Slider created.');
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
                if (!btn) {
                    return;
                }
                const action = btn.getAttribute('data-action');
                const sid = btn.getAttribute('data-id');
                if (!sid) {
                    return;
                }
                try {
                    if (action === 'edit') {
                        const data = await window.FrontendApi.apiFetch('{{ url('/api/sliders') }}/' + sid, {
                            method: 'GET',
                            headers: window.FrontendApi.authHeadersJson()
                        });
                        fillForm(data.data);
                    }
                    if (action === 'delete') {
                        if (!window.confirm('Delete slider #' + sid + '?')) {
                            return;
                        }
                        await window.FrontendApi.apiFetch('{{ url('/api/sliders') }}/' + sid, {
                            method: 'DELETE',
                            headers: window.FrontendApi.authHeadersJson()
                        });
                        setMessage('Slider deleted.');
                        await loadTable();
                    }
                } catch (err) {
                    setMessage(err.message || 'Action failed.', true);
                }
            });

            loadTable().catch(function (err) {
                tableEl.innerHTML = '<tr><td colspan="7" class="err">' + (err.message || 'Failed to load sliders.') + '</td></tr>';
            });
        })();
    </script>
@endpush
