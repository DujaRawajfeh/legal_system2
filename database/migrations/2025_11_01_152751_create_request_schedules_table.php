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
        Schema::create('request_schedules', function (Blueprint $table) {
    $table->id();

    $table->string('request_number')->nullable();
    $table->string('court_number')->nullable();
    $table->string('court_desk')->nullable();
    $table->string('court_year')->nullable();

    $table->date('session_date')->nullable();
    $table->time('session_time')->nullable();
    $table->string('session_type')->nullable();
    $table->string('session_purpose')->nullable();
    $table->string('session_status')->nullable();
    $table->text('session_reason')->nullable();
    $table->date('original_date')->nullable();
    $table->string('judge_name')->nullable();

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_schedules');
    }
};
