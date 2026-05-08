@extends('layouts.admin')

@section('title', 'Brand Settings')

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3>Brand Settings</h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"> <i data-feather="home"></i></a>
                    </li>
                    <li class="breadcrumb-item">Settings</li>
                    <li class="breadcrumb-item active">Brand Settings</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<!-- Container-fluid starts-->
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h5>Brand Settings Management</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="dealershipSelect" class="form-label">Select Dealership</label>
                        <select class="form-select" id="dealershipSelect" onchange="handleDealershipChange(this.value)">
                            <option value="">-- Select a Dealership --</option>
                            @foreach ($dealerships as $dealership)
                            <option value="{{ $dealership->id }}" {{ $selectedDealershipId == $dealership->id ?'selected':'' }}>{{ $dealership->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div id="brandSettingsDisplay" class="mt-4">
                        <p>Select a dealership to view its brand settings.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Container-fluid Ends-->
@endsection

@push('scripts')
<style>
    .toast.bg-success {
        background-color: #28a745 !important;
        /* Bootstrap's default success green */
    }
</style>
<script>
    const WEB_BASE_URL = '{{ url('/') }}';

    function showToast(type, message) {
        // Assuming a global showToast function exists for notifications
        // Replace with your actual toast notification logic if different

        // Example: You might use a library like Toastr or SweetAlert2
        // toastr[type](message);
    }

    async function fetchDealershipSettings(dealershipId) {
        const brandSettingsDisplay = document.getElementById('brandSettingsDisplay');
        if (!dealershipId) {
            brandSettingsDisplay.innerHTML = '<p>Select a dealership to view its brand settings.</p>';
            return;
        }

        brandSettingsDisplay.innerHTML = '<p>Loading settings...</p>';

        try {
            const response = await fetch(`${WEB_BASE_URL}/brand-settings/${dealershipId}/settings`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}' // Include CSRF token if your API uses web middleware
                }
            });

            if (!response.ok) {
                // Attempt to read response as text to get more details if it's not JSON
                const errorText = await response.text();
                console.error('API response not OK:', response.status, errorText);
                throw new Error(`HTTP error! status: ${response.status}, response: ${errorText}`);
            }

            const data = await response.json();
            renderSettings(dealershipId, data.setting, data.dealershipSetting);

        } catch (error) {
            console.error('Error fetching dealership settings:', error);
            brandSettingsDisplay.innerHTML = '<p class="text-danger">Failed to load settings. Please try again.</p>';
            showToast('Failed to load settings.', 'error');
        }
    }

    function renderSettings(dealershipId, setting, dealershipSetting) {
        const brandSettingsDisplay = document.getElementById('brandSettingsDisplay');
        if (!setting) {
            brandSettingsDisplay.innerHTML = '<p>No settings found for this dealership.</p>';
            return;
        }

        const isChecked = dealershipSetting && dealershipSetting.enabled ? 'checked' : '';

        brandSettingsDisplay.innerHTML = `
            <div class="row">
                <div class="col-12 mb-3"><h6>Settings for Dealership ${dealershipId}</h6></div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="enabled" value="1" id="task_continuation_approval"
                                    data-dealership-id="${dealershipId}"
                                    data-setting-id="${setting.id}"
                                    ${isChecked}
                                    onchange="updateDealershipSetting(this)">
                                <label class="form-check-label" for="task_continuation_approval">${setting.name}</label>
                                <p class="text-muted">${setting.description}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    function handleDealershipChange(dealershipId) {
        fetchDealershipSettings(dealershipId);
    }

    function updateDealershipSetting(checkbox) {
        const dealershipId = checkbox.dataset.dealershipId;
        const settingId = checkbox.dataset.settingId;
        const enabled = checkbox.checked ? 1 : 0;

        fetch(`${WEB_BASE_URL}/update-setting`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    dealership_id: dealershipId,
                    setting_id: settingId,
                    enabled: enabled
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                } else {
                    showToast(data.message, 'error');
                    // Revert checkbox state if update failed
                    checkbox.checked = !checkbox.checked;
                }
            })
            .catch(error => {
                console.error('Network error:', error);
                showToast('error', 'Failed to update setting due to network error.');
                // Revert checkbox state on network error
                checkbox.checked = !checkbox.checked;
            });
    }

    // Initial load: if a dealership is already selected (e.g., from a previous redirect or URL), load its settings
    document.addEventListener('DOMContentLoaded', () => {
        const selectedDealershipId = document.getElementById('dealershipSelect').value;
        if (selectedDealershipId) {
            fetchDealershipSettings(selectedDealershipId);
        }
    });
</script>
@endpush