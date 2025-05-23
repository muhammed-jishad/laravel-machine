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
   Schema::create('tasks', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->text('description')->nullable();
    $table->unsignedBigInteger('project_id');
    $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
    $table->unsignedBigInteger('assigned_user_id')->nullable();
    $table->string('status')->default('pending');
    $table->timestamps();
    $table->foreign('assigned_user_id')->references('id')->on('users')->onDelete('set null');
});


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
