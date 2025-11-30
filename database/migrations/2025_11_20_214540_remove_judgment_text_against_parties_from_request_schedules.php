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
         Schema::table('request_schedules', function (Blueprint $table) {
            if (Schema::hasColumn('request_schedules', 'judgment_text_against_parties')) {
                $table->dropColumn('judgment_text_against_parties');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('request_schedules', function (Blueprint $table) {
            //
        });
    }
};
