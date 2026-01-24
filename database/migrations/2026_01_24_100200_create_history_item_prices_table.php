<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('history_item_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items');
            $table->foreignId('category_price_id')->constrained('category_prices');
            $table->decimal('old_price', 18, 5)->default(0);
            $table->decimal('new_price', 18, 5)->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();

            // Index for efficient querying by date range
            $table->index('created_at');
            $table->index(['item_id', 'category_price_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('history_item_prices');
    }
};
