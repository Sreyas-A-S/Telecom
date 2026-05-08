@extends('layouts.admin')

@section('title', 'Organization Hierarchy')

@push('styles')
<style>
    .iframe-wrapper {
        position: relative;
        /* Needed for positioning the loader */
        width: 100%;
        height: 75vh;
        background: #f8f9fa;
    }

    .loader-container {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        z-index: 10;
        background: rgba(255, 255, 255, 0.95);
        /* Semi-transparent white overlay */
        transition: opacity 0.3s ease-out;
    }

    .spinner {
        border: 6px solid #f3f3f3;
        /* Light grey */
        border-top: 6px solid #3498db;
        /* Blue */
        border-radius: 50%;
        width: 60px;
        height: 60px;
        animation: spin 1s linear infinite;
    }

    .loading-text {
        margin-top: 20px;
        color: #555;
        font-size: 16px;
        font-weight: 500;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h1 class="h4 mb-0">Organization</h1>
            <button id="fullscreen-btn" class="btn btn-sm btn-outline-secondary">Fullscreen</button>
        </div>
        <div class="card-body">
            <div class="iframe-wrapper">
                <div id="iframe-loader" class="loader-container">
                    <div class="spinner"></div>
                    <div class="loading-text">Loading organization chart...</div>
                </div>
                <iframe id="orgchart-iframe" src="{{ route('organization.embed') }}" title="Organization Chart" style="width: 100%; height: 75vh; border: 0;"></iframe>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const fullscreenBtn = document.getElementById('fullscreen-btn');
        const iframe = document.getElementById('orgchart-iframe');
        const loader = document.getElementById('iframe-loader');
        let chartReady = false;

        function hideLoader() {
            if (loader && !chartReady) {
                chartReady = true;
                loader.style.opacity = '0';
                setTimeout(() => loader.style.display = 'none', 300);
            }
        }

        // Fallback: Hide loader after 1.5 seconds even if no message received
        setTimeout(hideLoader, 1500);

        // Listen for message from iframe
        window.addEventListener('message', function(event) {
            if (event.data === 'orgChartReady') {

                hideLoader();
            }
        });

        if (fullscreenBtn && iframe) {
            fullscreenBtn.addEventListener('click', function() {
                if (document.fullscreenElement) {
                    document.exitFullscreen();
                } else {
                    iframe.requestFullscreen().catch(err => {
                        alert(`Error attempting to enable full-screen mode: ${err.message} (${err.name})`);
                    });
                }
            });

            document.addEventListener('fullscreenchange', function() {
                if (document.fullscreenElement) {
                    fullscreenBtn.textContent = 'Exit Fullscreen';
                } else {
                    fullscreenBtn.textContent = 'Fullscreen';
                }
            });
        }
    });
</script>
@endpush