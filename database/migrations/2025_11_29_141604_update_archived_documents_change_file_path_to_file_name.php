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
         Schema::table('archived_documents', function (Blueprint $table) {
            // حذف العمود القديم
            $table->dropColumn('file_path');

            // إضافة العمود الجديد لتخزين اسم الملف فقط
            $table->string('file_name')->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('file_name', function (Blueprint $table) {
            //
        });
    }
};
