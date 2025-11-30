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
    {Schema::create('case_judgments', function (Blueprint $table) {
    $table->id();

    // ربط الحكم بالدعوى
    $table->foreignId('court_case_id')->constrained('court_cases')->onDelete('cascade');

    // تاريخ الحكم وتاريخ الإغلاق
    $table->date('judgment_date')->nullable();
    $table->date('closure_date')->nullable();

    // نوع الحكم: ضد الأطراف، فاصل، إسقاط الحق الشخصي
    $table->enum('judgment_type', [
        'ضد الأطراف',
        'الحكم الفاصل',
        'إسقاط الحق الشخصي'
    ]);

    // المستخدم اللي أدخل الحكم
    $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('case_judgments');
    }
};
