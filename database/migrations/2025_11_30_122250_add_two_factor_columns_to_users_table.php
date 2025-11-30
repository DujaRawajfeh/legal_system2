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

        
        Schema::table('users', function (Blueprint $table) {
            // المفتاح السري لـ TOTP
            $table->string('two_factor_secret')->nullable();

            // حالة التفعيل
            $table->boolean('two_factor_enabled')->default(false);

            // رموز احتياطية (بنخزنها كمصفوفة JSON)
            $table->json('two_factor_recovery_codes')->nullable();

            // (اختياري) وقت آخر تغيير كلمة السر لمنطق الثلاث شهور
            $table->timestamp('password_changed_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
