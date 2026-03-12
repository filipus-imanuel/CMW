<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('return_headers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('transaction_type', 5)->default('SO')->comment('SO (sales), PO (purchasing future)');
            $table->string('return_type', 20)->comment('ITEM, INVOICE_RETURN, INVOICE_DISCARD');
            $table->date('date');
            $table->foreignId('order_header_id')->constrained('order_headers');
            $table->foreignId('delivery_header_id')->constrained('delivery_headers');
            $table->foreignId('ar_invoice_header_id')->nullable()->constrained('ar_invoice_headers');
            $table->foreignId('partner_id')->constrained('partners');
            $table->foreignId('company_id')->nullable()->constrained('companies');
            $table->foreignId('currency_id')->default(1)->constrained('currencies');
            $table->string('status', 20)->default('INIT')->comment('INIT, APPROVAL, PROCESSING, FINISH, CANCELLED, REJECTED');
            $table->string('rejection_reason', 1024)->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users');
            $table->timestamp('received_at')->nullable();
            $table->decimal('subtotal', 13, 2)->default(0);
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
        Schema::dropIfExists('return_headers');
    }
};
