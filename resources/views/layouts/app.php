<?php

declare(strict_types=1);

// Gets the clean path (e.g., "/businesses/users" instead of "/businesses/users?id=1")
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$title = $title ?? 'Mesker Financial';

$content = $content ?? 'No content available.';
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

<nav class="navbar navbar-expand-lg bg-body-tertiary">
  <div class="container">
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarTogglerDemo01" aria-controls="navbarTogglerDemo01" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarTogglerDemo01">
      <a class="navbar-brand" href="#">
        <img src="/assets/images/logo.png" alt="Logo" width="30" height="24" class="d-inline-block align-text-top">
        Mesker Financial
      </a>
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
        <li class="nav-item me-2">
          <a href="/dashboard" class="btn <?= $currentPath === '/dashboard' ? 'btn-outline-primary' : 'btn-outline-secondary' ?>">
                <i class="bi bi-speedometer2 me-1"></i>Dashboard
            </a>
        </li>
        <li class="nav-item me-2">
          <a href="/account" class="btn <?= $currentPath === '/account' ? 'btn-outline-primary' : 'btn-outline-secondary' ?>">
                <i class="bi bi-person-fill me-1"></i>Account
            </a>
        </li>
        <li class="nav-item me-2">
          <a href="/businesses" class="btn <?= str_starts_with($currentPath, '/businesses') ? 'btn-outline-primary' : 'btn-outline-secondary' ?>">
                <i class="bi bi-building-fill me-1"></i>Businesses
            </a>
        </li>
        <li class="nav-item me-2">
          <a href="/users" class="btn <?= str_starts_with($currentPath, '/users') ? 'btn-outline-primary' : 'btn-outline-secondary' ?>">
                <i class="bi bi-people-fill me-1"></i>Users
            </a>
        </li>
      </ul>
      <form class="d-flex"  method="POST" action="/logout">
        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars((new \App\Support\Csrf())->token(), ENT_QUOTES, 'UTF-8') ?>">
        <button type="submit" class="btn btn-outline-secondary">Sign Out</button>
      </form>
    </div>
  </div>
</nav>

<main class="container py-5">

    <?= $content ?>

</main>

</body>

</html>