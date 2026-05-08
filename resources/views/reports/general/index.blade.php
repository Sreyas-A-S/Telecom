@extends('layouts.admin')

@section('title', 'General Reports')

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3>General Reports</h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item active">General Reports</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h5>Generate Report</h5>
                </div>
                <div class="card-body">
                    <form id="reportForm">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label" for="report_type">Report Type</label>
                                <select class="form-select" id="report_type" name="type" required>
                                    <option value="" selected disabled>Select Type</option>
                                    <option value="leave">Leave Request</option>
                                    <option value="expense">Expense Request</option>
                                    <option value="legacy_expense">Legacy Expense Request</option>
                                    <option value="document">Document Request</option>
                                    <option value="loan">Loan Request</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="employee_id">Employee (Optional)</label>
                                <select class="form-select select2" id="employee_id" name="employee_id">
                                    <option value="">All Employees</option>
                                    @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="start_date">Start Date</label>
                                <input class="form-control" type="date" id="start_date" name="start_date">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="end_date">End Date</label>
                                <input class="form-control" type="date" id="end_date" name="end_date">
                            </div>
                        </div>
                        <div class="row mt-4">
                            <div class="col-12 text-end">
                                <button type="button" class="btn btn-primary" id="viewReportBtn">View Report</button>
                                <button type="button" class="btn btn-success" id="exportExcelBtn">Export Excel</button>
                                <button type="button" class="btn btn-danger" id="exportPdfBtn">Export PDF</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row" id="resultsContainer" style="display: none;">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h5>Report Results</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="reportTable">
                            <thead>
                                <tr id="tableHeaders">
                                    {{-- Headers will be populated via JS --}}
                                </tr>
                            </thead>
                            <tbody id="tableBody">
                                {{-- Rows will be populated via JS --}}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('.select2').select2();

        $('#report_type').change(function() {
            if ($(this).val() === 'legacy_expense') {
                var today = new Date();
                var day = today.getDay(); // 0 is Sunday

                // Set start date to last Sunday
                var lastSunday = new Date(today);
                lastSunday.setDate(today.getDate() - day);

                // Set end date to next Monday (9 days total: Sun to next Mon)
                var nextMonday = new Date(lastSunday);
                nextMonday.setDate(lastSunday.getDate() + 8);

                $('#start_date').val(lastSunday.toISOString().split('T')[0]);
                $('#end_date').val(nextMonday.toISOString().split('T')[0]);
            }
        });

        function getQueryParams() {
            return $('#reportForm').serialize();
        }

        $('#viewReportBtn').click(function() {
            var type = $('#report_type').val();
            if (!type) {
                alert('Please select a report type.');
                return;
            }

            $.ajax({
                url: "{{ route('general-reports.data') }}",
                data: getQueryParams(),
                method: 'GET',
                success: function(response) {
                    var data = response.data;
                    var headers = '';
                    var rows = '';

                    // Define headers based on type
                    if (type === 'leave') {
                        headers = '<th>SL.NO</th><th>Employee</th><th>Type</th><th>Duration</th><th>Reason</th><th>Status</th>';
                    } else if (type === 'expense') {
                        headers = '<th>SL.NO</th><th>Employee</th><th>Type</th><th>Date</th><th>Description</th><th>Amount</th><th>Status</th>';
                    } else if (type === 'legacy_expense') {
                        headers = '<th>SL.NO</th><th>Employee</th><th>Type</th><th>Date</th><th>Description</th><th>Amount</th><th>Approved Amount</th><th>Status</th>';
                    } else if (type === 'document') {
                        headers = '<th>SL.NO</th><th>Employee</th><th>Document Type</th><th>Date</th><th>Remarks</th><th>Status</th>';
                    } else if (type === 'loan') {
                        headers = '<th>SL.NO</th><th>Employee</th><th>Date</th><th>Amount</th><th>Status</th>';
                    }

                    if (type === 'legacy_expense') {
                        headers += '<th>Action</th>';
                    }

                    $('#tableHeaders').html(headers);
                    $('#tableBody').empty();

                    if (data.length === 0) {
                        $('#tableBody').html('<tr><td colspan="100%" class="text-center">No records found.</td></tr>');
                    } else {
                        data.forEach(function(item, index) {
                            var row = '<tr>';
                            row += '<td>' + (index + 1) + '</td>';
                            row += '<td>' + item.employee + '</td>';

                            if (type === 'leave') {
                                row += '<td>' + item.type + '</td>';
                                row += '<td>' + item.date + '</td>'; // Duration range
                                row += '<td>' + (item.details || '-') + '</td>';
                                row += '<td>' + item.status + '</td>';
                            } else if (type === 'expense') {
                                row += '<td>' + item.type + '</td>';
                                row += '<td>' + item.date + '</td>';
                                row += '<td>' + item.details + '</td>';
                                row += '<td>' + item.amount + '</td>';
                                row += '<td>' + item.status + '</td>';
                            } else if (type === 'legacy_expense') {
                                row += '<td>' + item.type + '</td>';
                                row += '<td>' + item.date + '</td>';
                                row += '<td>' + item.details + '</td>';
                                row += '<td>' + item.amount + '</td>';
                                row += '<td>' + item.approved_amount + '</td>';
                                row += '<td>' + item.status + '</td>';
                            } else if (type === 'document') {
                                row += '<td>' + item.type + '</td>';
                                row += '<td>' + item.date + '</td>';
                                row += '<td>' + item.details + '</td>';
                                row += '<td>' + item.status + '</td>';
                            } else if (type === 'loan') {
                                row += '<td>' + item.date + '</td>';
                                row += '<td>' + item.amount + '</td>';
                                row += '<td>' + item.status + '</td>';
                            }

                            if (type === 'legacy_expense') {
                                var viewUrl = "{{ route('expense-requests.view-legacy-report') }}?week_date=" + item.raw_date + "&employee_id=" + item.user_id;
                                var exportUrl = "{{ route('expense-requests.export-legacy-pdf') }}?week_date=" + item.raw_date + "&employee_id=" + item.user_id;
                                row += '<td><a href="' + viewUrl + '" class="btn btn-sm btn-primary" target="_blank" title="View Legacy Report"><i class="fas fa-eye"></i></a> ';
                                row += '<a href="' + exportUrl + '" class="btn btn-sm btn-dark" target="_blank" title="Export Legacy PDF"><i class="fas fa-file-invoice"></i></a></td>';
                            }

                            row += '</tr>';
                            $('#tableBody').append(row);
                        });
                    }

                    $('#resultsContainer').show();
                },
                error: function(xhr) {
                    alert('Error fetching data.');
                }
            });
        });

        $('#exportExcelBtn').click(function() {
            var type = $('#report_type').val();
            if (!type) {
                alert('Please select a report type.');
                return;
            }
            window.location.href = "{{ route('general-reports.export-excel') }}?" + getQueryParams();
        });

        $('#exportPdfBtn').click(function() {
            var type = $('#report_type').val();
            if (!type) {
                alert('Please select a report type.');
                return;
            }
            window.location.href = "{{ route('general-reports.export-pdf') }}?" + getQueryParams();
        });
    });
</script>
@endpush