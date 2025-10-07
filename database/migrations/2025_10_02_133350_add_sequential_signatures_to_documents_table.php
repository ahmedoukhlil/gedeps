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
        Schema::table('documents', function (Blueprint $table) {
            // Ajouter le support des signatures séquentielles
            $table->json('signature_queue')->nullable()->comment('File des signataires dans l\'ordre');
            $table->integer('current_signature_index')->default(0)->comment('Index du signataire actuel');
            $table->json('completed_signatures')->nullable()->comment('Signatures complétées avec timestamps');
            $table->boolean('sequential_signatures')->default(false)->comment('Activer les signatures séquentielles');
            $table->timestamp('last_signature_at')->nullable()->comment('Dernière signature effectuée');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn([
                'signature_queue',
                'current_signature_index', 
                'completed_signatures',
                'sequential_signatures',
                'last_signature_at'
            ]);
        });
    }
};
