@extends('layouts.admin')

@section('title', 'FSR Quotations')

@section('breadcrumb')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                @isset($task)
                <h3>FSR Parts Quotation Review for Task #{{ $task->id }}</h3>
                @else
                <h3>FSR Parts Quotation</h3>
                @endisset
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"> <i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item active">FSR Quotations</li>
                </ol>
            </div>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">
    @isset($task)
    <div class="card mb-3">
        <div class="card-header">Task Details</div>
        <div class="card-body">
            <p><strong>Title:</strong> {{ $task->title }}</p>
            <p><strong>Description:</strong> {{ $task->description }}</p>
        </div>
    </div>
    @endisset

    <ul class="nav nav-tabs" id="fsrQuotationsTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="datatable-tab" data-bs-toggle="tab" data-bs-target="#datatable-pane" type="button" role="tab" aria-controls="datatable-pane" aria-selected="true">Quotations Overview</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="details-tab" data-bs-toggle="tab" data-bs-target="#details-pane" type="button" role="tab" aria-controls="details-pane" aria-selected="false" disabled>FSR Report Details</button>
        </li>
    </ul>
    <div class="tab-content" id="fsrQuotationsTabContent">
        <div class="tab-pane fade show active" id="datatable-pane" role="tabpanel" aria-labelledby="datatable-tab" tabindex="0">
            <div class="card mt-3">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="fsr-quotations-review-table">
                            <thead>
                                <tr>
                                    <th>Sl No</th>
                                    <th>Task Name</th>
                                    <th>Submitted By</th>
                                    <th>Quotations</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data will be loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="details-pane" role="tabpanel" aria-labelledby="details-tab" tabindex="0">
            <div class="card mt-3">
                <div class="card-header">
                    <a href="javascript:void(0)" id="export-fsr-pdf" class="btn btn-sm btn-danger float-end me-2" target="_blank">
                        <i class="fa fa-file-pdf-o"></i> Export PDF
                    </a>
                    <button type="button" class="btn-close float-end" aria-label="Close" id="close-fsr-details"></button>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Task Title:</strong> <span id="fsr-task-title"></span></p>
                            <p><strong>Submitted By:</strong> <span id="fsr-submitted-by"></span></p>
                            <p><strong>Report Date:</strong> <span id="fsr-report-date"></span></p>
                            <p><strong>Payment Status:</strong> <span id="fsr-payment-status"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>On-site Assessment:</strong> <span id="fsr-on-site-assessment"></span></p>
                            <p><strong>Analysis of Cause:</strong> <span id="fsr-analysis-of-cause"></span></p>
                            <p><strong>Actions Taken:</strong> <span id="fsr-actions-taken"></span></p>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between">
                        <h5>Quotations</h5>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="enable-editing-switch">
                            <label class="form-check-label" for="enable-editing-switch">Enable Editing</label>
                        </div>
                    </div>
                    <form id="fsr-quotations-form">
                        <input type="hidden" id="fsr-report-id" name="fsr_report_id">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="fsr-quotations-list">
                                <thead>
                                    <tr>
                                        <th>Part</th>
                                        <th>Quoted Quantity</th>
                                        <th>Price</th>
                                        <th>Approved Quantity</th>
                                        <th>Approved Total Price</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Quotations will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                        <button type="submit" class="btn btn-primary mt-3" id="save-approved-quantities">Save Approved Quantities</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        function showToast(message, type) {
            var toastContainer = $('#toast-container');
            if (toastContainer.length === 0) {
                toastContainer = $(
                    '<div id="toast-container" class="position-fixed top-0 end-0 p-3" style="z-index: 1050;"></div>'
                );
                $('body').append(toastContainer);
            }

            var toastClass = '';
            switch (type) {
                case 'success':
                    toastClass = 'text-bg-success';
                    break;

                case 'offline':
                    toastClass = 'text-bg-danger';
                    break;
                case 'busy':

                    toastClass = 'text-bg-warning';
                    break;
                default:
                    toastClass = 'text-bg-primary';
            }

            var toastId = 'toast-' + Date.now();
            var toastHtml = `
                    <div id="${toastId}" class="toast align-items-center ${toastClass} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="d-flex">
                            <div class="toast-body">
                                ${message}
                            </div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                    </div>
                `;

            toastContainer.append(toastHtml);
            var toastEl = new bootstrap.Toast(document.getElementById(toastId));
            toastEl.show();
        }

        function formatPaymentStatus(status) {

            let badgeClass = '';
            let displayText = '';
            const lowerCaseStatus = status ? status.toLowerCase() : 'unknown';

            switch (lowerCaseStatus) {
                case 'pending':
                    badgeClass = 'warning';
                    displayText = 'Pending';
                    break;
                case 'paid':
                    badgeClass = 'success';
                    displayText = 'Paid';
                    break;
                case 'partially_paid':
                    badgeClass = 'info';
                    displayText = 'Partially Paid';
                    break;
                default:
                    badgeClass = 'secondary';
                    displayText = 'Unknown';
            }
            return `<span class="badge bg-${badgeClass}">${displayText}</span>`;
        }

        var ajaxUrl;
        @isset($task)
        ajaxUrl = "{{ route('fsr-quotations.review.data',['task' => $task->id]) }}";
        @else
        ajaxUrl = "{{ route('fsr-quotations.review.data') }}";
        @endisset

        // Use Bootstrap 5 styling and layout
        var fsrQuotationsReviewTable = $('#fsr-quotations-review-table').DataTable({
            processing: true,
            serverSide: true,
            dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f><'col-sm-12 col-md-6 text-end'B>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            buttons: [{
                    extend: 'csv',
                    className: 'btn btn-sm btn-primary text-white'
                },
                {
                    extend: 'excel',
                    className: 'btn btn-sm btn-success text-white'
                },
                {
                    extend: 'pdf',
                    className: 'btn btn-sm btn-danger text-white'
                },
                {
                    extend: 'print',
                    className: 'btn btn-sm btn-info text-white'
                }
            ],
            ajax: {
                url: ajaxUrl,
                data: function(d) {
                    // d.status = 'pending'; // Filter for pending quotations
                }
            },
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'task_title',
                    name: 'task_title'
                },
                {
                    data: 'submitted_by',
                    name: 'submitted_by'
                },
                {
                    data: 'quotations',
                    name: 'quotations',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'overall_status',
                    name: 'overall_status',
                    orderable: false,
                    searchable: false
                },
                {
                    data: null,
                    name: 'actions',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        let actionsHtml = '';
                        // View button for the FSR Report
                        actionsHtml += `<button class="btn btn-sm btn-info view-fsr-report-btn me-1" data-id="${row.id}"><i class="fa fa-eye"></i> View</button>`;

                        // PDF Export button
                        actionsHtml += `<a href="/fsr-quotations/${row.id}/export-pdf" class="btn btn-sm btn-danger me-1" target="_blank"><i class="fa fa-file-pdf-o"></i> PDF</a>`;

                        // Approve/Reject buttons for each quotation
                        if (row.partQuotations && row.partQuotations.length > 0) {
                            row.partQuotations.forEach(function(quotation) {
                                if (quotation.status === 'pending') {
                                    actionsHtml += `<button class="btn btn-sm btn-success approve-quotation-btn me-1" data-id="${quotation.id}"><i class="fa fa-check"></i> Approve</button>`;
                                    actionsHtml += `<button class="btn btn-sm btn-warning reject-quotation-btn" data-id="${quotation.id}"><i class="fa fa-times"></i> Reject</button>`;
                                }
                            });
                        }
                        return actionsHtml;
                    }
                },
            ]
        });

        function getStatusBadgeClass(status) {
            switch (status) {
                case 'pending':
                    return 'warning';
                case 'approved':
                    return 'success';
                case 'rejected':
                    return 'danger';
                default:
                    return 'secondary';
            }
        }

        // Event listeners for Approve/Reject buttons
        $(document).on('click', '.approve-quotation-btn', function() {
            const quotationId = $(this).data('id');
            if (confirm('Are you sure you want to approve this quotation?')) {
                $.ajax({
                    url: `/api/fsr-quotations/${quotationId}/approve`,
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        showToast(response.message, 'success');
                        fsrQuotationsReviewTable.ajax.reload(); // Reload DataTable
                    },
                    error: function(xhr) {
                        showToast('Error approving quotation: ' + (xhr.responseJSON
                            .message || ''), 'danger');
                    }
                });
            }
        });

        $(document).on('click', '.reject-quotation-btn', function() {
            const quotationId = $(this).data('id');
            if (confirm('Are you sure you want to reject this quotation?')) {
                $.ajax({
                    url: `/api/fsr-quotations/${quotationId}/reject`,
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        showToast(response.message, 'success');
                        fsrQuotationsReviewTable.ajax.reload(); // Reload DataTable
                    },
                    error: function(xhr) {
                        showToast('Error rejecting quotation: ' + (xhr.responseJSON
                            .message || ''), 'danger');
                    }
                });
            }
        });

        // Event listener for View FSR Report button
        $(document).on('click', '.view-fsr-report-btn', function() {
            const fsrReportId = $(this).data('id');
            // Activate the details tab
            $('#datatable-pane').removeClass('show active');
            $('#details-pane').addClass('show active');
            $('#datatable-tab').removeClass('active');
            $('#details-tab').addClass('active').prop('disabled', false);

            // Fetch FSR Report details via AJAX
            $.ajax({
                url: `{{ route('fsr-reports.details', ['fsrReport' => '__FSR_REPORT_ID__']) }}`.replace('__FSR_REPORT_ID__', fsrReportId),
                method: 'GET',
                success: function(response) {
                    // Update PDF export link
                    $('#export-fsr-pdf').attr('href', `/fsr-quotations/${response.id}/export-pdf`);

                    // Populate FSR Report details
                    $('#fsr-report-id').val(response.id);
                    $('#fsr-task-title').text(response.task ? response.task.title : 'N/A');
                    $('#fsr-submitted-by').text(response.submitted_by ? response.submitted_by.name : 'N/A');
                    $('#fsr-report-date').text(response.created_at ? new Date(response.created_at).toLocaleDateString() : 'N/A');
                    $('#fsr-on-site-assessment').text(response.on_site_assessment || 'N/A');
                    $('#fsr-analysis-of-cause').text(response.analysis_of_cause || 'N/A');
                    $('#fsr-actions-taken').text(response.actions_taken || 'N/A');
                    $('#fsr-payment-status').html(formatPaymentStatus(response.payment_status));

                    // Populate quotations with editable approved_quantity
                    let quotationsListHtml = '';
                    let allEditable = false;
                    response.part_quotations.forEach(function(quotation) {
                        const partName = quotation.part ? quotation.part.part_number : 'N/A';
                        const quotedQuantity = quotation.quoted_quantity;
                        const approvedQuantity = quotation.approved_quantity !== null ? quotation.approved_quantity : quotedQuantity; // Default to quoted if not approved
                        const isEditable = quotation.status === 'pending';
                        if (isEditable) allEditable = true;

                        let statusBadgeClass = '';
                        switch (quotation.status) {
                            case 'Approved':
                                statusBadgeClass = 'success';
                                break;
                            case 'Pending':
                                statusBadgeClass = 'warning';
                                break;
                            case 'Rejected':
                                statusBadgeClass = 'danger';
                                break;
                            default:
                                statusBadgeClass = 'secondary';
                        }
                        const statusBadge = `<span class="badge bg-${statusBadgeClass}">${quotation.status}</span>`;

                        quotationsListHtml += `
                                <tr>
                                    <td>${partName}</td>
                                    <td>${quotedQuantity}</td>
                                    <td>${quotation.quoted_unit_price}</td>
                                    <td>
 
                                        <input type="number" class="form-control form-control-sm approved-quantity-input" 
                                               id="approved_quantity_${quotation.id}" 
                                               name="quotations[${quotation.id}][approved_quantity]" 
                                               value="${approvedQuantity}" 
                                               min="0" style="width: 80px;" data-stock-quantity="${quotation.part.stock_quantity}" data-quoted-unit-price="${quotation.quoted_unit_price}"
                                               ${!isEditable ? 'disabled' : ''}>
                                    </td>
                                    <td class="approved-total-price">${(approvedQuantity * quotation.quoted_unit_price).toFixed(2)}</td>
                                    <td>${statusBadge}</td>
                                </tr>
                            `;
                    });
                    $('#fsr-quotations-list tbody').html(quotationsListHtml);

                    // Initially disable all inputs and hide the save button
                    $('.approved-quantity-input').prop('disabled', true);
                    $('#save-approved-quantities').hide();
                    $('#enable-editing-switch').prop('checked', false); // Ensure switch is off by default

                    $('#enable-editing-switch').off('change').on('change', function() {
                        if ($(this).is(':checked')) {
                            $('.approved-quantity-input').prop('disabled', false);
                            $('#save-approved-quantities').show();
                        } else {
                            $('.approved-quantity-input').prop('disabled', true);
                            $('#save-approved-quantities').hide();
                        }
                    });

                    fsrQuotationsReviewTable.ajax.reload();
                },
                error: function(xhr) {
                    showToast('Error fetching FSR Report details: ' + (xhr.responseJSON.message || ''), 'danger');
                    $('#datatable-pane').addClass('show active'); // Show datatable on error
                    $('#details-pane').removeClass('show active'); // Hide details container
                    $('#datatable-tab').addClass('active');
                    $('#details-tab').removeClass('active').prop('disabled', true);
                }
            });
        });

        // Event listener for closing FSR Report details
        $(document).on('click', '#close-fsr-details', function() {
            $('#datatable-pane').addClass('show active');
            $('#details-pane').removeClass('show active');
            $('#datatable-tab').addClass('active');
            $('#details-tab').removeClass('active').prop('disabled', true);
        });

        // Event listener for saving approved quantities
        $(document).on('submit', '#fsr-quotations-form', function(e) {
            e.preventDefault();
            const fsrReportId = $('#fsr-report-id').val();
            const formData = $(this).serializeArray();
            const approvedQuantities = {};
            let isValid = true;

            formData.forEach(item => {
                const match = item.name.match(/quotations\[(\d+)\]\[approved_quantity\]/);
                if (match) {
                    const quotationId = match[1];
                    const approvedQuantity = parseInt(item.value);
                    const stockQuantity = parseInt($(`#approved_quantity_${quotationId}`).data('stock-quantity'));

                    // if (approvedQuantity > stockQuantity) {
                    //     showToast(`Approved quantity for part ${quotationId} cannot be greater than stock quantity (${stockQuantity}).`, 'danger');
                    //     isValid = false;
                    //     return;
                    // }
                    approvedQuantities[quotationId] = approvedQuantity;
                }
            });

            if (!isValid) {
                return;
            }

            $.ajax({
                url: `{{ route('fsr-quotations.update-approved-quantities', ['fsrReport' => '__FSR_REPORT_ID__']) }}`.replace('__FSR_REPORT_ID__', fsrReportId),
                method: 'PUT',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    quantities: approvedQuantities
                },
                success: function(response) {
                    showToast(response.message, 'success');
                    // Optionally, reload the main datatable or update the details view
                    fsrQuotationsReviewTable.ajax.reload();
                },
                error: function(xhr) {
                    showToast('Error saving approved quantities: ' + (xhr.responseJSON.message || ''), 'danger');
                }
            });
        });

        $(document).on('input', '.approved-quantity-input', function() {
            const approvedQuantity = $(this).val();
            const unitPrice = $(this).data('quoted-unit-price');
            const totalPrice = (approvedQuantity * unitPrice).toFixed(2);
            $(this).closest('tr').find('.approved-total-price').text(totalPrice);
        });
    });
</script>
@endpush