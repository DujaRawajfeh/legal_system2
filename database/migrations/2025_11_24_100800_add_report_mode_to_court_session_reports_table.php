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
        Schema::table('court_session_reports', function (Blueprint $table) {
            // 🔹 نضيف العمود بعد role مثلاً (اختياري مكانه)
            $table->string('report_mode', 20)
                  ->nullable()
                  ->after('role'); // تقدري تغيّري المكان لو حابة
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('court_session_reports', function (Blueprint $table) {
            $table->dropColumn('report_mode');
        });
    }
};