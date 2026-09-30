<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Puihaha Electric</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { min-height: 100vh; display: grid; place-items: center; background: linear-gradient(135deg, #667eea, #764ba2); }
        .login-card { width: min(100% - 2rem, 440px); border: 0; border-radius: 16px; box-shadow: 0 15px 45px rgba(0,0,0,.18); }
    </style>
</head>
<body>
    <main class="card login-card">
        <div class="card-body p-4 p-md-5">
            <h1 class="h3 text-center mb-2"><i class="bi bi-lightning-charge-fill text-warning"></i> Puihaha Electric</h1>
            <p class="text-muted text-center mb-4">Open the customer account dashboard</p>
            <a href="<?= site_url('dashboard') ?>" class="btn btn-primary w-100">Login</a>
        </div>
    </main>
</body>
</html>
