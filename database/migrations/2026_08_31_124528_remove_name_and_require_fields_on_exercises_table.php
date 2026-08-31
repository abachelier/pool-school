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
            $table->dropColumn('name');
            $table->string('category')->nullable(false)->change();
            $table->string('image_path')->nullable(false)->change();
            $table->unsignedTinyInteger('difficulty')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exercises', function (Blueprint $table) {
            $table->string('name')->after('id');
            $table->string('category')->nullable()->change();
            $table->string('image_path')->nullable()->change();
            $table->unsignedTinyInteger('difficulty')->nullable()->change();
        });
    }
};
