<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ar_payment_headers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 45)->unique();
            $table->date('date');
            $table->foreignId('partner_id')->constrained('partners');
            $table->decimal('amount', 18, 2)->default(0);
            $table->string('payment_method', 50)->nullable();
            $table->string('reference', 100)->nullable();
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
        Schema::dropIfExists('ar_payment_headers');
    }
};
