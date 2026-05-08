@extends('layouts.admin')

@section('title', 'Task Continuation Approvals')

@section('content')
<div class="container-fluid">
    <h1>Task Continuation</h1>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Tasks Awaiting Early Action Approval</h5>
            <div class="table-responsive">
                <table class="table" id="sm-approvals-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Employee</th>
                            <th>Title</th>
                            <th>Task Type</th>
                            <th>For Date</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Last Approved Date</th>
                            <th>Actions</th>
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
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        var smApprovalsTable = $('#sm-approvals-table').DataTable({
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
                url: "{{ route('task_continuation.index') }}",
            },
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'employee_name',
                    name: 'employee_name'
                },
                {
                    data: 'title',
                    name: 'title'
                },
                {
                    data: 'type',
                    name: 'type'
                },
                {
                    data: 'due_date',
                    name: 'due_date'
                },
                {
                    data: 'start_date_time',
                    name: 'start_date_time',
                    render: function(data) {
                        return data;
                    }
                },
                {
                    data: 'end_date_time',
                    name: 'end_date_time'
                },
                {
                    data: 'last_approved_date',
                    name: 'last_approved_date'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
            ]
        });

        // Handle Approve Early Action button click
        $('#sm-approvals-table').on('click', '.approve-early-action-btn', function() {
            var taskId = $(this).data('id');

            let url = '{{ route("tasks.approveEarlyAction", ["task" => ":id"]) }}';
            url = url.replace(':id', taskId);

            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    showToast(response.message, 'success');
                    smApprovalsTable.ajax.reload();
                },
                error: function(response) {
                    console.error('Error approving early action:', response);
                    let errorMessage = 'Error approving early action.';
                    if (response.responseJSON && response.responseJSON.message) {
                        errorMessage = response.responseJSON.message;
                    }
                    showToast(errorMessage, 'danger');
                }
            });
        });

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
    });
</script>
@endpush