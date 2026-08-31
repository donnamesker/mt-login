<?php

declare(strict_types=1);

$title = 'Edit Business | Mesker Financial';

$errors = $errors ?? [];

$name = $name
    ?? $business['name'];
?>

<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h1 class="mb-1">
                Edit Business
            </h1>

            <p class="text-muted mb-0">
                <?= htmlspecialchars(
                    $context['tenant']['name'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </p>

        </div>

        <a
            href="/businesses"
            class="btn btn-outline-secondary"
        >
            Back to Businesses
        </a>

    </div>

    <?php if ($errors !== []): ?>

        <?php foreach ($errors as $error): ?>

            <div
                class="alert alert-danger"
                role="alert"
            >
                <?= htmlspecialchars(
                    $error,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </div>

        <?php endforeach; ?>

    <?php endif; ?>

    <div class="row">

        <div class="col-lg-8">

            <div class="card">

                <div class="card-body">

                    <h2 class="h5 mb-3">
                        Business Information
                    </h2>

                    <form
                        method="POST"
                        action="/businesses/update"
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
                            name="business_id"
                            value="<?= (int) $business['id'] ?>"
                        >

                        <div class="mb-3">

                            <label
                                for="name"
                                class="form-label"
                            >
                                Business Name
                            </label>

                            <input
                                type="text"
                                name="name"
                                id="name"
                                class="form-control"
                                value="<?= htmlspecialchars(
                                    $name,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                                maxlength="255"
                                required
                            >

                        </div>

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            Save Changes
                        </button>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>