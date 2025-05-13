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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id('review_id');
            $table->integer('rating');
            $table->timestamp('created_at')->useCurrent();
            $table->unsignedBigInteger('reviewer_id');
            $table->unsignedBigInteger('reviewee_id');
            $table->unsignedBigInteger('job_id');


            $table->foreign('reviewer_id')->references('user_id')->on('users')->onDelete('cascade') ->onUpdate('cascade');
            $table->foreign('reviewee_id')->references('user_id')->on('users')->onDelete('cascade') ->onUpdate('cascade');
            $table->foreign('job_id')->references('job_id')->on('jobposts')->onDelete('cascade') ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
