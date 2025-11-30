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
    {Schema::create('court_session_reports', function (Blueprint $table) {
        $table->id();

        // ربط الجلسة
        $table->foreignId('case_session_id')
              ->constrained('case_sessions')
              ->onDelete('cascade');

        // ربط القضية
        $table->foreignId('court_case_id')
              ->constrained('court_cases')
              ->onDelete('cascade');

        // الطرف (اختياري)
        $table->foreignId('participant_id')
              ->nullable()
              ->constrained('participants')
              ->onDelete('set null');

        // إذا الطرف جديد (شاهد أو غير مسجل)
        $table->string('name')->nullable();
        $table->string('role')->nullable();

        // أقوال الطرف
        $table->longText('statement_text')->nullable();

        // بصمة الطرف في هذه الجلسة
        $table->text('fingerprint')->nullable();

        // محضر الجلسة العام
        $table->longText('report_text')->nullable();

        // قرار الجلسة
        $table->longText('decision_text')->nullable();

        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('court_session_reports');
    }
};
