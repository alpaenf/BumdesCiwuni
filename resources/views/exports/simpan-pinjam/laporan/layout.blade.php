<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Poppins', 'DejaVu Sans', sans-serif; font-size: 9pt; color: #1a1a1a; }

    .page-header { border-bottom: 2.5px solid {{ $headerColor ?? '#1B5E20' }}; padding-bottom: 10px; margin-bottom: 14px; }
    .org-name { font-size: 14pt; font-weight: bold; color: {{ $headerColor ?? '#1B5E20' }}; }
    .report-title { font-size: 11pt; font-weight: bold; margin-top: 2px; }
    .report-meta { font-size: 8pt; color: #555; margin-top: 3px; }

    .summary-box { display: flex; gap: 8px; margin-bottom: 14px; flex-wrap: wrap; }
    .summary-card { border: 1px solid #ddd; border-radius: 4px; padding: 8px 12px; flex: 1; min-width: 100px; background: #f9f9f9; }
    .summary-card .label { font-size: 7.5pt; color: #666; margin-bottom: 2px; }
    .summary-card .value { font-size: 10pt; font-weight: bold; color: {{ $headerColor ?? '#1B5E20' }}; }

    table { width: 100%; border-collapse: collapse; font-size: 8pt; }
    thead tr { background-color: {{ $headerColor ?? '#1B5E20' }}; color: white; }
    thead th { padding: 6px 7px; text-align: left; font-weight: bold; font-size: 8pt; }
    tbody tr:nth-child(even) { background-color: #f5f5f5; }
    tbody tr td { padding: 5px 7px; border-bottom: 1px solid #eee; vertical-align: top; }
    tbody tr:last-child td { border-bottom: 2px solid {{ $headerColor ?? '#1B5E20' }}; }

    .text-right { text-align: right; }
    .text-center { text-align: center; }

    .page-footer { margin-top: 16px; border-top: 1px solid #ddd; padding-top: 6px; font-size: 7.5pt; color: #888; display: flex; justify-content: space-between; }
</style>
</head>
<body>
    <div class="page-header">
        <div class="org-name">BUMDes Dammar Wulan &mdash; Unit Simpan Pinjam</div>
        <div class="report-title">{{ $reportTitle }}</div>
        <div class="report-meta">
            {{ $periodLabel ?? '' }}
            &nbsp;|&nbsp; Dicetak: {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>

    @if(isset($summaryItems) && count($summaryItems))
    <div class="summary-box">
        @foreach($summaryItems as $item)
        <div class="summary-card">
            <div class="label">{{ $item['label'] }}</div>
            <div class="value">{{ $item['value'] }}</div>
        </div>
        @endforeach
    </div>
    @endif

    @yield('content')

    <div class="page-footer">
        <span>BUMDes Dammar Wulan &mdash; Unit Simpan Pinjam</span>
        <span>Dokumen resmi &mdash; tidak perlu tanda tangan digital</span>
    </div>
</body>
</html>
