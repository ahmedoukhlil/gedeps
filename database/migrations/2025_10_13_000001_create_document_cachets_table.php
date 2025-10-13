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
        Schema::create('document_cachets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->onDelete('cascade');
            $table->foreignId('cacheted_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('cacheted_at');
            $table->text('cachet_comment')->nullable();
            $table->string('path_cacheted_pdf');
            $table->string('path_cachet')->nullable(); // Chemin vers l'image du cachet utilisée
            $table->enum('cachet_type', ['png', 'live'])->default('png');
            $table->text('live_cachet_data')->nullable(); // Données du cachet live (base64)
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('cachet_positions')->nullable(); // Pour les cachets multi-pages
            $table->integer('page_number')->nullable(); // Numéro de page pour cachet simple
            $table->boolean('is_multi_page')->default(false);
            $table->integer('total_pages')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_cachets');
    }
};

