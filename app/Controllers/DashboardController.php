<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Support\View;

class DashboardController extends AuthenticatedController
{
    public function index(): void
    {
        $context = $this->requireContext();

        $contextError = $_GET['context_error'] ?? null;

        View::render('dashboard/index', [
            'context' => $context,
            'contextError' => $contextError
        ]);
    }
}
