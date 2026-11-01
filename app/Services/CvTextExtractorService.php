<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use PhpOffice\PhpWord\IOFactory;
use Smalot\PdfParser\Parser as PdfParser;

class CvTextExtractorService
{
    /**
     * Ekstrak teks mentah dari file CV (pdf/docx) berdasarkan path absolut file.
     * Membutuhkan package composer: smalot/pdfparser dan phpoffice/phpword.
     */
    public function extract(string $absolutePath): string
    {
        $extension = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));

        try {
            return match ($extension) {
                'pdf' => $this->extractFromPdf($absolutePath),
                'docx', 'doc' => $this->extractFromWord($absolutePath),
                default => '',
            };
        } catch (\Throwable $e) {
            Log::warning('Gagal mengekstrak teks CV', [
                'path' => $absolutePath,
                'error' => $e->getMessage(),
            ]);

            return '';
        }
    }

    protected function extractFromPdf(string $path): string
    {
        $parser = new PdfParser();
        $pdf = $parser->parseFile($path);

        return trim($pdf->getText());
    }

    protected function extractFromWord(string $path): string
    {
        $phpWord = IOFactory::load($path);
        $text = '';

        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                if (method_exists($element, 'getText')) {
                    $text .= $element->getText() . "\n";
                } elseif (method_exists($element, 'getElements')) {
                    foreach ($element->getElements() as $childElement) {
                        if (method_exists($childElement, 'getText')) {
                            $text .= $childElement->getText() . "\n";
                        }
                    }
                }
            }
        }

        return trim($text);
    }
}