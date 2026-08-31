<?php

declare(strict_types=1);

$title = 'Add User';
?>

<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>Add User</h1>
            <p class="text-muted mb-0">
                Add an existing application user to
                <?= htmlspecialchars(
                    $context['tenant']['name'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>.
            </p>
        </div>

        <a href="/users" class="btn btn-outline-secondary">
            Back to Users
        </a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
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

    <div class="card">
        <div class="card-body">

            <form method="post" action="/users">

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
                        for="email"
                        class="form-label"
                    >
                        Email Address
                    </label>

                    <input
                        type="email"
                        name="email"
                        id="email"
                        class="form-control"
                        value="<?= htmlspecialchars(
                            $email ?? '',
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                        required
                    >

                    <div class="form-text">
                        The user must already have an account.
                    </div>
                </div>

                <div class="mb-3">
                    <label
                        for="role"
                        class="form-label"
                    >
                        Role
                    </label>

                    <select
                        name="role"
                        id="role"
                        class="form-select"
                    >
                        <option
                            value="member"
                            <?= ($role ?? 'member') === 'member'
                                ? 'selected'
                                : '' ?>
                        >
                            Member
                        </option>

                        <option
                            value="admin"
                            <?= ($role ?? '') === 'admin'
                                ? 'selected'
                                : '' ?>
                        >
                            Admin
                        </option>

                        <option
                            value="owner"
                            <?= ($role ?? '') === 'owner'
                                ? 'selected'
                                : '' ?>
                        >
                            Owner
                        </option>
                    </select>
                </div>

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Add User
                </button>

                <a
                    href="/users"
                    class="btn btn-outline-secondary"
                >
                    Cancel
                </a>

            </form>

        </div>
    </div>

</div>