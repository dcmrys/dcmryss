<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Initial setup - Puihaha Electric</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { min-height: 100vh; display: grid; place-items: center; background: linear-gradient(135deg, #667eea, #764ba2); }
        .setup-card { width: min(100% - 2rem, 480px); border: 0; border-radius: 16px; box-shadow: 0 15px 45px rgba(0,0,0,.18); }
    </style>
</head>
<body>
    <main class="card setup-card">
        <div class="card-body p-4 p-md-5">
            <h1 class="h3 text-center mb-2">Create the admin account</h1>
            <p class="text-muted text-center mb-4">This page is available on this computer until the first account is created.</p>
            <form action="<?= site_url('setup') ?>" method="post">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input id="name" name="name" type="text" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" value="<?= esc($name) ?>" autocomplete="name" required>
                    <?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?= esc($errors['name']) ?></div><?php endif; ?>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email address</label>
                    <input id="email" name="email" type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" value="<?= esc($email) ?>" autocomplete="username" required>
                    <?php if (isset($errors['email'])): ?><div class="invalid-feedback"><?= esc($errors['email']) ?></div><?php endif; ?>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input id="password" name="password" type="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" minlength="12" autocomplete="new-password" required>
                    <div class="form-text">Use at least 12 characters.</div>
                    <?php if (isset($errors['password'])): ?><div class="invalid-feedback"><?= esc($errors['password']) ?></div><?php endif; ?>
                </div>
                <div class="mb-4">
                    <label for="confirm_password" class="form-label">Confirm password</label>
                    <input id="confirm_password" name="confirm_password" type="password" class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>" autocomplete="new-password" required>
                    <?php if (isset($errors['confirm_password'])): ?><div class="invalid-feedback"><?= esc($errors['confirm_password']) ?></div><?php endif; ?>
                </div>
                <button type="submit" class="btn btn-primary w-100">Create admin account</button>
            </form>
        </div>
    </main>
</body>
</html>
