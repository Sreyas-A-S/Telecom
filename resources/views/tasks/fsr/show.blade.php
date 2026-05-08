@extends('layouts.admin')

@section('title', 'View FSR')

@section('content')

<div class="">
    <h1 class="mb-2">View FSR for Task</h1>

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
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="on_site_assessment"><strong>On-site Assessment:</strong></label>
                        <p>{{ $fsrReport->on_site_assessment ??'N/A' }}</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="analysis_of_cause"><strong>Analysis of Cause:</strong></label>
                        <p>{{ $fsrReport->analysis_of_cause ??'N/A' }}</p>
                    </div>
                </div>
            </div>

            <div class="form-group mb-3">
                <label for="actions_taken"><strong>Actions Taken:</strong></label>
                <p>{{ $fsrReport->actions_taken ??'N/A' }}</p>
            </div>

            <div class="card mt-3">
                <div class="card-header bg-info text-white">Payment Status & Collections</div>
                <div class="card-body">
                    <div class="row mb-3 text-center">
                        @php
                            $totalPrice = $task->lead_id ? ($task->lead->lead_value ?? 0) : ($task->entry->price ?? 0);
                            $priceLabel = $task->lead_id ? 'Lead Value' : 'Service Price';
                        @endphp
                        <div class="col-md-4">
                            <label class="fw-bold">{{ $priceLabel }}</label>
                            <div class="h5">{{ number_format($totalPrice, 2) }}</div>
                        </div>
                        <div class="col-md-4">
                            <label class="fw-bold">Total Collected</label>
                            <div class="h5 text-success">{{ number_format($fsrReport->paymentHistory->sum('amount'), 2) }}</div>
                        </div>
                        <div class="col-md-4">
                            <label class="fw-bold">Balance Due</label>
                            <div class="h5 text-danger">{{ number_format($totalPrice - $fsrReport->paymentHistory->sum('amount'), 2) }}</div>
                        </div>
                    </div>

                    <div class="table-responsive mt-3">
                        <table class="table table-sm table-bordered table-striped">
                            <thead>
                                <tr class="table-light">
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Mode</th>
                                    <th>Remarks</th>
                                    <th>Collected By</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($fsrReport->paymentHistory as $payment)
                                <tr>
                                    <td>{{ $payment->collected_at->format('d-M-Y H:i') }}</td>
                                    <td>{{ number_format($payment->amount, 2) }}</td>
                                    <td><span class="badge bg-light text-dark">{{ ucfirst($payment->payment_mode) }}</span></td>
                                    <td>{{ $payment->remarks ?? '-' }}</td>
                                    <td>{{ $payment->collectedBy->name ?? 'N/A' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No payments recorded.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">Images</div>
                <div class="card-body">
                    @if($fsrReport->images && count($fsrReport->images) > 0)
                    <div class="d-flex flex-wrap gap-2">
                        @foreach ($fsrReport->images as $imagePath)
                        <a href="{{ route('fsr.showImage',['fsrReport' => $fsrReport->id,'imageIndex' => $loop->index]) }}" target="_blank" class="view-image-link">
                            <img src="{{ route('fsr.showImage',['fsrReport' => $fsrReport->id,'imageIndex' => $loop->index]) }}" class="img-thumbnail" style="width:150px;height:150px;object-fit:cover;">
                        </a>
                        @endforeach
                    </div>
                    @else
                    <p>No images associated with this FSR report.</p>
                    @endif
                </div>
            </div>

            @if($task->lead_id)
            <div class="card mt-3">
                <div class="card-header">Requested Parts Quotation</div>
                <div class="card-body">
                    @if($fsrReport->partQuotations->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Part Number</th>
                                    <th>Material Description</th>
                                    <th>Quoted Quantity</th>
                                    <th>Quoted Unit Price</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($fsrReport->partQuotations as $quotation)
                                <tr>
                                    <td>{{ $quotation->part->part_number ?? 'N/A' }}</td>
                                    <td>{{ $quotation->part->material_description ?? 'N/A' }}</td>
                                    <td>{{ $quotation->quoted_quantity }}</td>
                                    <td>{{ $quotation->quoted_unit_price }}</td>
                                    <td>{{ ucfirst($quotation->status) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p>No parts quoted for this FSR.</p>
                    @endif
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">Approved Parts Quotation</div>
                <div class="card-body">
                    @php
                    $approvedQuotations = $fsrReport->partQuotations->where('status', 'approved');
                    @endphp
                    @if($approvedQuotations->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Part Number</th>
                                    <th>Material Description</th>
                                    <th>Quoted Quantity</th>
                                    <th>Quoted Unit Price</th>
                                    <th>Approved By</th>
                                    <th>Approval Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($approvedQuotations as $quotation)
                                <tr>
                                    <td>{{ $quotation->part->part_number ?? 'N/A' }}</td>
                                    <td>{{ $quotation->part->material_description ?? 'N/A' }}</td>
                                    <td>{{ $quotation->quoted_quantity }}</td>
                                    <td>{{ $quotation->quoted_unit_price }}</td>
                                    <td>{{ $quotation->approver->name ?? 'N/A' }}</td>
                                    <td>{{ $quotation->approval_date ? $quotation->approval_date->format('d-M-Y H:i') : 'N/A' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p>No approved parts quotations.</p>
                    @endif
                </div>
            </div>
            @endif
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
    <script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&libraries=places,marker&loading=async" async defer></script>

    <script>
        let fsrMap;
        let fsrMarker;

        function initFSRMap(lat, lng) {
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
                initFSRMap(lat, lng);
            } else {
                showToast('GPS coordinates not available.', 'warning');
            }
        });
        $(document).on('click', '.view-image-link', function(e) {
            e.preventDefault(); // Prevent default link behavior
            const imageUrl = $(this).attr('href');

            if (confirm('Are you sure you want to view this image in a new tab?')) {
                window.open(imageUrl, '_blank');
            }
        });
    </script>
    @endpush