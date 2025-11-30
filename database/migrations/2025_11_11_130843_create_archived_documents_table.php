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
        // 📌 تأكّد أولاً إذا الجدول غير موجود قبل الإنشاء
        if (!Schema::hasTable('archived_documents')) {
            Schema::create('archived_documents', function (Blueprint $table) {
                $table->id();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 📌 حذف الجدول فقط إذا كان موجود
        if (Schema::hasTable('archived_documents')) {
            Schema::dropIfExists('archived_documents');
        }
    }
};