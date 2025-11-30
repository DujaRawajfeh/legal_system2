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

        // حذف الحقول التي لا تحتاجيها
        $table->dropColumn('plaintiff');
        $table->dropColumn('defendant');
        $table->dropColumn('third_party');
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
