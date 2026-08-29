<?php

declare(strict_types=1);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>Dashboard | Mesker Financial</title>

    <link
        href="/assets/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        href="/assets/css/app.css"
        rel="stylesheet"
    >
</head>

<body>

<nav class="navbar navbar-light bg-light border-bottom">

    <div class="container">

        <span class="navbar-brand">
            Mesker Financial
        </span>

        <form
            method="POST"
            action="/logout"
            class="d-inline"
        >
            <input
                type="hidden"
                name="_csrf_token"
                value="<?= htmlspecialchars(
                    (new \App\Support\Csrf())->token(),
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
            >

            <button
                type="submit"
                class="btn btn-outline-secondary"
            >
                Sign Out
            </button>
        </form>

    </div>

</nav>

<main class="container py-5">

    <h1 class="mb-4">
        Dashboard
    </h1>

    <?php if ($user !== null): ?>

        <p class="lead">
            Welcome,
            <?= htmlspecialchars(
                $user['name'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>.
        </p>

        <p>
            You are signed in as
            <strong>
                <?= htmlspecialchars(
                    $user['email'],
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

                        <p class="mb-0">
                            <?= htmlspecialchars(
                                $tenant['name'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </p>

                    </div>

                </div>

            </div>

            <div class="col-md-6">

                <div class="card mb-4">

                    <div class="card-body">

                        <h2 class="h5">
                            Current Business
                        </h2>

                        <p class="mb-0">
                            <?= htmlspecialchars(
                                $business['name'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </p>

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
                                    === (int) $business['id']
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

</main>

</body>
</html>