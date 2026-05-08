@extends('layouts.admin')

@section('title', 'Follow Ups')

@section('breadcrumb')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3>All Follow Ups</h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"> <i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item active">All Follow Ups</li>
                </ol>
            </div>
        </div>
    </div>
</div>
@endsection

@section('content')
@if(checkMenu(Session::get('role_id'), 11, 'read'))
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h5>All Follow Ups</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="display" id="all-followups-table">
                            <thead>
                                <tr>
                                    <th>Sl No</th>
                                    <th>Lead Name</th>
                                    <th>Next Follow Up Date</th>
                                    <th>Status</th>
                                    <th>Remarks</th>
                                    <th>Created Date</th>
                                    {{-- <th>Actions</th> --}}
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Data will be loaded via AJAX --}}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@else
<div class="container-fluid">
    <div class="alert alert-danger" role="alert">
        You do not have permission to view all follow ups.
    </div>
</div>
@endif
@endsection

@push('scripts')
<!-- DataTables with Bootstrap 5 integration -->
<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css" rel="stylesheet">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script>
    $(document).ready(function() {
        var allFollowupsTable = $('#all-followups-table').DataTable({
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
            ajax: "{{ route('followups.data') }}",
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'lead_name',
                    name: 'lead_name'
                },
                {
                    data: 'next_follow_up_date',
                    name: 'next_follow_up_date',
                    render: function(data, type, row) {
                        if (!data) {
                            return 'N/A';
                        }
                        var dateTime = new Date(data);
                        var datePart = dateTime.toLocaleDateString();
                        var timePart = dateTime.toLocaleTimeString([], {
                            hour: '2-digit',
                            minute: '2-digit'
                        });
                        return `<div>${datePart}</div><span class="badge bg-info">${timePart}</span>`;
                    }
                },
                {
                    data: 'new_status',
                    name: 'new_status',
                    render: function(data, type, row) {
                        var statusColors = {
                            'pending': 'bg-warning',
                            'in progress': 'bg-info',
                            'win': 'bg-success',
                            'lost': 'bg-danger',
                            'positive': 'bg-primary'
                        };
                        var colorClass = statusColors[data] || 'bg-secondary';
                        return '<span class="badge rounded-pill ' + colorClass + '">' + data + '</span>';
                    }
                },
                {
                    data: 'remarks',
                    name: 'remarks'
                },
                {
                    data: 'created_at',
                    name: 'created_at'
                },
                // { data: 'action', name: 'action', orderable: false, searchable: false }, // Uncomment if actions are needed
            ]
        });
    });
</script>
@endpush