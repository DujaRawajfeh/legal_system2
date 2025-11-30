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

    // حذف العلاقة القديمة
    if (Schema::hasColumn('request_schedules', 'user_id')) {
        $table->dropForeign(['user_id']);
        $table->dropColumn('user_id');
    }

    // ✔ إضافة العلاقة الجديدة فقط إذا لم يكن العمود موجوداً
    if (!Schema::hasColumn('request_schedules', 'judge_id')) {
        $table->foreignId('judge_id')->nullable()->constrained('users')->nullOnDelete();
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
