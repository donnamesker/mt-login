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

    <title>Choose a New Password | Mesker Financial</title>

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
                        Choose a New Password
                    </h1>

                    <p class="text-muted text-center mb-4">
                        Enter your new password below.
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

                    <?php if ($validToken): ?>

                        <form
                            method="POST"
                            action="/reset-password"
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
                                name="token"
                                value="<?= htmlspecialchars(
                                    $token,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                            >

                            <div class="mb-3">

                                <label
                                    for="password"
                                    class="form-label"
                                >
                                    New password
                                </label>

                                <input
                                    type="password"
                                    class="form-control"
                                    id="password"
                                    name="password"
                                    required
                                    minlength="12"
                                    autocomplete="new-password"
                                    autofocus
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
                                    Confirm new password
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
                                Reset Password
                            </button>

                        </form>

                    <?php else: ?>

                        <div class="text-center">
                            <a
                                href="/forgot-password"
                                class="btn btn-primary"
                            >
                                Request a New Reset Link
                            </a>
                        </div>

                    <?php endif; ?>

                </div>
            </div>

        </div>

    </div>
</div>

</body>
</html>