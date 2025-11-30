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
        $table->text('judgment_text_against_parties')->nullable()->after('third_party');
        $table->text('judgment_text_final')->nullable()->after('judgment_text_against_parties');
        $table->text('judgment_text_waiver')->nullable()->after('judgment_text_final');
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
