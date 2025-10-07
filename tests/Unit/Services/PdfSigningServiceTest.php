<?php

namespace Tests\Unit\Services;

use App\Services\PdfSigningService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PdfSigningServiceTest extends TestCase
{
    use RefreshDatabase;

    private PdfSigningService $pdfSigningService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pdfSigningService = new PdfSigningService();
        Storage::fake('local');
    }

    /** @test */
    public function it_can_validate_pdf_files()
    {
        // Créer un fichier PDF de test simple
        $pdfContent = '%PDF-1.4
1 0 obj
<<
/Type /Catalog
/Pages 2 0 R
>>
endobj
2 0 obj
<<
/Type /Pages
/Kids [3 0 R]
/Count 1
>>
endobj
3 0 obj
<<
/Type /Page
/Parent 2 0 R
/MediaBox [0 0 612 792]
>>
endobj
xref
0 4
0000000000 65535 f 
0000000009 00000 n 
0000000058 00000 n 
0000000115 00000 n 
trailer
<<
/Size 4
/Root 1 0 R
>>
startxref
174
%%EOF';

        $pdfPath = 'test.pdf';
        Storage::put($pdfPath, $pdfContent);

        $this->assertTrue($this->pdfSigningService->isValidPdf($pdfPath));
    }

    /** @test */
    public function it_can_get_pdf_page_count()
    {
        // Créer un fichier PDF de test simple
        $pdfContent = '%PDF-1.4
1 0 obj
<<
/Type /Catalog
/Pages 2 0 R
>>
endobj
2 0 obj
<<
/Type /Pages
/Kids [3 0 R]
/Count 1
>>
endobj
3 0 obj
<<
/Type /Page
/Parent 2 0 R
/MediaBox [0 0 612 792]
>>
endobj
xref
0 4
0000000000 65535 f 
0000000009 00000 n 
0000000058 00000 n 
0000000115 00000 n 
trailer
<<
/Size 4
/Root 1 0 R
>>
startxref
174
%%EOF';

        $pdfPath = 'test.pdf';
        Storage::put($pdfPath, $pdfContent);

        $pageCount = $this->pdfSigningService->getPdfPageCount($pdfPath);
        $this->assertEquals(1, $pageCount);
    }

    /** @test */
    public function it_returns_false_for_invalid_pdf()
    {
        $invalidPath = 'invalid.txt';
        Storage::put($invalidPath, 'This is not a PDF');

        $this->assertFalse($this->pdfSigningService->isValidPdf($invalidPath));
    }

    /** @test */
    public function it_returns_zero_page_count_for_invalid_pdf()
    {
        $invalidPath = 'invalid.txt';
        Storage::put($invalidPath, 'This is not a PDF');

        $pageCount = $this->pdfSigningService->getPdfPageCount($invalidPath);
        $this->assertEquals(0, $pageCount);
    }

    /** @test */
    public function it_throws_exception_when_original_pdf_does_not_exist()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Le fichier PDF original n'existe pas");

        $this->pdfSigningService->signPdf(
            'nonexistent.pdf',
            'signature.png',
            'output.pdf'
        );
    }

    /** @test */
    public function it_throws_exception_when_signature_file_does_not_exist()
    {
        // Créer un PDF de test
        $pdfContent = '%PDF-1.4
1 0 obj
<<
/Type /Catalog
/Pages 2 0 R
>>
endobj
2 0 obj
<<
/Type /Pages
/Kids [3 0 R]
/Count 1
>>
endobj
3 0 obj
<<
/Type /Page
/Parent 2 0 R
/MediaBox [0 0 612 792]
>>
endobj
xref
0 4
0000000000 65535 f 
0000000009 00000 n 
0000000058 00000 n 
0000000115 00000 n 
trailer
<<
/Size 4
/Root 1 0 R
>>
startxref
174
%%EOF';

        $pdfPath = 'test.pdf';
        Storage::put($pdfPath, $pdfContent);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Le fichier de signature n'existe pas");

        $this->pdfSigningService->signPdf(
            $pdfPath,
            'nonexistent-signature.png',
            'output.pdf'
        );
    }
}
