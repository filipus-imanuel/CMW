<?php

namespace App\Http\Controllers;

use App\Helpers\CMW\NumberToWordsHelper;
use App\Models\CMW\Transaction\DeliveryHeader;
use App\Models\CMW\Transaction\OrderHeader;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class PdfController extends Controller
{
    /**
     * Generate Faktur Penjualan PDF for a Sales Order.
     */
    public function salesOrder(int $id): Response
    {
        Gate::authorize('view sales order');

        $order = OrderHeader::with([
            'partner', 'company', 'currency',
            'details.item', 'details.itemUom.uom',
            'createdBy', 'arInvoices',
        ])->findOrFail($id);

        // Only orders that have been approved (have code_order) can generate PDF
        if (! $order->code_order) {
            abort(403, 'PDF only available for approved orders.');
        }

        $isLunas = $order->arInvoices->isNotEmpty()
            && $order->arInvoices->every(fn ($inv) => $inv->status === 'paid');

        $items = $order->details->map(fn ($d) => [
            'code' => $d->item?->code,
            'name' => $d->item?->name,
            'quantity' => $d->quantity,
            'uom' => $d->itemUom?->uom?->name ?? '',
            'price' => $d->price_deal ?: $d->price_proposed,
            'total' => $d->total,
        ]);

        $data = [
            'companyName' => $order->company?->name ?? '-',
            'bankName' => $order->company?->bank_name,
            'bankAccountName' => $order->company?->bank_account_name,
            'bankAccountNumber' => $order->company?->bank_account_number,
            'documentCode' => $order->code_order,
            'date' => $order->date?->format('d/m/Y'),
            'reference' => $order->code_request,
            'salesPerson' => $order->createdBy?->name ?? '-',
            'customerName' => $order->partner?->name ?? '-',
            'items' => $items,
            'subtotal' => $order->subtotal,
            'discount' => $order->discount,
            'tax' => $order->tax,
            'grandTotal' => $order->total,
            'terbilang' => NumberToWordsHelper::convert((float) $order->total, $order->currency?->code ?? 'Rupiah'),
            'isLunas' => $isLunas,
        ];

        $pdf = Pdf::loadView('pdf.faktur-penjualan', $data);
        $pdf->setPaper('a5', 'portrait');

        $filename = 'Faktur-'.$order->code_order.'.pdf';
        $filename = str_replace('/', '-', $filename);

        return $pdf->download($filename);
    }

    /**
     * Generate Faktur Penjualan PDF for a Delivery Order.
     */
    public function deliveryOrder(int $id): Response
    {
        Gate::authorize('view delivery order');

        $header = DeliveryHeader::with([
            'orderHeader.company', 'partner', 'currency',
            'details.item', 'details.itemUom.uom',
            'createdBy', 'arInvoices',
        ])->findOrFail($id);

        // Only ongoing or finished DOs
        if (! in_array($header->status, ['ongoing', 'finished'])) {
            abort(403, 'PDF only available for ongoing or finished deliveries.');
        }

        $company = $header->orderHeader?->company;

        $isLunas = $header->arInvoices->isNotEmpty()
            && $header->arInvoices->every(fn ($inv) => $inv->status === 'paid');

        $items = $header->details->map(fn ($d) => [
            'code' => $d->item?->code,
            'name' => $d->item?->name,
            'quantity' => $d->quantity_sent,
            'uom' => $d->itemUom?->uom?->name ?? '',
            'price' => $d->price,
            'total' => $d->total,
        ]);

        $data = [
            'companyName' => $company?->name ?? '-',
            'bankName' => $company?->bank_name,
            'bankAccountName' => $company?->bank_account_name,
            'bankAccountNumber' => $company?->bank_account_number,
            'documentCode' => $header->code,
            'date' => $header->date?->format('d/m/Y'),
            'reference' => $header->orderHeader?->code_order ?? $header->orderHeader?->code_request ?? '-',
            'salesPerson' => $header->createdBy?->name ?? '-',
            'customerName' => $header->partner?->name ?? '-',
            'items' => $items,
            'subtotal' => $header->subtotal,
            'discount' => 0,
            'tax' => $header->tax,
            'grandTotal' => $header->total,
            'terbilang' => NumberToWordsHelper::convert((float) $header->total, $header->currency?->code ?? 'Rupiah'),
            'isLunas' => $isLunas,
        ];

        $pdf = Pdf::loadView('pdf.faktur-penjualan', $data);
        $pdf->setPaper('a5', 'portrait');

        $filename = 'Faktur-'.$header->code.'.pdf';
        $filename = str_replace('/', '-', $filename);

        return $pdf->download($filename);
    }
}
