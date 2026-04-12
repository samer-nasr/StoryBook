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

            // Images are now pre-padded to A4 landscape ratio in splitGrid()
            // Just place them to fill the page exactly
            $pdf->Image($imagePath, 0, 0, $pageWidth, $pageHeight);
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

    /**
     * Stitches extracted 512x512 pages into 1024x1024 grid payloads to drastically cut API compute requirements.
     */
    public function createGrids(int $storyId, array $pageImages): array
    {
        $grids = [];
        $chunks = array_chunk($pageImages, 4, true); // Chunks of 4 pages
        $outputDirectory = storage_path('app/pdf_pages');
        $gridIndex = 1;

        // A4 native panel size (Portrait scaled to width 512)
        // Ratio 297/210 = 1.414. Height = 512 / 1.414 = 362.
        $panelW = 512;
        $panelH = 362;

        foreach ($chunks as $chunk) {
            $gridCanvas = new \Imagick();
            $gridCanvas->newImage(1024, 1024, new \ImagickPixel('white')); // Standard OpenAI size
            $gridCanvas->setImageFormat('png');

            // Vertically center the 1024x724 content block (1024 - (362*2)) / 2 = 150
            $vOffset = 150;

            $positions = [
                ['x' => 0, 'y' => $vOffset],                // Top-Left
                ['x' => 512, 'y' => $vOffset],              // Top-Right
                ['x' => 0, 'y' => $vOffset + $panelH],      // Bottom-Left
                ['x' => 512, 'y' => $vOffset + $panelH],    // Bottom-Right
            ];

            $pageIndices = [];
            $posId = 0;
            foreach ($chunk as $originalIndex => $pageImagePath) {
                // Determine 1-based index based on position in source array
                $realPageIndex = $originalIndex + 1;
                $pageIndices[] = $realPageIndex;

                $page = new \Imagick($pageImagePath);

                // Resize proportionally to A4-native panel size (512x362)
                $page->resizeImage($panelW, $panelH, \Imagick::FILTER_LANCZOS, 1);

                // Drop the panel directly into its coordinate on the master canvas
                $gridCanvas->compositeImage($page, \Imagick::COMPOSITE_OVER, $positions[$posId]['x'], $positions[$posId]['y']);
                
                $page->clear();
                $posId++;
            }

            $gridCanvas->setImageFormat('png');

            $gridFileName = $outputDirectory . "/story_{$storyId}_grid_{$gridIndex}.png";
            $gridCanvas->writeImage($gridFileName);
            $gridCanvas->clear();

            $grids[$gridFileName] = $pageIndices;
            $gridIndex++;
        }

        return $grids;
    }

    /**
     * Splits a generated 1024x1024 grid back into the standard individual 512x512 generated page arrays natively.
     */
    public function splitGrid(string $editedGridPath, int $storyId, array $pageIndices): void
    {
        $outputDirectory = storage_path('app/generated_pages');
        if (!file_exists($outputDirectory)) {
            mkdir($outputDirectory, 0755, true);
        }

        $grid = new \Imagick($editedGridPath);
        
        // Final A4 Landscape dimensions (300 DPI)
        $a4Width = 3508;
        $a4Height = 2480;

        // A4 native panel size in the 1024x1024 grid
        $panelW = 512;
        $panelH = 362;
        $vOffset = 150;

        $positions = [
            ['x' => 0, 'y' => $vOffset],
            ['x' => 512, 'y' => $vOffset],
            ['x' => 0, 'y' => $vOffset + $panelH],
            ['x' => 512, 'y' => $vOffset + $panelH],
        ];

        foreach ($pageIndices as $iterator => $actualPageIndex) {
            $splitPiece = clone $grid;
            
            // 3. Extract the A4-native panel slice (512x362)
            $splitPiece->cropImage($panelW, $panelH, $positions[$iterator]['x'], $positions[$iterator]['y']);
            $splitPiece->setImagePage($panelW, $panelH, 0, 0);
            
            // 4. Directly upscale to full-bleed A4 Landscape (3508x2480)
            $splitPiece->resizeImage($a4Width, $a4Height, \Imagick::FILTER_LANCZOS, 1);
            
            // 5. Set 300 DPI resolution
            $splitPiece->setImageResolution(300, 300);
            $splitPiece->setImageUnits(\Imagick::RESOLUTION_PIXELSPERINCH);
            
            $savePath = $outputDirectory . "/story_{$storyId}_page_{$actualPageIndex}.png";
            
            $splitPiece->writeImage($savePath);
            
            $splitPiece->clear();
        }

        $grid->clear();
    }
}
