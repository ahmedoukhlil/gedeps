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
        Schema::table('sequential_signatures', function (Blueprint $table) {
            $table->timestamp('paraphed_at')->nullable()->after('signed_at');
            $table->json('paraphe_data')->nullable()->after('signature_data');
            $table->text('paraphe_comment')->nullable()->after('notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sequential_signatures', function (Blueprint $table) {
            $table->dropColumn(['paraphed_at', 'paraphe_data', 'paraphe_comment']);
        });
    }
};
