<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f5f7fb; color: #0f172a; }
        .container { max-width: 1024px; margin: 0 auto; padding: 20px; }
        .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
        .card { background: #fff; border-radius: 10px; box-shadow: 0 8px 24px rgba(0,0,0,.08); padding: 16px; margin-bottom: 16px; }
        h2, h3 { margin: 0 0 12px; }
        .muted { color: #64748b; font-size: 14px; }
        .row { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; margin-bottom: 10px; }
        input, textarea { width: 100%; padding: 10px; border: 1px solid #d9d9d9; border-radius: 6px; box-sizing: border-box; }
        textarea { min-height: 80px; resize: vertical; }
        button { background: #2563eb; color: #fff; border: 0; border-radius: 6px; padding: 9px 12px; cursor: pointer; }
        button.secondary { background: #475569; }
        button.danger { background: #dc2626; }
        .actions { display: flex; gap: 8px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 10px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
        .message { min-height: 22px; font-size: 14px; margin-top: 8px; }
        .ok { color: #166534; }
        .err { color: #dc2626; }
    </style>
</head>
<body>
    <div class="container">
        <div class="topbar">
            <h2>Frontend API Dashboard</h2>
            <button id="logout-btn" class="danger">Logout</button>
        </div>

        <div class="card">
            <h3>Profile</h3>
            <div id="profile" class="muted">Loading profile...</div>
        </div>

        <div class="card">
            <h3>Products CRUD</h3>
            <form id="product-form">
                <input type="hidden" id="product-id">
                <div class="row">
                    <input id="name" placeholder="Name" required>
                    <input id="price" type="number" step="0.01" min="0" placeholder="Price">
                    <input id="image" placeholder="Image URL">
                </div>
                <textarea id="description" placeholder="Description"></textarea>
                <div class="actions" style="margin-top:10px;">
                    <button type="submit" id="save-btn">Create Product</button>
                    <button type="button" id="reset-btn" class="secondary">Reset</button>
                </div>
                <div id="product-message" class="message"></div>
            </form>
        </div>

        <div class="card">
            <h3>Product List</h3>
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
                    <tr><td colspan="5" class="muted">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        const token = localStorage.getItem('api_token');
        if (!token) {
            window.location.href = '/login';
        }

        const authHeaders = () => ({
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': `Bearer ${token}`
        });

        const profileEl = document.getElementById('profile');
        const productTableEl = document.getElementById('product-table');
        const msgEl = document.getElementById('product-message');
        const formEl = document.getElementById('product-form');
        const saveBtn = document.getElementById('save-btn');

        function setMessage(text, isError = false) {
            msgEl.textContent = text;
            msgEl.className = `message ${isError ? 'err' : 'ok'}`;
        }

        async function apiFetch(url, options = {}) {
            const response = await fetch(url, options);
            const data = await response.json();
            if (response.status === 401) {
                localStorage.removeItem('api_token');
                window.location.href = '/login';
                return;
            }
            if (!response.ok) {
                throw new Error(data.message || 'Request failed.');
            }
            return data;
        }

        async function loadProfile() {
            const data = await apiFetch('/api/profile', {
                method: 'GET',
                headers: authHeaders()
            });
            profileEl.textContent = `${data.user.name} (${data.user.email})`;
        }

        function fillForm(product) {
            document.getElementById('product-id').value = product.id;
            document.getElementById('name').value = product.name || '';
            document.getElementById('price').value = product.price || '';
            document.getElementById('image').value = product.image || '';
            document.getElementById('description').value = product.description || '';
            saveBtn.textContent = 'Update Product';
        }

        function resetForm() {
            formEl.reset();
            document.getElementById('product-id').value = '';
            saveBtn.textContent = 'Create Product';
        }

        async function loadProducts() {
            const data = await apiFetch('/api/products', {
                method: 'GET',
                headers: authHeaders()
            });

            if (!data.data.length) {
                productTableEl.innerHTML = '<tr><td colspan="5" class="muted">No products found.</td></tr>';
                return;
            }

            productTableEl.innerHTML = data.data.map((item) => `
                <tr>
                    <td>${item.id}</td>
                    <td>${item.name ?? ''}</td>
                    <td>${item.price ?? ''}</td>
                    <td>${item.status ? 'Active' : 'Inactive'}</td>
                    <td class="actions">
                        <button data-action="edit" data-id="${item.id}">Edit</button>
                        <button data-action="delete" data-id="${item.id}" class="danger">Delete</button>
                    </td>
                </tr>
            `).join('');
        }

        formEl.addEventListener('submit', async (event) => {
            event.preventDefault();
            setMessage('');

            const id = document.getElementById('product-id').value;
            const payload = {
                name: document.getElementById('name').value.trim(),
                price: document.getElementById('price').value ? Number(document.getElementById('price').value) : null,
                image: document.getElementById('image').value.trim() || null,
                description: document.getElementById('description').value.trim() || null,
                status: true
            };

            try {
                if (id) {
                    await apiFetch(`/api/products/${id}`, {
                        method: 'PUT',
                        headers: authHeaders(),
                        body: JSON.stringify(payload)
                    });
                    setMessage('Product updated successfully.');
                } else {
                    await apiFetch('/api/products', {
                        method: 'POST',
                        headers: authHeaders(),
                        body: JSON.stringify(payload)
                    });
                    setMessage('Product created successfully.');
                }

                resetForm();
                await loadProducts();
            } catch (error) {
                setMessage(error.message, true);
            }
        });

        document.getElementById('reset-btn').addEventListener('click', () => {
            resetForm();
            setMessage('');
        });

        productTableEl.addEventListener('click', async (event) => {
            const target = event.target;
            const action = target.getAttribute('data-action');
            const id = target.getAttribute('data-id');
            if (!action || !id) {
                return;
            }

            try {
                if (action === 'edit') {
                    const data = await apiFetch(`/api/products/${id}`, {
                        method: 'GET',
                        headers: authHeaders()
                    });
                    fillForm(data.data);
                    setMessage('Edit mode enabled.');
                }

                if (action === 'delete') {
                    const confirmed = window.confirm('Delete this product?');
                    if (!confirmed) {
                        return;
                    }
                    await apiFetch(`/api/products/${id}`, {
                        method: 'DELETE',
                        headers: authHeaders()
                    });
                    setMessage('Product deleted successfully.');
                    await loadProducts();
                }
            } catch (error) {
                setMessage(error.message, true);
            }
        });

        document.getElementById('logout-btn').addEventListener('click', async () => {
            try {
                await apiFetch('/api/logout', {
                    method: 'POST',
                    headers: authHeaders()
                });
            } catch (error) {
                // Continue logout on frontend even if API call fails.
            } finally {
                localStorage.removeItem('api_token');
                window.location.href = '/login';
            }
        });

        (async () => {
            try {
                await loadProfile();
                await loadProducts();
            } catch (error) {
                setMessage(error.message, true);
            }
        })();
    </script>
</body>
</html>
