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
        Schema::create('document_signatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->onDelete('cascade')
                  ->comment('Document associé à cette signature');
            $table->foreignId('signed_by')->constrained('users')
                  ->comment('Utilisateur qui a signé le document');
            $table->string('path_signature')->nullable()->comment('Chemin vers l\'image de signature');
            $table->string('path_signed_pdf')->nullable()->comment('Chemin vers le PDF signé');
            $table->text('comment_manager')->nullable()->comment('Commentaire du manager (refus ou validation)');
            $table->timestamp('signed_at')->nullable()->comment('Date et heure de la signature');
            $table->string('ip_address')->nullable()->comment('Adresse IP lors de la signature');
            $table->text('user_agent')->nullable()->comment('User agent lors de la signature');
            $table->timestamps();

            // Index pour optimiser les requêtes
            $table->index(['document_id', 'signed_by']);
            $table->index('signed_at');
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
        Schema::dropIfExists('document_signatures');
    }
};
