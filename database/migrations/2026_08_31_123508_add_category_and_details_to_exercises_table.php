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
        Schema::table('exercises', function (Blueprint $table) {
            $table->string('category')->nullable()->after('name');
            $table->string('image_path')->nullable()->after('description');
            $table->unsignedTinyInteger('difficulty')->nullable()->after('image_path');
            $table->text('notes')->nullable()->after('difficulty');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exercises', function (Blueprint $table) {
            $table->dropColumn(['category', 'image_path', 'difficulty', 'notes']);
        });
    }
};
