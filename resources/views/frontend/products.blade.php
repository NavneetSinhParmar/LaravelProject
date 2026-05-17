@extends('frontend.layout')

@section('title', 'Products')

@section('content')
    <div class="card">
        <h1>Products</h1>
        <p class="muted">
            Manage downloadable products. Public download:
            <code>POST /api/products/{id}/download</code>
            (max 3 downloads per IP per product).
        </p>
    </div>

    <div class="card">
        <h2 id="form-title">Create product</h2>
        <form id="product-form">
            <input type="hidden" id="product-id">

            <div class="row">
                <div>
                    <label for="name">Product name</label>
                    <input id="name" required>
                </div>
                <div>
                    <label for="status">Status</label>
                    <select id="status">
                        <option value="1" selected>Active</option>
                        <option value="0">Deactive</option>
                    </select>
                </div>
                <div>
                    <label for="category_id">Category</label>
                    <select id="category_id">
                        <option value="">— None —</option>
                    </select>
                </div>
            </div>

            <label for="description">Description (HTML supported)</label>
            <textarea id="description" rows="5" placeholder="Product description…"></textarea>

            <label for="seo_tags">SEO tags (comma-separated)</label>
            <input id="seo_tags" placeholder="tag1, tag2, tag3">

            <div class="row">
                <div>
                    <label for="primary_image">Primary image</label>
                    <input id="primary_image" type="file" accept="image/*">
                    <p class="muted" id="primary-image-hint" style="margin-top:4px;"></p>
                </div>
                <div>
                    <label for="download_file">Download file</label>
                    <input id="download_file" type="file">
                    <p class="muted" id="download-file-hint" style="margin-top:4px;"></p>
                </div>
            </div>

            <div class="actions" style="margin-top:12px;">
                <button type="submit" id="save-btn">Save</button>
                <button type="button" class="secondary" id="reset-btn">Reset</button>
            </div>
            <div id="product-message" class="message"></div>
        </form>
    </div>

    <div class="card">
        <h2>Product list</h2>
        <div style="overflow:auto;">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Downloads</th>
                        <th>Image</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="product-table">
                    <tr><td colspan="7" class="muted">Loading…</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal-backdrop" id="detail-modal" role="dialog" aria-modal="true">
        <div class="modal">
            <div style="display:flex; justify-content:space-between; align-items:center; gap:12px;">
                <h2 style="margin:0;">Product detail</h2>
                <button type="button" class="secondary" id="detail-close">Close</button>
            </div>
            <p class="muted" style="margin:8px 0 12px;">Includes download count and recent download history.</p>
            <pre class="detail" id="detail-json"></pre>
        </div>
    </div>
@endsection

@push('scripts')
<script>
(function () {
    const tableEl = document.getElementById('product-table');
    const msgEl = document.getElementById('product-message');
    const formEl = document.getElementById('product-form');
    const saveBtn = document.getElementById('save-btn');
    const formTitle = document.getElementById('form-title');
    const modal = document.getElementById('detail-modal');
    const detailJson = document.getElementById('detail-json');
    const primaryImageHint = document.getElementById('primary-image-hint');
    const downloadFileHint = document.getElementById('download-file-hint');

    function assetUrl(path) {
        if (!path) return '';
        if (path.indexOf('http') === 0) return path;
        return @json(asset('storage')) + '/' + path.replace(/^\/+/, '');
    }

    function setMessage(text, isError) {
        msgEl.textContent = text || '';
        msgEl.className = 'message ' + (isError ? 'err' : 'ok');
    }

    function resetForm() {
        formEl.reset();
        document.getElementById('product-id').value = '';
        document.getElementById('status').value = '1';
        primaryImageHint.textContent = '';
        downloadFileHint.textContent = '';
        saveBtn.textContent = 'Save';
        formTitle.textContent = 'Create product';
        setMessage('');
    }

    function fillForm(p) {
        document.getElementById('product-id').value = p.id;
        document.getElementById('name').value = p.name || '';
        document.getElementById('description').value = p.description || '';
        document.getElementById('seo_tags').value = p.seo_tags || '';
        document.getElementById('status').value = p.status ? '1' : '0';
        document.getElementById('category_id').value = p.category_id ? String(p.category_id) : '';
        primaryImageHint.textContent = p.primary_image
            ? 'Current: ' + p.primary_image + ' (leave empty to keep)'
            : '';
        downloadFileHint.textContent = p.download_file
            ? 'Current: ' + p.download_file + ' (leave empty to keep)'
            : '';
        saveBtn.textContent = 'Update';
        formTitle.textContent = 'Edit product #' + p.id;
        setMessage('Edit mode — download count: ' + (p.download_count ?? 0));
    }

    async function loadCategories() {
        try {
            const res = await window.FrontendApi.apiFetch('{{ url('/api/portfolio-categories') }}', {
                method: 'GET',
                headers: window.FrontendApi.authHeadersJson()
            });
            const rows = (res && res.data) ? res.data : [];
            const sel = document.getElementById('category_id');
            const keep = sel.querySelector('option[value=""]');
            sel.innerHTML = '';
            if (keep) sel.appendChild(keep);
            rows.forEach(function (c) {
                const opt = document.createElement('option');
                opt.value = String(c.id);
                opt.textContent = c.name;
                sel.appendChild(opt);
            });
        } catch (e) {
            /* optional */
        }
    }

    async function loadTable() {
        const data = await window.FrontendApi.apiFetch('{{ url('/api/products') }}', {
            method: 'GET',
            headers: window.FrontendApi.authHeadersJson()
        });
        const rows = (data && Array.isArray(data.data)) ? data.data : [];
        if (!rows.length) {
            tableEl.innerHTML = '<tr><td colspan="7" class="muted">No products yet.</td></tr>';
            return;
        }
        tableEl.innerHTML = rows.map(function (item) {
            const img = item.primary_image
                ? '<img class="thumb" src="' + assetUrl(item.primary_image) + '" alt="">'
                : '<span class="muted">—</span>';
            const cat = (item.category && item.category.name) ? item.category.name : '—';
            return '<tr>' +
                '<td>' + item.id + '</td>' +
                '<td>' + (item.name || '') + '</td>' +
                '<td>' + cat + '</td>' +
                '<td>' + (item.status ? 'Active' : 'Deactive') + '</td>' +
                '<td>' + (item.download_count ?? 0) + '</td>' +
                '<td>' + img + '</td>' +
                '<td class="actions">' +
                '<button type="button" data-action="detail" data-id="' + item.id + '">Detail</button> ' +
                '<button type="button" data-action="edit" data-id="' + item.id + '">Edit</button> ' +
                '<button type="button" class="danger" data-action="delete" data-id="' + item.id + '">Delete</button>' +
                '</td></tr>';
        }).join('');
    }

    formEl.addEventListener('submit', async function (e) {
        e.preventDefault();
        setMessage('');
        const id = document.getElementById('product-id').value;
        const imageInput = document.getElementById('primary_image');
        const fileInput = document.getElementById('download_file');
        const hasImage = imageInput.files && imageInput.files[0];
        const hasFile = fileInput.files && fileInput.files[0];

        try {
            const fd = new FormData();
            fd.append('name', document.getElementById('name').value.trim());
            fd.append('description', document.getElementById('description').value);
            fd.append('seo_tags', document.getElementById('seo_tags').value.trim());
            fd.append('status', document.getElementById('status').value === '1' ? '1' : '0');
            const cat = document.getElementById('category_id').value;
            if (cat) fd.append('category_id', cat);
            if (hasImage) fd.append('primary_image', imageInput.files[0]);
            if (hasFile) fd.append('download_file', fileInput.files[0]);

            if (id) {
                await window.FrontendApi.apiFetch('{{ url('/api/products') }}/' + id, {
                    method: hasImage || hasFile ? 'POST' : 'PUT',
                    headers: hasImage || hasFile
                        ? window.FrontendApi.authHeadersMultipart()
                        : window.FrontendApi.authHeadersJson(),
                    body: hasImage || hasFile ? fd : JSON.stringify({
                        name: document.getElementById('name').value.trim(),
                        description: document.getElementById('description').value,
                        seo_tags: document.getElementById('seo_tags').value.trim(),
                        status: document.getElementById('status').value === '1',
                        category_id: cat ? Number(cat) : null
                    })
                });
                setMessage('Product updated.');
            } else {
                if (!hasFile) {
                    setMessage('Download file is required for new products.', true);
                    return;
                }
                await window.FrontendApi.apiFetch('{{ url('/api/products') }}', {
                    method: 'POST',
                    headers: window.FrontendApi.authHeadersMultipart(),
                    body: fd
                });
                setMessage('Product created.');
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
        const pid = btn.getAttribute('data-id');
        if (!pid) return;
        try {
            if (action === 'detail' || action === 'edit') {
                const data = await window.FrontendApi.apiFetch('{{ url('/api/products') }}/' + pid, {
                    method: 'GET',
                    headers: window.FrontendApi.authHeadersJson()
                });
                if (action === 'detail') {
                    detailJson.textContent = JSON.stringify(data, null, 2);
                    modal.classList.add('open');
                } else {
                    fillForm(data.data);
                }
            }
            if (action === 'delete') {
                if (!window.confirm('Delete product #' + pid + '?')) return;
                await window.FrontendApi.apiFetch('{{ url('/api/products') }}/' + pid, {
                    method: 'DELETE',
                    headers: window.FrontendApi.authHeadersJson()
                });
                setMessage('Product deleted.');
                await loadTable();
            }
        } catch (err) {
            setMessage(err.message || 'Action failed.', true);
        }
    });

    document.getElementById('detail-close').addEventListener('click', function () {
        modal.classList.remove('open');
    });
    modal.addEventListener('click', function (e) {
        if (e.target === modal) modal.classList.remove('open');
    });

    loadCategories().then(function () {
        loadTable().catch(function (err) {
            tableEl.innerHTML = '<tr><td colspan="7" class="err">' + (err.message || 'Failed to load products.') + '</td></tr>';
        });
    });
})();
</script>
@endpush
