<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\ActivityLog;
use App\Core\Auth;
use App\Core\Database;
use App\Core\HttpException;
use App\Core\LabelCode;
use App\Core\LabelSheetPdf;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;

/**
 * Labels → Code sheets. Codes are generated in advance, 48 ... 240 at a time
 * (a "generation" of 1 ... 5 A4 sheets of one prefix); numbering continues
 * separately for each prefix. The list shows generations; a generation page
 * shows its sheets as 6 x 8 grids, like the printed paper. Nothing is ever
 * deleted: a label spoilt in printing is marked "spoiled" (and can be marked
 * back); any sheet can be printed again.
 */
final class LabelSheetController extends Controller
{
    private const BASE = '/labels';

    /** Allowed number of codes per generation: 1 ... 5 sheets. */
    public const QUANTITIES = [48, 96, 144, 192, 240];

    /** Layout id stored with each sheet (see LabelSheetPdf). */
    private const LAYOUT = 'A4-6x8-35x37.125';

    /** At most this many sheets in one PDF request. */
    private const MAX_PDF_SHEETS = 20;

    public function index(Request $request): string
    {
        $generations = Database::fetchAll(
            'SELECT g.id, g.first_code, g.last_code, g.codes_count, g.created_at,
                    p.prefix, u.display_name AS created_by_name,
                    (SELECT GROUP_CONCAT(s.id ORDER BY s.id) FROM code_sheets s WHERE s.generation_id = g.id) AS sheet_ids,
                    SUM(cp.status = \'free\')     AS free_count,
                    SUM(cp.status = \'assigned\') AS assigned_count,
                    SUM(cp.status = \'spoiled\')  AS spoiled_count
               FROM code_generations g
               JOIN code_prefixes p ON p.id = g.code_prefix_id
               LEFT JOIN users u ON u.id = g.created_by
               LEFT JOIN code_sheets s ON s.generation_id = g.id
               LEFT JOIN code_pool cp ON cp.sheet_id = s.id
              GROUP BY g.id
              ORDER BY g.id DESC'
        );

        $download = Session::get('label_download');
        Session::forget('label_download');

        return View::render('labels/sheets/index', [
            'title'       => t('menu.labels'),
            'activePath'  => self::BASE,
            'generations' => $generations,
            'prefixes'    => array_values(array_unique(array_column($generations, 'prefix'))),
            'download'    => is_array($download) ? $download : null,
            'scripts'     => ['datatables'],
        ]);
    }

    public function create(Request $request): string
    {
        $prefixes = Database::fetchAll(
            'SELECT p.id, p.prefix, p.description, p.is_default,
                    (SELECT MAX(SUBSTRING(s.last_code, 3, 6)) FROM code_sheets s WHERE s.code_prefix_id = p.id) AS last_number
               FROM code_prefixes p
              WHERE p.is_active = 1
              ORDER BY p.is_default DESC, p.prefix'
        );

        return View::render('labels/sheets/create', [
            'title'       => t('sheets.new'),
            'activePath'  => self::BASE,
            'breadcrumbs' => [[t('menu.labels'), url(self::BASE)]],
            'prefixes'    => $prefixes,
            'quantities'  => self::QUANTITIES,
            'action'      => url(self::BASE),
            'backUrl'     => url(self::BASE),
        ]);
    }

    public function store(Request $request): Response
    {
        $prefixId = (int) $request->input('code_prefix_id');
        $quantity = (int) $request->input('quantity');
        $errors   = [];

        if (!in_array($quantity, self::QUANTITIES, true)) {
            $errors['quantity'] = t('sheets.invalid_quantity');
        }
        $prefix = Database::fetch('SELECT id, prefix FROM code_prefixes WHERE id = ? AND is_active = 1', [$prefixId]);
        if ($prefix === null) {
            $errors['code_prefix_id'] = t('sheets.invalid_prefix');
        }
        if ($errors !== []) {
            return $this->backWithErrors(url(self::BASE . '/create'), $errors, $request->all());
        }

        $result = Database::transaction(function () use ($prefix, $quantity): ?array {
            // Lock the prefix row: two generations for the same prefix run one after another.
            Database::fetch('SELECT id FROM code_prefixes WHERE id = ? FOR UPDATE', [$prefix['id']]);
            $last = Database::value(
                'SELECT MAX(SUBSTRING(last_code, 3, 6)) FROM code_sheets WHERE code_prefix_id = ?',
                [$prefix['id']]
            );
            $next = $last === null ? 1 : (int) $last + 1;
            if ($next + $quantity - 1 > LabelCode::MAX_NUMBER) {
                return null;
            }

            $firstCode = LabelCode::make($prefix['prefix'], $next);
            $lastCode  = LabelCode::make($prefix['prefix'], $next + $quantity - 1);
            Database::query(
                'INSERT INTO code_generations (code_prefix_id, first_code, last_code, codes_count, created_by)
                 VALUES (?, ?, ?, ?, ?)',
                [$prefix['id'], $firstCode, $lastCode, $quantity, Auth::id()]
            );
            $generationId = Database::lastInsertId();

            $sheetIds = [];
            for ($s = 0; $s < intdiv($quantity, LabelSheetPdf::PER_SHEET); $s++) {
                $codes = [];
                for ($i = 0; $i < LabelSheetPdf::PER_SHEET; $i++) {
                    $codes[] = LabelCode::make($prefix['prefix'], $next++);
                }
                Database::query(
                    'INSERT INTO code_sheets (generation_id, code_prefix_id, first_code, last_code, codes_count, layout, printed_by)
                     VALUES (?, ?, ?, ?, ?, ?, ?)',
                    [$generationId, $prefix['id'], $codes[0], end($codes), count($codes), self::LAYOUT, Auth::id()]
                );
                $sheetId = Database::lastInsertId();

                $placeholders = implode(', ', array_fill(0, count($codes), '(?, ?)'));
                $params = [];
                foreach ($codes as $code) {
                    $params[] = $code;
                    $params[] = $sheetId;
                }
                Database::query('INSERT INTO code_pool (code, sheet_id) VALUES ' . $placeholders, $params);
                $sheetIds[] = $sheetId;
            }

            ActivityLog::log('create', 'code_generation', $generationId, [
                'prefix'      => [null, $prefix['prefix']],
                'first_code'  => [null, $firstCode],
                'last_code'   => [null, $lastCode],
                'codes_count' => [null, $quantity],
            ]);

            return ['id' => $generationId, 'sheets' => $sheetIds, 'first' => $firstCode, 'last' => $lastCode];
        });

        if ($result === null) {
            Session::flash('danger', t('sheets.numbers_exhausted', ['prefix' => $prefix['prefix']]));
            return Response::redirect(url(self::BASE . '/create'));
        }

        $range = LabelCode::format($result['first']) . ' – ' . LabelCode::format($result['last']);
        Session::flash('success', t('sheets.created', ['range' => $range, 'sheets' => count($result['sheets'])]));
        Session::set('label_download', ['id' => $result['id'], 'ids' => $result['sheets'], 'range' => $range]);
        return Response::redirect(url(self::BASE));
    }

    /** A generation: its sheets as 6 x 8 grids. */
    public function show(Request $request, int $id): string
    {
        $generation = $this->find($id);
        $sheets = Database::fetchAll(
            'SELECT id, first_code, last_code FROM code_sheets WHERE generation_id = ? ORDER BY id',
            [$id]
        );
        $codes = [];
        foreach (Database::fetchAll(
            'SELECT cp.sheet_id, cp.code, cp.status
               FROM code_pool cp
               JOIN code_sheets s ON s.id = cp.sheet_id
              WHERE s.generation_id = ?
              ORDER BY cp.code',
            [$id]
        ) as $row) {
            $codes[$row['sheet_id']][] = $row;
        }

        return View::render('labels/sheets/show', [
            'title'       => LabelCode::format($generation['first_code']) . ' – ' . LabelCode::format($generation['last_code']),
            'activePath'  => self::BASE,
            'breadcrumbs' => [[t('menu.labels'), url(self::BASE)]],
            'generation'  => $generation,
            'sheets'      => $sheets,
            'codes'       => $codes,
        ]);
    }

    /** PDF of one or more sheets: /labels/pdf?ids=12,13,14 (new print or reprint). */
    public function pdf(Request $request): Response
    {
        $ids = array_values(array_unique(array_filter(
            array_map('intval', explode(',', $request->query('ids'))),
            static fn (int $v): bool => $v > 0
        )));
        if ($ids === [] || count($ids) > self::MAX_PDF_SHEETS) {
            throw new HttpException(404);
        }
        sort($ids);

        $placeholders = implode(', ', array_fill(0, count($ids), '?'));
        $sheets = Database::fetchAll(
            'SELECT id, first_code, last_code FROM code_sheets WHERE id IN (' . $placeholders . ') ORDER BY id',
            $ids
        );
        if (count($sheets) !== count($ids)) {
            throw new HttpException(404);
        }

        $pages = [];
        foreach ($sheets as $sheet) {
            $pages[] = array_column(
                Database::fetchAll('SELECT code FROM code_pool WHERE sheet_id = ? ORDER BY code', [$sheet['id']]),
                'code'
            );
            ActivityLog::log('print', 'code_sheet', (int) $sheet['id']);
        }

        $first = $sheets[0]['first_code'];
        $last  = end($sheets)['last_code'];
        $title = 'KI-BASE ' . LabelCode::format($first) . ' – ' . LabelCode::format($last);
        $name  = substr($first, 0, 2) === substr($last, 0, 2)
            ? sprintf('kody-%s-%s-%s.pdf', substr($first, 0, 2), substr($first, 2, 6), substr($last, 2, 6))
            : 'kody.pdf';

        return Response::pdf(LabelSheetPdf::render($pages, $title), $name);
    }

    /** Mark a free code as spoiled, or a spoiled one as free again. */
    public function spoil(Request $request, int $id): Response
    {
        $this->find($id);
        $code = $request->input('code');
        $row  = Database::fetch(
            'SELECT cp.code, cp.status, cp.sheet_id
               FROM code_pool cp
               JOIN code_sheets s ON s.id = cp.sheet_id
              WHERE cp.code = ? AND s.generation_id = ?',
            [$code, $id]
        );
        if ($row === null) {
            throw new HttpException(404);
        }
        $back = url(self::BASE . '/' . $id) . '#sheet-' . (int) $row['sheet_id'];

        if ($row['status'] === 'assigned') {
            Session::flash('danger', t('sheets.cannot_spoil_assigned', ['code' => LabelCode::format($code)]));
            return Response::redirect($back);
        }

        $new = $row['status'] === 'free' ? 'spoiled' : 'free';
        Database::query(
            'UPDATE code_pool SET status = ?, status_changed_at = UTC_TIMESTAMP() WHERE code = ? AND status = ?',
            [$new, $code, $row['status']]
        );
        ActivityLog::log('status', 'code_sheet', (int) $row['sheet_id'], [$code => [$row['status'], $new]]);

        Session::flash('success', t($new === 'spoiled' ? 'sheets.spoiled' : 'sheets.unspoiled', ['code' => LabelCode::format($code)]));
        return Response::redirect($back);
    }

    private function find(int $id): array
    {
        $generation = Database::fetch(
            'SELECT g.id, g.first_code, g.last_code, g.codes_count, g.created_at, p.prefix, p.description,
                    u.display_name AS created_by_name
               FROM code_generations g
               JOIN code_prefixes p ON p.id = g.code_prefix_id
               LEFT JOIN users u ON u.id = g.created_by
              WHERE g.id = ?',
            [$id]
        );
        if ($generation === null) {
            throw new HttpException(404);
        }
        return $generation;
    }
}
