<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pendapatan - Domos Kost Group</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #1e3a8a;
            padding-bottom: 20px;
        }
        
        .header h1 {
            font-size: 20px;
            color: #1e3a8a;
            margin-bottom: 5px;
        }
        
        .header h2 {
            font-size: 16px;
            color: #666;
            margin-bottom: 10px;
        }
        
        .header .address {
            font-size: 10px;
            color: #888;
        }
        
        .periode-info {
            background-color: #f8fafc;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 25px;
            border-left: 4px solid #3b82f6;
        }
        
        .periode-info h3 {
            color: #1e3a8a;
            margin-bottom: 10px;
        }
        
        .ringkasan {
            margin-bottom: 30px;
        }
        
        .ringkasan h3 {
            color: #1e3a8a;
            margin-bottom: 15px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 5px;
        }
        
        .ringkasan-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .ringkasan-item {
            background-color: #f9fafb;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #e5e7eb;
        }
        
        .ringkasan-item .label {
            font-weight: bold;
            color: #374151;
            margin-bottom: 5px;
        }
        
        .ringkasan-item .value {
            font-size: 16px;
            font-weight: bold;
            color: #1e3a8a;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            background-color: white;
        }
        
        .table th {
            background-color: #1e3a8a;
            color: white;
            padding: 12px 8px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #ddd;
        }
        
        .table td {
            padding: 10px 8px;
            border: 1px solid #ddd;
            vertical-align: top;
        }
        
        .table tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }
        
        .table tbody tr:hover {
            background-color: #f3f4f6;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .currency {
            font-family: 'Courier New', monospace;
            font-weight: bold;
        }
        
        .positive {
            color: #059669;
        }
        
        .negative {
            color: #dc2626;
        }
        
        .section {
            margin-bottom: 30px;
        }
        
        .section h3 {
            color: #1e3a8a;
            margin-bottom: 15px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 5px;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        
        .breakdown-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .breakdown-item {
            text-align: center;
            padding: 10px;
            background-color: #f9fafb;
            border-radius: 5px;
            border: 1px solid #e5e7eb;
        }
        
        .breakdown-item .percentage {
            font-size: 14px;
            font-weight: bold;
            color: #1e3a8a;
        }
        
        .summary-total {
            background-color: #1e3a8a;
            color: white;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .summary-total .amount {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        @media print {
            body {
                font-size: 11px;
            }
            
            .section {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>DOMOS KOST GROUP</h1>
        <h2>LAPORAN PENDAPATAN</h2>
        <div class="address">
            Jl. Parang III Gg. Pekan Jaya No. 88, Kelurahan Kwala Bekala<br>
            Kecamatan Medan Johor, P. Bulan, 20142<br>
            Telp: 081234567890 | Email: info@domoskost.com
        </div>
    </div>

    <!-- Periode Info -->
    <div class="periode-info">
        <h3>Informasi Periode</h3>
        <p><strong>Periode:</strong> {{ \Carbon\Carbon::parse($data->periode->mulai)->format('d F Y') }} s/d {{ \Carbon\Carbon::parse($data->periode->selesai)->format('d F Y') }}</p>
        <p><strong>Format:</strong> {{ ucfirst($data->periode->format) }}</p>
        <p><strong>Tanggal Cetak:</strong> {{ \Carbon\Carbon::parse($data->generated_at)->format('d F Y H:i') }} WIB</p>
    </div>

    <!-- Summary Total -->
    <div class="summary-total">
        <div class="amount">Rp {{ number_format($data->ringkasan->total_pendapatan, 0, ',', '.') }}</div>
        <div>Total Pendapatan Periode</div>
    </div>

    <!-- Ringkasan Pendapatan -->
    <div class="ringkasan">
        <h3>Ringkasan Pendapatan</h3>
        <div class="breakdown-grid">
            <div class="breakdown-item">
                <div class="label">Sewa Kamar</div>
                <div class="currency">Rp {{ number_format($data->ringkasan->pendapatan_sewa, 0, ',', '.') }}</div>
                <div class="percentage">{{ $data->ringkasan->persentase_sewa }}%</div>
            </div>
            <div class="breakdown-item">
                <div class="label">Laundry</div>
                <div class="currency">Rp {{ number_format($data->ringkasan->pendapatan_laundry, 0, ',', '.') }}</div>
                <div class="percentage">{{ $data->ringkasan->persentase_laundry }}%</div>
            </div>
            <div class="breakdown-item">
                <div class="label">Denda</div>
                <div class="currency">Rp {{ number_format($data->ringkasan->pendapatan_denda, 0, ',', '.') }}</div>
                <div class="percentage">{{ $data->ringkasan->persentase_denda }}%</div>
            </div>
        </div>
    </div>

    @if(count($data->breakdown_periode) > 0)
    <!-- Breakdown Per Periode -->
    <div class="section">
        <h3>Rincian Pendapatan Per Periode</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Periode</th>
                    <th class="text-right">Sewa Kamar</th>
                    <th class="text-right">Laundry</th>
                    <th class="text-right">Denda</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data->breakdown_periode as $periode)
                <tr>
                    <td>{{ $periode->periode_display }}</td>
                    <td class="text-right currency">Rp {{ number_format($periode->sewa, 0, ',', '.') }}</td>
                    <td class="text-right currency">Rp {{ number_format($periode->laundry, 0, ',', '.') }}</td>
                    <td class="text-right currency">Rp {{ number_format($periode->denda, 0, ',', '.') }}</td>
                    <td class="text-right currency"><strong>Rp {{ number_format($periode->total, 0, ',', '.') }}</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Piutang -->
    <div class="section">
        <h3>Informasi Piutang</h3>
        <div class="ringkasan-grid">
            <div class="ringkasan-item">
                <div class="label">Piutang Sewa</div>
                <div class="value currency">Rp {{ number_format($data->piutang->piutang_sewa, 0, ',', '.') }}</div>
            </div>
            <div class="ringkasan-item">
                <div class="label">Piutang Laundry</div>
                <div class="value currency">Rp {{ number_format($data->piutang->piutang_laundry, 0, ',', '.') }}</div>
            </div>
        </div>
        <div class="ringkasan-item" style="margin-top: 15px;">
            <div class="label">Total Piutang</div>
            <div class="value currency">Rp {{ number_format($data->piutang->total_piutang, 0, ',', '.') }}</div>
        </div>
    </div>

    <!-- Breakdown Metode Pembayaran -->
    @if(count((array)$data->breakdown_metode) > 0)
    <div class="section">
        <h3>Breakdown Metode Pembayaran</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Metode Pembayaran</th>
                    <th class="text-right">Jumlah</th>
                    <th class="text-right">Persentase</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data->breakdown_metode as $metode => $jumlah)
                <tr>
                    <td>{{ $metode }}</td>
                    <td class="text-right currency">Rp {{ number_format($jumlah, 0, ',', '.') }}</td>
                    <td class="text-right">{{ $data->ringkasan->total_pendapatan > 0 ? round(($jumlah / $data->ringkasan->total_pendapatan) * 100, 2) : 0 }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Top Penghuni -->
    @if(count($data->top_penghuni) > 0)
    <div class="section">
        <h3>10 Penghuni dengan Pembayaran Tertinggi</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Penghuni</th>
                    <th>Kamar</th>
                    <th class="text-right">Total Pembayaran</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data->top_penghuni as $index => $penghuni)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $penghuni->penghuni }}</td>
                    <td class="text-center">{{ $penghuni->kamar }}</td>
                    <td class="text-right currency">Rp {{ number_format($penghuni->total_bayar, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>Laporan ini dibuat secara otomatis oleh Sistem Informasi Domos Kost Group</p>
        <p>Dicetak pada: {{ now()->format('d F Y H:i:s') }} WIB</p>
        <br>
        <p style="margin-top: 30px;">
            <strong>Pelita Ginting</strong><br>
            Pemilik Domos Kost Group
        </p>
    </div>
</body>
</html>
