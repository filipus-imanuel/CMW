<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transfer_headers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 45)->unique();
            $table->date('date');
            $table->foreignId('warehouse_from_id')->constrained('warehouses');
            $table->foreignId('warehouse_to_id')->constrained('warehouses');
            $table->string('status', 20)->default('draft');
            $table->text('remarks')->nullable();
            $table->boolean('is_edit_locked')->default(false);
            $table->boolean('is_delete_locked')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('version_number')->default(1);
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->foreignId('deleted_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transfer_headers');
    }
};
