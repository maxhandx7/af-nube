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
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique(); 
            $table->string('original_name');
            $table->string('path'); 
            $table->string('mime')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->string('delete_token', 64)->nullable(); 
            $table->timestamp('expires_at')->nullable();
            $table->unsignedInteger('downloads_count')->default(0);
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('file_password')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
