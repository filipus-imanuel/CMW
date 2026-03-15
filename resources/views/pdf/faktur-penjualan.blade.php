<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 12mm 10mm 10mm 10mm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9pt;
            color: #111;
            margin: 0;
            padding: 0;
        }

        /* ── LUNAS watermark ─────────────────────── */
        .watermark {
            position: fixed;
            top: 35%;
            left: 15%;
            z-index: -1;
            opacity: 0.18;
            font-size: 72pt;
            font-weight: bold;
            color: #c00;
            transform: rotate(-30deg);
            letter-spacing: 12px;
        }

        /* ── Header ──────────────────────────────── */
        .header {
            text-align: center;
            margin-bottom: 4px;
        }
        .header .company-name {
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* ── Title ───────────────────────────────── */
        .title-block {
            text-align: center;
            margin-bottom: 6px;
        }
        .title-block .title {
            font-size: 12pt;
            font-weight: bold;
            text-decoration: underline;
            letter-spacing: 2px;
        }

        /* ── Info box ────────────────────────────── */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }
        .info-table td {
            padding: 1px 4px;
            vertical-align: top;
        }
        .info-left {
            width: 55%;
            text-align: left;
        }
        .info-right {
            width: 45%;
            text-align: left;
        }
        .info-right table td {
            padding: 1px 3px;
            font-size: 8.5pt;
        }
        .info-right table td.label {
            white-space: nowrap;
        }

        /* ── Items table ─────────────────────────── */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2px;
        }
        .items-table th,
        .items-table td {
            border: 1px solid #333;
            padding: 3px 5px;
            font-size: 8.5pt;
        }
        .items-table th {
            background: #eee;
            font-weight: bold;
            text-align: center;
        }
        .items-table td.num {
            text-align: right;
            font-family: 'Courier New', monospace;
            white-space: nowrap;
        }
        .items-table td.center {
            text-align: center;
        }

        /* ── Totals ──────────────────────────────── */
        .totals-row {
            width: 100%;
            border-collapse: collapse;
        }
        .totals-row td {
            padding: 2px 5px;
            font-size: 8.5pt;
        }
        .totals-row .label {
            text-align: right;
            font-weight: bold;
        }
        .totals-row .value {
            text-align: right;
            font-family: 'Courier New', monospace;
            white-space: nowrap;
        }

        /* ── Terbilang ───────────────────────────── */
        .terbilang {
            font-style: italic;
            font-size: 8pt;
            margin: 4px 0;
            padding: 2px 4px;
            border-top: 1px solid #333;
        }

        /* ── Bank info ───────────────────────────── */
        .bank-info {
            font-size: 8pt;
            margin: 4px 0;
            font-weight: bold;
        }

        /* ── Footer ──────────────────────────────── */
        .footer-notes {
            font-size: 7pt;
            margin-top: 4px;
            padding: 3px 4px;
            background: #f5f5f5;
            border: 1px solid #ccc;
            line-height: 1.4;
        }

        .signatures {
            width: 100%;
            margin-top: 12px;
        }
        .signatures td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            font-size: 8.5pt;
            padding-top: 4px;
        }
        .signatures .sign-space {
            height: 45px;
        }
    </style>
</head>
<body>

@if ($isLunas)
    <div class="watermark">LUNAS</div>
@endif

{{-- Header --}}
<div class="header">
    <div class="company-name">{{ $companyName }}</div>
</div>

{{-- Title --}}
<div class="title-block">
    <div class="title">FAKTUR PENJUALAN</div>
</div>

{{-- Info block --}}
<table class="info-table">
    <tr>
        <td class="info-left">
            <strong>Sales:</strong> {{ $salesPerson }}<br>
            <strong>Customer:</strong> {{ $customerName }}
        </td>
        <td class="info-right">
            <table>
                <tr>
                    <td class="label">No</td>
                    <td>:</td>
                    <td><strong>{{ $documentCode }}</strong></td>
                </tr>
                <tr>
                    <td class="label">Tgl</td>
                    <td>:</td>
                    <td>{{ $date }}</td>
                </tr>
                <tr>
                    <td class="label">No. Reff</td>
                    <td>:</td>
                    <td>{{ $reference }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

{{-- Items Table --}}
<table class="items-table">
    <thead>
        <tr>
            <th style="width: 20px;">No.</th>
            <th style="width: 60px;">KODE</th>
            <th>Nama Barang</th>
            <th style="width: 65px;">QTY</th>
            <th style="width: 70px;">H. SATUAN</th>
            <th style="width: 80px;">NILAI</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($items as $i => $item)
            <tr>
                <td class="center">{{ $i + 1 }}</td>
                <td>{{ $item['code'] }}</td>
                <td>{{ $item['name'] }}</td>
                <td class="num">{{ number_format((float) $item['quantity'], 2, '.', ',') }} {{ $item['uom'] }}</td>
                <td class="num">{{ number_format((float) $item['price'], 2, '.', ',') }}</td>
                <td class="num">{{ number_format((float) $item['total'], 2, '.', ',') }}</td>
            </tr>
        @endforeach

        {{-- Empty rows to fill space --}}
        @for ($j = count($items); $j < 8; $j++)
            <tr>
                <td>&nbsp;</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        @endfor
    </tbody>
</table>

{{-- Terbilang --}}
<div class="terbilang">
    Terbilang: &nbsp; <em>{{ $terbilang }}</em>
</div>

{{-- Totals --}}
<table class="totals-row" style="margin-bottom: 2px;">
    @if ((float) $discount > 0)
        <tr>
            <td class="label" style="width: 75%;">Subtotal</td>
            <td class="value">{{ number_format((float) $subtotal, 2, '.', ',') }}</td>
        </tr>
        <tr>
            <td class="label">Discount</td>
            <td class="value">{{ number_format((float) $discount, 2, '.', ',') }}</td>
        </tr>
    @endif
    @if ((float) $tax > 0)
        <tr>
            <td class="label" style="width: 75%;">Tax</td>
            <td class="value">{{ number_format((float) $tax, 2, '.', ',') }}</td>
        </tr>
    @endif
    <tr>
        <td class="label" style="font-size: 10pt; width: 75%;">Grand Total</td>
        <td class="value" style="font-size: 10pt; border-top: 1px solid #333; border-bottom: 2px double #333;">
            {{ number_format((float) $grandTotal, 2, '.', ',') }}
        </td>
    </tr>
</table>

{{-- Bank info --}}
@if ($bankName || $bankAccountNumber)
    <div class="bank-info">
        Rekening: {{ $bankName }} {{ $bankAccountName }} {{ $bankAccountNumber }}
    </div>
@endif

{{-- Footer notes --}}
<div class="footer-notes">
    Pembayaran dengan Cheque/Giro dianggap sah bila sudah cair.<br>
    Barang yang sudah dibeli tidak dapat ditukar/dikembalikan.
</div>

{{-- Signatures --}}
<table class="signatures">
    <tr>
        <td>Penerima</td>
        <td>Hormat Kami</td>
    </tr>
    <tr>
        <td class="sign-space"></td>
        <td class="sign-space"></td>
    </tr>
    <tr>
        <td>( .............................. )</td>
        <td>( .............................. )</td>
    </tr>
</table>

</body>
</html>
