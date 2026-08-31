<?php

declare(strict_types=1);

$title = 'Business Users | Mesker Financial';

$errors = $errors ?? [];
?>

<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h1 class="mb-1">Business Users</h1>

            <p class="text-muted mb-0">
                <?= htmlspecialchars(
                    $business['name'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </p>
        </div>

        <div class="d-flex gap-2">
            <a
                href="/businesses"
                class="btn btn-outline-secondary"
            >
                Businesses
            </a>

            <a
                href="/dashboard"
                class="btn btn-outline-secondary"
            >
                Dashboard
            </a>
        </div>

    </div>

    <?php if ($errors !== []): ?>

        <?php foreach ($errors as $error): ?>

            <div
                class="alert alert-danger"
                role="alert"
            >
                <?= htmlspecialchars(
                    $error,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </div>

        <?php endforeach; ?>

    <?php endif; ?>

    <div class="row">

        <div class="col-lg-8">

            <div class="card mb-4">

                <div class="card-body">

                    <h2 class="h5 mb-3">
                        Business Users
                    </h2>

                    <?php if ($users === []): ?>

                        <p class="text-muted mb-0">
                            No users are assigned to this business.
                        </p>

                    <?php else: ?>

                        <div class="list-group">

                            <?php foreach ($users as $user): ?>

                                <div class="list-group-item">

                                    <div class="row align-items-center g-3">

                                        <div class="col-md-5">

                                            <strong>
                                                <?= htmlspecialchars(
                                                    $user['name'],
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>
                                            </strong>

                                            <div class="small text-muted">
                                                <?= htmlspecialchars(
                                                    $user['email'],
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>
                                            </div>

                                        </div>

                                        <div class="col-md-7">

                                            <form
                                                method="POST"
                                                action="/businesses/users/update"
                                                class="d-flex gap-2"
                                            >

                                                <input
                                                    type="hidden"
                                                    name="_csrf_token"
                                                    value="<?= htmlspecialchars(
                                                        $csrfToken,
                                                        ENT_QUOTES,
                                                        'UTF-8'
                                                    ) ?>"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="business_id"
                                                    value="<?= (int) $business['id'] ?>"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="user_id"
                                                    value="<?= (int) $user['id'] ?>"
                                                >

                                                <select
                                                    name="role"
                                                    class="form-select form-select-sm"
                                                >

                                                    <option
                                                        value="owner"
                                                        <?= $user['role'] === 'owner'
                                                            ? 'selected'
                                                            : '' ?>
                                                    >
                                                        Owner
                                                    </option>

                                                    <option
                                                        value="admin"
                                                        <?= $user['role'] === 'admin'
                                                            ? 'selected'
                                                            : '' ?>
                                                    >
                                                        Admin
                                                    </option>

                                                    <option
                                                        value="member"
                                                        <?= $user['role'] === 'member'
                                                            ? 'selected'
                                                            : '' ?>
                                                    >
                                                        Member
                                                    </option>

                                                </select>

                                                <button
                                                    type="submit"
                                                    class="btn btn-sm btn-outline-primary"
                                                >
                                                    Save
                                                </button>

                                            </form>

                                            <form
                                                method="POST"
                                                action="/businesses/users/remove"
                                                class="mt-2"
                                                onsubmit="return confirm('Remove this user from the business?');"
                                            >

                                                <input
                                                    type="hidden"
                                                    name="_csrf_token"
                                                    value="<?= htmlspecialchars(
                                                        $csrfToken,
                                                        ENT_QUOTES,
                                                        'UTF-8'
                                                    ) ?>"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="business_id"
                                                    value="<?= (int) $business['id'] ?>"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="user_id"
                                                    value="<?= (int) $user['id'] ?>"
                                                >

                                                <button
                                                    type="submit"
                                                    class="btn btn-sm btn-outline-danger"
                                                >
                                                    Remove
                                                </button>

                                            </form>

                                        </div>

                                    </div>

                                </div>

                            <?php endforeach; ?>

                        </div>

                    <?php endif; ?>

                </div>

            </div>

        </div>

        <div class="col-lg-4">

            <div class="card">

                <div class="card-body">

                    <h2 class="h5 mb-3">
                        Add User
                    </h2>

                    <?php if ($availableUsers === []): ?>

                        <p class="text-muted mb-0">
                            All users in this account are already assigned
                            to this business.
                        </p>

                    <?php else: ?>

                        <form
                            method="POST"
                            action="/businesses/users/add"
                        >

                            <input
                                type="hidden"
                                name="_csrf_token"
                                value="<?= htmlspecialchars(
                                    $csrfToken,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                            >

                            <input
                                type="hidden"
                                name="business_id"
                                value="<?= (int) $business['id'] ?>"
                            >

                            <div class="mb-3">

                                <label
                                    for="user_id"
                                    class="form-label"
                                >
                                    User
                                </label>

                                <select
                                    name="user_id"
                                    id="user_id"
                                    class="form-select"
                                    required
                                >

                                    <option value="">
                                        Select a user
                                    </option>

                                    <?php foreach (
                                        $availableUsers
                                        as $availableUser
                                    ): ?>

                                        <option
                                            value="<?= (int) $availableUser['id'] ?>"
                                        >
                                            <?= htmlspecialchars(
                                                $availableUser['name'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>
                                            -
                                            <?= htmlspecialchars(
                                                $availableUser['email'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>
                                        </option>

                                    <?php endforeach; ?>

                                </select>

                            </div>

                            <div class="mb-3">

                                <label
                                    for="role"
                                    class="form-label"
                                >
                                    Business Role
                                </label>

                                <select
                                    name="role"
                                    id="role"
                                    class="form-select"
                                >

                                    <option value="member">
                                        Member
                                    </option>

                                    <option value="admin">
                                        Admin
                                    </option>

                                    <option value="owner">
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

                        </form>

                    <?php endif; ?>

                </div>

            </div>

        </div>

    </div>

</div>