<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_headers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->date('date');
            $table->foreignId('order_header_id')->constrained('order_headers');
            $table->foreignId('partner_id')->constrained('partners');
            $table->foreignId('company_id')->nullable()->constrained('companies');
            $table->foreignId('currency_id')->default(1)->constrained('currencies');
            $table->string('status', 20)->default('ongoing')->comment('ongoing, finished, cancelled');
            $table->string('cancel_reason', 1024)->nullable();
            $table->foreignId('confirmed_by')->nullable()->constrained('users');
            $table->timestamp('confirmed_at')->nullable();
            $table->decimal('subtotal', 13, 2)->default(0);
            $table->decimal('tax', 13, 2)->default(0);
            $table->decimal('total', 13, 2)->default(0);
            $table->string('remarks', 1024)->nullable();
            $table->string('delivery_address', 1024)->nullable();
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

        // Add FK to ar_invoice_headers (created before delivery_headers)
        Schema::table('ar_invoice_headers', function (Blueprint $table) {
            $table->foreign('delivery_header_id')->references('id')->on('delivery_headers');
        });
    }

    public function down(): void
    {
        Schema::table('ar_invoice_headers', function (Blueprint $table) {
            $table->dropForeign(['delivery_header_id']);
        });
        Schema::dropIfExists('delivery_headers');
    }
};
