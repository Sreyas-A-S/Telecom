<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="icon" href="{{ asset('admin/assets/images/favicon.png') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('admin/assets/images/favicon.png') }}" type="image/x-icon">
    <title>@yield('title')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        window.baseUrl = "{{ url('/') }}";
    </script>
    <!-- Google font-->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="">
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;1,200;1,300;1,400;1,500;1,600;1,700;1,800&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --bs-body-font-family: 'Plus Jakarta Sans', sans-serif !important;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif !important;
        }
    </style>
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/assets/scss/pages/_social-app.scss') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/assets/css/font-awesome.css') }}">
    <!-- ico-font-->
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/assets/css/vendors/icofont.css') }}">
    <!-- Themify icon-->
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/assets/css/vendors/themify.css') }}">
    <!-- Flag icon-->
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/assets/css/vendors/flag-icon.css') }}">
    <!-- Feather icon-->
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/assets/css/vendors/feather-icon.css') }}">
    {{-- social app scss --}}

    <!-- Plugins css start-->
    <!-- Plugins css Ends-->
    <!-- Bootstrap css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/assets/css/vendors/bootstrap.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <style>
        .img-container {
            /* This is important for Cropper.js to work correctly */
            min-height: 300px;
            /* Or a fixed height like 400px */
            min-width: 300px;
            /* Or a fixed width like 400px */
            max-height: 500px;
            /* Adjust as needed */
            max-width: 100%;
            /* Ensure it's responsive */
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #f7f7f7;
        }

        .img-container img {
            /* Ensure the image itself is responsive within its container */
            display: block;
            max-width: 100%;
            height: auto;
        }

        .social-status {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 5px;
        }

        .social-success {
            background-color: #28a745;
            /* Green */
        }

        .social-busy {
            background-color: #ffc107;
            /* Yellow/Orange */
        }

        .social-offline {
            background-color: #dc3545;
            /* Red */
        }

        .social-secondary {
            background-color: #797979;
            /* Red */
        }

        /* Premium DataTable Stying Overrides */
        .dataTables_wrapper .dataTables_filter input,
        .dataTables_wrapper .dataTables_length select {
            border-radius: 12px !important;
            border: 2px solid #e2e8f0 !important;
            background-color: #f8fafc !important;
            padding: 8px 14px !important;
            font-size: 13px !important;
            font-weight: 600 !important;
            color: #1e293b !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            box-shadow: none !important;
            cursor: pointer !important;
            appearance: none !important;
            /* Premium custom arrow handling */
            background-image: url("data:image/svg+xml,%3Csvgxmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2.5' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E") !important;
            background-repeat: no-repeat !important;
            background-position: right 12px center !important;
            background-size: 14px !important;
            padding-right: 36px !important;
        }

        .dataTables_wrapper .dataTables_filter input:focus,
        .dataTables_wrapper .dataTables_length select:focus {
            border-color: #6366f1 !important;
            background-color: #fff !important;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.12) !important;
            outline: none !important;
        }

        /* DataTable Pagination */
        .dataTables_wrapper .dataTables_paginate {
            margin-top: 15px !important;
            padding-top: 10px !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            border-radius: 10px !important;
            border: 1px solid #e2e8f0 !important;
            background: #fff !important;
            color: #475569 !important;
            padding: 8px 16px !important;
            margin-left: 5px !important;
            font-weight: 600 !important;
            font-size: 12px !important;
            transition: all 0.2s ease !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%) !important;
            color: #fff !important;
            border: none !important;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2) !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover:not(.current) {
            background: #f1f5f9 !important;
            border-color: #cbd5e1 !important;
            color: #1e293b !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
            background: transparent !important;
            border-color: #f1f5f9 !important;
            color: #cbd5e1 !important;
            opacity: 0.5 !important;
        }

        .dataTables_wrapper .btn-link {
            color: #6366f1 !important;
            font-weight: 600 !important;
        }
    </style>

    <style>
        /* Premium Minimalist Global Scrollbars - Restricted to Body */
        body::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        body::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.05);
        }

        body::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.15);
            border-radius: 10px;
        }

        body::-webkit-scrollbar-thumb:hover {
            background: rgba(0, 0, 0, 0.25);
        }

        /* Support for Firefox */
        body {
            scrollbar-width: thin;
            scrollbar-color: rgba(0, 0, 0, 0.15) rgba(0, 0, 0, 0.05);
        }

        /* Prevent Double Scrollbars in SimpleBar while maintaining functionality */
        [data-simplebar] ::-webkit-scrollbar,
        .simplebar-content-wrapper::-webkit-scrollbar,
        .simplebar-hide-scrollbar::-webkit-scrollbar,
        .sidebar-wrapper ::-webkit-scrollbar,
        .sidebar-main ::-webkit-scrollbar {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
        }

        [data-simplebar],
        .simplebar-content-wrapper,
        .simplebar-hide-scrollbar,
        .sidebar-wrapper,
        .sidebar-main {
            scrollbar-width: none !important;
            -ms-overflow-style: none !important;
        }

        /* Premium Minimalist SimpleBar Styling */
        .simplebar-scrollbar::before {
            background: rgba(0, 0, 0, 0.2) !important;
            width: 4px !important;
            border-radius: 10px !important;
            opacity: 0 !important;
            transition: opacity 0.3s ease, background 0.3s ease !important;
            left: 1px !important;
            right: 1px !important;
        }

        .simplebar-scrollbar.simplebar-visible::before {
            opacity: 1 !important;
        }

        .simplebar-track.simplebar-vertical {
            width: 6px !important;
            background: transparent !important;
        }

        .simplebar-track.simplebar-horizontal {
            height: 6px !important;
            background: transparent !important;
        }
    </style>
    <!-- App css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/assets/css/style.css') }}">
    <link id="color" rel="stylesheet" href="{{ asset('admin/assets/css/color-1.css') }}" media="screen">
    <!-- Responsive css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/assets/css/responsive.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdn.sheetjs.com/xlsx-0.20.1/package/xlsx.min.js"></script>
    @stack('styles')


    <style>
        /* Premium Card Design System */
        .card {
            border-radius: 24px !important;
            border: 1px solid rgba(255, 255, 255, 0.8) !important;
            background: #ffffff !important;
            box-shadow:
                0 4px 6px -1px rgba(0, 0, 0, 0.05),
                0 10px 20px -5px rgba(0, 0, 0, 0.03) !important;
            transition: all 0.5s cubic-bezier(0.23, 1, 0.32, 1) !important;
            overflow: hidden !important;
            margin-bottom: 25px !important;
            position: relative !important;
        }

        /* Card Hover Removed as per request */
        .card:hover {
            transform: none !important;
            box-shadow:
                0 4px 6px -1px rgba(0, 0, 0, 0.05),
                0 10px 20px -5px rgba(0, 0, 0, 0.03) !important;
            z-index: 1 !important;
        }

        .card-header {
            background: transparent !important;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05) !important;
            padding: 20px 25px !important;
        }

        .card-header h5 {
            font-weight: 700 !important;
            font-size: 16px !important;
            color: #1e293b !important;
            margin-bottom: 0 !important;
        }

        .card-body {
            padding: 25px !important;
        }

        /* Widget Icon Enhancements */
        .widget-icon {
            width: 50px !important;
            height: 50px !important;
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
            border-radius: 14px !important;
            background: rgba(99, 102, 241, 0.08) !important;
            /* Default Light Indigo */
            transition: all 0.3s ease !important;
        }

        .card:hover .widget-icon {
            transform: scale(1.1) rotate(5deg) !important;
        }

        /* --- Detailed Card Enhancements --- */
        .static-top-widget-card {
            background: rgba(255, 255, 255, 1) !important;
            border: 1px solid rgba(255, 255, 255, 1) !important;
            position: relative !important;
            overflow: hidden !important;
        }

        .static-top-widget-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.2) 0%, rgba(255, 255, 255, 0) 50%) !important;
            pointer-events: none;
            z-index: 1;
        }

        .static-top-widget-card .card-body {
            padding: 22px !important;
            position: relative !important;
            z-index: 2 !important;
        }

        .static-top-widget-card .widget-icon {
            width: 56px !important;
            height: 56px !important;
            border-radius: 18px !important;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.04) !important;
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
        }

        .static-top-widget-card h6 {
            color: #94a3b8 !important;
            /* Lighter Slate 400 */
            font-weight: 700 !important;
            font-size: 10px !important;
            text-transform: uppercase !important;
            letter-spacing: 1.2px !important;
            margin-bottom: 4px !important;
        }

        .static-top-widget-card h5 {
            color: #1e293b !important;
            font-weight: 850 !important;
            font-size: 20px !important;
            margin: 0 !important;
            letter-spacing: -0.5px !important;
        }

        /* Vibrant Icon Container Colors */
        .static-top-widget-card .widget-icon i.text-primary {
            color: #6366f1 !important;
        }

        .static-top-widget-card .widget-icon i.text-success {
            color: #10b981 !important;
        }

        .static-top-widget-card .widget-icon i.text-danger {
            color: #ef4444 !important;
        }

        .static-top-widget-card .widget-icon i.text-warning {
            color: #f59e0b !important;
        }

        .static-top-widget-card .widget-icon i.text-secondary {
            color: #64748b !important;
        }

        .static-top-widget-card .widget-icon i.text-info {
            color: #0ea5e9 !important;
        }

        /* Thematic Icon Backgrounds (Glassmorphic) */
        .static-top-widget-card .widget-icon:has(i.text-primary) {
            background: rgba(99, 102, 241, 0.12) !important;
        }

        .static-top-widget-card .widget-icon:has(i.text-success) {
            background: rgba(16, 185, 129, 0.12) !important;
        }

        .static-top-widget-card .widget-icon:has(i.text-danger) {
            background: rgba(239, 68, 68, 0.12) !important;
        }

        .static-top-widget-card .widget-icon:has(i.text-warning) {
            background: rgba(245, 158, 11, 0.12) !important;
        }

        .static-top-widget-card .widget-icon:has(i.text-secondary) {
            background: rgba(100, 116, 139, 0.12) !important;
        }

        .static-top-widget-card .widget-icon:has(i.text-info) {
            background: rgba(14, 165, 233, 0.12) !important;
        }

        /* Premium Modern Form Inputs & Selects */
        .form-control,
        .form-select,
        .datepicker,
        select.form-control,
        .select2-container--default .select2-selection--single {
            border-radius: 12px !important;
            border: 2px solid #e2e8f0 !important;
            background-color: #f8fafc !important;
            padding: 10px 16px !important;
            height: auto !important;
            font-size: 14px !important;
            font-weight: 500 !important;
            color: #1e293b !important;
            transition: all 0.25s ease !important;
            box-shadow: none !important;
            appearance: none !important;
            background-image: url("data:image/svg+xml,%3Csvgxmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2.5' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E") !important;
            background-repeat: no-repeat !important;
            background-position: right 12px center !important;
            background-size: 14px !important;
        }

        /* Remove arrow from non-select inputs */
        input.form-control {
            background-image: none !important;
        }

        .form-control:hover,
        .form-select:hover,
        .select2-container--default .select2-selection--single:hover {
            border-color: #cbd5e1 !important;
            background-color: #f1f5f9 !important;
        }

        .form-control:focus,
        .form-select:focus,
        .select2-container--default.select2-container--focus .select2-selection--single,
        .select2-container--default.select2-container--open .select2-selection--single {
            border-color: #7366ff !important;
            background-color: #ffffff !important;
            box-shadow: 0 0 0 4px rgba(115, 102, 255, 0.12) !important;
            outline: none !important;
            background-position: right 12px center !important;
            transform: translateY(-1px) !important;
        }

        /* Chevron Rotation on Open */
        .select2-container--default.select2-container--open .select2-selection--single {
            background-image: url("data:image/svg+xml,%3Csvgxmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%237366ff'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2.5' d='M5 15l7-7 7 7'%3E%3C/path%3E%3C/svg%3E") !important;
        }

        /* Small variant specifically for tight sidebars */
        .form-control-sm,
        .form-select-sm {
            padding: 8px 12px !important;
            font-size: 13px !important;
        }

        .form-group label {
            font-weight: 700 !important;
            color: #64748b !important;
            font-size: 12px !important;
            text-transform: uppercase !important;
            letter-spacing: 0.5px !important;
            margin-bottom: 8px !important;
            display: block !important;
        }

        /* Select2 Specific Fixes */
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: normal !important;
            padding-left: 0 !important;
            padding-top: 2px !important;
            color: #1e293b !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 100% !important;
            right: 12px !important;
        }

        /* --- Clean Global Dropdown System --- */
        .dropdown-menu {
            border-radius: 12px !important;
            border: 1px solid rgba(0, 0, 0, 0.05) !important;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1) !important;
            padding: 5px !important;
            background: #ffffff !important;
            z-index: 1060 !important;
        }

        /* Fixed Select2 Visibility (Z-Index Fix) */
        .select2-container--default .select2-dropdown {
            border-radius: 12px !important;
            border: 1px solid #e2e8f0 !important;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1) !important;
            z-index: 9999 !important;
            background: #ffffff !important;
        }

        .dropdown-item {
            border-radius: 8px !important;
            padding: 8px 16px !important;
            font-weight: 500 !important;
            font-size: 13.5px !important;
            color: #475569 !important;
            transition: all 0.2s ease !important;
        }

        .dropdown-item:hover {
            background: #f1f5f9 !important;
            color: #7366ff !important;
        }

        /* Select2 Search Input */
        .select2-search--dropdown {
            padding: 8px !important;
        }

        .select2-search--dropdown .select2-search__field {
            border-radius: 10px !important;
            border: 2px solid #e2e8f0 !important;
            padding: 8px 12px !important;
            background: #f8fafc !important;
        }

        .select2-search--dropdown .select2-search__field:focus {
            border-color: #6366f1 !important;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1) !important;
            outline: none !important;
        }

        .dropdown-item {
            border-radius: 12px !important;
            padding: 10px 16px !important;
            font-weight: 500 !important;
            font-size: 13.5px !important;
            color: #475569 !important;
            transition: all 0.2s ease !important;
            margin-bottom: 2px !important;
        }

        .dropdown-item:hover,
        .dropdown-item:focus {
            background: rgba(99, 102, 241, 0.08) !important;
            color: #6366f1 !important;
            transform: translateX(4px) !important;
        }

        .dropdown-item i {
            font-size: 1.1rem !important;
            margin-right: 12px !important;
            vertical-align: middle !important;
        }

        .dropdown-divider {
            margin: 8px 0 !important;
            border-top-color: #f1f5f9 !important;
            opacity: 0.8 !important;
        }

        /* --- Premium Nav Tabs System --- */
        .nav-tabs {
            border-bottom: none !important;
            background: #f1f5f9 !important;
            padding: 6px !important;
            border-radius: 16px !important;
            display: inline-flex !important;
            gap: 4px !important;
            margin-bottom: 25px !important;
        }

        .nav-tabs .nav-item {
            margin-bottom: 0 !important;
        }

        .nav-tabs .nav-link {
            border: none !important;
            border-radius: 12px !important;
            padding: 10px 24px !important;
            font-weight: 600 !important;
            font-size: 13px !important;
            color: #64748b !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            background: transparent !important;
            white-space: nowrap !important;
        }

        .nav-tabs .nav-link:hover {
            color: #1e293b !important;
            background: rgba(255, 255, 255, 0.5) !important;
        }

        .nav-tabs .nav-link.active {
            background: #ffffff !important;
            color: #6366f1 !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08) !important;
            position: relative !important;
        }

        /* Active Indicator Line */
        .nav-tabs .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: 6px;
            left: 50%;
            transform: translateX(-50%);
            width: 12px;
            height: 3px;
            background: #6366f1 !important;
            border-radius: 10px !important;
        }

        .tab-content {
            padding-top: 10px !important;
        }

        /* High-End Enterprise Button System */
        .btn {
            border-radius: 12px !important;
            font-weight: 600 !important;
            text-transform: uppercase !important;
            font-size: 11px !important;
            letter-spacing: 1.2px !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            border: none !important;
            padding: 12px 26px !important;
            position: relative !important;
            overflow: hidden !important;
            box-shadow:
                0 4px 6px -1px rgba(0, 0, 0, 0.1),
                0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
            background-size: 200% auto !important;
            color: #fff !important;
            margin: 6px 3px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 8px !important;
        }

        /* Subtle Premium Top Highlight */
        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(to right, rgba(255, 255, 255, 0) 0%, rgba(255, 255, 255, 0.3) 50%, rgba(255, 255, 255, 0) 100%) !important;
            pointer-events: none;
        }

        .btn:hover {
            transform: translateY(-2px) !important;
            background-position: right center !important;
            box-shadow:
                0 10px 15px -3px rgba(0, 0, 0, 0.1),
                0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
            color: #fff !important;
        }

        .btn:active {
            transform: translateY(0px) scale(0.97) !important;
        }

        /* Vibrant 'Recommended' Gradient Palette */

        /* Vibrant Violet (Theme Primary - #7366ff) */
        .btn-primary,
        .btn-gradient-primary {
            background: linear-gradient(135deg, #7366ff 0%, #5e52d1 100%) !important;
            box-shadow: 0 4px 15px rgba(115, 102, 255, 0.3) !important;
        }

        /* Vibrant Green (Success) */
        .btn-success,
        .btn-gradient-success {
            background: linear-gradient(135deg, #51bb25 0%, #3ca018 100%) !important;
            box-shadow: 0 4px 15px rgba(81, 187, 37, 0.3) !important;
        }

        /* Vibrant Red (Danger) */
        .btn-danger,
        .btn-gradient-danger {
            background: linear-gradient(135deg, #ff3860 0%, #dc2626 100%) !important;
            box-shadow: 0 4px 15px rgba(255, 56, 96, 0.3) !important;
        }

        /* Vibrant Amber (Warning) */
        .btn-warning,
        .btn-gradient-warning {
            background: linear-gradient(135deg, #ffb822 0%, #ff9700 100%) !important;
            box-shadow: 0 4px 15px rgba(255, 184, 34, 0.3) !important;
        }

        /* Modern Slate (Secondary) */
        .btn-secondary,
        .btn-gradient-secondary {
            background: linear-gradient(135deg, #626ed4 0%, #4451b2 100%) !important;
            box-shadow: 0 4px 15px rgba(98, 110, 212, 0.3) !important;
        }

        /* Readable Light Button */
        .btn-light {
            background: #f8fafc !important;
            color: #475569 !important;
            border: 1px solid #e2e8f0 !important;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05) !important;
        }

        .btn-light:hover {
            background: #f1f5f9 !important;
            color: #1e293b !important;
            border-color: #cbd5e1 !important;
        }

        /* --- Refined Professional Outline Buttons --- */

        [class*="btn-outline-"] {
            background: transparent !important;
            border-width: 2px !important;
            font-weight: 600 !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            border-radius: 12px !important;
        }

        /* Primary Outline (Violet) */
        .btn-outline-primary {
            border-color: #7366ff !important;
            color: #7366ff !important;
        }

        .btn-outline-primary:hover,
        .btn-check:checked+.btn-outline-primary {
            background: linear-gradient(135deg, #7366ff 0%, #5e52d1 100%) !important;
            color: #fff !important;
            border-color: transparent !important;
            box-shadow: 0 4px 15px rgba(115, 102, 255, 0.2) !important;
        }

        /* Success Outline (Green) */
        .btn-outline-success {
            border-color: #51bb25 !important;
            color: #51bb25 !important;
        }

        .btn-outline-success:hover,
        .btn-check:checked+.btn-outline-success {
            background: linear-gradient(135deg, #51bb25 0%, #3ca018 100%) !important;
            color: #fff !important;
            border-color: transparent !important;
            box-shadow: 0 4px 15px rgba(81, 187, 37, 0.2) !important;
        }

        /* Danger Outline (Rose) */
        .btn-outline-danger {
            color: #e11d48 !important;
        }

        .btn-outline-danger:hover,
        .btn-check:checked+.btn-outline-danger {
            background: linear-gradient(135deg, #f43f5e 0%, #9f1239 100%) !important;
            color: #fff !important;
            border-color: transparent !important;
            box-shadow: 0 4px 15px rgba(244, 63, 94, 0.2) !important;
        }

        /* Warning Outline (Amber) */
        .btn-outline-warning {
            border-color: #fbbf24 !important;
            color: #d97706 !important;
        }

        .btn-outline-warning:hover,
        .btn-check:checked+.btn-outline-warning {
            background: linear-gradient(135deg, #fbbf24 0%, #d97706 100%) !important;
            color: #fff !important;
            border-color: transparent !important;
            box-shadow: 0 4px 15px rgba(251, 191, 36, 0.2) !important;
        }

        /* Secondary/Info Outline */
        .btn-outline-secondary {
            border-color: #94a3b8 !important;
            color: #475569 !important;
        }

        .btn-outline-secondary:hover {
            background: #64748b !important;
            color: #fff !important;
            border-color: transparent !important;
        }

        .btn-outline-info {
            border-color: #0ea5e9 !important;
            color: #0284c7 !important;
        }

        .btn-outline-info:hover {
            background: #0ea5e9 !important;
            color: #fff !important;
            border-color: transparent !important;
        }

        /* Ensure Bootstrap modals appear above any custom high-z elements (e.g. autocomplete containers) */
        .modal-backdrop {
            z-index: 299999 !important;
        }

        .modal {
            z-index: 300000 !important;
        }

        /* Sidebar Toggle Logic Fix - Mini Sidebar */
        @media (min-width: 768px) {
            .sidebar-wrapper.close_icon {
                display: block !important;
                width: 80px !important;
                min-width: 80px !important;
                max-width: 80px !important;
                overflow: hidden !important;
                transition: width 0.3s ease;
            }

            /* Mini Sidebar Internal Adjustments - jQuery Animation Base */
            .sidebar-wrapper.close_icon .logo-wrapper {
                padding-left: 0 !important;
                padding-right: 0 !important;
                justify-content: center !important;
            }

            .sidebar-wrapper.close_icon .logo-wrapper a {
                display: none;
                /* jQuery will handle this */
            }

            .sidebar-wrapper.close_icon .logo-icon-wrapper {
                display: block;
                /* jQuery will handle this */
            }

            .sidebar-wrapper.close_icon .logo-wrapper .toggle-sidebar {
                margin: 0 auto !important;
                display: flex !important;
                align-items: center;
                justify-content: center;
                width: 40px !important;
                height: 40px !important;
                transition: all 0.3s ease;
            }

            .sidebar-wrapper.close_icon .sidebar-main-title {
                display: none !important;
                /* Hide section titles */
            }

            /* Hide Text and Arrows in Mini Sidebar */
            .sidebar-wrapper.close_icon .sidebar-link span,
            .sidebar-wrapper.close_icon .according-menu,
            .sidebar-wrapper.close_icon .badge {
                display: none !important;
            }

            /* Ensure Icons are centered */
            .sidebar-wrapper.close_icon .sidebar-list .sidebar-link {
                justify-content: center;
                padding-left: 0;
                padding-right: 0;
            }

            .sidebar-wrapper.close_icon .sidebar-list .sidebar-link svg {
                margin-right: 0 !important;
            }

            /* Restore on hover via :has() to avoid instant expansion on toggle button hover */
            .sidebar-wrapper.close_icon:has(.sidebar-main:hover) {
                width: 280px !important;
                max-width: 280px !important;
                overflow: visible !important;
                z-index: 9999;
                transition-delay: 0.3s;
                /* Delay expansion to prevent accidental triggers */
            }

            .sidebar-wrapper.close_icon:has(.sidebar-main:hover) .logo-wrapper {
                display: flex !important;
                justify-content: space-between !important;
                align-items: center !important;
                padding-left: 20px !important;
                padding-right: 20px !important;
                width: 100% !important;
            }

            .sidebar-wrapper.close_icon:has(.sidebar-main:hover) .logo-wrapper a {
                display: block !important;
            }

            .sidebar-wrapper.close_icon:has(.sidebar-main:hover) .logo-wrapper .toggle-sidebar {
                margin: 0 !important;
                width: auto !important;
                height: auto !important;
                display: block !important;
            }

            .sidebar-wrapper.close_icon:has(.sidebar-main:hover) .logo-icon-wrapper {
                display: none !important;
            }

            .sidebar-wrapper.close_icon:has(.sidebar-main:hover) .sidebar-main-title {
                display: block !important;
            }

            .sidebar-wrapper.close_icon:has(.sidebar-main:hover) .sidebar-link span,
            .sidebar-wrapper.close_icon:has(.sidebar-main:hover) .according-menu {
                display: inline-block !important;
            }

            .sidebar-wrapper.close_icon:has(.sidebar-main:hover) .sidebar-list .sidebar-link {
                justify-content: flex-start;
                padding-left: 20px;
            }

            .sidebar-wrapper.close_icon:has(.sidebar-main:hover) .sidebar-list .sidebar-link svg {
                margin-right: 15px !important;
            }

            /* Adjust page body when sidebar is hidden */
            .sidebar-wrapper.close_icon~.page-body {
                margin-left: 80px !important;
            }

            /* Adjust header when sidebar is hidden */
            .page-header.close_icon {
                margin-left: 80px !important;
                width: calc(100% - 80px) !important;
            }
        }

        /* Base Logo Styles */
        .logo-wrapper {
            position: relative;
            overflow: hidden;
        }

        .logo-icon-wrapper {
            display: none;
            padding: 20px 0;
            text-align: center;
            width: 100%;
            position: absolute;
            top: 0;
            left: 0;
            z-index: 10;
        }

        /* Ensure the Navbar Toggle Button is visible when sidebar is closed */
        .page-header .header-logo-wrapper,
        .page-header .toggle-sidebar {
            display: flex !important;
            visibility: visible !important;
            opacity: 1 !important;
        }

        /* Modern Sidebar Enhancements - Pronounced Emerald Gradient */
        .sidebar-wrapper {
            background: linear-gradient(180deg, #0d9488 0%, #064e3b 50%, #022c22 100%) !important;
            /* Vibrant Teal to Deepest Emerald */
            box-shadow: 12px 0 35px rgba(0, 0, 0, 0.25) !important;
            border-right: 1px solid rgba(255, 255, 255, 0.1) !important;
            z-index: 99 !important;
        }

        /* Logo Visibility: Default logos work best on this dark teal */
        .logo-wrapper img,
        .logo-icon-wrapper img {
            filter: none !important;
            transition: all 0.3s ease;
        }

        .sidebar-main-title h6 {
            font-size: 11px !important;
            font-weight: 800 !important;
            color: rgba(204, 251, 241, 0.5) !important;
            /* Mint-light translucent */
            margin: 0;
        }

        .sidebar-link span {
            font-size: 14px;
            font-weight: 600 !important;
            color: rgba(255, 255, 255, 0.9) !important;
            /* White-ish text */
        }

        .sidebar-link svg {
            width: 18px !important;
            height: 18px !important;
            margin-right: 14px !important;
            stroke: rgba(255, 255, 255, 0.8) !important;
            stroke-width: 1.8px !important;
        }

        .sidebar-list.active {
            background: transparent !important;
        }

        .sidebar-link.active {
            background: rgba(255, 255, 255, 0.15) !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2) !important;
        }

        .sidebar-link.active span {
            color: #fbbf24 !important;
            /* Amber for active text */
            font-weight: 800 !important;
        }

        .sidebar-link.active svg {
            stroke: #fbbf24 !important;
            /* Amber for active icon */
            transform: scale(1.1);
        }

        .sidebar-link {
            border-radius: 10px !important;
            transition: all 0.3s ease !important;
        }

        .sidebar-link:hover {
            background: rgba(255, 255, 255, 0.1) !important;
            transform: translateX(5px) !important;
        }

        .sidebar-list:hover .sidebar-link span,
        .sidebar-link:hover span {
            color: #fbbf24 !important;
        }

        .sidebar-list:hover .sidebar-link svg,
        .sidebar-link:hover svg {
            stroke: #fbbf24 !important;
        }

        .sidebar-submenu li a {
            color: rgba(255, 255, 255, 0.7) !important;
            font-weight: 500 !important;
        }

        .sidebar-submenu li a:hover {
            color: #fbbf24 !important;
            /* Vibrant Amber Gold */
            padding-left: 20px !important;
        }

        /* Highlight active submenu links */
        .sidebar-submenu li.active a,
        .sidebar-submenu li a.active {
            color: #fbbf24 !important;
            font-weight: 700 !important;
            padding-left: 20px !important;
        }

        .sidebar-submenu li.active a::before,
        .sidebar-submenu li a.active::before {
            content: "•";
            margin-right: 8px;
            color: #fbbf24;
        }

        .toggle-sidebar i {
            color: #fff !important;
        }

        .sidebar-wrapper [data-feather] {
            stroke: #fff !important;
        }

        /* Layout & Spacing Tweaks */
        .sidebar-wrapper.close_icon .sidebar-list {
            margin: 4px 8px !important;
            display: flex !important;
            justify-content: center !important;
        }

        .sidebar-wrapper.close_icon .sidebar-link {
            padding: 6px !important;
            justify-content: center !important;
        }

        .sidebar-main {
            padding-top: 15px;
        }

        .sidebar-main-title {
            padding: 15px 25px 8px !important;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .sidebar-list {
            margin: 2px 12px !important;
            border-radius: 10px !important;
            position: relative;
            transition: all 0.4s ease;
        }

        .sidebar-list::before {
            content: '';
            position: absolute;
            left: -12px;
            top: 25%;
            height: 50%;
            width: 4px;
            background: #f59e0b;
            /* Amber Amber pop */
            border-radius: 0 4px 4px 0;
            opacity: 0;
            transition: all 0.3s ease;
        }

        .sidebar-list.active::before {
            opacity: 1;
            left: -12px;
        }

        .sidebar-list:hover {
            background: transparent !important;
            transform: none !important;
            box-shadow: none !important;
        }

        /* Fix: Ensure Loader covers EVERYTHING including the high z-index sidebar */
        .loader-wrapper {
            z-index: 999999 !important;
            background-color: #ffffff !important;
            position: fixed !important;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }
    </style>
</head>

<body>
    <!-- loader starts-->
    <div class="loader-wrapper">
        <div class="loader" style="display: flex; justify-content: center; align-items: center; height: 100%;">
            <img class="img-fluid" width="110" src="{{ asset('admin/assets/images/logo/svhe.png') }}" alt="">
        </div>
    </div>
    <!-- loader ends-->
    <div aria-live="polite" aria-atomic="true" class="position-relative">
        <div class="toast-container position-fixed bottom-0 end-0 p-3">
            <!-- Toasts will be appended here -->
        </div>
    </div>
    <!-- tap on top starts-->
    <div class="tap-top"><i data-feather="chevrons-up"></i></div>
    <!-- tap on tap ends-->
    <!-- page-wrapper Start-->
    <div class="page-wrapper compact-wrapper" id="pageWrapper">
        @include('partials.navbar')
        <!-- Page Body Start-->
        <div class="page-body-wrapper">
            @include('partials.sidebar')
            <div class="page-body d-flex flex-column">
                @yield('breadcrumb') {{-- Breadcrumb section --}}
                <div class="container-fluid flex-grow-1">
                    @yield('content')
                </div>
                @include('partials.footer')
            </div>
            <!-- Container-fluid Ends-->
        </div>
        <!-- login page start-->

        <!-- latest jquery-->
        <script
            src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>

        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
        {{--
        <script src="{{ asset('admin/assets/js/datatable/datatables/datatable.custom.js') }}"></script> --}}
        <!-- Bootstrap js-->
        <script src="{{ asset('admin/assets/js/bootstrap/bootstrap.bundle.min.js') }}"></script>
        <!-- feather icon js-->
        <script src="{{ asset('admin/assets/js/icons/feather-icon/feather.min.js') }}"></script>
        <script src="{{ asset('admin/assets/js/icons/feather-icon/feather-icon.js') }}"></script>
        <!-- scrollbar js-->
        <!-- Sidebar jquery-->
        <script src="{{ asset('admin/assets/js/config.js') }}"></script>
        <!-- Plugins JS start-->
        <!-- calendar js-->
        <!-- Plugins JS Ends-->
        <!-- Theme js-->
        <script src="{{ asset('admin/assets/js/script.js') }}"></script>
        <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
        <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
        <script type="text/javascript"
            src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
        <!-- scrollbar js-->
        <script src="{{ asset('admin/assets/js/scrollbar/simplebar.js') }}"></script>
        <script src="{{ asset('admin/assets/js/scrollbar/custom.js') }}"></script>
        <!-- Sidebar jquery-->
        <script src="{{ asset('admin/assets/js/sidebar-menu.js') }}"></script>
        <script src="{{ asset('admin/assets/js/sidebar-pin.js') }}"></script>
        <script src="{{ asset('admin/assets/js/slick/slick.min.js') }}"></script>
        <script src="{{ asset('admin/assets/js/slick/slick.js') }}"></script>
        <script src="{{ asset('admin/assets/js/header-slick.js') }}"></script>
        <script src="{{ asset('admin/assets/js/chart/apex-chart/apex-chart.js') }}"></script>
        <script src="{{ asset('admin/assets/js/chart/apex-chart/stock-prices.js') }}"></script>
        <!-- Range Slider js-->
        <script src="{{ asset('admin/assets/js/range-slider/rSlider.min.js') }}"></script>
        <script src="{{ asset('admin/assets/js/rangeslider/rangeslider.js') }}"></script>
        <script src="{{ asset('admin/assets/js/prism/prism.min.js') }}"></script>
        <script src="{{ asset('admin/assets/js/clipboard/clipboard.min.js') }}"></script>
        <script src="{{ asset('admin/assets/js/counter/jquery.waypoints.min.js') }}"></script>
        <script src="{{ asset('admin/assets/js/counter/jquery.counterup.min.js') }}"></script>
        <script src="{{ asset('admin/assets/js/counter/counter-custom.js') }}"></script>
        <script src="{{ asset('admin/assets/js/custom-card/custom-card.js') }}"></script>
        <!-- calendar js-->


        <script>
            // Global DataTables Defaults for PDF Export
            if ($.fn.dataTable && $.fn.dataTable.Buttons) {
                // Configure PDF button defaults globally
                $.extend(true, $.fn.dataTable.Buttons.defaults.buttons, {
                    pdf: {
                        orientation: 'landscape',
                        pageSize: 'A3',
                        exportOptions: {
                            columns: ':visible:not(.no-export):not(:last-child)' // Exclude hidden, no-export, and likely 'Actions'
                        },
                        customize: function (doc) {
                            doc.defaultStyle.fontSize = 7;
                            doc.styles.tableHeader.fontSize = 8;
                            doc.styles.tableHeader.alignment = 'left';
                            doc.content[1].table.widths = Array(doc.content[1].table.body[0].length).fill('*');

                            // Center the table
                            doc.content[1].margin = [0, 0, 0, 0];
                            doc.content[1].alignment = 'center';

                            // Style adjustments
                            var objLayout = {};
                            objLayout['hLineWidth'] = function (i) {
                                return .5;
                            };
                            objLayout['vLineWidth'] = function (i) {
                                return .5;
                            };
                            objLayout['hLineColor'] = function (i) {
                                return '#aaa';
                            };
                            objLayout['vLineColor'] = function (i) {
                                return '#aaa';
                            };
                            objLayout['paddingLeft'] = function (i) {
                                return 4;
                            };
                            objLayout['paddingRight'] = function (i) {
                                return 4;
                            };
                            doc.content[1].layout = objLayout;
                        }
                    }
                });

                // Also extend individual button types if they exist
                if ($.fn.dataTable.ext.buttons.pdfHtml5) {
                    $.extend(true, $.fn.dataTable.ext.buttons.pdfHtml5, {
                        orientation: 'landscape',
                        pageSize: 'A3',
                        exportOptions: {
                            columns: ':visible:not(.no-export):not(:last-child)'
                        },
                        customize: function (doc) {
                            doc.defaultStyle.fontSize = 7;
                            doc.styles.tableHeader.fontSize = 8;
                            doc.styles.tableHeader.alignment = 'left';
                            // Automatic width distribution
                            var colCount = doc.content[1].table.body[0].length;
                            doc.content[1].table.widths = Array(colCount).fill('*');
                        }
                    });
                }
            }
        </script>
        <script src="{{ asset('admin/assets/js/animation/wow/wow.min.js') }}"></script>
        <!-- Plugins JS Ends-->
        <!-- Theme js-->
        {{--
        <script src="{{ asset('admin/assets/js/theme-customizer/customizer.js') }}"></script> --}}
        <script>
            new WOW().init();
        </script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        @stack('scripts')
        <script>
            // Define toastContainer globally
            let globalToastContainer;

            // Define showToast globally
            function showToast(message, type) {
                if (!globalToastContainer) {
                    globalToastContainer = document.querySelector('.toast-container');
                }
                const toastHtml = `
            <div class="toast align-items-center text-white bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;
                const toastElement = document.createRange().createContextualFragment(toastHtml).children[0];
                globalToastContainer.append(toastElement);
                const toast = new bootstrap.Toast(toastElement);
                toast.show();
            }

            let globalTimerInterval;
            let globalTimerSeconds = 0;

            window.updateGlobalTimer = function () {
                $('#global-task-timer').slideDown(); // Show the spinner immediately
                $.ajax({
                    url: "{{ route('tasks.globalTimerStatus') }}",
                    method: 'GET',
                    success: function (response) {
                        if (response.active && response.status === 'in_progress') {
                            globalTimerSeconds = Math.floor(response.elapsed_time);
                            clearInterval(globalTimerInterval);
                            // Initial display
                            let initialHours = Math.floor(globalTimerSeconds / 3600).toString().padStart(2,
                                '0');
                            let initialMinutes = Math.floor((globalTimerSeconds % 3600) / 60).toString()
                                .padStart(2, '0');
                            let initialSeconds = (globalTimerSeconds % 60).toString().padStart(2, '0');
                            $('#timer-display').html(`${initialHours}:${initialMinutes}:${initialSeconds}`);

                            globalTimerInterval = setInterval(function () {
                                globalTimerSeconds++;
                                let hours = Math.floor(globalTimerSeconds / 3600).toString().padStart(2,
                                    '0');
                                let minutes = Math.floor((globalTimerSeconds % 3600) / 60).toString()
                                    .padStart(2, '0');
                                let seconds = (globalTimerSeconds % 60).toString().padStart(2, '0');
                                $('#timer-display').html(`${hours}:${minutes}:${seconds}`);
                            }, 1000);
                        } else {
                            $('#global-task-timer').slideUp();
                            clearInterval(globalTimerInterval);
                        }
                    },
                    error: function () {
                        $('#global-task-timer').slideUp(); // Hide on error as well
                    }
                });
            }

            document.addEventListener('DOMContentLoaded', function () {
                // Check for session messages and display toasts
                @if(session('success'))
                    showToast("{{ session('success') }}", 'success');
                @endif

                @if(session('error'))
                    showToast("{{ session('error') }}", 'danger');
                @endif

                window.updateGlobalTimer(); // Initial call on DOMContentLoaded
            });
        </script>
        <script>
            $(document).ready(function () {
                // Smooth Logo Swap Animation using jQuery
                $(document).on('click', '.toggle-sidebar', function () {
                    const $wrapper = $('.sidebar-wrapper');
                    const $fullLogo = $('.logo-wrapper a');
                    const $iconLogo = $('.logo-icon-wrapper');

                    // Small delay to let the theme toggle the class first
                    setTimeout(() => {
                        const isClosed = $wrapper.hasClass('close_icon');

                        if (isClosed) {
                            $fullLogo.stop(true, true).animate({
                                opacity: 0
                            }, 200, function () {
                                $(this).hide();
                                $iconLogo.stop(true, true).css('opacity', 0).show().animate({
                                    opacity: 1
                                }, 400);
                            });
                        } else {
                            $iconLogo.stop(true, true).animate({
                                opacity: 0
                            }, 200, function () {
                                $(this).hide();
                                $fullLogo.stop(true, true).css('opacity', 0).show().animate({
                                    opacity: 1
                                }, 400);
                            });
                        }
                    }, 50);
                });

                var currentUrl = window.location.href;

                // Function to set active state
                function setActiveSidebarLink() {
                    var currentPath = window.location.pathname;
                    var currentUrl = window.location.href;
                    var $activeLink = null;

                    $('.sidebar-links a').each(function () {
                        var $this = $(this);
                        var linkHref = $this.attr('href');

                        if (!linkHref || linkHref === '#') return;

                        var isActive = false;

                        // Case 1: Dashboard with specific dealership_id
                        if (currentUrl.includes('dashboard?dealership_id=') && linkHref.includes('dashboard?dealership_id=')) {
                            if (currentUrl === linkHref) {
                                isActive = true;
                            }
                        }
                        // Case 2: General Dashboard (no dealership_id)
                        else if (currentUrl.includes('dashboard') && !currentUrl.includes('dealership_id=') && linkHref.endsWith('dashboard')) {
                            isActive = true;
                        }
                        // Case 3: Other routes
                        else if (!currentUrl.includes('dashboard') && currentUrl.includes(linkHref) && linkHref.length > 1) {
                            isActive = true;
                        }

                        if (isActive) {
                            $('.sidebar-links a').removeClass('active');
                            $('.sidebar-links li').removeClass('active');

                            $this.addClass('active');
                            $this.closest('li').addClass('active');
                            $activeLink = $this;

                            var $parentUl = $this.closest('ul.sidebar-submenu');
                            if ($parentUl.length > 0) {
                                $parentUl.addClass('d-block');
                                $parentUl.closest('li.sidebar-list').addClass('active');
                                $parentUl.closest('li.sidebar-list').find('a.sidebar-title').addClass('active');
                            }
                            return false; // break loop
                        }
                    });

                    if ($activeLink) {
                        setTimeout(function () {
                            $activeLink[0].scrollIntoView({
                                behavior: 'smooth',
                                block: 'center'
                            });
                        }, 500);
                    }
                }

                // Call the function on page load
                setActiveSidebarLink();

                // Global fix for 'dim effect' (stuck modal backdrop) on exports
                $(document).on('submit', 'form', function () {
                    const $form = $(this);
                    // Check if it's a GET request (download/export) inside a modal
                    if ($form.closest('.modal').length > 0 && ($form.attr('method') === 'GET' || $form.attr('method') === 'get')) {
                        const modalEl = $form.closest('.modal')[0];
                        const modal = bootstrap.Modal.getInstance(modalEl);

                        // Wait for the request to initiate (200ms) before hiding to avoid cancelling it
                        // This ensures the download request is fully sent to the browser
                        setTimeout(() => {
                            if (modal) {
                                modal.hide();
                            } else {
                                $(modalEl).modal('hide');
                            }

                            // Force cleanup of sticky backdrops
                            $('.modal-backdrop').remove();
                            $('body').removeClass('modal-open').css('overflow', '');
                            $('body').css('padding-right', '');
                        }, 200);
                    }
                });
            });
        </script>

        @yield('modal')
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            function setSidebarHeight() {
                const sidebarMenu = document.getElementById('sidebar-menu');
                if (!sidebarMenu) return;

                const footer = document.querySelector('.footer');
                const footerHeight = footer ? footer.offsetHeight : 0;

                // Calculate starts from the actual position of the menu
                const menuOffsetTop = sidebarMenu.getBoundingClientRect().top;
                const availableHeight = window.innerHeight - menuOffsetTop - footerHeight - 20; // 20px padding buffer

                sidebarMenu.style.maxHeight = `${availableHeight}px`;
            }

            setSidebarHeight();
            window.addEventListener('resize', setSidebarHeight);
        });
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>

    @include('partials.smart-search')
</body>

</html>