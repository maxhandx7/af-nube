<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('files', function (Blueprint $table) {
            // Tipo de entrada: 'file' | 'note' | 'link'
            $table->enum('type', ['file', 'note', 'link'])->default('file')->after('id');

            // Contenido del bloc de notas o la URL del link
            $table->longText('content')->nullable()->after('user_agent');

            // Slug personalizado — ya existe 'slug', solo añadimos un flag
            $table->boolean('custom_slug')->default(false)->after('slug');

            // Título opcional para notas y links
            $table->string('title')->nullable()->after('original_name');
        });
    }

    public function down(): void
    {
        Schema::table('files', function (Blueprint $table) {
            $table->dropColumn(['type', 'content', 'custom_slug', 'title']);
        });
    }
};