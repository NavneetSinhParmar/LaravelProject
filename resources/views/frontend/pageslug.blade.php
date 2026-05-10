@extends('frontend.layout')

@section('title', 'Page Slugs')

@section('content')
    <div class="card">
        <h1>Page Slugs</h1>
        <p class="muted">
            Manage reusable page slug values used across modules.
        </p>
    </div>

    <div class="card">
        <h2 id="form-title">Create Page Slug</h2>

        <form id="pageslug-form">
            <input type="hidden" id="pageslug-id">

            <div class="row">
                <div>
                    <label for="name">Name</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        placeholder="Enter name"
                        required
                    >
                </div>

                <div>
                    <label for="slug">Slug (Optional)</label>
                    <input
                        type="text"
                        id="slug"
                        name="slug"
                        placeholder="auto-generated-if-empty"
                    >
                </div>
            </div>

            <div class="actions" style="margin-top: 16px;">
                <button type="submit" id="save-btn">
                    Save
                </button>

                <button
                    type="button"
                    class="secondary"
                    id="reset-btn"
                >
                    Reset
                </button>
            </div>

            <div id="pageslug-message" class="message"></div>
        </form>
    </div>

    <div class="card">
        <h2>All Page Slugs</h2>

        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th width="80">ID</th>
                        <th>Name</th>
                        <th>Slug</th>
                        <th width="180">Actions</th>
                    </tr>
                </thead>

                <tbody id="pageslug-table">
                    <tr>
                        <td colspan="4" class="muted">
                            Loading...
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

    const API_URL = "{{ url('/api/pageslug') }}";

    const formEl = document.getElementById('pageslug-form');
    const tableEl = document.getElementById('pageslug-table');
    const msgEl = document.getElementById('pageslug-message');

    const idEl = document.getElementById('pageslug-id');
    const nameEl = document.getElementById('name');
    const slugEl = document.getElementById('slug');

    const saveBtn = document.getElementById('save-btn');
    const resetBtn = document.getElementById('reset-btn');
    const formTitle = document.getElementById('form-title');

    // -----------------------------------
    // Message Helper
    // -----------------------------------

    function setMessage(text = '', isError = false) {

        msgEl.textContent = text;

        msgEl.classList.remove('ok', 'err');

        if (text) {
            msgEl.classList.add(isError ? 'err' : 'ok');
        }
    }

    // -----------------------------------
    // Reset Form
    // -----------------------------------

    function resetForm() {

        formEl.reset();

        idEl.value = '';

        saveBtn.disabled = false;
        saveBtn.textContent = 'Save';

        formTitle.textContent = 'Create Page Slug';

        setMessage();
    }

    // -----------------------------------
    // Fill Edit Form
    // -----------------------------------

    function fillForm(item) {

        idEl.value = item.id || '';

        nameEl.value = item.name || '';

        slugEl.value = item.slug || '';

        formTitle.textContent = `Edit Page Slug #${item.id}`;

        saveBtn.textContent = 'Update';

        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }

    // -----------------------------------
    // Render Table
    // -----------------------------------

    function renderTable(rows = []) {

        if (!rows.length) {

            tableEl.innerHTML = `
                <tr>
                    <td colspan="4" class="muted">
                        No page slugs found.
                    </td>
                </tr>
            `;

            return;
        }

        tableEl.innerHTML = rows.map((item) => {

            return `
                <tr>
                    <td>${item.id}</td>

                    <td>${escapeHtml(item.name || '')}</td>

                    <td>
                        <code>${escapeHtml(item.slug || '')}</code>
                    </td>

                    <td class="actions">
                        <button
                            type="button"
                            data-action="edit"
                            data-id="${item.id}"
                        >
                            Edit
                        </button>

                        <button
                            type="button"
                            class="danger"
                            data-action="delete"
                            data-id="${item.id}"
                        >
                            Delete
                        </button>
                    </td>
                </tr>
            `;
        }).join('');
    }

    // -----------------------------------
    // Load Table Data
    // -----------------------------------

    async function loadTable() {

        try {

            tableEl.innerHTML = `
                <tr>
                    <td colspan="4" class="muted">
                        Loading...
                    </td>
                </tr>
            `;

            const response = await window.FrontendApi.apiFetch(
                API_URL,
                {
                    method: 'GET',
                    headers: window.FrontendApi.authHeadersJson()
                }
            );

            renderTable(response.data || []);

        } catch (error) {

            tableEl.innerHTML = `
                <tr>
                    <td colspan="4" class="err">
                        ${error.message || 'Failed to load data.'}
                    </td>
                </tr>
            `;
        }
    }

    // -----------------------------------
    // Save / Update
    // -----------------------------------

    formEl.addEventListener('submit', async function (e) {

        e.preventDefault();

        setMessage();

        saveBtn.disabled = true;

        saveBtn.textContent = 'Saving...';

        const id = idEl.value;

        const payload = {
            name: nameEl.value.trim(),
            slug: slugEl.value.trim()
        };

        try {

            if (id) {

                await window.FrontendApi.apiFetch(
                    `${API_URL}/${id}`,
                    {
                        method: 'PUT',
                        headers: window.FrontendApi.authHeadersJson(),
                        body: JSON.stringify(payload)
                    }
                );

                setMessage('Page slug updated successfully.');

            } else {

                await window.FrontendApi.apiFetch(
                    API_URL,
                    {
                        method: 'POST',
                        headers: window.FrontendApi.authHeadersJson(),
                        body: JSON.stringify(payload)
                    }
                );

                setMessage('Page slug created successfully.');
            }

            resetForm();

            await loadTable();

        } catch (error) {

            setMessage(
                error.message || 'Save failed.',
                true
            );

        } finally {

            saveBtn.disabled = false;

            saveBtn.textContent = id ? 'Update' : 'Save';
        }
    });

    // -----------------------------------
    // Reset Button
    // -----------------------------------

    resetBtn.addEventListener('click', function () {

        resetForm();
    });

    // -----------------------------------
    // Table Actions
    // -----------------------------------

    tableEl.addEventListener('click', async function (e) {

        const btn = e.target.closest('button[data-action]');

        if (!btn) return;

        const action = btn.dataset.action;

        const id = btn.dataset.id;

        if (!id) return;

        try {

            // -----------------------------
            // Edit
            // -----------------------------

            if (action === 'edit') {

                const response = await window.FrontendApi.apiFetch(
                    `${API_URL}/${id}`,
                    {
                        method: 'GET',
                        headers: window.FrontendApi.authHeadersJson()
                    }
                );

                fillForm(response.data);

                return;
            }

            // -----------------------------
            // Delete
            // -----------------------------

            if (action === 'delete') {

                const confirmed = confirm(
                    `Are you sure you want to delete page slug #${id}?`
                );

                if (!confirmed) return;

                await window.FrontendApi.apiFetch(
                    `${API_URL}/${id}`,
                    {
                        method: 'DELETE',
                        headers: window.FrontendApi.authHeadersJson()
                    }
                );

                setMessage('Page slug deleted successfully.');

                await loadTable();
            }

        } catch (error) {

            setMessage(
                error.message || 'Action failed.',
                true
            );
        }
    });

    // -----------------------------------
    // Escape HTML
    // -----------------------------------

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // -----------------------------------
    // Initial Load
    // -----------------------------------

    loadTable();

})();
</script>
@endpush