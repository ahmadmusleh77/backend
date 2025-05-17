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
        Schema::create('bids', function (Blueprint $table) {
            $table->id('bids_id');
            $table->string('user_name');
            $table->integer('price_estimate');
            $table->string("timeline");
            $table->string("status")->default('Pending');
            $table->timestamps();
            $table->unsignedBigInteger('artisan_id');
            $table->unsignedBigInteger('job_id');
            $table->foreign('artisan_id')->references('user_id')->on('users')->onDelete('cascade') ->onUpdate('cascade');
            $table->foreign('job_id')->references('job_id')->on('jobposts')->onDelete('cascade') ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bids');
    }
};
