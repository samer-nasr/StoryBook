<?php

namespace App\Services;

use Spatie\PdfToImage\Pdf;
use Spatie\PdfToImage\Enums\OutputFormat;
use Illuminate\Support\Facades\Log;

class StoryGenerationService
{
    /**
     * Converts a PDF file into individual page images.
     *
     * @param string $pdfPath  The path to the source PDF.
     * @param int    $storyId  The story generation ID for scoped file naming.
     * @return array Array of paths to the generated page images.
     */
    public function extractPages(string $pdfPath, int $storyId): array
    {
        $pdf = new Pdf($pdfPath);
        $numberOfPages = $pdf->pageCount();

        $outputDirectory = storage_path('app/pdf_pages');

        if (!file_exists($outputDirectory)) {
            mkdir($outputDirectory, 0755, true);
        }

        $pageImages = [];

        for ($i = 1; $i <= $numberOfPages; $i++) {
            $outputPath = $outputDirectory . "/story_{$storyId}_page_{$i}.png";
            $pdf->selectPage($i)
                ->format(OutputFormat::Png)
                ->save($outputPath);

            $pageImages[] = $outputPath;
        }

        Log::info("[Story #{$storyId}] Extracted {$numberOfPages} pages from PDF.");

        return $pageImages;
    }

    /**
     * Rebuilds a PDF from an array of image paths.
     *
     * @param array  $imagePaths Array of image file paths to compile.
     * @param string $childName  The child's name for the output filename.
     * @return string Path to the newly generated PDF.
     */
    public function rebuildPdf(array $imagePaths, string $childName): string
    {
        $outputDirectory = storage_path('app/public/generated_stories');

        if (!file_exists($outputDirectory)) {
            mkdir($outputDirectory, 0755, true);
        }

        $timestamp = time();
        $finalPdfPath = $outputDirectory . "/story_{$childName}_{$timestamp}.pdf";

        // --- A4 Landscape Configuration ---
        $pageWidth = 297;
        $pageHeight = 210;
        $pdf = new \FPDF('L', 'mm', 'A4');
        $pageOrientation = 'L';

        // --- A4 Portrait Configuration (Commented out for easy switching) ---
        // $pageWidth = 210;
        // $pageHeight = 297;
        // $pdf = new \FPDF('P', 'mm', 'A4');
        // $pageOrientation = 'P'; 

        $pdf->SetAutoPageBreak(false);

        foreach ($imagePaths as $imagePath) {
            $pdf->AddPage($pageOrientation, 'A4'); 

            // Get image pixel dimensions
            $size = getimagesize($imagePath);
            $imgW = $size[0];
            $imgH = $size[1];

            // Calculate aspect ratios
            $imgRatio = $imgW / $imgH;
            $pageRatio = $pageWidth / $pageHeight;

            // COVER: scale so the image completely fills the page without white borders
            if ($imgRatio > $pageRatio) {
                // Image is wider than page ratio → scale by height to fill the page, excess width gets cropped
                $finalH = $pageHeight;
                $finalW = $pageHeight * $imgRatio;
            } else {
                // Image is taller than page ratio → scale by width to fill the page, excess height gets cropped
                $finalW = $pageWidth;
                $finalH = $pageWidth / $imgRatio;
            }

            // Center the image on the page
            $x = ($pageWidth - $finalW) / 2;
            $y = ($pageHeight - $finalH) / 2;

            $pdf->Image($imagePath, $x, $y, $finalW, $finalH);
        }

        $pdf->Output('F', $finalPdfPath);

        return $finalPdfPath;
    }

    /**
     * Collects all generated page images for a story, sorted by page index.
     *
     * @param int $storyId    The story generation ID.
     * @param int $totalPages The total number of pages.
     * @return array Ordered array of generated page image paths.
     */
    public function collectGeneratedPages(int $storyId, int $totalPages): array
    {
        $pages = [];
        $directory = storage_path('app/generated_pages');

        for ($i = 1; $i <= $totalPages; $i++) {
            $path = $directory . "/story_{$storyId}_page_{$i}.png";
            if (file_exists($path)) {
                $pages[$i] = $path;  // Key by page index for guaranteed ordering
            } else {
                Log::warning("[Story #{$storyId}] Missing generated page {$i} at: {$path}");
            }
        }

        // Sort by key (page index) to guarantee correct order
        ksort($pages);

        return array_values($pages);
    }

    /**
     * Cleans up temporary extracted PDF pages and generated page images for a story.
     *
     * @param int $storyId    The story generation ID.
     * @param int $totalPages The total number of pages.
     */
    public function cleanupTempFiles(int $storyId, int $totalPages): void
    {
        $pdfPagesDir = storage_path('app/pdf_pages');
        $generatedPagesDir = storage_path('app/generated_pages');

        for ($i = 1; $i <= $totalPages; $i++) {
            // Clean extracted PDF pages
            $pdfPage = $pdfPagesDir . "/story_{$storyId}_page_{$i}.png";
            if (file_exists($pdfPage)) {
                @unlink($pdfPage);
            }

            // Clean generated pages (after PDF is rebuilt)
            $generatedPage = $generatedPagesDir . "/story_{$storyId}_page_{$i}.png";
            if (file_exists($generatedPage)) {
                // @unlink($generatedPage);
            }
        }

        Log::info("[Story #{$storyId}] Cleaned up temporary page files.");
    }
}
