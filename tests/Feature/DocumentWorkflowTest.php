<?php

namespace Tests\Feature;

use App\Events\DocumentSigned;
use App\Events\DocumentUploaded;
use App\Models\Document;
use App\Models\User;
use App\Services\PdfSigningService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DocumentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $agent;
    private User $dg;
    private User $daf;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        // Créer les permissions
        $permissions = [
            'documents.upload',
            'documents.view',
            'documents.view-own',
            'documents.approve',
            'documents.sign',
            'documents.refuse',
            'documents.download',
            'documents.history',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Créer les rôles
        $agentRole = Role::create(['name' => 'Agent']);
        $dgRole = Role::create(['name' => 'DG']);
        $dafRole = Role::create(['name' => 'DAF']);

        // Assigner les permissions
        $agentRole->givePermissionTo(['documents.upload', 'documents.view-own', 'documents.download']);
        $dgRole->givePermissionTo($permissions);
        $dafRole->givePermissionTo($permissions);

        // Créer les utilisateurs
        $this->agent = User::factory()->create();
        $this->agent->assignRole('Agent');

        $this->dg = User::factory()->create();
        $this->dg->assignRole('DG');

        $this->daf = User::factory()->create();
        $this->daf->assignRole('DAF');
    }

    /** @test */
    public function agent_can_upload_document()
    {
        Event::fake();

        $this->actingAs($this->agent);

        $file = UploadedFile::fake()->create('document.pdf', 1000, 'application/pdf');

        Livewire::test('upload-document')
            ->set('file', $file)
            ->set('type', 'contrat')
            ->set('comment_agent', 'Test document')
            ->call('upload')
            ->assertHasNoErrors()
            ->assertSet('file', null);

        $this->assertDatabaseHas('documents', [
            'type' => 'contrat',
            'comment_agent' => 'Test document',
            'uploaded_by' => $this->agent->id,
            'status' => Document::STATUS_PENDING,
        ]);

        Event::assertDispatched(DocumentUploaded::class);
    }

    /** @test */
    public function dg_can_see_pending_documents()
    {
        // Créer un document en attente
        $document = Document::factory()->create([
            'uploaded_by' => $this->agent->id,
            'status' => Document::STATUS_PENDING,
        ]);

        $this->actingAs($this->dg);

        $component = Livewire::test('document-approval');

        $this->assertTrue($component->get('documents')->contains('id', $document->id));
    }

    /** @test */
    public function dg_can_sign_document()
    {
        Event::fake();

        // Créer un document en attente
        $document = Document::factory()->create([
            'uploaded_by' => $this->agent->id,
            'status' => Document::STATUS_PENDING,
        ]);

        $this->actingAs($this->dg);

        $signatureFile = UploadedFile::fake()->image('signature.png', 150, 75);

        Livewire::test('document-approval')
            ->call('showSignModal', $document->id)
            ->set('signatureFile', $signatureFile)
            ->call('signDocument')
            ->assertHasNoErrors();

        $document->refresh();
        $this->assertEquals(Document::STATUS_SIGNED, $document->status);

        $this->assertDatabaseHas('document_signatures', [
            'document_id' => $document->id,
            'signed_by' => $this->dg->id,
        ]);

        Event::assertDispatched(DocumentSigned::class);
    }

    /** @test */
    public function dg_can_refuse_document()
    {
        Event::fake();

        // Créer un document en attente
        $document = Document::factory()->create([
            'uploaded_by' => $this->agent->id,
            'status' => Document::STATUS_PENDING,
        ]);

        $this->actingAs($this->dg);

        Livewire::test('document-approval')
            ->call('showRefuseModal', $document->id)
            ->set('refusalComment', 'Document incomplet')
            ->call('refuseDocument')
            ->assertHasNoErrors();

        $document->refresh();
        $this->assertEquals(Document::STATUS_REFUSED, $document->status);

        $this->assertDatabaseHas('document_signatures', [
            'document_id' => $document->id,
            'signed_by' => $this->dg->id,
            'comment_manager' => 'Document incomplet',
        ]);

        Event::assertDispatched(DocumentRefused::class);
    }

    /** @test */
    public function agent_can_see_their_own_documents()
    {
        // Créer des documents pour différents utilisateurs
        $agentDocument = Document::factory()->create([
            'uploaded_by' => $this->agent->id,
        ]);

        $otherDocument = Document::factory()->create([
            'uploaded_by' => $this->dg->id,
        ]);

        $this->actingAs($this->agent);

        $component = Livewire::test('document-list');

        $this->assertTrue($component->get('documents')->contains('id', $agentDocument->id));
        $this->assertFalse($component->get('documents')->contains('id', $otherDocument->id));
    }

    /** @test */
    public function dg_can_see_all_documents()
    {
        // Créer des documents pour différents utilisateurs
        $agentDocument = Document::factory()->create([
            'uploaded_by' => $this->agent->id,
        ]);

        $dgDocument = Document::factory()->create([
            'uploaded_by' => $this->dg->id,
        ]);

        $this->actingAs($this->dg);

        $component = Livewire::test('document-list');

        $this->assertTrue($component->get('documents')->contains('id', $agentDocument->id));
        $this->assertTrue($component->get('documents')->contains('id', $dgDocument->id));
    }

    /** @test */
    public function agent_cannot_access_approval_page()
    {
        $this->actingAs($this->agent);

        $response = $this->get(route('documents.pending'));
        $response->assertStatus(403);
    }

    /** @test */
    public function dg_can_access_approval_page()
    {
        $this->actingAs($this->dg);

        $response = $this->get(route('documents.pending'));
        $response->assertStatus(200);
    }

    /** @test */
    public function document_upload_validation_works()
    {
        $this->actingAs($this->agent);

        // Test avec un fichier trop volumineux
        $largeFile = UploadedFile::fake()->create('document.pdf', 15000, 'application/pdf');

        Livewire::test('upload-document')
            ->set('file', $largeFile)
            ->set('type', 'contrat')
            ->call('upload')
            ->assertHasErrors(['file']);

        // Test sans type
        $file = UploadedFile::fake()->create('document.pdf', 1000, 'application/pdf');

        Livewire::test('upload-document')
            ->set('file', $file)
            ->set('type', '')
            ->call('upload')
            ->assertHasErrors(['type']);
    }
}
