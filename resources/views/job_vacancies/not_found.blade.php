<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('admin/assets/images/favicon.png') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('admin/assets/images/favicon.png') }}" type="image/x-icon">
    <title>Job Not Found | Careers</title>

    <!-- Google font-->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@200;300;400;500;600;700;800&amp;display=swap" rel="stylesheet">

    <link rel="stylesheet" type="text/css" href="{{ asset('admin/assets/css/font-awesome.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/assets/css/vendors/icofont.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/assets/css/vendors/themify.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/assets/css/vendors/flag-icon.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/assets/css/vendors/feather-icon.css') }}">

    <!-- Bootstrap css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/assets/css/vendors/bootstrap.css') }}">

    <!-- App css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/assets/css/style.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/assets/css/responsive.css') }}">

    <style>
        /* Custom tweaks for public view */
        body {
            background-color: #f6f7fb;
        }

        .page-wrapper.compact-wrapper .page-body-wrapper .page-body {
            margin-left: 0 !important;
            padding-top: 0;
        }

        .job-card-container {
            margin-top: 50px;
            position: relative;
            z-index: 10;
        }

        .job-card {
            border: none;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.08);
            border-radius: 16px;
            overflow: hidden;
            background: #ffffff;
            text-align: center;
            padding: 50px 20px;
        }

        .error-icon {
            font-size: 80px;
            color: var(--theme-deafult);
            margin-bottom: 25px;
            display: inline-block;
            background: rgba(115, 102, 255, 0.1);
            /* Keep generic light bg or match theme light */
            padding: 30px;
            border-radius: 50%;
        }

        .btn-home {
            background: linear-gradient(135deg, var(--theme-deafult) 0%, var(--theme-secondary) 100%);
            border: none;
            color: white !important;
            padding: 14px 45px;
            font-weight: 600;
            font-size: 16px;
            border-radius: 8px;
            letter-spacing: 0.5px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-home:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.15);
            background: linear-gradient(135deg, var(--theme-deafult) 0%, var(--theme-secondary) 100%);
            text-decoration: none;
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
    <!-- Tap on Top -->
    <div class="tap-top"><i data-feather="chevrons-up"></i></div>

    <!-- Page Wrapper -->
    <div class="page-wrapper compact-wrapper" id="pageWrapper">



        <!-- Page Body Start -->
        <div class="page-body-wrapper horizontal-menu">
            <div class="page-body">
                <div class="container-fluid">
                    <div class="row justify-content-center">
                        <div class="col-sm-12 col-md-8 col-lg-6 job-card-container">
                            <div class="card job-card">
                                <div class="card-header border-0 pb-0 bg-transparent">
                                    <div class="text-center mb-0 mt-4">
                                        <a href="/" class="d-inline-block transition-hover">
                                            <img class="img-fluid" src="{{ asset('admin/assets/images/logo/svhe.png') }}" alt="logo" style="max-height: 50px;">
                                        </a>
                                    </div>
                                </div>
                                <div class="card-body pt-2 text-center">
                                    <div class="mb-4">
                                        <div class="error-icon">
                                            <i data-feather="search" style="width: 60px; height: 60px;"></i>
                                        </div>
                                    </div>

                                    <h2 class="mb-3 fw-bold text-dark">Job Vacancy Not Found</h2>
                                    <p class="text-muted mb-5 lead" style="font-size: 16px;">
                                        The job vacancy you are looking for might have been removed, closed, or the link is incorrect.
                                    </p>

                                    <div>
                                        <a href="/" class="btn-home">
                                            <i data-feather="home" class="me-2"></i> Back to Careers
                                        </a>
                                    </div>
                                </div>
                                <div class="card-footer bg-dark text-center py-3">
                                    <div class="mb-2">
                                        <img src="{{ asset('admin/assets/images/logo/korps-sync-crm-logo-white.png') }}" alt="Korps" style="height: 30px; opacity: 0.9;">
                                    </div>
                                    <p class="mb-0 text-white-50 f-12">&copy; {{ date('Y') }} Logiprompt. All rights reserved.</p>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- latest jquery-->
    <script src="{{ asset('admin/assets/js/jquery-3.5.1.min.js') }}"></script>
    <!-- Bootstrap js-->
    <script src="{{ asset('admin/assets/js/bootstrap/bootstrap.bundle.min.js') }}"></script>
    <!-- feather icon js-->
    <script src="{{ asset('admin/assets/js/icons/feather-icon/feather.min.js') }}"></script>
    <script src="{{ asset('admin/assets/js/icons/feather-icon/feather-icon.js') }}"></script>
    <!-- Theme js-->
    <script src="{{ asset('admin/assets/js/script.js') }}"></script>
</body>

</html>