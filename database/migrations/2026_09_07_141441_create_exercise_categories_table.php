<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('exercise_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->timestamps();
        });

        $categories = [
            ['name' => 'Basic Potting', 'slug' => 'basic-potting', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Potting Down the Rail', 'slug' => 'potting-down-the-rail', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Back Spin', 'slug' => 'back-spin', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Top Spin', 'slug' => 'top-spin', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Stop Shot', 'slug' => 'stop-shot', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Break', 'slug' => 'break', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Pattern Play', 'slug' => 'pattern-play', 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('exercise_categories')->insert($categories);

        $slugMap = [
            'basic_potting' => 'basic-potting',
            'potting_down_the_rail' => 'potting-down-the-rail',
            'back_spin' => 'back-spin',
            'top_spin' => 'top-spin',
            'stop_shot' => 'stop-shot',
            'break' => 'break',
            'pattern_play' => 'pattern-play',
        ];

        Schema::table('exercises', function (Blueprint $table) {
            $table->foreignId('exercise_category_id')->nullable()->after('id')->constrained('exercise_categories');
        });

        foreach ($slugMap as $enumValue => $slug) {
            $categoryId = DB::table('exercise_categories')->where('slug', $slug)->value('id');

            if ($categoryId) {
                DB::table('exercises')->where('category', $enumValue)->update(['exercise_category_id' => $categoryId]);
            }
        }

        Schema::table('exercises', function (Blueprint $table) {
            $table->dropColumn('category');
            $table->foreignId('exercise_category_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $slugMap = [
            'basic-potting' => 'basic_potting',
            'potting-down-the-rail' => 'potting_down_the_rail',
            'back-spin' => 'back_spin',
            'top-spin' => 'top_spin',
            'stop-shot' => 'stop_shot',
            'break' => 'break',
            'pattern-play' => 'pattern_play',
        ];

        Schema::table('exercises', function (Blueprint $table) {
            $table->string('category')->nullable()->after('id');
        });

        foreach ($slugMap as $slug => $enumValue) {
            $categoryId = DB::table('exercise_categories')->where('slug', $slug)->value('id');

            if ($categoryId) {
                DB::table('exercises')->where('exercise_category_id', $categoryId)->update(['category' => $enumValue]);
            }
        }

        Schema::table('exercises', function (Blueprint $table) {
            $table->dropForeign(['exercise_category_id']);
            $table->dropColumn('exercise_category_id');
            $table->string('category')->nullable(false)->change();
        });

        Schema::dropIfExists('exercise_categories');
    }
};
