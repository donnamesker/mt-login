<?php

declare(strict_types=1);

$title = 'Dashboard | Mesker Financial';

$contextUser = $context['user'];
$contextTenant = $context['tenant'];
$contextBusiness = $context['business'];
$availableTenants = $context['availableTenants'];
$availableBusinesses = $context['availableBusinesses'];

?>

    <?php if (
        isset($contextError)
        && $contextError !== null
    ): ?>

        <div class="alert alert-danger" role="alert">
            <?= htmlspecialchars(
                $contextError,
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </div>

    <?php endif; ?>

    <h1 class="mb-4">
        Dashboard
    </h1>

    <?php if ($contextUser !== null): ?>

        <p class="lead">
            Welcome,
            <?= htmlspecialchars(
                $contextUser['name'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>.
        </p>

        <p>
            You are signed in as
            <strong>
                <?= htmlspecialchars(
                    $contextUser['email'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </strong>.
        </p>

        <div class="row mt-4">

            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-body">

                        <h2 class="h5">
                            Account
                        </h2>

                        <p class="text-muted">
                            <?= htmlspecialchars(
                                $contextTenant['name'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </p>

                        <form method="POST" action="/context/tenant">

                            <input
                                type="hidden"
                                name="_csrf_token"
                                value="<?= htmlspecialchars(
                                    (new \App\Support\Csrf())->token(),
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                            >

                            <label
                                for="tenant_id"
                                class="form-label"
                            >
                                Switch Account
                            </label>

                            <select
                                name="tenant_id"
                                id="tenant_id"
                                class="form-select"
                                onchange="this.form.submit()"
                            >
                                <?php foreach (
                                    $availableTenants as $availableTenant
                                ): ?>

                                    <option
                                        value="<?= (int) $availableTenant['id'] ?>"
                                        <?= (int) $availableTenant['id']
                                            === (int) $contextTenant['id']
                                            ? 'selected'
                                            : '' ?>
                                    >
                                        <?= htmlspecialchars(
                                            $availableTenant['name'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </option>

                                <?php endforeach; ?>
                            </select>

                            <noscript>
                                <button
                                    type="submit"
                                    class="btn btn-primary mt-2"
                                >
                                    Switch Account
                                </button>
                            </noscript>

                        </form>

                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-body">

                        <h2 class="h5">
                            Current Business
                        </h2>

                        <p class="text-muted">
                            <?= htmlspecialchars(
                                $contextBusiness['name'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </p>

                        <form method="POST" action="/context/business">

                            <input
                                type="hidden"
                                name="_csrf_token"
                                value="<?= htmlspecialchars(
                                    (new \App\Support\Csrf())->token(),
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                            >

                            <label
                                for="business_id"
                                class="form-label"
                            >
                                Switch Business
                            </label>

                            <select
                                name="business_id"
                                id="business_id"
                                class="form-select"
                                onchange="this.form.submit()"
                            >
                                <?php foreach (
                                    $availableBusinesses as $availableBusiness
                                ): ?>

                                    <option
                                        value="<?= (int) $availableBusiness['id'] ?>"
                                        <?= (int) $availableBusiness['id']
                                            === (int) $contextBusiness['id']
                                            ? 'selected'
                                            : '' ?>
                                    >
                                        <?= htmlspecialchars(
                                            $availableBusiness['name'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </option>

                                <?php endforeach; ?>
                            </select>

                            <noscript>
                                <button
                                    type="submit"
                                    class="btn btn-primary mt-2"
                                >
                                    Switch Business
                                </button>
                            </noscript>

                        </form>

                    </div>
                </div>
            </div>

        </div>

        <div class="card mt-2">

            <div class="card-body">

                <h2 class="h5">
                    Businesses in this Account
                </h2>

                <?php if ($availableBusinesses === []): ?>

                    <p class="text-muted mb-0">
                        No businesses found.
                    </p>

                <?php else: ?>

                    <ul class="mb-0">

                        <?php foreach (
                            $availableBusinesses as $availableBusiness
                        ): ?>

                            <li>
                                <?= htmlspecialchars(
                                    $availableBusiness['name'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>

                                <?php if (
                                    (int) $availableBusiness['id']
                                    === (int) $contextBusiness['id']
                                ): ?>

                                    <strong>
                                        (Current)
                                    </strong>

                                <?php endif; ?>

                            </li>

                        <?php endforeach; ?>

                    </ul>

                <?php endif; ?>

            </div>

        </div>

        <div class="alert alert-success mt-4">

            Authentication, session, tenant, and business
            authorization are working.

        </div>

    <?php endif; ?>