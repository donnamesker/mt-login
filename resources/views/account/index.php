<?php

declare(strict_types=1);

$title = 'Account | Mesker Financial';

$tenant = $context['tenant'];
$businesses = $context['availableBusinesses'];

$role = $role ?? null;
$errors = $errors ?? [];
$success = $success ?? null;
$csrfToken = $csrfToken ?? null;

$canManage = in_array(
    $role,
    ['owner', 'admin'],
    true
);

$accountName = $name ?? $tenant['name'];
?>

<div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h1 class="mb-4">
                Account
            </h1>

        </div>

        <a
            href="/onboarding"
            class="btn btn-outline-secondary"
        >
            <i class="bi bi-people-fill me-1"></i>Register
        </a>

</div>

<?php if ($success !== null): ?>

    <div class="alert alert-success" role="alert">
        <?= htmlspecialchars(
            $success,
            ENT_QUOTES,
            'UTF-8'
        ) ?>
    </div>

<?php endif; ?>

<?php if ($errors !== []): ?>

    <div class="alert alert-danger" role="alert">

        <?php foreach ($errors as $error): ?>

            <div>
                <?= htmlspecialchars(
                    $error,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </div>

        <?php endforeach; ?>

    </div>

<?php endif; ?>

<div class="card mb-4">
    <div class="card-body">

        <h2 class="h5 mb-3">
            Account Information
        </h2>

        <?php if ($canManage): ?>

            <form method="post" action="/account">

                <input
                    type="hidden"
                    name="_csrf_token"
                    value="<?= htmlspecialchars(
                        $csrfToken,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                >

                <div class="mb-3">

                    <label
                        for="account-name"
                        class="form-label"
                    >
                        Account Name
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        id="account-name"
                        name="name"
                        value="<?= htmlspecialchars(
                            $accountName,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                        maxlength="255"
                        required
                    >

                </div>

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Save Changes
                </button>

            </form>

        <?php else: ?>

            <p class="mb-0">
                <?= htmlspecialchars(
                    $tenant['name'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </p>

        <?php endif; ?>

    </div>
</div>

<div class="card">
    <div class="card-body">

        <div class="d-flex justify-content-between align-items-center mb-3">

            <h2 class="h5 mb-0">
                Businesses
            </h2>

        </div>

        <?php if ($businesses === []): ?>

            <p class="text-muted mb-0">
                No businesses found.
            </p>

        <?php else: ?>

            <ul class="list-group">

                <?php foreach ($businesses as $business): ?>

                    <li class="list-group-item">

                        <?= htmlspecialchars(
                            $business['name'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>

                    </li>

                <?php endforeach; ?>

            </ul>

        <?php endif; ?>

    </div>
</div>