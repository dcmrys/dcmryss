<?php
$isEdit = $account !== null;
$formAction = $isEdit ? site_url('account/' . $account['id']) : site_url('account');
$errors = session()->getFlashdata('errors') ?? [];
$value = static fn (string $field) => esc(old($field, $account[$field] ?? ''));
$type = old('connection_type', $account['connection_type'] ?? 'residential');
$status = old('status', $account['status'] ?? 'active');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $isEdit ? 'Edit Account' : 'New Account' ?> - Puihaha Electric</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<main class="container py-5" style="max-width: 850px;">
    <div class="card shadow-sm">
        <div class="card-body p-4 p-md-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h2 mb-1"><?= $isEdit ? 'Edit Customer Account' : 'New Customer Account' ?></h1>
                    <p class="text-muted mb-0">Puihaha Electric Company</p>
                </div>
                <a href="<?= site_url('dashboard') ?>" class="btn btn-outline-secondary">Dashboard</a>
            </div>

            <?php if ($errors !== []): ?>
                <div class="alert alert-danger" role="alert">Please correct the highlighted fields.</div>
            <?php endif; ?>

            <form action="<?= $formAction ?>" method="post">
                <?= csrf_field() ?>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="account_number" class="form-label">Account Number *</label>
                        <input id="account_number" name="account_number" class="form-control <?= isset($errors['account_number']) ? 'is-invalid' : '' ?>" maxlength="50" value="<?= $value('account_number') ?>" required>
                        <?php if (isset($errors['account_number'])): ?><div class="invalid-feedback"><?= esc($errors['account_number']) ?></div><?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label for="customer_name" class="form-label">Customer Name *</label>
                        <input id="customer_name" name="customer_name" class="form-control <?= isset($errors['customer_name']) ? 'is-invalid' : '' ?>" maxlength="150" value="<?= $value('customer_name') ?>" required>
                        <?php if (isset($errors['customer_name'])): ?><div class="invalid-feedback"><?= esc($errors['customer_name']) ?></div><?php endif; ?>
                    </div>
                    <div class="col-12">
                        <label for="address" class="form-label">Address *</label>
                        <textarea id="address" name="address" class="form-control <?= isset($errors['address']) ? 'is-invalid' : '' ?>" rows="3" required><?= $value('address') ?></textarea>
                        <?php if (isset($errors['address'])): ?><div class="invalid-feedback"><?= esc($errors['address']) ?></div><?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label for="phone" class="form-label">Phone</label>
                        <input id="phone" name="phone" class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>" maxlength="20" value="<?= $value('phone') ?>">
                        <?php if (isset($errors['phone'])): ?><div class="invalid-feedback"><?= esc($errors['phone']) ?></div><?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email</label>
                        <input id="email" type="email" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" maxlength="100" value="<?= $value('email') ?>">
                        <?php if (isset($errors['email'])): ?><div class="invalid-feedback"><?= esc($errors['email']) ?></div><?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label for="meter_number" class="form-label">Meter Number</label>
                        <input id="meter_number" name="meter_number" class="form-control <?= isset($errors['meter_number']) ? 'is-invalid' : '' ?>" maxlength="50" value="<?= $value('meter_number') ?>">
                        <?php if (isset($errors['meter_number'])): ?><div class="invalid-feedback"><?= esc($errors['meter_number']) ?></div><?php endif; ?>
                    </div>
                    <div class="col-md-3">
                        <label for="connection_type" class="form-label">Connection Type *</label>
                        <select id="connection_type" name="connection_type" class="form-select <?= isset($errors['connection_type']) ? 'is-invalid' : '' ?>" required>
                            <?php foreach (['residential' => 'Residential', 'commercial' => 'Commercial', 'industrial' => 'Industrial'] as $key => $label): ?>
                                <option value="<?= $key ?>" <?= $type === $key ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['connection_type'])): ?><div class="invalid-feedback"><?= esc($errors['connection_type']) ?></div><?php endif; ?>
                    </div>
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status *</label>
                        <select id="status" name="status" class="form-select <?= isset($errors['status']) ? 'is-invalid' : '' ?>" required>
                            <?php foreach (['active' => 'Active', 'inactive' => 'Inactive', 'suspended' => 'Suspended'] as $key => $label): ?>
                                <option value="<?= $key ?>" <?= $status === $key ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['status'])): ?><div class="invalid-feedback"><?= esc($errors['status']) ?></div><?php endif; ?>
                    </div>
                </div>
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Save Changes' : 'Create Account' ?></button>
                    <a href="<?= $isEdit ? site_url('account/' . $account['id']) : site_url('dashboard') ?>" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</main>
</body>
</html>
