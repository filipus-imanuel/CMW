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
        .title-block .doc-code {
            font-size: 10pt;
            font-weight: bold;
            margin-top: 2px;
        }

        /* ── Info box ────────────────────────────── */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }
        .info-table td {
            padding: 1px 4px;
            vertical-align: top;
            font-size: 8.5pt;
        }
        .info-left {
            width: 55%;
        }
        .info-right {
            width: 45%;
            text-align: right;
        }
        .info-left table td,
        .info-right table td {
            padding: 1px 3px;
            font-size: 8.5pt;
        }
        .info-left table td.label,
        .info-right table td.label {
            white-space: nowrap;
            font-weight: bold;
        }
        .info-right table td.address {
            font-size: 7.5pt;
            max-width: 140px;
            word-wrap: break-word;
        }

        /* ── Items table ─────────────────────────── */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
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

        /* ── Satpam box ──────────────────────────── */
        .satpam-box {
            border: 1px solid #333;
            padding: 4px 6px;
            font-size: 7.5pt;
            width: 45%;
            float: right;
            margin-top: 4px;
        }
        .satpam-box .satpam-title {
            font-weight: bold;
            text-align: center;
            margin-bottom: 3px;
            font-size: 8pt;
        }
        .satpam-box table {
            width: 100%;
        }
        .satpam-box table td {
            padding: 1px 2px;
            font-size: 7.5pt;
        }
        .satpam-box table td.dots {
            border-bottom: 1px dotted #999;
            width: 50%;
        }

        /* ── Footer ──────────────────────────────── */
        .footer-notes {
            font-size: 7pt;
            margin-top: 6px;
            padding: 3px 4px;
            background: #f5f5f5;
            border: 1px solid #ccc;
            line-height: 1.4;
            clear: both;
        }

        .signatures {
            width: 100%;
            margin-top: 10px;
            clear: both;
        }
        .signatures td {
            width: 25%;
            text-align: center;
            vertical-align: top;
            font-size: 8pt;
            padding-top: 4px;
        }
        .signatures .sign-space {
            height: 40px;
        }
        .signatures .role {
            font-size: 7pt;
            color: #555;
        }
    </style>
</head>
<body>

{{-- Header --}}
<div class="header">
    <div class="company-name">{{ $companyName }}</div>
    @if($companyAddress)
        <div style="font-size: 8pt;">{{ $companyAddress }}</div>
    @endif
</div>

{{-- Title --}}
<div class="title-block">
    <div class="title">SURAT JALAN / FAKTUR</div>
    <div class="doc-code">NO SJ: {{ $documentCode }}</div>
</div>

{{-- Info block --}}
<table class="info-table">
    <tr>
        <td class="label" style="width: 95px;">NO. ODR</td>
        <td style="width: 8px;">:</td>
        <td style="width: 120px;">{{ $orderCode }}</td>
        <td style="width: 20px;">&nbsp;</td>
        <td class="label" style="width: 75px;">TANGGAL</td>
        <td style="width: 8px;">:</td>
        <td>{{ $date }}</td>
    </tr>
    <tr>
        <td class="label">NO. KENDARAAN</td>
        <td>:</td>
        <td>{{ $vehicleNumber ?: '-' }}</td>
        <td>&nbsp;</td>
        <td class="label">KEPADA Yth</td>
        <td>:</td>
        <td>{{ $customerName }}</td>
    </tr>
    @if($deliveryAddress)
        <tr>
            <td colspan="4"></td>
            <td></td>
            <td></td>
            <td style="font-size: 7.5pt;">{{ $deliveryAddress }}</td>
        </tr>
    @endif
</table>

{{-- Notice bar --}}
<div style="text-align: center; font-size: 7.5pt; border: 1px solid #333; padding: 2px; margin-bottom: 4px;">
    BARANG HARAP DICEK SESUAI NOTA TIBA / SELESAI <br>.............. S/D ..............
</div>

{{-- Items Table --}}
<table class="items-table">
    <thead>
        <tr>
            <th style="width: 80px;">Banyaknya</th>
            <th>Nama Barang</th>
            <th style="width: 80px;">SO</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($items as $i => $item)
            <tr>
                <td class="num">{{ number_format((float) $item['quantity'], 2, '.', ',') }} {{ $item['uom'] }}</td>
                <td>{{ $item['code'] }} - {{ $item['name'] }}</td>
                <td class="center">{{ $orderCode }}</td>
            </tr>
        @endforeach

        {{-- Empty rows to fill space --}}
        @for ($j = count($items); $j < 6; $j++)
            <tr>
                <td>&nbsp;</td>
                <td></td>
                <td></td>
            </tr>
        @endfor
    </tbody>
</table>

{{-- Satpam box --}}
<div class="satpam-box">
    <div class="satpam-title">MENGETAHUI SATPAM</div>
    <table>
        <tr>
            <td>MASUK JAM</td>
            <td>:</td>
            <td class="dots">&nbsp;</td>
        </tr>
        <tr>
            <td>KELUAR JAM</td>
            <td>:</td>
            <td class="dots">&nbsp;</td>
        </tr>
        <tr>
            <td>TANGGAL</td>
            <td>:</td>
            <td class="dots">&nbsp;</td>
        </tr>
        <tr>
            <td>TANDA TANGAN</td>
            <td>:</td>
            <td class="dots">&nbsp;</td>
        </tr>
    </table>
</div>

<div style="clear: both;"></div>

{{-- Footer notes --}}
<div class="footer-notes">
    <strong>PERHATIAN:</strong><br>
    Barang-barang yang sudah di beli tidak dapat di tukar atau di kembalikan.
    Barang-barang tersebut merupakan titipan yang sewaktu-waktu dapat di ambil kembali,
    jikalau pembayaran belum lunas. Pembayaran dengan Giro/Cheque dianggap lunas
    jika Giro/Cheque tersebut telah dicairkan.
</div>

{{-- Signatures --}}
<table class="signatures">
    <tr>
        <td>
            Tanda tangan si penerima
        </td>
        <td>Disetujui</td>
        <td>Disaksikan</td>
        <td>Hormat Kami</td>
    </tr>
    <tr>
        <td class="sign-space"></td>
        <td class="sign-space"></td>
        <td class="sign-space"></td>
        <td class="sign-space"></td>
    </tr>
    <tr>
        <td>( .............................. )</td>
        <td>( .............................. )</td>
        <td>( .............................. )</td>
        <td>( .............................. )</td>
    </tr>
    <tr>
        <td class="role">&nbsp;</td>
        <td class="role">Plant Mgr</td>
        <td class="role">Driver</td>
        <td class="role">Warehouse</td>
    </tr>
</table>

</body>
</html>
