<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<section class="hero-section">
    <div class="container">
        <div class="row text-center">
            <div class="col-lg-8 mx-auto">
                <h1 class="display-4 fw-bold mb-4">Our Services</h1>
                <p class="lead mb-0">Trusted electrical solutions for homes, businesses, and critical infrastructure.</p>
            </div>
        </div>
    </div>
</section>

<section class="section-padding bg-light-custom">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 p-4">
                    <div class="feature-icon">
                        <i class="fas fa-home"></i>
                    </div>
                    <h4 class="text-primary-custom mb-3">Residential Wiring</h4>
                    <p class="text-muted mb-0">Safe and efficient electrical installations, upgrades, and troubleshooting for homes and apartments.</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 p-4">
                    <div class="feature-icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <h4 class="text-primary-custom mb-3">Commercial Services</h4>
                    <p class="text-muted mb-0">Reliable electrical support for offices, retail spaces, and commercial properties, all built to code.</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 p-4">
                    <div class="feature-icon">
                        <i class="fas fa-plug"></i>
                    </div>
                    <h4 class="text-primary-custom mb-3">Installations</h4>
                    <p class="text-muted mb-0">New electrical installations, panel upgrades, lighting systems, and custom power solutions tailored to your needs.</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 p-4">
                    <div class="feature-icon">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <h4 class="text-primary-custom mb-3">Emergency Repairs</h4>
                    <p class="text-muted mb-0">Fast, dependable emergency electrical repair services to restore safety and functionality when it matters most.</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 p-4">
                    <div class="feature-icon">
                        <i class="fas fa-solar-panel"></i>
                    </div>
                    <h4 class="text-primary-custom mb-3">Solar Solutions</h4>
                    <p class="text-muted mb-0">Energy-efficient solar installations and upgrades designed to reduce costs and improve sustainability.</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 p-4">
                    <div class="feature-icon">
                        <i class="fas fa-tools"></i>
                    </div>
                    <h4 class="text-primary-custom mb-3">Maintenance</h4>
                    <p class="text-muted mb-0">Preventive maintenance programs that keep your electrical systems reliable, efficient, and code-compliant.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <div class="row text-center mb-5">
            <div class="col-lg-8 mx-auto">
                <h2 class="display-5 fw-bold text-primary-custom mb-3">Why Choose Us</h2>
                <p class="lead text-muted">We focus on safety, quality workmanship, and long-term reliability in every project.</p>
            </div>
        </div>
        <div class="row g-4">
            <div class="col-lg-3 col-md-6 text-center">
                <i class="fas fa-shield-alt fa-2x text-primary-custom mb-3"></i>
                <h5>Safe & Compliant</h5>
            </div>
            <div class="col-lg-3 col-md-6 text-center">
                <i class="fas fa-stopwatch fa-2x text-primary-custom mb-3"></i>
                <h5>On-Time Delivery</h5>
            </div>
            <div class="col-lg-3 col-md-6 text-center">
                <i class="fas fa-user-check fa-2x text-primary-custom mb-3"></i>
                <h5>Professional Team</h5>
            </div>
            <div class="col-lg-3 col-md-6 text-center">
                <i class="fas fa-award fa-2x text-primary-custom mb-3"></i>
                <h5>Quality Guarantee</h5>
            </div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>
