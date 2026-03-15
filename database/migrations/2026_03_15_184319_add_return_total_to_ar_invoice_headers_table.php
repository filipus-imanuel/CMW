<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ar_invoice_headers', function (Blueprint $table) {
            $table->decimal('return_total', 15, 2)->default(0)->after('paid');
        });

        // Backfill return_total from existing finished returns
        DB::statement('
            UPDATE ar_invoice_headers inv
            JOIN (
                SELECT ar_invoice_header_id, SUM(total) AS return_sum
                FROM return_headers
                WHERE status = "FINISH"
                  AND return_type IN ("ITEM_INVOICE", "INVOICE_RETURN", "INVOICE_DISCARD")
                  AND deleted_at IS NULL
                GROUP BY ar_invoice_header_id
            ) ret ON ret.ar_invoice_header_id = inv.id
            SET inv.return_total = ret.return_sum,
                inv.balance = GREATEST(0, inv.total - inv.paid - ret.return_sum),
                inv.status = CASE
                    WHEN GREATEST(0, inv.total - inv.paid - ret.return_sum) <= 0 THEN "paid"
                    WHEN inv.paid + ret.return_sum > 0 THEN "partial"
                    ELSE inv.status
                END
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ar_invoice_headers', function (Blueprint $table) {
            $table->dropColumn('return_total');
        });
    }
};
