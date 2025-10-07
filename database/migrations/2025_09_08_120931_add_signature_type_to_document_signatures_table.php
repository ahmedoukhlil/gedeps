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
            $table->string('signature_type')->default('png')->after('signed_by')
                  ->comment('Type de signature: png ou live');
            $table->text('live_signature_data')->nullable()->after('signature_type')
                  ->comment('Données base64 de la signature live');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_signatures', function (Blueprint $table) {
            $table->dropColumn(['signature_type', 'live_signature_data']);
        });
    }
};
