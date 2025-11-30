<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::table('case_judgments', function (Blueprint $table) {
        $table->string('judgment_mode')->nullable()->after('charge_decision');
        $table->string('termination_type')->nullable()->after('judgment_mode');
        $table->text('execution_details')->nullable()->after('termination_type');
        $table->text('judgment_summary')->nullable()->after('execution_details');
        $table->text('charge_text')->nullable()->after('judgment_summary');
        $table->string('charge_split_type')->nullable()->after('charge_text');
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
