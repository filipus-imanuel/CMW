<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_adjustment_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_adjustment_header_id')->constrained('stock_adjustment_headers');
            $table->foreignId('item_id')->constrained('items');
            $table->foreignId('uom_id')->constrained('uoms');
            $table->foreignId('warehouse_id')->constrained('warehouses');
            $table->decimal('quantity_system', 18, 4)->default(0);
            $table->decimal('quantity_actual', 18, 4)->default(0);
            $table->decimal('quantity_difference', 18, 4)->default(0);
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
        Schema::dropIfExists('stock_adjustment_details');
    }
};
