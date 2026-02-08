<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_headers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->date('date');
            $table->foreignId('currency_id')->default(1)->constrained('currencies');
            $table->foreignId('partner_id')->constrained('partners');
            $table->foreignId('company_id')->nullable()->constrained('companies');
            $table->foreignId('item_category_id')->nullable()->constrained('item_categories');
            $table->string('status', 20)->default('INIT'); // INIT, APPROVAL, REQUEST, ORDER, DELIVERY, FINISH, FINAL
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->string('rejection_reason', 1024)->nullable();
            $table->decimal('subtotal', 13, 2)->default(0);
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
        Schema::dropIfExists('order_headers');
    }
};
