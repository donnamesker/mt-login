<?php

declare(strict_types=1);

// Gets the clean path (e.g., "/businesses/users" instead of "/businesses/users?id=1")
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

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

    <!-- Bootstrap Icons CSS -->
    <link 
        href="/assets/css/bootstrap-icons.css" 
        rel="stylesheet"
    >

</head>

<nav class="navbar navbar-light bg-light border-bottom">

    <div class="container">

        <a
            href="/dashboard"
            class="navbar-brand"
        >
            Mesker Financial
        </a>

        <div class="d-flex align-items-center gap-2">

            <a href="/dashboard" class="btn <?= $currentPath === '/dashboard' ? 'btn-outline-primary' : 'btn-outline-secondary' ?>">
                <i class="bi bi-speedometer2 me-1"></i>Dashboard
            </a>

            <a href="/account" class="btn <?= $currentPath === '/account' ? 'btn-outline-primary' : 'btn-outline-secondary' ?>">
                <i class="bi bi-person-fill me-1"></i>Account
            </a>


            <a href="/businesses" class="btn <?= str_starts_with($currentPath, '/businesses') ? 'btn-outline-primary' : 'btn-outline-secondary' ?>">
                <i class="bi bi-building-fill me-1"></i>Businesses
            </a>

            <a href="/users" class="btn <?= str_starts_with($currentPath, '/users') ? 'btn-outline-primary' : 'btn-outline-secondary' ?>">
                <i class="bi bi-people-fill me-1"></i>Users
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