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
                    <label for="slug_select">Slug</label>
                    <select id="slug_select" name="slug_select">
                        <option value="">— Select page slug —</option>
                        <option value="custom">Custom slug...</option>
                    </select>
                    <input id="slug" name="slug" required placeholder="product-slug" style="margin-top:8px; display:block; width:100%; box-sizing:border-box;">
                </div>
                <div>
                    <label for="status">Status</label>
                    <select id="status">
                        <option value="1" selected>Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div>
                    <label for="product_type">Product type</label>
                    <select id="product_type">
                        <option value="paid" selected>Paid</option>
                        <option value="free">Free</option>
                    </select>
                </div>
                <div>
                    <label for="price">Price</label>
                    <input id="price" type="number" min="0" step="0.01" placeholder="0.00">
                </div>
                <div>
                    <label for="discount_price">Discount price</label>
                    <input id="discount_price" type="number" min="0" step="0.01" placeholder="0.00">
                </div>
            </div>

            <div class="row">
                <div>
                    <label for="category_id">Category</label>
                    <select id="category_id">
                        <option value="">— None —</option>
                    </select>
                </div>
                <div>
                    <label for="gallery_images">Gallery images</label>
                    <input id="gallery_images" type="file" accept="image/*" multiple>
                    <p class="muted" id="gallery-images-hint" style="margin-top:4px;"></p>
                </div>
                <div>
                    <label for="view_count">View count</label>
                    <input id="view_count" type="number" min="0" placeholder="0">
                </div>
            </div>

            <div class="row">
                <div>
                    <label for="seo_title">SEO title</label>
                    <input id="seo_title" placeholder="SEO title">
                </div>
                <div>
                    <label for="seo_keywords">SEO keywords</label>
                    <input id="seo_keywords" placeholder="keyword1, keyword2">
                </div>
                <div>
                    <label for="sales_count">Sales count</label>
                    <input id="sales_count" type="number" min="0" placeholder="0">
                </div>
            </div>

            <label for="short_description">Short description</label>
            <textarea id="short_description" rows="3" placeholder="Short description…"></textarea>

            <label for="description">Description (HTML supported)</label>
            <textarea id="description" rows="5" placeholder="Product description…"></textarea>

            <label for="seo_description">SEO description</label>
            <textarea id="seo_description" rows="3" placeholder="SEO description…"></textarea>

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
                <div style="padding-top: 28px;">
                    <label>&nbsp;</label>
                    <div style="display:flex; gap:12px; flex-wrap:wrap;">
                        <label><input id="is_featured" type="checkbox"> Featured</label>
                        <label><input id="is_best_seller" type="checkbox"> Best seller</label>
                    </div>
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
                        <th>Type</th>
                        <th>Price</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Downloads</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="product-table">
                    <tr><td colspan="8" class="muted">Loading…</td></tr>
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
            <table class="detail-table" id="detail-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Product ID</th>
                        <th>Product Name</th>
                        <th>User ID</th>
                        <th>Email</th>
                        <th>IP Address</th>
                        <th>User Agent</th>
                        <th>Fingerprint</th>
                        <th>Product Type</th>
                        <th>Action Type</th>
                        <th>Download Count</th>
                        <th>Downloaded At</th>
                        <th>Created At</th>
                        <th>Updated At</th>
                    </tr>
                </thead>
                <tbody id="detail-table-body"></tbody>
            </table>
            <p id="detail-empty" class="muted">No history available.</p>
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
    const primaryImageHint = document.getElementById('primary-image-hint');
    const downloadFileHint = document.getElementById('download-file-hint');
    const slugSelect = document.getElementById('slug_select');
    const slugInput = document.getElementById('slug');
    const galleryHint = document.getElementById('gallery-images-hint');
    const detailTableBody = document.getElementById('detail-table-body');
    const detailEmpty = document.getElementById('detail-empty');
    const detailClose = document.getElementById('detail-close');
    
    if (!slugSelect || !slugInput) {
        console.error('Slug select or input is missing', {slugSelect: !!slugSelect, slugInput: !!slugInput});
    }
    if (!modal) {
        console.error('Detail modal element is missing');
    }
    if (!detailTableBody || !detailEmpty) {
        console.error('Detail table elements are missing');
    }
    
    if (!slugSelect || !slugInput || !galleryHint || !detailTableBody || !detailEmpty) {
        console.error('Missing form elements. Slug select:', !!slugSelect, 'Slug input:', !!slugInput);
    }

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
        document.getElementById('product_type').value = 'paid';
        document.getElementById('price').value = '';
        document.getElementById('discount_price').value = '';
        document.getElementById('slug_select').value = '';
        document.getElementById('slug').value = '';
        document.getElementById('slug').readOnly = false;
        document.getElementById('short_description').value = '';
        document.getElementById('seo_title').value = '';
        document.getElementById('seo_description').value = '';
        document.getElementById('seo_keywords').value = '';
        document.getElementById('gallery_images').value = '';
        galleryHint.textContent = '';
        document.getElementById('view_count').value = '';
        document.getElementById('sales_count').value = '';
        document.getElementById('is_featured').checked = false;
        document.getElementById('is_best_seller').checked = false;
        primaryImageHint.textContent = '';
        downloadFileHint.textContent = '';
        saveBtn.textContent = 'Save';
        formTitle.textContent = 'Create product';
        setMessage('');
    }

    function fillForm(p) {
        document.getElementById('product-id').value = p.id;
        document.getElementById('name').value = p.name || '';
        document.getElementById('slug').value = p.slug || '';
        document.getElementById('product_type').value = p.product_type || 'paid';
        document.getElementById('price').value = p.price ?? '';
        document.getElementById('discount_price').value = p.discount_price ?? '';
        document.getElementById('short_description').value = p.short_description || '';
        document.getElementById('description').value = p.description || '';
        document.getElementById('seo_title').value = p.seo_title || '';
        document.getElementById('seo_description').value = p.seo_description || '';
        document.getElementById('seo_keywords').value = p.seo_keywords || '';
        document.getElementById('status').value = p.status ? '1' : '0';
        document.getElementById('category_id').value = p.category_id ? String(p.category_id) : '';
        document.getElementById('gallery_images').value = '';
        galleryHint.textContent = Array.isArray(p.gallery_images) && p.gallery_images.length
            ? 'Existing: ' + p.gallery_images.join(', ')
            : '';
        
        if (slugSelect && slugInput) {
            const existingSlugOption = Array.from(slugSelect.options).find(function (option) {
                return option.value === p.slug;
            });
            if (existingSlugOption) {
                slugSelect.value = p.slug;
                slugInput.value = p.slug;
                slugInput.readOnly = true;
            } else {
                slugSelect.value = 'custom';
                slugInput.value = p.slug || '';
                slugInput.readOnly = false;
            }
        }
        document.getElementById('view_count').value = p.view_count ?? '';
        document.getElementById('sales_count').value = p.sales_count ?? '';
        document.getElementById('is_featured').checked = !!p.is_featured;
        document.getElementById('is_best_seller').checked = !!p.is_best_seller;
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

    async function loadPageSlugs() {
        try {
            const res = await window.FrontendApi.apiFetch('{{ url('/api/pageslug') }}', {
                method: 'GET',
                headers: window.FrontendApi.authHeadersJson()
            });
            const rows = (res && res.data) ? res.data : [];
            if (!slugSelect) {
                console.warn('Slug select element not found');
                return;
            }
            rows.forEach(function (item) {
                const opt = document.createElement('option');
                opt.value = item.slug;
                opt.textContent = item.name + ' — ' + item.slug;
                slugSelect.appendChild(opt);
            });
        } catch (e) {
            console.error('Error loading page slugs:', e);
        }
    }

    async function loadTable() {
        const data = await window.FrontendApi.apiFetch('{{ url('/api/products') }}', {
            method: 'GET',
            headers: window.FrontendApi.authHeadersJson()
        });
        const rows = (data && Array.isArray(data.data)) ? data.data : (Array.isArray(data) ? data : []);
        if (!rows.length) {
            tableEl.innerHTML = '<tr><td colspan="8" class="muted">No products yet.</td></tr>';
            return;
        }
        tableEl.innerHTML = rows.map(function (item) {
            const type = item.product_type || '—';
            const price = typeof item.price === 'number' || !isNaN(item.price)
                ? Number(item.price).toFixed(2)
                : '—';
            const cat = (item.category && item.category.name) ? item.category.name : '—';
            return '<tr>' +
                '<td>' + item.id + '</td>' +
                '<td>' + (item.name || '') + '</td>' +
                '<td>' + type + '</td>' +
                '<td>' + price + '</td>' +
                '<td>' + cat + '</td>' +
                '<td>' + (item.status ? 'Active' : 'Inactive') + '</td>' +
                '<td>' + (item.download_count ?? 0) + '</td>' +
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
        const slugInputEl = document.getElementById('slug');
        
        if (!slugInputEl) {
            setMessage('Slug field not found. Please refresh the page.', true);
            return;
        }
        
        const hasImage = imageInput && imageInput.files && imageInput.files[0];
        const hasFile = fileInput && fileInput.files && fileInput.files[0];

        try {
            const fd = new FormData();
            const nameEl = document.getElementById('name');
            const viewCountEl = document.getElementById('view_count');
            const salesCountEl = document.getElementById('sales_count');
            const categoryEl = document.getElementById('category_id');
            
            const nameValue = nameEl ? nameEl.value.trim() : '';
            const slugValue = slugInputEl ? slugInputEl.value.trim() : '';
            const viewCount = viewCountEl ? viewCountEl.value.trim() : '';
            const salesCount = salesCountEl ? salesCountEl.value.trim() : '';
            
            if (!nameValue) {
                setMessage('Product name is required.', true);
                return;
            }
            if (!slugValue) {
                setMessage('Slug is required.', true);
                return;
            }

            fd.append('name', nameValue);
            fd.append('slug', slugValue);
            const productTypeEl = document.getElementById('product_type');
            const priceEl = document.getElementById('price');
            const discountEl = document.getElementById('discount_price');
            const shortDescEl = document.getElementById('short_description');
            const descEl = document.getElementById('description');
            const seoTitleEl = document.getElementById('seo_title');
            const seoDescEl = document.getElementById('seo_description');
            const seoKeywordsEl = document.getElementById('seo_keywords');
            const statusEl = document.getElementById('status');
            const featuredEl = document.getElementById('is_featured');
            const bestSellerEl = document.getElementById('is_best_seller');
            
            if (productTypeEl) fd.append('product_type', productTypeEl.value);
            const priceValue = priceEl ? priceEl.value.trim() : '';
            const discountValue = discountEl ? discountEl.value.trim() : '';
            if (priceValue) fd.append('price', priceValue);
            if (discountValue) fd.append('discount_price', discountValue);
            if (shortDescEl) fd.append('short_description', shortDescEl.value);
            if (descEl) fd.append('description', descEl.value);
            if (seoTitleEl) fd.append('seo_title', seoTitleEl.value.trim());
            if (seoDescEl) fd.append('seo_description', seoDescEl.value);
            if (seoKeywordsEl) fd.append('seo_keywords', seoKeywordsEl.value.trim());
            if (statusEl) fd.append('status', statusEl.value === '1' ? '1' : '0');
            if (featuredEl) fd.append('is_featured', featuredEl.checked ? '1' : '0');
            if (bestSellerEl) fd.append('is_best_seller', bestSellerEl.checked ? '1' : '0');
            if (viewCount) fd.append('view_count', viewCount);
            if (salesCount) fd.append('sales_count', salesCount);

            const cat = categoryEl ? categoryEl.value : '';
            if (cat) fd.append('category_id', cat);
            const galleryFiles = document.getElementById('gallery_images').files;
            if (galleryFiles && galleryFiles.length) {
                for (let i = 0; i < galleryFiles.length; i += 1) {
                    fd.append('gallery_images[]', galleryFiles[i]);
                }
            }
            if (hasImage) fd.append('primary_image', imageInput.files[0]);
            if (hasFile) fd.append('download_file', fileInput.files[0]);

            const endpoint = id ? '{{ url('/api/products') }}/' + id : '{{ url('/api/products') }}';
            await window.FrontendApi.apiFetch(endpoint, {
                method: 'POST',
                headers: window.FrontendApi.authHeadersMultipart(),
                body: fd
            });

            setMessage(id ? 'Product updated.' : 'Product created.');
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
                const payload = (data && data.data) ? data.data : data;
                if (action === 'detail') {
                    renderDetailTable(payload);
                    if (modal) {
                        modal.classList.add('open');
                    }
                } else {
                    fillForm(payload);
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

    if (detailClose && modal) {
        detailClose.addEventListener('click', function () {
            modal.classList.remove('open');
        });
    }

    if (slugSelect && slugInput) {
        slugSelect.addEventListener('change', function () {
            const value = slugSelect.value;
            if (value && value !== 'custom') {
                slugInput.value = value;
                slugInput.readOnly = true;
            } else {
                slugInput.value = '';
                slugInput.readOnly = false;
                slugInput.focus();
            }
        });
    }

    function renderDetailTable(product) {
        detailTableBody.innerHTML = '';
        const rows = (product && Array.isArray(product.downloads)) ? product.downloads : [];
        if (!rows.length) {
            detailEmpty.style.display = 'block';
            return;
        }
        detailEmpty.style.display = 'none';
        rows.forEach(function (item) {
            detailTableBody.insertAdjacentHTML('beforeend', '<tr>' +
                '<td>' + item.id + '</td>' +
                '<td>' + item.product_id + '</td>' +
                '<td>' + (product.product_name || product.name || '') + '</td>' +
                '<td>' + (item.user_id ?? '—') + '</td>' +
                '<td>' + (item.email || '—') + '</td>' +
                '<td>' + (item.ip_address || '—') + '</td>' +
                '<td>' + (item.user_agent || '—') + '</td>' +
                '<td>' + (item.fingerprint || '—') + '</td>' +
                '<td>' + (item.product_type || '—') + '</td>' +
                '<td>' + (item.action_type || '—') + '</td>' +
                '<td>' + (item.download_count ?? 0) + '</td>' +
                '<td>' + (item.downloaded_at || '—') + '</td>' +
                '<td>' + (item.created_at || '—') + '</td>' +
                '<td>' + (item.updated_at || '—') + '</td>' +
            '</tr>');
        });
    }
    modal.addEventListener('click', function (e) {
        if (e.target === modal) modal.classList.remove('open');
    });

    Promise.all([loadCategories(), loadPageSlugs()]).then(function () {
        loadTable().catch(function (err) {
            tableEl.innerHTML = '<tr><td colspan="7" class="err">' + (err.message || 'Failed to load products.') + '</td></tr>';
        });
    });
})();
</script>
@endpush
