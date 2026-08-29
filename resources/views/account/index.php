<?php

declare(strict_types=1);

$title = 'Account | Mesker Financial';

$tenant = $context['tenant'];
$businesses = $context['availableBusinesses'];
?>

<h1 class="mb-4">Account</h1>

<div class="card mb-4">
    <div class="card-body">

        <h2 class="h5">
            Account Information
        </h2>

        <p class="mb-0">
            <?= htmlspecialchars(
                $tenant['name'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </p>

    </div>
</div>

<div class="card">
    <div class="card-body">

        <div class="d-flex justify-content-between align-items-center mb-3">

            <h2 class="h5 mb-0">
                Businesses
            </h2>

        </div>

        <?php if ($businesses === []): ?>

            <p class="text-muted mb-0">
                No businesses found.
            </p>

        <?php else: ?>

            <ul class="list-group">

                <?php foreach ($businesses as $business): ?>

                    <li class="list-group-item">

                        <?= htmlspecialchars(
                            $business['name'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>

                    </li>

                <?php endforeach; ?>

            </ul>

        <?php endif; ?>

    </div>
</div>