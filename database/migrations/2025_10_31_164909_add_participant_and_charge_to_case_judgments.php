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
         Schema::table('case_judgments', function (Blueprint $table) {
        // ربط الحكم بطرف من جدول participants
        $table->unsignedBigInteger('participant_id')->nullable()->after('court_case_id');
        $table->foreign('participant_id')->references('id')->on('participants')->onDelete('set null');

        // تخزين نوع فصل التهمة
        $table->string('charge_decision')->nullable()->after('judgment_type');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('case_judgments', function (Blueprint $table) {
            //
        });
    }
};
