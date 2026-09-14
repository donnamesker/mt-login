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

    <title>Sign In | Mesker Financial</title>

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

        <div class="col-12 col-sm-10 col-md-6 col-lg-4">

            <div class="card shadow-sm">
                <div class="card-body p-4">

                    <h1 class="h3 text-center mb-4">
                        Sign In
                    </h1>

                    <?php if (!empty($message)): ?>

                        <div
                            class="alert alert-success"
                            role="alert"
                        >
                            <?= htmlspecialchars(
                                $message,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </div>

                    <?php endif; ?>

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

                    <form method="POST" action="/login">

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

                        <div class="mb-2">

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
                                autocomplete="current-password"
                            >

                        </div>

                        <div class="text-end mb-4">
                            <a href="/forgot-password">
                                Forgot your password?
                            </a>
                        </div>

                        <button
                            type="submit"
                            class="btn btn-primary w-100"
                        >
                            Sign In
                        </button>

                    </form>

                </div>
            </div>

        </div>

    </div>
</div>

</body>
</html>