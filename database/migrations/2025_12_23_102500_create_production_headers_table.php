<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_headers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 45)->unique();
            $table->date('date');
            $table->foreignId('item_id')->constrained('items'); // Finished product
            $table->foreignId('bom_header_id')->nullable()->constrained('bom_headers');
            $table->foreignId('warehouse_id')->constrained('warehouses');
            $table->decimal('quantity', 18, 4)->default(0);
            $table->foreignId('uom_id')->constrained('uoms');
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
        Schema::dropIfExists('production_headers');
    }
};
