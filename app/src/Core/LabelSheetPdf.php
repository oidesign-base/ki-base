<?php

declare(strict_types=1);

namespace App\Core;

/**
 * PDF of QR label sheets (TCPDF, app/vendor/tcpdf).
 *
 * Layout verified on the real label sheet (LOG.md, 26.09): A4, 6 x 8 labels
 * of 35 x 37.125 mm edge to edge; in each label a 25 x 25 mm zone in the
 * centre with a 19 x 19 mm QR code (level H, no quiet zone of its own - the
 * white label margins serve as one) and the code in bold 10 pt below it.
 * Print at 100 % ("actual size").
 */
final class LabelSheetPdf
{
    public const COLS = 6;
    public const ROWS = 8;
    public const PER_SHEET = self::COLS * self::ROWS;

    private const LABEL_W = 35.0;
    private const LABEL_H = 37.125;
    private const ZONE = 25.0;
    private const QR = 19.0;
    private const FONT_SIZE = 10;

    /**
     * @param array<int, list<string>> $sheets  one list of up to 48 codes per page
     * @return string PDF document
     */
    public static function render(array $sheets, string $title): string
    {
        self::loadTcpdf();

        $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('KI-BASE');
        $pdf->SetAuthor('KI-BASE');
        $pdf->SetTitle($title);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false, 0);
        $pdf->setCellPaddings(0, 0, 0, 0);
        $pdf->SetFont('helveticaB', '', self::FONT_SIZE);
        $pdf->SetTextColor(0, 0, 0);

        $qrStyle = [
            'border'   => false,
            'padding'  => 0,
            'fgcolor'  => [0, 0, 0],
            'bgcolor'  => false,
        ];

        foreach ($sheets as $codes) {
            $pdf->AddPage();
            foreach (array_values($codes) as $i => $code) {
                $col = $i % self::COLS;
                $row = intdiv($i, self::COLS);
                $zoneX = $col * self::LABEL_W + (self::LABEL_W - self::ZONE) / 2;
                $zoneY = $row * self::LABEL_H + (self::LABEL_H - self::ZONE) / 2;

                $pdf->write2DBarcode($code, 'QRCODE,H', $zoneX + (self::ZONE - self::QR) / 2, $zoneY, self::QR, self::QR, $qrStyle, 'N');

                $text  = LabelCode::format($code);
                $textX = $zoneX + (self::ZONE - $pdf->GetStringWidth($text)) / 2;
                // Baseline on the bottom edge of the zone.
                $pdf->Text($textX, $zoneY + self::ZONE, $text, false, false, true, 0, 0, '', false, '', 0, false, 'L');
            }
        }

        return $pdf->Output('', 'S');
    }

    private static function loadTcpdf(): void
    {
        if (class_exists('TCPDF', false)) {
            return;
        }
        // Skip TCPDF's own config file (its defaults suit us) and make errors
        // throw an exception instead of stopping the script with a message.
        if (!defined('K_TCPDF_EXTERNAL_CONFIG')) {
            define('K_TCPDF_EXTERNAL_CONFIG', true);
        }
        if (!defined('K_TCPDF_THROW_EXCEPTION_ERROR')) {
            define('K_TCPDF_THROW_EXCEPTION_ERROR', true);
        }
        require_once APP_ROOT . '/vendor/tcpdf/tcpdf.php';
    }
}
