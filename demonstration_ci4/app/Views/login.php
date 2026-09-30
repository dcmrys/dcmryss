<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<section class="hero-section">
    <div class="container text-center">
        <h1 class="display-4 fw-bold mb-3">Login</h1>
        <p class="lead mb-0">Open the customer account dashboard</p>
    </div>
</section>

<section class="section-padding bg-light-custom">
    <div class="container">
        <div class="card mx-auto text-center p-4 p-md-5" style="max-width: 440px;">
            <h2 class="h4 text-primary-custom mb-3">Puihaha Electric</h2>
            <p class="text-muted mb-4">Click Login to continue to the dashboard.</p>
            <a href="/dcmryss/ci4_pagination/dashboard" class="btn btn-primary">Login</a>
        </div>
    </div>
</section>
<?= $this->endSection() ?>
