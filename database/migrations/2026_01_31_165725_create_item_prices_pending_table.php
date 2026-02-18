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
        Schema::create('item_prices_pending', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_price_id')->constrained('item_prices');
            $table->foreignId('item_uom_id')->constrained('item_uoms');
            $table->foreignId('category_price_id')->constrained('category_prices');
            $table->decimal('old_price', 13, 2)->default(0);
            $table->decimal('new_price', 13, 2)->default(0);
            $table->decimal('change_percentage', 5, 2)->default(0);
            $table->string('status', 20)->default('pending'); // pending, approved, rejected
            $table->foreignId('submitted_by')->constrained('users');
            $table->timestamp('submitted_at');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('approval_notes')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->foreignId('deleted_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['item_price_id', 'status']);
            $table->index(['status', 'submitted_at']);
            $table->index(['item_price_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_prices_pending');
    }
};
