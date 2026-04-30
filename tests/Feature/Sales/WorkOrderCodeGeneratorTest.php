<?php

use App\Helpers\CMW\CodeGeneratorHelper;
use App\Models\CMW\Master\Currency;
use App\Models\CMW\Master\Partner;
use App\Models\CMW\Transaction\OrderHeader;
use App\Models\User;

function makeOrderHeaderForWoTest(?string $workOrderAuto = null): OrderHeader
{
    $currency = Currency::firstOrCreate(
        ['code' => 'IDR'],
        ['name' => 'Rupiah', 'symbol' => 'Rp', 'is_active' => true]
    );

    $partner = Partner::create([
        'code' => 'CUST-WO-'.uniqid(),
        'name' => 'WO Test Customer',
        'is_customer' => true,
        'is_supplier' => false,
        'is_active' => true,
    ]);

    $user = User::factory()->withoutTwoFactor()->create();

    return OrderHeader::create([
        'code_request' => 'SR/'.date('ym').'/'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT),
        'date' => now()->format('Y-m-d'),
        'currency_id' => $currency->id,
        'partner_id' => $partner->id,
        'tax_mode' => 'NONE',
        'tax_rate' => 0,
        'status' => 'ORDER',
        'work_order_auto' => $workOrderAuto,
        'subtotal' => 0,
        'discount' => 0,
        'tax' => 0,
        'total' => 0,
        'created_by' => $user->id,
    ]);
}

it('generates first work order code for the current month', function () {
    $code = CodeGeneratorHelper::generateWorkOrderCode();

    expect($code)->toMatch('/^WO\/\d{4}\/0001$/');
    expect($code)->toStartWith('WO/'.date('ym').'/');
});

it('increments work order sequence based on existing rows', function () {
    $yearMonth = date('ym');

    makeOrderHeaderForWoTest("WO/{$yearMonth}/0001");
    makeOrderHeaderForWoTest("WO/{$yearMonth}/0007");

    $next = CodeGeneratorHelper::generateWorkOrderCode();

    expect($next)->toBe("WO/{$yearMonth}/0008");
});

it('ignores work order rows from other months', function () {
    makeOrderHeaderForWoTest('WO/9901/0099');

    $next = CodeGeneratorHelper::generateWorkOrderCode();

    expect($next)->toBe('WO/'.date('ym').'/0001');
});
