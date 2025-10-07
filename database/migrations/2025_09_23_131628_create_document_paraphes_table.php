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
        Schema::create('document_paraphes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->onDelete('cascade');
            $table->foreignId('paraphed_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('paraphed_at');
            $table->text('paraphe_comment')->nullable();
            $table->string('path_paraphed_pdf');
            $table->enum('paraphe_type', ['png', 'live'])->default('png');
            $table->json('paraphe_positions')->nullable(); // Pour les paraphes multi-pages
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
        Schema::dropIfExists('document_paraphes');
    }
};
