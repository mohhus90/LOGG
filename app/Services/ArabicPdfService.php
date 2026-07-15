<?php

namespace App\Services;

use Mpdf\Mpdf;

/**
 * dompdf has no Unicode bidi implementation (verified: no bidi-related code
 * anywhere in vendor/dompdf/dompdf/src) - it draws Arabic text glyphs in
 * logical (source) order instead of correctly bidi-reordered visual order,
 * so RTL text renders reversed in every standards-compliant PDF viewer
 * (confirmed on Android and desktop, not just one "quirky" renderer). mPdf
 * implements the bidi algorithm properly and is the standard choice for
 * Arabic PDF generation in PHP - used here instead of dompdf for the
 * employee-facing PDFs (salary certificate, payslip).
 */
class ArabicPdfService
{
    public static function fromView(string $view, array $data = []): Mpdf
    {
        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'default_font' => 'dejavusans',
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
        ]);

        $mpdf->SetDirectionality('rtl');
        $mpdf->WriteHTML(view($view, $data)->render());

        return $mpdf;
    }

    public static function download(string $view, array $data, string $fileName)
    {
        return response(self::fromView($view, $data)->Output($fileName, 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }
}
