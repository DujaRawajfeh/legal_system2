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
        $table->string('plaintiff')->nullable();
        $table->string('defendant')->nullable();
        $table->string('third_party')->nullable();
        $table->string('title')->nullable();
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
