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
        Schema::create('judge_user', function (Blueprint $table) {
        $table->id();

        // المستخدم (كاتب أو طابعة)
        $table->unsignedBigInteger('user_id');

        // القاضي
        $table->unsignedBigInteger('judge_id');

        // لضمان عدم التكرار
        $table->unique(['user_id', 'judge_id']);

        // علاقات
        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        $table->foreign('judge_id')->references('id')->on('users')->onDelete('cascade');

        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('judge_user');
    }
};
