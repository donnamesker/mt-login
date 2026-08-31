<?php

declare(strict_types=1);

$title = 'Users';
?>

<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1">Users</h1>
            <p class="text-muted mb-0">
                Users with access to <?= htmlspecialchars(
                    $context['tenant']['name'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </p>
        </div>

        <a href="/users/new" class="btn btn-primary">
            Add User
        </a>
    </div>

    <?php if ($users === []): ?>

        <div class="alert alert-info">
            No users are currently assigned to this account.
        </div>

    <?php else: ?>

        <div class="card">
            <div class="card-body p-0">

                <div class="table-responsive">
                    <table class="table table-hover mb-0">

                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php foreach ($users as $user): ?>

                                <tr>
                                    <td>
                                        <?= htmlspecialchars(
                                            $user['name'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $user['email'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $user['role'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </td>
                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>
                </div>

            </div>
        </div>

    <?php endif; ?>

</div>