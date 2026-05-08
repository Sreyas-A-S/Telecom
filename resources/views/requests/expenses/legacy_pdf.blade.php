@php
$settingValue = function ($key, $default = null) {
    if (function_exists('get_setting')) {
        return get_setting($key, $default);
    }
    $setting = \App\Models\Setting::where('key', $key)->first();
    return $setting ? $setting->value : $default;
};

$organizationName = $settingValue('organization_name', config('app.name', 'Laravel'));
$organizationAddress = $settingValue('organization_address', '');
$organizationPhone = $settingValue('organization_phone', '');
$organizationWebsite = $settingValue('organization_website', '');
$organizationLogo = $settingValue('organization_logo', '');
$organizationLogoPath = $organizationLogo ? public_path($organizationLogo) : null;
$hasOrganizationLogo = $organizationLogoPath && file_exists($organizationLogoPath);
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Travel Expense Report | {{ $employee->name ?? 'Report' }}</title>
    <style>
        /* Standalone Print settings for landscape */
        @page {
            size: A4 landscape;
            margin: 40px;
        }

        :root {
            --primary: #1d3557;
            --secondary: #457b9d;
            --accent: #e63946;
            --bg-light: #f8f9fa;
            --border: #dee2e6;
            --text-dark: #212529;
            --text-muted: #6c757d;
        }

        body {
            font-family: 'Inter', 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: var(--text-dark);
            background-color: transparent;
            margin: 0;
            padding: 0;
        }

        #report-wrapper {
            position: relative;
        }

        @media screen {
            body {
                background-color: #f0f2f5;
                padding: 40px 0;
            }

            #report-wrapper {
                width: 95%;
                max-width: 1400px;
                margin: 0 auto;
                background: #fff;
                padding: 40px;
                box-shadow: 0 15px 50px rgba(0, 0, 0, 0.1);
                border-radius: 12px;
            }

            .btn-print {
                display: flex !important;
            }
        }

        /* Internal Header Branding */
        .card-internal-header {
            display: table;
            width: 100%;
            border-bottom: 2px solid var(--border);
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .header-left-col {
            display: table-cell;
            vertical-align: middle;
        }

        .header-right-col {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
        }

        .org-brand {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .org-logo {
            max-width: 60px;
            max-height: 60px;
            object-fit: contain;
        }

        .org-name {
            font-size: 20px;
            font-weight: 800;
            color: var(--primary);
            letter-spacing: -0.5px;
        }

        .org-line {
            font-size: 10px;
            color: var(--text-muted);
            line-height: 1.4;
        }

        .confidential-markings {
            text-align: right;
        }

        .conf-status {
            font-weight: 900;
            font-size: 14px;
            color: var(--secondary);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .doc-type {
            font-size: 9px;
            color: var(--text-muted);
            font-weight: 600;
        }

        .main-report-title {
            text-align: center;
            margin-bottom: 30px;
        }

        .main-report-title h1 {
            font-size: 28px;
            font-weight: 900;
            color: var(--primary);
            text-transform: uppercase;
            margin: 0;
            letter-spacing: -1px;
        }

        .main-report-title p {
            font-size: 11px;
            color: var(--text-muted);
            font-weight: 700;
            margin-top: 5px;
        }

        /* Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 25px;
            margin-bottom: 30px;
            background: var(--bg-light);
            padding: 15px 20px;
            border-radius: 8px;
            border: 1px solid var(--border);
        }

        .info-item {
            display: flex;
            flex-direction: column;
        }

        .info-label {
            font-size: 9px;
            text-transform: uppercase;
            color: var(--text-muted);
            font-weight: 700;
            margin-bottom: 4px;
        }

        .info-value {
            font-size: 13px;
            font-weight: 700;
            color: var(--primary);
        }

        /* Expense Grid Styles */
        .expense-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            table-layout: fixed;
            border: 2px solid var(--primary);
        }

        .expense-grid th {
            background-color: var(--primary);
            color: #fff;
            font-weight: 700;
            padding: 8px 2px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            text-transform: uppercase;
            font-size: 10px;
            vertical-align: middle;
        }

        .expense-grid td {
            border: 1px solid var(--border);
            height: 32px;
            vertical-align: middle;
            font-size: 11px;
        }

        .category-label {
            text-align: left;
            padding-left: 12px;
            font-weight: 700;
            background-color: var(--bg-light);
            width: 160px;
            color: var(--primary);
        }

        .row-total-cell {
            background-color: #f1f3f5;
            font-weight: 800;
        }

        .col-total-row td {
            background-color: var(--primary);
            color: #fff;
            font-weight: 800;
        }

        .grand-total-box {
            background-color: var(--accent) !important;
            color: #fff !important;
        }

        /* Breakdown Styles */
        .section-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 40px 0 15px 0;
        }

        .section-header h2 {
            font-size: 14px;
            font-weight: 900;
            text-transform: uppercase;
            margin: 0;
            color: var(--primary);
        }

        .accent-line {
            height: 3px;
            flex-grow: 1;
            background: linear-gradient(to right, var(--accent), transparent);
        }

        .detail-table {
            width: 100%;
            border-collapse: collapse;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
        }

        .detail-table th {
            background-color: #f1f3f5;
            border: 1px solid var(--border);
            padding: 10px;
            text-align: left;
            font-weight: 800;
            font-size: 10px;
            text-transform: uppercase;
        }

        .detail-table td {
            border: 1px solid var(--border);
            padding: 8px 12px;
            height: 28px;
        }

        /* Footer Branding */
        .card-internal-footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 10px;
            color: var(--text-muted);
        }

        .report-footer-meta {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-top: 30px;
        }

        .approval-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            width: 60%;
        }

        .approval-item {
            border: 1px solid var(--border);
            border-radius: 4px;
            text-align: center;
        }

        .approval-label {
            background: #f8f9fa;
            padding: 6px;
            font-weight: 800;
            font-size: 9px;
            border-bottom: 1px solid var(--border);
            text-transform: uppercase;
        }

        .approval-space {
            height: 50px;
        }

        .user-sig-block {
            width: 35%;
            padding: 15px;
            border: 2px dashed var(--border);
            border-radius: 8px;
        }

        .sig-entry {
            margin-bottom: 10px;
            display: flex;
            align-items: flex-end;
            gap: 10px;
        }

        .sig-label {
            font-weight: 700;
            color: var(--text-muted);
            min-width: 80px;
            font-size: 9px;
        }

        .sig-under {
            flex-grow: 1;
            border-bottom: 1px solid #000;
            height: 15px;
        }

        .btn-print {
            position: fixed;
            top: 20px;
            right: 40px;
            background: var(--primary);
            color: #fff;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            display: none;
            align-items: center;
            gap: 10px;
            z-index: 9999;
        }

        [contenteditable="true"]:hover {
            background-color: rgba(255, 255, 0, 0.1) !important;
            cursor: text;
        }

        [contenteditable="true"]:focus {
            background-color: #fff !important;
            outline: 2px solid var(--secondary);
            border-radius: 2px;
            z-index: 10;
        }

        @media print {
            .btn-print {
                display: none !important;
            }
            [contenteditable="true"] {
                outline: none !important;
            }
            body {
                background-color: #fff;
            }
            #report-wrapper {
                width: 100%;
                margin: 0;
                padding: 0;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>

<div id="report-wrapper">
    <a href="javascript:window.print()" class="btn-print">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2M6 14h12v10H6z" />
        </svg>
        PRINT EXPORT
    </a>

    <!-- Standing Branding -->
    <header class="card-internal-header">
        <div class="header-left-col">
            <div class="org-brand">
                @if($hasOrganizationLogo)
                    <img class="org-logo" src="{{ $organizationLogoPath }}" alt="Organization Logo">
                @endif
                <div class="org-details">
                    <div class="org-name">{{ $organizationName }}</div>
                    @if($organizationAddress)
                        <div class="org-line">{{ $organizationAddress }}</div>
                    @endif
                    <div class="org-line">
                        @if($organizationPhone) Phone: {{ $organizationPhone }} @endif
                        @if($organizationPhone && $organizationWebsite) | @endif
                        @if($organizationWebsite) {{ $organizationWebsite }} @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="header-right-col">
            <div class="confidential-markings">
                <div class="conf-status">Confidential</div>
                <div class="doc-type">ACCOUNTING DOCUMENT</div>
            </div>
        </div>
    </header>

    <div class="main-report-title">
        <h1>Travel Expense Report</h1>
        <p>WEEKLY FINANCIAL SUBMISSION FORM</p>
    </div>

    <!-- Info Grid -->
    <section class="info-grid">
        <div class="info-item">
            <span class="info-label">Employee Name</span>
            <span class="info-value" contenteditable="true">{{ $employee->name ?? 'N/A' }}</span>
        </div>
        <div class="info-item">
            <span class="info-label">Reference ID</span>
            <span class="info-value" contenteditable="true">#{{ date('Y') }}-{{ str_pad($employee->id, 4, '0', STR_PAD_LEFT) }}</span>
        </div>
        <div class="info-item">
            <span class="info-label">Division / Area</span>
            <span class="info-value" contenteditable="true">{{ $employee->employee->department->name ?? 'Head Office' }}</span>
        </div>
        <div class="info-item">
            <span class="info-label">Week Ending Date</span>
            <span class="info-value" contenteditable="true">{{ $weekEnding ?? date('d-m-Y') }}</span>
        </div>
    </section>

    <!-- Expense Matrix -->
    <table class="expense-grid">
        <thead>
            <tr>
                <th class="category-label" style="background-color: var(--primary); color: #fff; vertical-align: middle;">EXPENSE HEAD</th>
                @foreach(['SUN','MON','TUE','WED','THU','FRI','SAT'] as $idx => $day)
                <th style="vertical-align: middle;">{{ $day }} <br> <span style="font-size: 8px; opacity: 0.8;">{{ $dates[$idx] }}</span></th>
                @endforeach
                <th style="background-color: var(--secondary); vertical-align: middle;">TOTAL <br> <span style="font-size: 8px; opacity: 0.5;">WEEKLY</span></th>
            </tr>
        </thead>
        <tbody>
            @php
            $categories = ['Daily Allowance', 'Conveyance', 'Rail / Bus Fare', 'Coolie Charge', 'Postage', 'Miscellaneous'];
            $colTotals = array_fill(0, 7, 0);
            $grandTotal = 0;
            @endphp

            @foreach($categories as $cat)
            <tr>
                <td class="category-label">{{ $cat }}</td>
                @php $rowSum = 0; @endphp
                @for($i=0; $i<7; $i++)
                    @php
                    $val=$matrix[$cat][$i] ?? 0;
                    $rowSum +=$val;
                    $colTotals[$i] +=$val;
                    @endphp
                    <td align="center" contenteditable="true">
                    @if($val > 0)
                        {{ number_format($val, 2) }}
                    @endif
                    </td>
                    @endfor
                    @php
                    $grandTotal += $rowSum;
                    @endphp
                    <td align="center" class="row-total-cell" contenteditable="true">
                        @if($rowSum > 0)
                        {{ number_format($rowSum, 2) }}
                        @endif
                    </td>
            </tr>
            @endforeach

            <tr class="col-total-row">
                <td class="category-label" style="background-color: var(--primary); color: #fff;">WEEKLY TOTAL</td>
                @foreach($colTotals as $colSum)
                <td align="center" contenteditable="true">
                    @if($colSum > 0)
                    {{ number_format($colSum, 2) }}
                    @endif
                </td>
                @endforeach
                <td align="center" class="grand-total-box" contenteditable="true">
                    @if($grandTotal > 0)
                    {{ number_format($grandTotal, 2) }}
                    @endif
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Detailed Breakdown -->
    <div class="section-header">
        <h2>Detailed Expense Breakdown</h2>
        <div class="accent-line"></div>
    </div>

    <table class="detail-table">
        <thead>
            <tr>
                <th width="40" style="text-align: center;">SL</th>
                <th width="100">Date</th>
                <th width="120">Category</th>
                <th>Explanation / Particulars</th>
                <th width="100" style="text-align: center;">Status</th>
                <th width="120" style="text-align: right;">Amount ({{ config('app.currency', 'INR') }})</th>
            </tr>
        </thead>
        <tbody>
            @foreach($expenses as $idx => $exp)
            <tr>
                <td align="center" style="color: var(--text-muted); font-weight: bold;">{{ $idx + 1 }}</td>
                <td align="center" contenteditable="true">{{ \Carbon\Carbon::parse($exp->date)->format('d-m-Y') }}</td>
                <td align="center" style="text-transform: uppercase; font-weight: bold; font-size: 9px;" contenteditable="true">{{ str_replace('_', ' ', $exp->expense_type) }}</td>
                <td style="font-weight: 500;" contenteditable="true">{{ $exp->description ?: 'Expense entry' }}</td>
                <td align="center">
                    <span style="font-size: 8px; padding: 2px 6px; border-radius: 4px; background: #eee; text-transform: uppercase;">{{ $exp->status }}</span>
                </td>
                <td align="right" style="font-weight: 800; color: var(--primary);" contenteditable="true">{{ number_format($exp->amount, 2) }}</td>
            </tr>
            @endforeach
            @for($i = count($expenses); $i < 6; $i++)
                <tr>
                <td>&nbsp;</td>
                <td contenteditable="true"></td>
                <td contenteditable="true"></td>
                <td contenteditable="true"></td>
                <td contenteditable="true"></td>
                <td contenteditable="true"></td>
                </tr>
            @endfor
        </tbody>
    </table>

    <!-- Footer Meta -->
    <div class="report-footer-meta">
        <div class="approval-grid">
            <div class="approval-item">
                <div class="approval-label">RECOMMENDED</div>
                <div class="approval-space"></div>
            </div>
            <div class="approval-item">
                <div class="approval-label">APPROVED BY</div>
                <div class="approval-space"></div>
            </div>
            <div class="approval-item">
                <div class="approval-label">CHIEF ACCOUNTANT</div>
                <div class="approval-space"></div>
            </div>
        </div>

        <div class="user-sig-block">
            <div class="sig-entry">
                <span class="sig-label">NAME</span>
                <span class="sig-under"></span>
            </div>
            <div class="sig-entry">
                <span class="sig-label">SIGNATURE</span>
                <span class="sig-under"></span>
            </div>
            <div class="sig-entry">
                <span class="sig-label">DATE</span>
                <span class="sig-under"></span>
            </div>
        </div>
    </div>

    <footer class="card-internal-footer">
        <div>Generated on {{ now()->format('d M Y, h:i A') }}</div>
        <div>Travel Expense Report Submission &bullet; {{ $organizationName }}</div>
        <div>&nbsp;</div>
    </footer>
</div>

</body>
</html>