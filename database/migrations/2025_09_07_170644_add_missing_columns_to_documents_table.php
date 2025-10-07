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
            $table->text('description')->nullable()->after('type')->comment('Description du document');
            $table->bigInteger('file_size')->nullable()->after('filename_original')->comment('Taille du fichier en octets');
            $table->string('mime_type')->nullable()->after('file_size')->comment('Type MIME du fichier');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn(['description', 'file_size', 'mime_type']);
        });
    }
};
