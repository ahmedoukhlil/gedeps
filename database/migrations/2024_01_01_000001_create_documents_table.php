<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('type', 100)->comment('Type de document (contrat, facture, etc.)');
            $table->string('path_original')->comment('Chemin vers le fichier original');
            $table->string('filename_original')->comment('Nom original du fichier');
            $table->text('comment_agent')->nullable()->comment('Commentaire de l\'agent qui a uploadé');
            $table->foreignId('uploaded_by')->constrained('users')->comment('Utilisateur qui a uploadé le document');
            $table->enum('status', ['pending', 'in_progress', 'signed', 'refused'])
                  ->default('pending')
                  ->comment('Statut du document dans le workflow');
            $table->timestamps();

            // Index pour optimiser les requêtes
            $table->index(['status', 'uploaded_by']);
            $table->index(['type', 'status']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('documents');
    }
};
