<?php

declare(strict_types=1);

$title = $title ?? 'Mesker Financial';
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title><?= htmlspecialchars(
        $title,
        ENT_QUOTES,
        'UTF-8'
    ) ?></title>

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

        <a
            href="/dashboard"
            class="navbar-brand"
        >
            Mesker Financial
        </a>

        <div class="d-flex align-items-center gap-2">

            <a href="/dashboard" class="btn btn-outline-primary">
                Dashboard
            </a>

            <a href="/account" class="btn btn-outline-primary">
                Account
            </a>

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

    </div>

</nav>

<main class="container py-5">

    <?= $content ?>

</main>

</body>

</html>