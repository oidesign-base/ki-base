<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\Request;
use App\Core\View;

final class DashboardController
{
    public function index(Request $request): string
    {
        $stats = Database::fetch(
            "SELECT
                (SELECT COUNT(*) FROM batches WHERE deleted_at IS NULL AND status = 'open')           AS open_batches,
                (SELECT COUNT(*) FROM items   WHERE deleted_at IS NULL
                                                AND status IN ('received','identified','priced','photographed')) AS in_work,
                (SELECT COUNT(*) FROM items   WHERE deleted_at IS NULL AND status = 'listed')          AS listed,
                (SELECT COUNT(*) FROM sales   WHERE deleted_at IS NULL AND status = 'completed'
                                                AND sale_date >= ?)                                  AS sold_this_month",
            [(new \DateTimeImmutable('first day of this month'))->format('Y-m-d')] // local (Warsaw) calendar month
        ) ?? [];

        return View::render('dashboard/index', [
            'title'      => t('menu.dashboard'),
            'activePath' => '/',
            'stats'      => $stats,
        ]);
    }
}
