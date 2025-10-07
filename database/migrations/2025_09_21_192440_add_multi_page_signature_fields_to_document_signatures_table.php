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
        Schema::table('document_signatures', function (Blueprint $table) {
            $table->json('signature_positions')->nullable()->comment('Positions des signatures par page (JSON)');
            $table->integer('page_number')->nullable()->comment('Numéro de page pour signature unique');
            $table->boolean('is_multi_page')->default(false)->comment('Indique si le document a des signatures sur plusieurs pages');
            $table->integer('total_pages')->nullable()->comment('Nombre total de pages du document');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_signatures', function (Blueprint $table) {
            $table->dropColumn(['signature_positions', 'page_number', 'is_multi_page', 'total_pages']);
        });
    }
};
