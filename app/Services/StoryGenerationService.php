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

        $pdf = new \FPDF();

        foreach ($imagePaths as $imagePath) {
            // Get image dimensions to set the PDF page size accordingly
            $size = getimagesize($imagePath);
            $width = $size[0] * 0.264583; // Convert pixels to mm (approx 96 DPI)
            $height = $size[1] * 0.264583;

            // Add a page with the image's dimensions
            $pdf->AddPage($width > $height ? 'L' : 'P', [$width, $height]);
            $pdf->Image($imagePath, 0, 0, $width, $height);
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
                $pages[] = $path;
            } else {
                Log::warning("[Story #{$storyId}] Missing generated page {$i} at: {$path}");
            }
        }

        return $pages;
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
                @unlink($generatedPage);
            }
        }

        Log::info("[Story #{$storyId}] Cleaned up temporary page files.");
    }
}
