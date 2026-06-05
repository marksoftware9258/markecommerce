<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            // Only add if column doesn't exist
            if (!Schema::hasColumn('personal_access_tokens', 'tokenable_type')) {
                $table->string('tokenable_type')->after('id');
            }
            if (!Schema::hasColumn('personal_access_tokens', 'tokenable_id')) {
                $table->unsignedBigInteger('tokenable_id')->after('tokenable_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->dropColumn(['tokenable_type', 'tokenable_id']);
        });
    }
};