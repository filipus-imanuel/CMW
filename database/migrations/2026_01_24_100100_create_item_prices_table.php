<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_uom_id')->constrained('item_uoms');
            $table->foreignId('category_price_id')->constrained('category_prices');
            $table->decimal('price', 13, 2)->default(0);
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

            // Composite unique constraint to prevent duplicate item_uom+category combinations
            $table->unique(['item_uom_id', 'category_price_id'], 'item_prices_uom_category_unique');
        });

        // Add foreign key constraint to partners.category_price_id
        Schema::table('partners', function (Blueprint $table) {
            $table->foreign('category_price_id')
                ->references('id')
                ->on('category_prices')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->dropForeign(['category_price_id']);
        });

        Schema::dropIfExists('item_prices');
    }
};
