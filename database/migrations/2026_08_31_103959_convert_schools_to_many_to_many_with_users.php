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
        Schema::create('school_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role')->default('member');
            $table->timestamps();

            $table->unique(['school_id', 'user_id']);
        });

        DB::table('schools')->whereNotNull('user_id')->orderBy('id')->each(function (object $school) {
            DB::table('school_user')->insert([
                'school_id' => $school->id,
                'user_id' => $school->user_id,
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        Schema::table('schools', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
        });

        DB::table('school_user')->where('role', 'admin')->orderBy('id')->each(function (object $pivot) {
            DB::table('schools')->where('id', $pivot->school_id)->update([
                'user_id' => $pivot->user_id,
            ]);
        });

        Schema::table('schools', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable(false)->change();
        });

        Schema::dropIfExists('school_user');
    }
};
