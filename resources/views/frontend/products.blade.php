@extends('frontend.layout')

@section('title', 'Products')

@section('content')
    <div class="card">
        <h1>Products</h1>
        <p class="muted">Authenticated API: list (<code>GET /api/products</code>), detail (<code>GET /api/products/{id}</code>), create, update, delete.</p>
    </div>

    <div class="card">
        <h2 id="form-title">Create product</h2>
        <form id="product-form">
            <input type="hidden" id="product-id">
            <div class="row">
                <div>
                    <label for="name">Name</label>
                    <input id="name" required placeholder="Product name">
                </div>
                <div>
                    <label for="price">Price</label>
                    <input id="price" type="number" step="0.01" min="0" placeholder="0.00">
                </div>
                <div>
                    <label for="status">Status</label>
                    <select id="status">
                        <option value="1" selected>Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>
            <label for="image">Image URL</label>
            <input id="image" placeholder="https://… or /path">
            <label for="description">Description</label>
            <textarea id="description" placeholder="Optional"></textarea>
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
                        <th>Price</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="product-table">
                    <tr><td colspan="5" class="muted">Loading…</td></tr>
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
            <p class="muted" style="margin:8px 0 12px;">Response from <code>GET /api/products/{id}</code></p>
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

            function setMessage(text, isError) {
                msgEl.textContent = text || '';
                msgEl.className = 'message ' + (isError ? 'err' : 'ok');
            }

            function resetForm() {
                formEl.reset();
                document.getElementById('product-id').value = '';
                document.getElementById('status').value = '1';
                saveBtn.textContent = 'Save';
                formTitle.textContent = 'Create product';
                setMessage('');
            }

            function fillForm(p) {
                document.getElementById('product-id').value = p.id;
                document.getElementById('name').value = p.name || '';
                document.getElementById('price').value = p.price != null ? String(p.price) : '';
                document.getElementById('image').value = p.image || '';
                document.getElementById('description').value = p.description || '';
                document.getElementById('status').value = p.status ? '1' : '0';
                saveBtn.textContent = 'Update';
                formTitle.textContent = 'Edit product #' + p.id;
                setMessage('Edit mode — adjust fields and save.');
            }

            async function loadTable() {
                const data = await window.FrontendApi.apiFetch('{{ url('/api/products') }}', {
                    method: 'GET',
                    headers: window.FrontendApi.authHeadersJson()
                });
                const rows = (data && Array.isArray(data.data)) ? data.data : [];
                if (!rows.length) {
                    tableEl.innerHTML = '<tr><td colspan="5" class="muted">No products yet.</td></tr>';
                    return;
                }
                tableEl.innerHTML = rows.map(function (item) {
                    return '<tr>' +
                        '<td>' + item.id + '</td>' +
                        '<td>' + (item.name || '') + '</td>' +
                        '<td>' + (item.price != null ? item.price : '') + '</td>' +
                        '<td>' + (item.status ? 'Active' : 'Inactive') + '</td>' +
                        '<td class="actions">' +
                        '<button type="button" data-action="detail" data-id="' + item.id + '">Detail</button> ' +
                        '<button type="button" data-action="edit" data-id="' + item.id + '">Edit</button> ' +
                        '<button type="button" class="danger" data-action="delete" data-id="' + item.id + '">Delete</button>' +
                        '</td>' +
                        '</tr>';
                }).join('');
            }

            formEl.addEventListener('submit', async function (e) {
                e.preventDefault();
                setMessage('');
                const id = document.getElementById('product-id').value;
                const payload = {
                    name: document.getElementById('name').value.trim(),
                    price: document.getElementById('price').value ? Number(document.getElementById('price').value) : null,
                    image: document.getElementById('image').value.trim() || null,
                    description: document.getElementById('description').value.trim() || null,
                    status: document.getElementById('status').value === '1'
                };
                try {
                    if (id) {
                        await window.FrontendApi.apiFetch('{{ url('/api/products') }}/' + id, {
                            method: 'PUT',
                            headers: window.FrontendApi.authHeadersJson(),
                            body: JSON.stringify(payload)
                        });
                        setMessage('Product updated.');
                    } else {
                        await window.FrontendApi.apiFetch('{{ url('/api/products') }}', {
                            method: 'POST',
                            headers: window.FrontendApi.authHeadersJson(),
                            body: JSON.stringify(payload)
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
                if (!btn) {
                    return;
                }
                const action = btn.getAttribute('data-action');
                const pid = btn.getAttribute('data-id');
                if (!pid) {
                    return;
                }
                try {
                    if (action === 'detail') {
                        const data = await window.FrontendApi.apiFetch('{{ url('/api/products') }}/' + pid, {
                            method: 'GET',
                            headers: window.FrontendApi.authHeadersJson()
                        });
                        detailJson.textContent = JSON.stringify(data, null, 2);
                        modal.classList.add('open');
                    }
                    if (action === 'edit') {
                        const data = await window.FrontendApi.apiFetch('{{ url('/api/products') }}/' + pid, {
                            method: 'GET',
                            headers: window.FrontendApi.authHeadersJson()
                        });
                        fillForm(data.data);
                    }
                    if (action === 'delete') {
                        if (!window.confirm('Delete product #' + pid + '?')) {
                            return;
                        }
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
                if (e.target === modal) {
                    modal.classList.remove('open');
                }
            });

            loadTable().catch(function (err) {
                tableEl.innerHTML = '<tr><td colspan="5" class="err">' + (err.message || 'Failed to load products.') + '</td></tr>';
            });
        })();
    </script>
@endpush
