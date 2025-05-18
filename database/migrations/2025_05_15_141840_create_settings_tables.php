<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id('setting_id');
            $table->unsignedBigInteger('user_id')->unique();

            $table->string('name');
            $table->string('country')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->date('birthday')->nullable();
            $table->enum('gender', ['Male', 'Female', 'Other'])->nullable();
            $table->text('about')->nullable();
            $table->string('languages')->nullable();

            $table->string('email')->unique();
            $table->string('password');
            $table->enum('visibility', ['Public', 'Private', 'Only Me'])->default('Public');

            $table->string('language')->default('English');
            $table->enum('theme', ['Light', 'Dark'])->default('Light');

            $table->json('skills')->nullable();
            $table->text('experience')->nullable();
            $table->text('education')->nullable();

            $table->timestamps();

            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
