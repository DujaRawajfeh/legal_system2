<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('case_judgments', function (Blueprint $table) {
            $table->text('personal_drop_text')->nullable()->after('judgment_summary');
        });
    }

    public function down(): void
    {
        Schema::table('case_judgments', function (Blueprint $table) {
            $table->dropColumn('personal_drop_text');
        });
    }
};