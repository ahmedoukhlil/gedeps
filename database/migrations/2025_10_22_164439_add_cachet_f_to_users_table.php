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
        Schema::table('users', function (Blueprint $table) {
            // Renommer cachet_path en cachet_p_path (Cachet Prestataire)
            $table->renameColumn('cachet_path', 'cachet_p_path');

            // Ajouter cachet_f_path (Cachet Fournisseur)
            $table->string('cachet_f_path')->nullable()->after('cachet_p_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Supprimer cachet_f_path
            $table->dropColumn('cachet_f_path');

            // Renommer cachet_p_path en cachet_path (retour en arrière)
            $table->renameColumn('cachet_p_path', 'cachet_path');
        });
    }
};
