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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->after('password')->constrained('departments')->nullOnDelete();
            $table->string('phone', 20)->nullable()->after('department_id');
            $table->string('photo_path', 255)->nullable()->after('phone');
            $table->boolean('is_active')->default(true)->after('photo_path');
            $table->foreignId('deleted_by')->nullable()->after('is_active')->constrained('users')->nullOnDelete();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropForeign(['deleted_by']);
            $table->dropColumn(['department_id', 'phone', 'photo_path', 'is_active', 'deleted_by', 'deleted_at']);
        });
    }
};
