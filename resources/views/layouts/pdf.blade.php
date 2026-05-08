<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>@yield('title')</title>
    <style>
        @page {
            margin: 120px 25px 80px 25px;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 0;
        }

        header {
            position: fixed;
            top: -95px;
            left: 0px;
            right: 0px;
            height: 85px;
            border-bottom: 2px solid #ddd;
            padding-bottom: 8px;
        }

        footer {
            position: fixed; 
            bottom: -60px; 
            left: 0px; 
            right: 0px;
            height: 40px; 
            border-top: 1px solid #ddd;
            padding-top: 10px;
            font-size: 10px;
            color: #777;
        }

        .header-content {
            display: table;
            width: 100%;
        }

        .header-left {
            display: table-cell;
            text-align: left;
            vertical-align: top;
            color: #1d3557;
        }

        .header-right {
            display: table-cell;
            text-align: right;
            vertical-align: top;
            font-size: 12px;
            color: #555;
        }

        .org-name {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 2px;
        }

        .org-brand {
            display: table;
            width: 100%;
        }

        .org-logo-cell {
            display: table-cell;
            width: 64px;
            vertical-align: top;
            padding-right: 10px;
        }

        .org-logo {
            max-width: 56px;
            max-height: 56px;
            object-fit: contain;
        }

        .org-text-cell {
            display: table-cell;
            vertical-align: top;
        }

        .org-line {
            font-size: 11px;
            color: #666;
            line-height: 1.4;
        }

        .footer-content {
            display: table;
            width: 100%;
        }

        .footer-left {
            display: table-cell;
            text-align: left;
        }

        .footer-right {
            display: table-cell;
            text-align: right;
        }

        .page-number:before {
            content: "Page " counter(page);
        }

        /* Common utility classes */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .mb-10 { margin-bottom: 10px; }
        .mt-10 { margin-top: 10px; }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #1d3557;
        }

        .report-head {
            text-align: center;
            margin-bottom: 20px;
        }

        .report-title {
            margin: 0;
            color: #1d3557;
            font-size: 20px;
            font-weight: 700;
        }

        .report-subtitle {
            color: #666;
            margin-top: 5px;
            font-size: 12px;
        }
    </style>
    @stack('styles')
</head>
<body>
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
        
        // Use specifically passed primary logo if it exists, otherwise fallback to org setting
        if (isset($primaryLogoPath) && file_exists($primaryLogoPath)) {
            $organizationLogoPath = $primaryLogoPath;
        }
        
        $hasOrganizationLogo = $organizationLogoPath && file_exists($organizationLogoPath);
    @endphp
    <header>
        <div class="header-content">
            <div class="header-left">
                <div class="org-brand">
                    @if($hasOrganizationLogo)
                        <div class="org-logo-cell">
                            <img class="org-logo" src="{{ $organizationLogoPath }}" alt="Organization Logo">
                        </div>
                    @endif
                    <div class="org-text-cell">
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
            <div class="header-right">
                @if(isset($secondaryLogoPath) && file_exists($secondaryLogoPath))
                    <img class="org-logo" src="{{ $secondaryLogoPath }}" alt="Secondary Logo" style="float: right;">
                @endif
                <div style="clear: both;"></div>
                @yield('header-right')
            </div>
        </div>
    </header>

    <footer>
        <div class="footer-content">
            <div class="footer-left">
                Generated on {{ now()->format('d M Y, h:i A') }}
            </div>
            <div class="footer-right">
                <span class="page-number"></span>
            </div>
        </div>
    </footer>

    <main>
        @yield('content')
    </main>
</body>
</html>
