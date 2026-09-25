<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\View;

/** Placeholder page for menu sections that are not built yet. */
final class SectionController
{
    public function show(Request $request, string $labelKey): string
    {
        return View::render('sections/placeholder', [
            'title'      => t($labelKey),
            'activePath' => $request->path,
        ]);
    }
}
