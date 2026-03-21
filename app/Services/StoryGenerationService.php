<?php

namespace App\Services;

use Spatie\PdfToImage\Pdf;
use Spatie\PdfToImage\Enums\OutputFormat;
use Illuminate\Support\Facades\Storage;

class StoryGenerationService
{
    /**
     * Converts a PDF file into individual page images.
     *
     * @param string $pdfPath
     * @return array Array of paths to the generated page images.
     */
    public function extractPages(string $pdfPath): array
    {
        $pdf = new Pdf($pdfPath);
        $numberOfPages = $pdf->pageCount();
        
        $outputDirectory = storage_path('app/pdf_pages');
        
        if (!file_exists($outputDirectory)) {
            mkdir($outputDirectory, 0755, true);
        }

        $pageImages = [];

        for ($i = 1; $i <= $numberOfPages; $i++) {
            $outputPath = $outputDirectory . '/page_' . $i . '.png';
            $pdf->selectPage($i)
                ->format(OutputFormat::Png)
                ->save($outputPath);
            
            $pageImages[] = $outputPath;
        }

        return $pageImages;
    }

    /**
     * Rebuilds a PDF from an array of image paths.
     *
     * @param array $imagePaths
     * @param string $childName
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
}
