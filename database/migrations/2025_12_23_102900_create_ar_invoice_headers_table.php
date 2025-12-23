<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ar_invoice_headers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 45)->unique();
            $table->date('date');
            $table->date('due_date')->nullable();
            $table->foreignId('partner_id')->constrained('partners');
            $table->foreignId('sales_order_header_id')->nullable()->constrained('sales_order_headers');
            $table->decimal('subtotal', 18, 2)->default(0);
            $table->decimal('tax', 18, 2)->default(0);
            $table->decimal('total', 18, 2)->default(0);
            $table->decimal('paid', 18, 2)->default(0);
            $table->decimal('balance', 18, 2)->default(0);
            $table->string('status', 20)->default('unpaid');
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
        Schema::dropIfExists('ar_invoice_headers');
    }
};
