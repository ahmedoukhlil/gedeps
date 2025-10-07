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
            // Modifier l'enum pour ajouter les nouveaux statuts
            $table->enum('status', [
                'pending', 
                'in_progress', 
                'signed', 
                'refused',
                'paraphed',
                'signed_and_paraphed'
            ])->default('pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            // Revenir à l'enum original
            $table->enum('status', [
                'pending', 
                'in_progress', 
                'signed', 
                'refused'
            ])->default('pending')->change();
        });
    }
};
