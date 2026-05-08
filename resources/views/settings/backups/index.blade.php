@extends('layouts.admin')
@section('title', 'Backups')

@section('css')
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>Backups</h5>
                <div class="d-flex gap-2">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createBackupModal">Create Backup</button>
                    <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#uploadBackupModal">Upload Backup</button>
                </div>
            </div>
            <div class="card-body">
                @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <div class="table-responsive">
                    <table class="table table-bordered" id="backups-table">
                        <thead>
                            <tr>
                                <th>Sl No</th>
                                <th>Name</th>
                                <th>Size</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Backup Modal -->
<div class="modal fade" id="createBackupModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="createBackupForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Create Backup</h5>
                    <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Backup Type</label>
                        <select class="form-select" name="type" id="backupType" onchange="toggleTableSelection()">
                            <option value="full">Full Backup (All Tables + Files)</option>
                            <option value="selective">Selective Backup</option>
                        </select>
                    </div>

                    <div id="selectiveOptions" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label">Include Files (Storage)</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="include_files" id="includeFiles">
                                <label class="form-check-label" for="includeFiles">Yes, include uploaded files</label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label mb-0">Select Data</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="advancedModeToggle" onchange="toggleAdvancedMode()">
                                    <label class="form-check-label" for="advancedModeToggle">Advanced Mode</label>
                                </div>
                            </div>

                            <!-- Modules View -->
                            <div id="modules-view">
                                <div class="row">
                                    @foreach($organizedTables as $module => $tables)
                                    <div class="col-md-6 mb-3">
                                        <div class="card h-100 border module-selection-card"
                                            style="cursor: pointer; transition: all 0.2s;"
                                            onclick="document.getElementById('module_{{ Str::slug($module) }}').click()">
                                            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                                                <div>
                                                    <h6 class="mb-1 text-dark fw-bold">{{ $module }}</h6>
                                                    <span class="text-muted small"><i class="fa fa-table me-1"></i>{{ count($tables) }} Tables</span>
                                                </div>
                                                <div class="form-check form-switch pointer-events-none"> <!-- pointer-events-none so clicking container toggles it -->
                                                    <input class="form-check-input module-checkbox fs-4" type="checkbox"
                                                        id="module_{{ Str::slug($module) }}"
                                                        onclick="event.stopPropagation()"
                                                        onchange="toggleModuleTables('{{ Str::slug($module) }}', this); updateCardVisual(this);" checked>
                                                </div>
                                            </div>

                                            <!-- Hidden Inputs for actual tables, managed by JS -->
                                            <div class="d-none module-tables-group" data-module="{{ Str::slug($module) }}">
                                                @foreach($tables as $table)
                                                <input type="checkbox" name="tables[]" value="{{ $table }}" class="table-checkbox" checked>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Advanced View (Raw Tables) -->
                            <div id="advanced-view" style="display: none;">
                                <div class="row" style="max-height: 300px; overflow-y: auto;">
                                    @foreach($organizedTables as $module => $tables)
                                    <div class="col-12 mb-2">
                                        <h6 class="border-bottom pb-1">{{ $module }}</h6>
                                    </div>
                                    @foreach($tables as $table)
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input table-checkbox-visible" type="checkbox"
                                                data-mirror-for="{{ $table }}"
                                                onchange="syncTableCheckbox('{{ $table }}', this)" checked>
                                            <label class="form-check-label" for="visible_table_{{ $table }}">{{ $table }}</label>
                                        </div>
                                    </div>
                                    @endforeach
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Close</button>
                    <button class="btn btn-primary" type="submit">Create Backup</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Upload Backup Modal -->
<div class="modal fade" id="uploadBackupModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('backups.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Upload Backup</h5>
                    <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Select Backup File (.zip)</label>
                        <input type="file" class="form-control" name="backup_file" required accept=".zip">
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Close</button>
                    <button class="btn btn-primary" type="submit">Upload & List</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#backups-table').DataTable({
            processing: true,
            serverSide: false, // Client side processing of the JSON data
            ajax: "{{ route('backups.index') }}",
            columns: [{
                    data: null,
                    name: 'sl_no',
                    searchable: false,
                    orderable: false,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'size',
                    name: 'size'
                },
                {
                    data: 'date',
                    name: 'date'
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        var downloadUrl = "{{ route('backups.download',':name') }}".replace(':name', row.name);
                        var restoreUrl = "{{ route('backups.restore',':name') }}".replace(':name', row.name);
                        var deleteUrl = "{{ route('backups.destroy',':name') }}".replace(':name', row.name);
                        var csrfToken = "{{ csrf_token() }}";

                        return `
                            <div class="d-flex gap-2">
                                <a href="${downloadUrl}" class="btn btn-sm btn-info" title="Download"><i class="fa fa-download"></i></a>
                                
                                <form action="${restoreUrl}" method="POST" onsubmit="return confirm('Are you sure you want to restore this backup? Current data will be overwritten.');" style="display:inline;">
                                    <input type="hidden" name="_token" value="${csrfToken}">
                                    <button type="submit" class="btn btn-sm btn-warning" title="Restore"><i class="fa fa-refresh"></i></button>
                                </form>

                                <form action="${deleteUrl}" method="POST" onsubmit="return confirm('Are you sure?');" style="display:inline;">
                                    <input type="hidden" name="_token" value="${csrfToken}">
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete"><i class="fa fa-trash"></i></button>
                                </form>
                            </div>
                        `;
                    }
                }
            ],
            order: [
                [3, "desc"] // Sort by Date column (index 3 now)
            ]
        });
    });

    function toggleTableSelection() {
        var type = document.getElementById('backupType').value;
        var options = document.getElementById('selectiveOptions');
        if (type === 'selective') {
            options.style.display = 'block';
        } else {
            options.style.display = 'none';
        }
    }

    function toggleAdvancedMode() {
        var isAdvanced = document.getElementById('advancedModeToggle').checked;
        var modulesView = document.getElementById('modules-view');
        var advancedView = document.getElementById('advanced-view');

        if (isAdvanced) {
            modulesView.style.display = 'none';
            advancedView.style.display = 'block';
        } else {
            modulesView.style.display = 'block';
            advancedView.style.display = 'none';
        }
    }

    function toggleModuleTables(moduleSlug, checkbox) {
        var isChecked = checkbox.checked;
        // Find the hidden container for this module's tables
        var group = document.querySelector('.module-tables-group[data-module="' + moduleSlug + '"]');
        if (group) {
            // Check/Uncheck the hidden inputs (real form inputs)
            var hiddenInputs = group.querySelectorAll('input.table-checkbox');
            hiddenInputs.forEach(function(input) {
                input.checked = isChecked;
                // Also sync the visible advanced checkbox if it exists
                var visibleCheckbox = document.querySelector('input.table-checkbox-visible[data-mirror-for="' + input.value + '"]');
                if (visibleCheckbox) {
                    visibleCheckbox.checked = isChecked;
                }
            });
        }
    }

    function syncTableCheckbox(tableName, visibleCheckbox) {
        // Find the hidden 'real' input and sync it
        var realInput = document.querySelector('input.table-checkbox[value="' + tableName + '"]');
        if (realInput) {
            realInput.checked = visibleCheckbox.checked;
        }

        // Optional: If we uncheck a table, we should probably uncheck the parent module in Modules view
        // But for simplicity, we leave it as is, or we could add logic to check if all siblings are checked.
    }

    function updateCardVisual(checkbox) {
        // Find parent card
        var card = checkbox.closest('.card');
        if (checkbox.checked) {
            card.classList.add('border-primary');
            card.classList.add('bg-light-primary'); // Optional: if template supports it, else just border
        } else {
            card.classList.remove('border-primary');
            card.classList.remove('bg-light-primary');
        }
    }

    // Initialize visuals
    document.addEventListener('DOMContentLoaded', function() {
        var checkboxes = document.querySelectorAll('.module-checkbox');
        checkboxes.forEach(function(cb) {
            updateCardVisual(cb);
        });

        // Handle Form Submission
        // Handle Form Submission
        $(document).on('submit', '#createBackupForm', function(e) {
            e.preventDefault();

            var form = $(this);
            var submitBtn = form.find('button[type="submit"]');
            var originalBtnText = submitBtn.text();

            var modal = $('#createBackupModal');
            var modalInstance = bootstrap.Modal.getInstance(modal[0]);

            // Show Loading State
            submitBtn.prop('disabled', true).text('Creating...');

            $.ajax({
                url: "{{ route('backups.store') }}",
                type: 'POST',
                data: form.serialize(),
                dataType: 'json',
                headers: {
                    'Accept': 'application/json'
                },
                success: function(response) {
                    if (response.success) {
                        showToast(response.message, 'success');

                        modalInstance.hide();
                        $('#backups-table').DataTable().ajax.reload();
                        form[0].reset();
                    } else {
                        showToast(response.message, 'danger');
                    }
                },
                error: function(xhr) {
                    var msg = 'An error occurred.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    } else if (xhr.responseText) {
                        // msg = xhr.responseText.substring(0, 100); 
                    }

                    showToast(msg, 'danger');
                },
                complete: function() {
                    // Reset button state
                    submitBtn.prop('disabled', false).text(originalBtnText);
                }
            });
        });
    });
</script>
@endpush