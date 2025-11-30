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

            // ✅ بيانات المشتكي
            $table->string('plaintiff_name')->nullable();
            $table->string('plaintiff_national_id')->nullable();
            $table->string('plaintiff_residence')->nullable();
            $table->string('plaintiff_job')->nullable();
            $table->string('plaintiff_phone')->nullable();

            // ✅ بيانات المشتكى عليه
            $table->string('defendant_name')->nullable();
            $table->string('defendant_national_id')->nullable();
            $table->string('defendant_residence')->nullable();
            $table->string('defendant_job')->nullable();
            $table->string('defendant_phone')->nullable();

            // ✅ بيانات الشاهد (third_party)
            $table->string('third_party_name')->nullable();
            $table->string('third_party_national_id')->nullable();
            $table->string('third_party_residence')->nullable();
            $table->string('third_party_job')->nullable();
            $table->string('third_party_phone')->nullable();

            // ✅ بيانات المحامي
            $table->string('lawyer_name')->nullable();
            $table->string('lawyer_national_id')->nullable();
            $table->string('lawyer_residence')->nullable();
            $table->string('lawyer_job')->nullable();
            $table->string('lawyer_phone')->nullable();
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
