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

    <title>Create Account | Mesker Financial</title>

    <link
        href="/assets/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        href="/assets/css/app.css"
        rel="stylesheet"
    >
</head>

<body class="bg-light">

<div class="container">
    <div class="row justify-content-center min-vh-100 align-items-center">

        <div class="col-12 col-sm-10 col-md-7 col-lg-5">

            <div class="card shadow-sm">
                <div class="card-body p-4">

                    <h1 class="h3 text-center mb-2">
                        Create Your Account
                    </h1>

                    <p class="text-muted text-center mb-4">
                        Get started with Mesker Financial.
                    </p>

                    <?php if (!empty($errors)): ?>

                        <div
                            class="alert alert-danger"
                            role="alert"
                        >
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

                    <form method="POST" action="/register">

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
                                Your name
                            </label>

                            <input
                                type="text"
                                class="form-control"
                                id="name"
                                name="name"
                                value="<?= htmlspecialchars(
                                    $name,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                                required
                                autocomplete="name"
                            >

                        </div>

                        <div class="mb-3">

                            <label
                                for="email"
                                class="form-label"
                            >
                                Email address
                            </label>

                            <input
                                type="email"
                                class="form-control"
                                id="email"
                                name="email"
                                value="<?= htmlspecialchars(
                                    $email,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                                required
                                autocomplete="email"
                            >

                        </div>

                        <div class="mb-3">

                            <label
                                for="password"
                                class="form-label"
                            >
                                Password
                            </label>

                            <input
                                type="password"
                                class="form-control"
                                id="password"
                                name="password"
                                required
                                minlength="12"
                                autocomplete="new-password"
                            >

                            <div class="form-text">
                                Password must be at least 12 characters.
                            </div>

                        </div>

                        <div class="mb-4">

                            <label
                                for="password_confirmation"
                                class="form-label"
                            >
                                Confirm password
                            </label>

                            <input
                                type="password"
                                class="form-control"
                                id="password_confirmation"
                                name="password_confirmation"
                                required
                                minlength="12"
                                autocomplete="new-password"
                            >

                        </div>

                        <button
                            type="submit"
                            class="btn btn-primary w-100"
                        >
                            Create Account
                        </button>

                    </form>

                </div>
            </div>

        </div>

    </div>
</div>

</body>
</html>