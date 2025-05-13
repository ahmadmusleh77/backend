<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jobposts', function (Blueprint $table) {
            $table->id('job_id');
            $table->string('title', 200);
            $table->text('description');
            $table->decimal('budget', 10, 2);
            $table->string('location', 100);
            $table->date('deadline');
            $table->string('image')->nullable();
            $table->string('status', 20)->default('Open');
            $table->timestamps();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jobposts');
    }
};
