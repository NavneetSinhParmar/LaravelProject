<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body { font-family: Arial, sans-serif; background: #f5f7fb; margin: 0; }
        .wrap { min-height: 100vh; display: grid; place-items: center; padding: 16px; }
        .card { width: 100%; max-width: 420px; background: #fff; border-radius: 10px; box-shadow: 0 8px 24px rgba(0,0,0,.08); padding: 24px; }
        h1 { margin-top: 0; font-size: 24px; }
        label { display: block; margin: 10px 0 6px; font-size: 14px; }
        input { width: 100%; padding: 10px; border: 1px solid #d9d9d9; border-radius: 6px; box-sizing: border-box; }
        button { width: 100%; margin-top: 16px; background: #2563eb; color: #fff; border: 0; border-radius: 6px; padding: 10px; cursor: pointer; }
        .error { margin-top: 12px; color: #dc2626; font-size: 14px; min-height: 20px; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="card">
            <h1>Project Login</h1>
            <form id="login-form">
                <label for="email">Email</label>
                <input id="email" type="email" required placeholder="Enter email">
                <label for="password">Password</label>
                <input id="password" type="password" required placeholder="Enter password">
                <button type="submit">Login</button>
                <div class="error" id="error"></div>
            </form>
        </div>
    </div>

    <script>
        const token = localStorage.getItem('api_token');
        if (token) {
            window.location.href = '/dashboard';
        }

        document.getElementById('login-form').addEventListener('submit', async (event) => {
            event.preventDefault();

            const errorEl = document.getElementById('error');
            errorEl.textContent = '';

            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;

            try {
                const response = await fetch('/api/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ email, password })
                });

                const data = await response.json();
                if (!response.ok || !data.token) {
                    throw new Error(data.message || 'Login failed.');
                }

                localStorage.setItem('api_token', data.token);

                // Create a web session so Laravel's `auth` middleware works for /dashboard
                await fetch('/web/login', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ token: data.token })
                });

                window.location.href = '/dashboard';
            } catch (error) {
                errorEl.textContent = error.message;
            }
        });
    </script>
</body>
</html>
