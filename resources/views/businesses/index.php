<?php

declare(strict_types=1);

$title = 'Businesses | Mesker Financial';

$errors = $errors ?? [];
$name = $name ?? '';
$canManage = $canManage ?? false;
?>

<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h1 class="mb-1">
                Businesses
            </h1>

            <p class="text-muted mb-0">
                <?= htmlspecialchars(
                    $context['tenant']['name'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </p>

        </div>

        <a
            href="/dashboard"
            class="btn btn-outline-secondary"
        >
            Dashboard
        </a>

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

        <div class="<?= $canManage ? 'col-lg-8' : 'col-lg-12' ?>">

            <div class="card mb-4">

                <div class="card-body">

                    <h2 class="h5 mb-3">
                        Your Businesses
                    </h2>

                    <?php if ($businesses === []): ?>

                        <p class="text-muted mb-0">
                            No businesses are available in this account.
                        </p>

                    <?php else: ?>

                        <div class="list-group">

                            <?php foreach ($businesses as $business): ?>

                                <div
                                    class="list-group-item d-flex justify-content-between align-items-center"
                                >

                                    <div>

                                        <strong>
                                            <?= htmlspecialchars(
                                                $business['name'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>
                                        </strong>

                                        <?php if (
                                            (int) $business['id']
                                            === (int) $context['business']['id']
                                        ): ?>

                                            <span class="badge bg-secondary ms-2">
                                                Current
                                            </span>

                                        <?php endif; ?>

                                    </div>

                                    <div class="d-flex gap-2">

                                        <?php if ($canManage): ?>

                                            <a
                                                href="/businesses/edit?id=<?= (int) $business['id'] ?>"
                                                class="btn btn-sm btn-outline-secondary"
                                            >
                                                Edit
                                            </a>

                                        <?php endif; ?>

                                        <?php if (
                                            (int) $business['id']
                                            !== (int) $context['business']['id']
                                        ): ?>

                                            <form
                                                method="POST"
                                                action="/context/business"
                                                class="d-inline"
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

                                                <?php if (
                                                    !empty(
                                                        $manageableBusinesses[(int) $business['id']]
                                                    )
                                                ): ?>

                                                    <a
                                                        href="/businesses/users?id=<?= (int) $business['id'] ?>"
                                                        class="btn btn-sm btn-outline-secondary"
                                                    >
                                                        Users
                                                    </a>

                                                <?php endif; ?>

                                                <button
                                                    type="submit"
                                                    class="btn btn-sm btn-outline-primary"
                                                >
                                                    Switch
                                                </button>

                                            </form>

                                        <?php endif; ?>

                                    </div>

                                </div>

                            <?php endforeach; ?>

                        </div>

                    <?php endif; ?>

                </div>

            </div>

        </div>

        <?php if ($canManage): ?>

            <div class="col-lg-4">

                <div class="card">

                    <div class="card-body">

                        <h2 class="h5 mb-3">
                            Add Business
                        </h2>

                        <form
                            method="POST"
                            action="/businesses"
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

                            <div class="mb-3">

                                <label
                                    for="name"
                                    class="form-label"
                                >
                                    Business Name
                                </label>

                                <input
                                    type="text"
                                    name="name"
                                    id="name"
                                    class="form-control"
                                    value="<?= htmlspecialchars(
                                        $name,
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
                                Add Business
                            </button>

                        </form>

                    </div>

                </div>

            </div>

        <?php endif; ?>

    </div>

</div>