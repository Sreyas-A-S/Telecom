@extends('layouts.admin')

@section('title', 'Create FSR')

@section('content')
<div class="">
    <h1 class="mb-2">FSR for Task</h1>

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

    <div class="card mt-2">
        <div class="card-body">
            <form id="addFSRForm" action="{{ route('tasks.fsr.store',['task' => $task->id]) }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="on_site_assessment">On-site Assessment</label>
                            <textarea name="on_site_assessment" id="on_site_assessment" class="form-control" rows="5"></textarea>
                            <div class="invalid-feedback" id="on_site_assessment_error"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="analysis_of_cause">Analysis of Cause</label>
                            <textarea name="analysis_of_cause" id="analysis_of_cause" class="form-control" rows="5"></textarea>
                            <div class="invalid-feedback" id="analysis_of_cause_error"></div>
                        </div>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label for="actions_taken">Actions Taken</label>
                    <textarea name="actions_taken" id="actions_taken" class="form-control" rows="5"></textarea>
                    <div class="invalid-feedback" id="actions_taken_error"></div>
                </div>

                <div class="form-group mb-3">
                    <label for="images">Attach Images</label>
                    <input type="file" name="images[]" id="images" class="form-control" multiple accept="image/*">
                    <div class="invalid-feedback" id="images_error"></div>
                    <div id="image-preview-container" class="mt-2 d-flex flex-wrap gap-2"></div>
                </div>

                @if($task->lead_id)
                <div class="card mt-3">
                    <div class="card-header">Parts Quotation</div>
                    <div class="card-body">
                        <div id="parts-quotation-container">
                            <!-- Dynamic part rows will be added here -->
                        </div>
                        <button type="button" id="add-part-row" class="btn btn-info btn-sm mt-3">
                            <i class="fa fa-plus"></i> Add Part
                        </button>
                    </div>
                </div>
                @endif
                <button type="submit" class="btn btn-primary mt-3">Submit FSR</button>
            </form>
        </div>
    </div>
</div>@endsection@section('modal')<!--GPSLocationMapModal-->
<div class="modal fade" id="gpsMapModal" tabindex="-1" aria-labelledby="gpsMapModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="gpsMapModalLabel">Client Location</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="fsrMap" style="height:400px;width:100%;">
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>
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
                if (key.startsWith('images.')) {
                    $('#images').addClass('is-invalid');
                    $('#images_error').text(value[0]);
                } else {
                    $('#' + key).addClass('is-invalid');
                    $('#' + key + '_error').text(value[0]);
                }
            });
        }

        let partRowIndex = 0;

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

        // Add initial row
        addPartRow();

        // Handle Add Part button click
        $('#add-part-row').on('click', function() {
            addPartRow();
        });

        // Handle Remove Part button click
        $(document).on('click', '.remove-part-row', function() {
            $(this).closest('.part-quotation-row').remove();
        });

        // Image preview functionality
        $('#images').on('change', function() {
            $('#image-preview-container').empty(); // Clear existing previews
            if (this.files) {
                for (let i = 0; i < this.files.length; i++) {
                    let file = this.files[i];
                    if (file.type.startsWith('image/')) {
                        let reader = new FileReader();
                        reader.onload = function(e) {
                            let imgHtml = `
                                    <div class="position-relative">
                                        <img src="${e.target.result}" class="img-thumbnail" style="width: 100px; height: 100px; object-fit: cover;">
                                        <button type="button" class="btn btn-danger btn-sm position-absolute top-0 start-100 translate-middle rounded-circle remove-image-preview" data-file-index="${i}" aria-label="Remove" style="width: 24px; height: 24px; padding: 0; display: flex; align-items: center; justify-content: center;"><i class="fa fa-times"></i></button>
                                    </div>
                                `;
                            $('#image-preview-container').append(imgHtml);
                        };
                        reader.readAsDataURL(file);
                    }
                }
            }
        });

        // Handle removing image preview (and implicitly the file from the input)
        $(document).on('click', '.remove-image-preview', function() {
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
            $('#image-preview-container').find('.remove-image-preview').each(function(newIndex) {
                $(this).data('file-index', newIndex);
            });
        });

        $('#addFSRForm').on('submit', function(e) {
            e.preventDefault();
            clearValidationErrors();

            var formData = new FormData(this);

            // Explicitly add task_id to formData
            formData.append('task_id', '{{ $task->id }}');

            // Append images to formData
            let files = $('#images')[0].files;
            for (let i = 0; i < files.length; i++) {
                formData.append('images[]', files[i]);
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
                    showToast(response.message || 'FSR added successfully.', 'success');
                    window.location.href = '{{ route('tasks.index') }}';
                },
                error: function(xhr) {
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        displayValidationErrors(xhr.responseJSON.errors);
                    } else {
                        showToast(xhr.responseJSON.message || 'Error adding FSR.', 'danger');
                    }
                }
            });
        });
    });
</script>
@endpush