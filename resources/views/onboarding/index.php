<?php

declare(strict_types=1);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Up Your Account</title>
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">

            <div class="mb-4">
                <h1>Set Up Your Account</h1>
                <p class="text-muted">
                    Let's get your account and first business set up.
                </p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php foreach ($errors as $error): ?>
                        <div><?= htmlspecialchars($error) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="/onboarding">

                <input
                    type="hidden"
                    name="_csrf_token"
                    value="<?= htmlspecialchars($csrfToken) ?>"
                >

                <div class="mb-3">
                    <label for="tenant_name" class="form-label">
                        Account Name
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        id="tenant_name"
                        name="tenant_name"
                        value="<?= htmlspecialchars($tenantName) ?>"
                        maxlength="255"
                        required
                        autofocus
                    >

                    <div class="form-text">
                        This is the name of your customer account.
                    </div>
                </div>

                <div class="mb-4">
                    <label for="business_name" class="form-label">
                        Business Name
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        id="business_name"
                        name="business_name"
                        value="<?= htmlspecialchars($businessName) ?>"
                        maxlength="255"
                        required
                    >

                    <div class="form-text">
                        This is the first business you'll manage in this account.
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    Continue
                </button>

            </form>

        </div>
    </div>
</div>

</body>
</html>