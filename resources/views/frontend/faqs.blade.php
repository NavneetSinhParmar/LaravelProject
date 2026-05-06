@extends('frontend.layout')

@section('title', 'FAQs')

@section('content')
    <div class="card">
        <h1>FAQs</h1>
        <p class="muted">Manage Frequently Asked Questions; filter by page and order items.</p>
    </div>

    <div class="card">
        <h2 id="form-title">Create FAQ</h2>

        <form id="faq-form">
            <input type="hidden" id="faq-id">

            <div class="row">
                <div>
                    <label for="page_slug">Page slug</label>
                    <select id="page_slug" name="page_slug" required>
                        <option value="">Loading…</option>
                    </select>
                </div>

                <div>
                    <label for="category">Category (optional)</label>
                    <input id="category" name="category">
                </div>

                <div>
                    <label for="order">Order</label>
                    <input id="order" name="order" type="number" min="0" value="0">
                </div>
            </div>

            <div class="row">
                <div style="flex:1 1 100%;">
                    <label for="question">Question</label>
                    <input id="question" name="question" required>
                </div>
            </div>

            <div class="row">
                <div style="flex:1 1 100%;">
                    <label for="answer">Answer</label>
                    <textarea id="answer" name="answer"></textarea>
                </div>
            </div>

            <div class="row">
                <div>
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>

                <div>
                    <label for="is_featured">Featured</label>
                    <select id="is_featured" name="is_featured">
                        <option value="0">No</option>
                        <option value="1">Yes</option>
                    </select>
                </div>
            </div>

            <div class="actions" style="margin-top:12px;">
                <button type="submit" id="save-btn">Save</button>
                <button type="button" class="secondary" id="reset-btn">Reset</button>
            </div>

            <div id="faq-message" class="message"></div>
        </form>
    </div>

    <div class="card">
        <h2>All FAQs</h2>

        <div style="overflow:auto;">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Page</th>
                        <th>Category</th>
                        <th>Question</th>
                        <th>Featured</th>
                        <th>Status</th>
                        <th>Order</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody id="faq-table">
                    <tr>
                        <td colspan="8" class="muted">Loading…</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
<script>
(function () {

    const tableEl = document.getElementById('faq-table');
    const msgEl = document.getElementById('faq-message');
    const formEl = document.getElementById('faq-form');
    const saveBtn = document.getElementById('save-btn');
    const formTitle = document.getElementById('form-title');

    function setMessage(text, isError = false) {
        msgEl.textContent = text || '';
        msgEl.className = 'message ' + (isError ? 'err' : 'ok');
    }

    function resetForm() {
        formEl.reset();
        document.getElementById('faq-id').value = '';
        document.getElementById('order').value = '0';
        saveBtn.textContent = 'Save';
        formTitle.textContent = 'Create FAQ';
        setMessage('');
    }

    function fillForm(f) {
        document.getElementById('faq-id').value = f.id;
        document.getElementById('page_slug').value = f.page_slug || '';
        document.getElementById('category').value = f.category || '';
        document.getElementById('question').value = f.question || '';
        document.getElementById('answer').value = f.answer || '';
        document.getElementById('status').value = String(f.status ? 1 : 0);
        document.getElementById('order').value = String(f.order ?? 0);
        document.getElementById('is_featured').value = f.is_featured ? '1' : '0';
        saveBtn.textContent = 'Update';
        formTitle.textContent = 'Edit FAQ #' + f.id;
    }

    async function loadPageSlugs() {
        try {
            const res = await window.FrontendApi.apiFetch('{{ url('/api/page-slugs') }}', { method: 'GET' });
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
            const sel = document.getElementById('page_slug');
            if (sel) sel.innerHTML = '<option value="home">home</option>';
        }
    }

    async function loadTable() {
        const res = await window.FrontendApi.apiFetch('{{ url('/api/faqs') }}', { method: 'GET', headers: window.FrontendApi.authHeadersJson() });
        const rows = (res && res.data) ? res.data : [];
        if (!rows.length) {
            tableEl.innerHTML = `<tr><td colspan="8" class="muted">No FAQs yet.</td></tr>`;
            return;
        }
        tableEl.innerHTML = rows.map(function (f) {
            return `
                <tr>
                    <td>${f.id}</td>
                    <td>${f.page_slug}</td>
                    <td>${f.category || ''}</td>
                    <td>${f.question}</td>
                    <td>${f.is_featured ? 'Yes' : 'No'}</td>
                    <td>${f.status ? 'Active' : 'Inactive'}</td>
                    <td>${f.order ?? ''}</td>
                    <td class="actions">
                        <button type="button" data-action="edit" data-id="${f.id}">Edit</button>
                        <button type="button" class="danger" data-action="delete" data-id="${f.id}">Delete</button>
                    </td>
                </tr>
            `;
        }).join('');
    }

    formEl.addEventListener('submit', async function (e) {
        e.preventDefault();
        setMessage('');
        const id = document.getElementById('faq-id').value;
        try {
            const payload = {
                page_slug: document.getElementById('page_slug').value,
                category: document.getElementById('category').value,
                question: document.getElementById('question').value,
                answer: document.getElementById('answer').value,
                status: Number(document.getElementById('status').value) === 1,
                order: Number(document.getElementById('order').value) || 0,
                is_featured: Number(document.getElementById('is_featured').value) === 1
            };

            if (id) {
                await window.FrontendApi.apiFetch('{{ url('/api/faqs') }}/' + id, { method: 'PUT', headers: window.FrontendApi.authHeadersJson(), body: JSON.stringify(payload) });
                setMessage('Updated.');
            } else {
                await window.FrontendApi.apiFetch('{{ url('/api/faqs') }}', { method: 'POST', headers: window.FrontendApi.authHeadersJson(), body: JSON.stringify(payload) });
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
                const data = await window.FrontendApi.apiFetch('{{ url('/api/faqs') }}/' + id, { method: 'GET', headers: window.FrontendApi.authHeadersJson() });
                fillForm(data.data);
            }
            if (action === 'delete') {
                if (!window.confirm('Delete FAQ #' + id + '?')) return;
                await window.FrontendApi.apiFetch('{{ url('/api/faqs') }}/' + id, { method: 'DELETE', headers: window.FrontendApi.authHeadersJson() });
                setMessage('Deleted.');
                await loadTable();
            }
        } catch (err) {
            setMessage(err.message || 'Action failed.', true);
        }
    });

    // load page slugs first so the select is populated
    loadPageSlugs().then(function () {
        loadTable().catch(function (err) {
            tableEl.innerHTML = `<tr><td colspan="8" class="err">${err.message || 'Failed to load FAQs.'}</td></tr>`;
        });
    }).catch(function () {
        loadTable().catch(function (err) {
            tableEl.innerHTML = `<tr><td colspan="8" class="err">${err.message || 'Failed to load FAQs.'}</td></tr>`;
        });
    });

})();
</script>
@endpush
