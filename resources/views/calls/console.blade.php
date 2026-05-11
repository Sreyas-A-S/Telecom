<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Call Console | {{ config('app.name') }}</title>
    
    <!-- Google font-->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Bootstrap css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/assets/css/vendors/bootstrap.css') }}">
    <!-- App css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/assets/css/style.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/assets/css/responsive.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/assets/css/vendors/icofont.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/assets/css/vendors/fontawesome.css') }}">

    <style>
        body {
            background-color: #f8f9fd;
            font-family: 'Outfit', sans-serif;
            overflow: hidden;
        }
        .console-container {
            height: 100vh;
            display: flex;
            flex-direction: column;
            padding: 20px;
        }
        .status-card {
            border-radius: 20px;
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            background: #fff;
            padding: 25px;
            text-align: center;
            margin-bottom: 20px;
        }
        .status-dot {
            width: 15px;
            height: 15px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }
        #availability-toggle-btn {
            border-radius: 12px;
            padding: 12px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        .active-call-panel {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            padding: 30px;
            display: none; /* Hidden by default */
        }
        .call-avatar {
            width: 100px;
            height: 100px;
            background: #f0f0f0;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 20px;
            font-size: 40px;
            color: #7366ff;
            border: 4px solid #f8f9fd;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .pulse-animation {
            animation: pulse-blue 2s infinite;
        }
        @keyframes pulse-blue {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(115, 102, 255, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 15px rgba(115, 102, 255, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(115, 102, 255, 0); }
        }
        .btn-hangup {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: #ff5252;
            color: white;
            border: none;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 24px;
            margin-top: 30px;
            box-shadow: 0 10px 20px rgba(255, 82, 82, 0.3);
            transition: all 0.3s ease;
        }
        .btn-hangup:hover {
            transform: scale(1.1);
            background: #ff1a1a;
        }
        .idle-panel {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: #ccc;
        }
    </style>
</head>
<body>

    <div class="console-container">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0 f-w-700 text-primary">Call Console</h5>
            <img src="{{ asset('admin/assets/images/logo/logo-icon.png') }}" alt="Logo" style="width: 30px;">
        </div>

        <!-- Status Card -->
        <div class="status-card">
            <div class="mb-3 d-flex align-items-center justify-content-center">
                <span id="agent-status-dot" class="status-dot bg-secondary"></span>
                <span id="agent-status-text" class="f-w-600">Offline</span>
                <div id="agent-status-loader" class="spinner-border spinner-border-sm text-primary ms-2" role="status" style="display: none;"></div>
            </div>
            
            <button id="availability-toggle-btn" class="btn btn-success w-100" onclick="window.exotelService.toggleAvailability()">
                <span id="btn-loader" class="spinner-border spinner-border-sm me-2" role="status" style="display: none;"></span>
                <span id="btn-text">Go Online</span>
            </button>
        </div>

        <!-- Active Call Panel -->
        <div id="exotel-active-call-bar" class="active-call-panel">
            <div class="call-avatar pulse-animation">
                <i class="icofont icofont-ui-call"></i>
            </div>
            <h4 class="f-w-700 mb-1" id="active-call-number">Unknown</h4>
            <p class="text-muted">On Call...</p>
            
            <button class="btn-hangup" onclick="window.exotelService.endCall()">
                <i class="icofont icofont-ui-close"></i>
            </button>
        </div>

        <!-- Idle Panel -->
        <div id="idle-panel" class="idle-panel">
            <i class="icofont icofont-headphone-alt" style="font-size: 60px; opacity: 0.2; margin-bottom: 15px;"></i>
            <p>Ready for calls</p>
        </div>
    </div>

    <!-- Modals (Ringing) -->
    <div class="modal fade" id="exotel-ringing-modal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0" style="border-radius: 25px; overflow: hidden;">
                <div class="modal-body text-center p-5">
                    <div class="call-avatar pulse-animation mx-auto" style="width: 80px; height: 80px;">
                        <i class="icofont icofont-ui-call"></i>
                    </div>
                    <h5 class="mb-1">Incoming Call</h5>
                    <h3 class="f-w-700 mb-4" id="caller-number">Unknown</h3>
                    
                    <div class="d-flex justify-content-center gap-4 mt-4">
                        <button class="btn btn-success btn-lg px-5 py-3" style="border-radius: 15px;" onclick="window.exotelService.answerCall()">
                            <i class="icofont icofont-ui-call me-2"></i>Answer
                        </button>
                        <button class="btn btn-danger btn-lg px-4 py-3" style="border-radius: 15px;" onclick="window.exotelService.closeRingingUI()">
                            Ignore
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('admin/assets/js/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('admin/assets/js/bootstrap/bootstrap.bundle.min.js') }}"></script>
    
    <!-- Exotel Service -->
    <script src="{{ asset('admin/assets/js/exotel-service.js') }}"></script>
    
    <script>
        $(document).ready(function() {
            // Overwrite updateUIStatus to handle local panels
            const originalUpdateUIStatus = window.exotelService.updateUIStatus;
            window.exotelService.updateUIStatus = function(status) {
                originalUpdateUIStatus.call(this, status);
                
                if (status === 'busy') {
                    $('#exotel-active-call-bar').css('display', 'flex');
                    $('#idle-panel').hide();
                } else {
                    $('#exotel-active-call-bar').hide();
                    $('#idle-panel').show();
                }
            };
        });
    </script>
</body>
</html>
