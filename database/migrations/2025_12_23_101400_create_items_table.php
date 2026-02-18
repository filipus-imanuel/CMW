<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 100);
            $table->string('type', 50)->nullable(); // raw_material, finished_goods, etc.
            $table->foreignId('item_category_id')->nullable()->constrained('item_categories');
            $table->foreignId('currency_id')->nullable()->constrained('currencies');
            $table->decimal('cost_price', 13, 2)->default(0);
            $table->decimal('sell_price', 13, 2)->default(0);
            $table->decimal('min_stock', 13, 2)->default(0);
            $table->decimal('max_stock', 13, 2)->default(0);
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
        Schema::dropIfExists('items');
    }
};
