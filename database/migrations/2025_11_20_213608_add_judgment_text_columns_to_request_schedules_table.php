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
            $table->text('judgment_text_plaintiff')->nullable()->after('title');
            $table->text('judgment_text_defendant')->nullable()->after('judgment_text_plaintiff');
            $table->text('judgment_text_third_party')->nullable()->after('judgment_text_defendant');
            $table->text('judgment_text_lawyer')->nullable()->after('judgment_text_third_party');
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
