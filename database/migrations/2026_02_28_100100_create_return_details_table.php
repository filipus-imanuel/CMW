<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('return_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('return_header_id')->constrained('return_headers');
            $table->foreignId('delivery_detail_id')->constrained('delivery_details');
            $table->foreignId('item_id')->constrained('items');
            $table->foreignId('item_uom_id')->nullable()->constrained('item_uoms');
            $table->decimal('quantity_return', 13, 2)->default(0);
            $table->decimal('quantity_received_good', 13, 2)->default(0);
            $table->decimal('quantity_received_damaged', 13, 2)->default(0);
            $table->decimal('quantity_redelivery', 13, 2)->default(0);
            $table->decimal('quantity_next_so', 13, 2)->default(0);
            $table->boolean('is_next_so_consumed')->default(false);
            $table->foreignId('consumed_by_order_id')->nullable()->constrained('order_headers');
            $table->decimal('price', 13, 2)->default(0);
            $table->decimal('discount', 13, 2)->default(0);
            $table->decimal('tax', 13, 2)->default(0);
            $table->decimal('total', 13, 2)->default(0);
            $table->string('remarks', 1024)->nullable();
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
        Schema::dropIfExists('return_details');
    }
};
