<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gl_journal_headers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->date('date');
            $table->foreignId('currency_id')->default(1)->constrained('currencies');
            $table->string('description', 255)->nullable();
            $table->decimal('total_debit', 13, 2)->default(0);
            $table->decimal('total_credit', 13, 2)->default(0);
            $table->string('status', 20)->default('draft'); // draft, posted, void
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
        Schema::dropIfExists('gl_journal_headers');
    }
};
