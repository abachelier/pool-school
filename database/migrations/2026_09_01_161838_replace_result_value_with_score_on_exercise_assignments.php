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
        Schema::table('exercise_assignments', function (Blueprint $table) {
            $table->dropColumn('result_value');
            $table->unsignedSmallInteger('score')->nullable()->after('exercise_id');
            $table->unsignedSmallInteger('max_score')->nullable()->after('score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exercise_assignments', function (Blueprint $table) {
            $table->dropColumn(['score', 'max_score']);
            $table->string('result_value')->nullable()->after('exercise_id');
        });
    }
};
