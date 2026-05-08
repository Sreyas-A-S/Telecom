@extends('layouts.admin')

@section('title', 'Edit FSR')

@section('content')

<div class="">
    <h1 class="mb-2">Edit FSR for Task</h1>


    @if($client || $task->entry || $lead)
    <div class="row">
        @if($client)
        <div class="col-md-6">
            <div class="card mb-3 h-100">
                <div class="card-header">Client Information</div>
                <div class="card-body">
                    <p><strong>Name:</strong> {{ $client->name }}</p>
                    <p><strong>Email:</strong> {{ $client->email }}</p>
                    <p><strong>Phone:</strong> {{ $client->phone_number }}</p>
                    @if($client->address)
                    <p><strong>Address:</strong> {{ $client->address }}</p>
                    @endif
                    @if($client->gps_location)
                    @php
                    $gpsCoords = explode(',', $client->gps_location);
                    $clientLat = trim($gpsCoords[0] ?? '');
                    $clientLng = trim($gpsCoords[1] ?? '');
                    @endphp
                    <p><strong>GPS Location:</strong> {{ $client->gps_location }} <a href="#"
                            class="show-map-btn" data-bs-toggle="modal" data-bs-target="#gpsMapModal"
                            data-lat="{{ $clientLat }}" data-lng="{{ $clientLng }}"><i
                                class="fa fa-map-marker text-primary"></i></a></p>
                    @endif
                    @if($client->latitude && $client->longitude)
                    <p><strong>Coordinates:</strong> {{ $client->latitude }}, {{ $client->longitude }} <a
                            href="#" class="show-map-btn" data-bs-toggle="modal"
                            data-bs-target="#gpsMapModal" data-lat="{{ $client->latitude }}"
                            data-lng="{{ $client->longitude }}"><i
                                class="fa fa-map-marker text-primary"></i></a></p>
                    @endif
                    @if($client->notes)
                    <p><strong>Notes:</strong> {{ $client->notes }}</p>
                    @endif

                    @if($client->lead)
                    <hr>
                    <h5>Lead Information</h5>
                    @if($client->lead->location)
                    <p><strong>Lead Location:</strong> {{ $client->lead->location }}</p>
                    @endif
                    @if($client->lead->leadSource)
                    <p><strong>Lead Source:</strong> {{ $client->lead->leadSource->name }}</p>
                    @endif
                    @if($client->lead->leadCategory)
                    <p><strong>Lead Category:</strong> {{ $client->lead->leadCategory->name }}</p>
                    @endif
                    @if($client->lead->lead_value)
                    <p><strong>Lead Value:</strong> {{ $client->lead->lead_value }}</p>
                    @endif
                    <p><strong>Allow Follow Up:</strong>
                        {{ $client->lead->allow_follow_up ?'Yes':'No' }}
                    </p>
                    <p><strong>Lead Status:</strong> {{ $client->lead->status }}</p>
                    @if($client->lead->chance_of_success)
                    <p><strong>Chance of Success:</strong> {{ $client->lead->chance_of_success }}%</p>
                    @endif
                    @if($client->lead->product)
                    <p><strong>Lead Product:</strong> {{ $client->lead->product->name }}</p>
                    @endif
                    @if($client->lead->productModel)
                    <p><strong>Lead Product Model:</strong> {{ $client->lead->productModel->name }}</p>
                    @endif
                    @if($client->lead->modelSeries)
                    <p><strong>Lead Model Series:</strong> {{ $client->lead->modelSeries->name }}</p>
                    @endif
                    @if($client->lead->quantity)
                    <p><strong>Quantity:</strong> {{ $client->lead->quantity }}</p>
                    @endif
                    @if($client->lead->financier)
                    <p><strong>Financier:</strong> {{ $client->lead->financier }}</p>
                    @endif
                    @if($client->lead->type)
                    <p><strong>Lead Type:</strong> {{ $client->lead->type }}</p>
                    @endif
                    @if($client->lead->login_status)
                    <p><strong>Login Status:</strong> {{ $client->lead->login_status }}</p>
                    @endif
                    @if($client->lead->stage)
                    <p><strong>Stage:</strong> {{ $client->lead->stage }}</p>
                    @endif
                    @if($client->lead->billing)
                    <p><strong>Billing Plan Month:</strong> {{ $client->lead->billing }}</p>
                    @endif
                    @if($client->lead->remarks)
                    <p><strong>Remarks:</strong> {{ $client->lead->remarks }}</p>
                    @endif
                    @endif
                </div>
            </div>
        </div>
        @elseif($lead)
        <div class="col-md-6">
            <div class="card mb-3 h-100">
                <div class="card-header">Lead Information</div>
                <div class="card-body">
                    <p><strong>Name:</strong> {{ $lead->name }}</p>
                    <p><strong>Email:</strong> {{ $lead->email }}</p>
                    <p><strong>Phone:</strong> {{ $lead->phone_number }}</p>
                    @if($lead->location)
                    <p><strong>Lead Location:</strong> {{ $lead->location }}</p>
                    @endif
                    @if($lead->leadSource)
                    <p><strong>Lead Source:</strong> {{ $lead->leadSource->name }}</p>
                    @endif
                    @if($lead->leadCategory)
                    <p><strong>Lead Category:</strong> {{ $lead->leadCategory->name }}</p>
                    @endif
                    @if($lead->lead_value)
                    <p><strong>Lead Value:</strong> {{ number_format($lead->lead_value, 2) }}</p>
                    @endif
                    <p><strong>Allow Follow Up:</strong>
                        {{ $lead->allow_follow_up ?'Yes':'No' }}
                    </p>
                    <p><strong>Lead Status:</strong> {{ $lead->status }}</p>
                    @if($lead->chance_of_success)
                    <p><strong>Chance of Success:</strong> {{ $lead->chance_of_success }}%</p>
                    @endif

                    @if($lead->items->isNotEmpty())
                    <hr>
                    <h6>Products Info:</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Model</th>
                                    <th>Qty</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($lead->items as $item)
                                <tr>
                                    <td>{{ $item->product->name ?? 'N/A' }}</td>
                                    <td>{{ $item->productModel->name ?? 'N/A' }}</td>
                                    <td>{{ $item->quantity }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                        @if($lead->product)
                        <p><strong>Lead Product:</strong> {{ $lead->product->name }}</p>
                        @endif
                        @if($lead->productModel)
                        <p><strong>Lead Product Model:</strong> {{ $lead->productModel->name }}</p>
                        @endif
                        @if($lead->modelSeries)
                        <p><strong>Lead Model Series:</strong> {{ $lead->modelSeries->name }}</p>
                        @endif
                        @if($lead->quantity)
                        <p><strong>Quantity:</strong> {{ $lead->quantity }}</p>
                        @endif
                    @endif

                    @if($lead->financier)
                    <p><strong>Financier:</strong> {{ $lead->financier }}</p>
                    @endif
                    @if($lead->type)
                    <p><strong>Lead Type:</strong> {{ $lead->type }}</p>
                    @endif
                    @if($lead->login_status)
                    <p><strong>Login Status:</strong> {{ $lead->login_status }}</p>
                    @endif
                    @if($lead->stage)
                    <p><strong>Stage:</strong> {{ $lead->stage }}</p>
                    @endif
                    @if($lead->billing)
                    <p><strong>Billing Plan Month:</strong> {{ $lead->billing }}</p>
                    @endif
                    @if($lead->remarks)
                    <p><strong>Remarks:</strong> {{ $lead->remarks }}</p>
                    @endif
                </div>
            </div>
        </div>
        @endif

        @if($task->entry)
        <div class="col-md-6">
            <div class="card mb-3 h-100">
                <div class="card-header">Service Information</div>
                <div class="card-body">
                    <p><strong>Service Name:</strong> {{ $task->entry->name }}</p>
                    <p><strong>Service Description:</strong> {{ $task->entry->description }}</p>
                    @if($task->entry->referral_id)
                    <p><strong>Referral ID:</strong> {{ $task->entry->referral_id }}</p>
                    @endif
                    @if($task->entry->machine_status)
                    <p><strong>Machine Status:</strong> {{ $task->entry->machine_status }}</p>
                    @endif
                    @if($task->entry->type_of_service)
                    <p><strong>Type of Service:</strong> {{ $task->entry->type_of_service }}</p>
                    @endif
                    @if($task->entry->contact_info)
                    <p><strong>Contact Info:</strong> {{ $task->entry->contact_info }}</p>
                    @endif
                    @if($task->entry->requested_location)
                    <p><strong>Requested Location:</strong> {{ $task->entry->requested_location }}</p>
                    @endif
                    @if($task->entry->latitude && $task->entry->longitude)
                    <p><strong>Coordinates:</strong> {{ $task->entry->latitude }},
                        {{ $task->entry->longitude }} <a href="#" class="show-map-btn"
                            data-bs-toggle="modal" data-bs-target="#gpsMapModal"
                            data-lat="{{ $task->entry->latitude }}"
                            data-lng="{{ $task->entry->longitude }}"><i
                                class="fa fa-map-marker text-primary"></i></a>
                    </p>
                    @endif
                    @if($task->entry->product)
                    <p><strong>Product Name:</strong> {{ $task->entry->product->name }}</p>
                    @endif
                    @if($task->entry->productModel)
                    <p><strong>Product Model:</strong> {{ $task->entry->productModel->name }}</p>
                    @endif
                    @if($task->entry->modelSeries)
                    <p><strong>Product Serial Number:</strong> {{ $task->entry->modelSeries->name }}</p>
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>
    @endif

    <div class="d-flex justify-content-end mb-3">
        <button type="button" id="toggle-edit-parts" class="btn btn-sm btn-primary">Enable Edit</button>
    </div>

    <div class="card mt-2">
        <div class="card-body">
            <form id="editFSRForm" action="{{ route('fsr.update', $fsrReport->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="on_site_assessment">On-site Assessment</label>
                            <textarea name="on_site_assessment" id="on_site_assessment" class="form-control" rows="5" readonly>{{ $fsrReport->on_site_assessment }}</textarea>
                            <div class="invalid-feedback" id="on_site_assessment_error"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="analysis_of_cause">Analysis of Cause</label>
                            <textarea name="analysis_of_cause" id="analysis_of_cause" class="form-control" rows="5" readonly>{{ $fsrReport->analysis_of_cause }}</textarea>
                            <div class="invalid-feedback" id="analysis_of_cause_error"></div>
                        </div>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label for="actions_taken">Actions Taken</label>
                    <textarea name="actions_taken" id="actions_taken" class="form-control" rows="5" readonly>{{ $fsrReport->actions_taken }}</textarea>
                    <div class="invalid-feedback" id="actions_taken_error"></div>
                </div>

                <div class="card mb-3">
                    <div class="card-header bg-info text-white">Payment Status & Collections</div>
                    <div class="card-body">
                        <div class="row mb-3">
                            @php
                                $totalPrice = $task->lead_id ? ($task->lead->lead_value ?? 0) : ($task->entry->price ?? 0);
                                $priceLabel = $task->lead_id ? 'Lead Value' : 'Service Price';
                            @endphp
                            <div class="col-md-3">
                                <label class="fw-bold">{{ $priceLabel }}:</label>
                                <div class="h5 text-primary">
                                    {{ number_format($totalPrice, 2) }}
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="fw-bold">Total Collected:</label>
                                <div class="h5 text-success" id="summary-total-collected">
                                    {{ number_format($fsrReport->paymentHistory->sum('amount'), 2) }}
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="fw-bold">Balance Due:</label>
                                <div class="h5 text-danger" id="summary-balance-due">
                                    {{ number_format($totalPrice - $fsrReport->paymentHistory->sum('amount'), 2) }}
                                </div>
                            </div>
                            <div class="col-md-3 text-end">
                                <div class="form-check form-switch mt-3">
                                    <input class="form-check-input" type="checkbox" role="switch" id="payment_done" name="payment_status" value="paid" {{ $fsrReport->payment_status == 'paid' ? 'checked' : '' }} disabled>
                                    <label class="form-check-label fw-bold" for="payment_done">Overall Paid</label>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <h6>Collection History</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped" id="payments-table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Mode</th>
                                        <th>Remarks</th>
                                        <th>Collected By</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($fsrReport->paymentHistory as $payment)
                                    <tr id="payment-row-{{ $payment->id }}">
                                        <td>{{ $payment->collected_at->format('d-M-Y H:i') }}</td>
                                        <td>{{ number_format($payment->amount, 2) }}</td>
                                        <td><span class="badge bg-light text-dark">{{ ucfirst($payment->payment_mode) }}</span></td>
                                        <td>{{ $payment->remarks }}</td>
                                        <td>{{ $payment->collectedBy->name ?? 'N/A' }}</td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-danger delete-payment-btn" data-id="{{ $payment->id }}" disabled>
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr id="no-payments-msg">
                                        <td colspan="6" class="text-center text-muted">No installments recorded yet.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div id="add-payment-section" class="mt-3 p-3 border rounded bg-light" style="display: none;">
                            <h6>Add New Installment</h6>
                            <div class="row g-2">
                                <div class="col-md-3">
                                    <input type="number" id="new_payment_amount" class="form-control form-control-sm" placeholder="Amount" step="0.01">
                                </div>
                                <div class="col-md-3">
                                    <select id="new_payment_mode" class="form-select form-select-sm">
                                        <option value="cash">Cash</option>
                                        <option value="online">Online</option>
                                        <option value="cheque">Cheque</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" id="new_payment_remarks" class="form-control form-control-sm" placeholder="Remarks (optional)">
                                </div>
                                <div class="col-md-2">
                                    <button type="button" id="submit-payment-btn" class="btn btn-sm btn-success w-100">Add</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group mb-3 mt-4">
                    <label>Existing Images</label>
                    <div id="existing-image-preview-container" class="mt-2 d-flex flex-wrap gap-2">
                        @if($fsrReport->images && count($fsrReport->images) > 0)
                        @foreach($fsrReport->images as $imagePath)
                        <div class="position-relative">
                            <img src="{{ route('fsr.showImage', ['fsrReport' => $fsrReport->id, 'imageIndex' => $loop->index]) }}" class="img-thumbnail" style="width: 100px; height: 100px; object-fit: cover;">
                            <button type="button" class="btn btn-danger btn-sm position-absolute top-0 start-100 translate-middle rounded-circle remove-existing-image" data-image-path="{{ $imagePath }}" aria-label="Remove" style="width: 24px; height: 24px; padding: 0; display: flex; align-items: center; justify-content: center;" disabled>
                                <i class="fa fa-times"></i>
                            </button>
                        </div>
                        @endforeach
                        @else
                        <p>No images associated with this FSR report.</p>
                        @endif
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label for="images">Add New Images</label>
                    <input type="file" name="images[]" id="images" class="form-control" multiple accept="image/*" disabled>
                    <div class="invalid-feedback" id="images_error"></div>
                    <div id="new-image-preview-container" class="mt-2 d-flex flex-wrap gap-2"></div>
                </div>

                @if($task->lead_id)
                <div class="card mt-3">
                    <div class="card-header">
                        <span>Parts Quotation</span>
                    </div>
                    <div class="card-body">
                        <div id="parts-quotation-container">
                            @foreach($fsrReport->partQuotations as $index => $quotation)
                            <div class="row mb-2 part-quotation-row" id="part-row-{{ $index }}">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <select class="form-control part-select" disabled name="part_quotations[{{ $index }}][part_id]" style="width: 100%;" data-initial-id="{{ $quotation->part->id }}" data-initial-text="{{ $quotation->part->part_number }} - {{ $quotation->part->material_description }}">
                                        </select>
                                        <div class="invalid-feedback" id="part_quotations.{{ $index }}.part_id_error"></div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <input type="number" readonly name="part_quotations[{{ $index }}][quoted_quantity]" class="form-control quoted-quantity" placeholder="Quantity" min="1" value="{{ $quotation->quoted_quantity }}">
                                        <div class="invalid-feedback" id="part_quotations.{{ $index }}.quoted_quantity_error"></div>
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-danger btn-sm remove-part-row" disabled>
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <button type="button" id="add-part-row" class="btn btn-info btn-sm mt-3" disabled>
                            <i class="fa fa-plus"></i> Add Part
                        </button>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header">Approved Quotations</div>
                    <div class="card-body">
                        <div id="approved-quotations-container">
                            @forelse($fsrReport->partQuotations->where('status', 'approved') as $quotation)
                            <div class="card mb-2">
                                <div class="card-body">
                                    <p><strong>Part:</strong> {{ $quotation->part->part_number }} - {{ $quotation->part->material_description }}</p>
                                    <p><strong>Quantity:</strong> {{ $quotation->quoted_quantity }}</p>
                                    <p><strong>Unit Price:</strong> {{ $quotation->quoted_unit_price }}</p>
                                    <p><strong>Approved By:</strong> {{ $quotation->approver->name ?? 'N/A' }}</p>
                                    <p><strong>Approval Date:</strong> {{ $quotation->approval_date ? $quotation->approval_date->format('d-M-Y H:i') : 'N/A' }}</p>
                                </div>
                            </div>
                            @empty
                            <p>No approved quotations.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
                @endif
                <button type="submit" class="btn btn-primary mt-3" disabled>Update FSR</button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('modal')
<!-- GPS Location Map Modal -->
<div class="modal fade" id="gpsMapModal" tabindex="-1" aria-labelledby="gpsMapModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="gpsMapModalLabel">Client Location</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="fsrMap" style="height: 400px; width: 100%;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        function showToast(message, type) {
            var toastContainer = $('#toast-container');
            if (toastContainer.length === 0) {
                toastContainer = $('<div id="toast-container" class="position-fixed top-0 end-0 p-3" style="z-index: 1050;"></div>');
                $('body').append(toastContainer);
            }
            var toastClass = '';
            switch (type) {
                case 'success':
                    toastClass = 'text-bg-success';
                    break;
                case 'error':
                case 'danger':
                    toastClass = 'text-bg-danger';
                    break;
                case 'warning':
                    toastClass = 'text-bg-warning';
                    break;
                case 'info':
                    toastClass = 'text-bg-info';
                    break;
                default:
                    toastClass = 'text-bg-primary';
            }
            var toastId = 'toast-' + Date.now();
            var toastHtml = `<div id="${toastId}" class="toast align-items-center ${toastClass} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>`;
            toastContainer.append(toastHtml);
            var toastEl = new bootstrap.Toast(document.getElementById(toastId));
            toastEl.show();
        }

        function clearValidationErrors() {
            $('.form-control').removeClass('is-invalid');
            $('.invalid-feedback').text('');
        }

        function displayValidationErrors(errors) {
            $.each(errors, function(key, value) {
                // Handle validation errors for images array
                if (key.startsWith('images.')) {
                    $('#images').addClass('is-invalid');
                    $('#images_error').text(value[0]);
                } else {
                    $('#' + key).addClass('is-invalid');
                    $('#' + key + '_error').text(value[0]);
                }
            });
        }

        let partRowIndex = {
            {
                $fsrReport->partQuotations->count()
            }
        };

        function initializeSelect2(element) {
            $(element).select2({
                placeholder: 'Search for a part',
                allowClear: true,
                ajax: {
                    url: '{{ route('parts.search') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        var selectedPartIds = [];
                        $('.part-select').each(function() {
                            var selectedId = $(this).val();
                            if (selectedId) {
                                selectedPartIds.push(selectedId);
                            }
                        });

                        return {
                            query: params.term, // search term
                            exclude: selectedPartIds // pass the array of selected part IDs
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: $.map(data, function(part) {
                                return {
                                    id: part.id,
                                    text: part.part_number + ' - ' + part.material_description
                                };
                            })
                        };
                    },
                    cache: true
                }
            });
        }

        // Initialize select2 for existing rows
        $('.part-select').each(function() {
            initializeSelect2(this);

            // Set initial value if present
            var initialId = $(this).data('initial-id');
            var initialText = $(this).data('initial-text');
            if (initialId && initialText) {
                var option = new Option(initialText, initialId, true, true);
                $(this).append(option).trigger('change');
            }
        });

        function addPartRow() {
            const newRow = `
            <div class="row mb-2 part-quotation-row" id="part-row-${partRowIndex}">
                <div class="col-md-8">
                    <div class="form-group">
                        <select class="form-control part-select" name="part_quotations[${partRowIndex}][part_id]" style="width: 100%;"></select>
                        <div class="invalid-feedback" id="part_quotations.${partRowIndex}.part_id_error"></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <input type="number" name="part_quotations[${partRowIndex}][quoted_quantity]" class="form-control quoted-quantity" placeholder="Quantity" min="1">
                        <div class="invalid-feedback" id="part_quotations.${partRowIndex}.quoted_quantity_error"></div>
                    </div>
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-danger btn-sm remove-part-row"><i class="fa fa-trash"></i></button>
                </div>
            </div>
        `;
            $('#parts-quotation-container').append(newRow);
            initializeSelect2($(`#part-row-${partRowIndex} .part-select`));
            partRowIndex++;
        }

        // Handle Add Part button click
        $('#add-part-row').on('click', function() {
            addPartRow();
        });

        // Handle Remove Part button click
        $(document).on('click', '.remove-part-row', function() {
            $(this).closest('.part-quotation-row').remove();
        });

        // New Image preview functionality
        $('#images').on('change', function() {
            $('#new-image-preview-container').empty(); // Clear existing previews
            if (this.files) {
                for (let i = 0; i < this.files.length; i++) {
                    let file = this.files[i];
                    if (file.type.startsWith('image/')) {
                        let reader = new FileReader();
                        reader.onload = function(e) {
                            let imgHtml = `
                                <div class="position-relative">
                                    <img src="${e.target.result}" class="img-thumbnail" style="width: 100px; height: 100px; object-fit: cover;">
                                    <button type="button" class="btn btn-danger btn-sm position-absolute top-0 start-100 translate-middle rounded-circle remove-new-image-preview" data-file-index="${i}" aria-label="Remove" style="width: 24px; height: 24px; padding: 0; display: flex; align-items: center; justify-content: center;"><i class="fa fa-times"></i></button>
                                </div>
                            `;
                            $('#new-image-preview-container').append(imgHtml);
                        };
                        reader.readAsDataURL(file);
                    }
                }
            }
        });

        // Handle removing new image preview (and implicitly the file from the input)
        $(document).on('click', '.remove-new-image-preview', function() {
            let indexToRemove = $(this).data('file-index');
            let dataTransfer = new DataTransfer();
            let files = $('#images')[0].files;

            for (let i = 0; i < files.length; i++) {
                if (i !== indexToRemove) {
                    dataTransfer.items.add(files[i]);
                }
            }

            $('#images')[0].files = dataTransfer.files;
            $(this).closest('div.position-relative').remove();

            // Re-index data-file-index for remaining previews
            $('#new-image-preview-container').find('.remove-new-image-preview').each(function(newIndex) {
                $(this).data('file-index', newIndex);
            });
        });

        // Handle removing existing image
        $(document).on('click', '.remove-existing-image', function() {
            const imagePath = $(this).data('image-path');
            const $imageContainer = $(this).closest('div.position-relative');
            const fsrReportId = '{{ $fsrReport->id }}';

            if (confirm('Are you sure you want to remove this image?')) {
                let url = "{{ route('fsr.deleteImage', ['fsrReport' => ':fsrReportId', 'imageIndex' => ':imageIndex']) }}";
                url = url.replace(':fsrReportId', fsrReportId).replace(':imageIndex', encodeURIComponent(imagePath));

                $.ajax({
                    url: url,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        showToast(response.message || 'Image removed successfully.', 'success');
                        $imageContainer.remove(); // Remove the image preview from DOM
                    },
                    error: function(xhr) {
                        showToast(xhr.responseJSON.message || 'Error removing image.', 'danger');
                    }
                });
            }
        });

        $('#editFSRForm').on('submit', function(e) {
            e.preventDefault();
            clearValidationErrors();
            var formData = new FormData(this);

            // Explicitly add task_id to formData
            formData.append('task_id', '{{ $task->id }}');

            // Append new images to formData
            let newFiles = $('#images')[0].files;
            for (let i = 0; i < newFiles.length; i++) {
                formData.append('images[]', newFiles[i]);
            }

            // Collect part quotations data manually to ensure correct array format
            $('.part-quotation-row').each(function(index) {
                const partId = $(this).find('.part-select').val();
                const quantity = $(this).find('.quoted-quantity').val();
                if (partId && quantity && quantity > 0) {
                    formData.append(`part_quotations[${index}][part_id]`, partId);
                    formData.append(`part_quotations[${index}][quoted_quantity]`, quantity);
                }
            });

            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    window.location.href = '{{ route('tasks.index') }}';
                },
                error: function(xhr) {
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        displayValidationErrors(xhr.responseJSON.errors);
                    } else {
                        showToast(xhr.responseJSON.message || 'Error updating FSR.', 'danger');
                    }
                }
            });
        });

        // Toggle edit mode for parts quotation
        $('#toggle-edit-parts').on('click', function() {
            const isEditing = $(this).text() === 'Disable Edit';
            $(this).text(isEditing ? 'Enable Edit' : 'Disable Edit');
            $(this).toggleClass('btn-primary btn-danger');

            // Toggle textareas
            $('#on_site_assessment, #analysis_of_cause, #actions_taken').prop('readonly', isEditing);

            // Toggle payment checkbox
            $('#payment_done').prop('disabled', isEditing);

            // Toggle image input
            $('#images').prop('disabled', isEditing);

            // Toggle remove existing image buttons
            $('.remove-existing-image').prop('disabled', isEditing);

            // Toggle parts quotation fields and buttons
            $('#parts-quotation-container .part-select').prop('disabled', isEditing);
            $('#parts-quotation-container .quoted-quantity').prop('readonly', isEditing);
            $('#parts-quotation-container .remove-part-row').prop('disabled', isEditing);
            $('#add-part-row').prop('disabled', isEditing);

            // Toggle update FSR button
            $('#editFSRForm button[type="submit"]').prop('disabled', isEditing);

            // Payment section toggles
            if (isEditing) {
                $('#add-payment-section').hide();
                $('.delete-payment-btn').prop('disabled', true);
            } else {
                $('#add-payment-section').show();
                $('.delete-payment-btn').prop('disabled', false);
            }

            // Re-initialize Select2 for enabled dropdowns
            if (!isEditing) {
                $('#parts-quotation-container .part-select').each(function() {
                    initializeSelect2(this);
                    // Set initial value if present
                    var initialId = $(this).data('initial-id');
                    var initialText = $(this).data('initial-text');
                    if (initialId && initialText) {
                        var option = new Option(initialText, initialId, true, true);
                        $(this).append(option).trigger('change');
                    }
                });
            }
        });

        // Handle Add Payment AJAX
        $('#submit-payment-btn').on('click', function() {
            const amount = $('#new_payment_amount').val();
            const mode = $('#new_payment_mode').val();
            const remarks = $('#new_payment_remarks').val();
            const fsrReportId = '{{ $fsrReport->id }}';

            if (!amount || amount <= 0) {
                alert('Please enter a valid amount.');
                return;
            }

            $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Adding...');

            $.ajax({
                url: `/fsr/${fsrReportId}/payments`,
                method: 'POST',
                data: {
                    amount: amount,
                    payment_mode: mode,
                    remarks: remarks,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    showToast(response.message, 'success');
                    location.reload();
                },
                error: function(xhr) {
                    showToast(xhr.responseJSON.message || 'Error adding payment.', 'danger');
                    $('#submit-payment-btn').prop('disabled', false).text('Add');
                }
            });
        });

        // Handle Delete Payment AJAX
        $(document).on('click', '.delete-payment-btn', function() {
            const paymentId = $(this).data('id');
            if (confirm('Are you sure you want to delete this payment record?')) {
                $.ajax({
                    url: `/fsr-payments/${paymentId}`,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        showToast(response.message, 'success');
                        location.reload();
                    },
                    error: function(xhr) {
                        showToast(xhr.responseJSON.message || 'Error deleting payment.', 'danger');
                    }
                });
            }
        });
    });
</script>
{{-- Google Maps API and FSR Map Logic --}}
<script>
    let fsrMap;
    let fsrMarker;

    function initMap(lat, lng) { // Renamed initializeFSRMap to initMap and made it global
        const defaultLatLng = {
            lat: parseFloat(lat),
            lng: parseFloat(lng)
        };

        if (!fsrMap) {
            fsrMap = new google.maps.Map(document.getElementById('fsrMap'), {
                zoom: 13,
                center: defaultLatLng,
                mapId: "DEMO_MAP_ID",
            });
            fsrMarker = new google.maps.marker.AdvancedMarkerElement({
                map: fsrMap,
                position: defaultLatLng,
            });
        } else {
            fsrMarker.position = defaultLatLng;
            fsrMap.setCenter(defaultLatLng);
            fsrMap.setZoom(13);
        }
    }
</script>
<script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&libraries=places,marker&callback=initMap" async defer></script>
<script>
    $(document).ready(function() {
        // ... existing document.ready content ...

        $('#gpsMapModal').on('shown.bs.modal', function() {
            // Trigger map resize to ensure it displays correctly
            if (fsrMap) {
                google.maps.event.trigger(fsrMap, 'resize');
                // Re-center the map after resize
                const currentCenter = fsrMap.getCenter();
                fsrMap.setCenter(currentCenter);
            }
        });

        $(document).on('click', '.show-map-btn', function(e) {
            e.preventDefault();
            const lat = $(this).data('lat');
            const lng = $(this).data('lng');

            if (lat && lng) {
                initMap(lat, lng); // Call the global initMap function
            } else {
                showToast('GPS coordinates not available.', 'warning');
            }
        });
    });
</script>
@endpush