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

        <form method="POST" action="/logout" class="d-inline">
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

    <h1 class="mb-3">
        Dashboard
    </h1>

    <?php if ($user !== null): ?>

        <p class="lead">
            Welcome, <?= htmlspecialchars(
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

        <div class="alert alert-success mt-4">
            Authentication and session handling are working.
        </div>

    <?php endif; ?>

</main>

</body>
</html>