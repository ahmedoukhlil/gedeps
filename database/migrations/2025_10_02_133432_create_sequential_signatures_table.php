<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sequential_signatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('signature_order')->comment('Ordre de signature (1, 2, 3...)');
            $table->enum('status', ['pending', 'signed', 'skipped'])->default('pending');
            $table->timestamp('signed_at')->nullable();
            $table->json('signature_data')->nullable()->comment('Données de la signature (coordonnées, image, etc.)');
            $table->text('notes')->nullable()->comment('Notes du signataire');
            $table->timestamps();
            
            $table->unique(['document_id', 'signature_order']);
            $table->index(['document_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sequential_signatures');
    }
};
