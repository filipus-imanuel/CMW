<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_header_id')->constrained('order_headers');
            $table->foreignId('item_id')->constrained('items');
            $table->foreignId('item_uom_id')->nullable()->constrained('item_uoms');
            $table->foreignId('company_setting_id')->nullable()->constrained('company_settings');
            $table->unsignedBigInteger('return_detail_id')->nullable();
            $table->decimal('quantity', 13, 2)->default(0);
            $table->decimal('price_proposed', 13, 2)->default(0);
            $table->decimal('price_deal', 13, 2)->default(0);
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
        Schema::dropIfExists('order_details');
    }
};
