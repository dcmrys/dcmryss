<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<section class="hero-section">
    <div class="container text-center">
        <h1 class="display-4 fw-bold mb-3">Staff Dashboard Login</h1>
        <p class="lead mb-0">Sign in to manage customer accounts</p>
    </div>
</section>

<section class="section-padding bg-light-custom">
    <div class="container">
        <div class="card overflow-hidden mx-auto" style="max-width: 760px;">
            <div class="row g-0">
                <div class="col-md-5 d-flex flex-column justify-content-center p-4 p-lg-5 text-white" style="background: linear-gradient(135deg, #1e40af, #2563eb);">
                    <i class="fas fa-bolt text-warning fa-3x mb-3" aria-hidden="true"></i>
                    <h2 class="h3">Welcome back</h2>
                    <p class="mb-0">Use your dashboard account to open Puihaha Electric's customer records.</p>
                </div>
                <div class="col-md-7 p-4 p-lg-5">
                    <h2 class="h4 mb-4">Log in</h2>
                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger" role="alert"><?= esc(session()->getFlashdata('error')) ?></div>
                    <?php endif; ?>
                    <form method="post" action="<?= site_url('login') ?>">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label for="username" class="form-label">Username or email</label>
                            <input class="form-control" type="text" id="username" name="username" maxlength="100" autocomplete="username" required autofocus>
                        </div>
                        <div class="mb-4">
                            <label for="password" class="form-label">Password</label>
                            <input class="form-control" type="password" id="password" name="password" autocomplete="current-password" required>
                        </div>
                        <button class="btn btn-primary w-100" type="submit">Log in</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>
