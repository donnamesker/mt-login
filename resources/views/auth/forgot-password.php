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

    <title>Forgot Password | Mesker Financial</title>

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
                        Reset Your Password
                    </h1>

                    <p class="text-muted text-center mb-4">
                        Enter your email address and, if an account exists,
                        we will send you a password reset link.
                    </p>

                    <?php if ($sent): ?>

                        <div
                            class="alert alert-success"
                            role="alert"
                        >
                            If an account exists for that email address,
                            we sent password reset instructions. Check your
                            inbox and follow the link in the email.
                        </div>

                        <div class="text-center">
                            <a
                                href="/login"
                                class="btn btn-outline-primary"
                            >
                                Return to Sign In
                            </a>
                        </div>

                    <?php else: ?>

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

                        <form
                            method="POST"
                            action="/forgot-password"
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

                            <div class="mb-4">

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
                                    autofocus
                                >

                            </div>

                            <button
                                type="submit"
                                class="btn btn-primary w-100 mb-3"
                            >
                                Email Me a Reset Link
                            </button>

                        </form>

                        <div class="text-center">
                            <a href="/login">
                                Return to Sign In
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