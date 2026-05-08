@extends('layouts.admin')

@section('title', 'Job Vacancies')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Job Vacancies</h5>
                    <div class="d-flex gap-2">
                        @if(checkMenu(Session::get('role_id'), 33, 'read'))
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#exportVacancyModal">Export to Excel</button>
                        @endif
                        @if(checkMenu(Session::get('role_id'), 33, 'create'))
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createVacancyModal">Create Job Vacancy</button>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="display datatables" id="vacancies-table" style="width:100%">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Views</th>
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
</div>

<!-- Create Job Vacancy Modal -->
<div class="modal fade" id="createVacancyModal" tabindex="-1" role="dialog" aria-labelledby="createVacancyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createVacancyModalLabel">Create Job Vacancy</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="create-vacancy-form">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="vacancy_title" class="form-label">Title</label>
                                <input type="text" class="form-control" id="vacancy_title" name="title" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="vacancy_status" class="form-label">Status</label>
                                <select class="form-control" id="vacancy_status" name="status">
                                    <option value="Open">Open</option>
                                    <option value="Closed">Closed</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="vacancy_description" class="form-label">Description</label>
                                <textarea class="form-control" id="vacancy_description" name="description"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Custom Application Form Fields</h6>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="addField('create')">
                                    <i class="fa fa-plus me-1"></i> Add Field
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="loadDefaultFields('create')">
                                    <i class="fa fa-list me-1"></i> Load Defaults
                                </button>
                            </div>
                        </div>

                        <div id="create-form-fields-container" class="row">
                            <!-- Dynamic fields will be added here -->
                        </div>
                    </div>

                    <div class="text-end mt-4">
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Vacancy</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Job Vacancy Modal -->
<div class="modal fade" id="editVacancyModal" tabindex="-1" role="dialog" aria-labelledby="editVacancyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editVacancyModalLabel">Edit Job Vacancy</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="edit-vacancy-form">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_vacancy_id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_vacancy_title" class="form-label">Title</label>
                                <input type="text" class="form-control" id="edit_vacancy_title" name="title" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_vacancy_status" class="form-label">Status</label>
                                <select class="form-control" id="edit_vacancy_status" name="status">
                                    <option value="Open">Open</option>
                                    <option value="Closed">Closed</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="edit_vacancy_description" class="form-label">Description</label>
                                <textarea class="form-control" id="edit_vacancy_description" name="description"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Custom Application Form Fields</h6>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="addField('edit')">
                                    <i class="fa fa-plus me-1"></i> Add Field
                                </button>
                            </div>
                        </div>

                        <div id="edit-form-fields-container" class="row">
                            <!-- Dynamic fields will be added here -->
                        </div>
                    </div>

                    <div class="text-end mt-4">
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Vacancy</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete Vacancy Modal -->
<div class="modal fade" id="deleteVacancyModal" tabindex="-1" role="dialog" aria-labelledby="deleteVacancyModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteVacancyModalLabel">Confirm Delete</h5>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this vacancy?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteVacancyBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Load Defaults Confirmation Modal -->
<div class="modal fade" id="loadDefaultsConfirmModal" tabindex="-1" role="dialog" aria-labelledby="loadDefaultsConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="loadDefaultsConfirmModalLabel">Confirm Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                This will append standard fields (Name, Email, Contact, etc.) to your form. Continue?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmLoadDefaultsBtn">Continue</button>
            </div>
        </div>
    </div>
</div>

<!-- Export Vacancies Modal -->
<div class="modal fade" id="exportVacancyModal" tabindex="-1" role="dialog" aria-labelledby="exportVacancyModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exportVacancyModalLabel">Export Job Vacancies</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('job-vacancies.export') }}" method="GET">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="from_date" class="form-label">From Date</label>
                                <input type="date" class="form-control" id="from_date" name="from_date">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="to_date" class="form-label">To Date</label>
                                <input type="date" class="form-control" id="to_date" name="to_date">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Export Excel</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<style>
    .f-10 {
        font-size: 10px;
    }

    .field-card {
        transition: all 0.2s ease;
    }

    .field-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .15) !important;
    }

    .field-controls {
        cursor: grab;
    }

    .field-controls:active {
        cursor: grabbing;
    }

    .remove-field-btn {
        width: 30px;
        height: 30px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #fff;
        color: #dc3545;
        border: 1px solid #fee2e2;
        box-shadow: 0 2px 4px rgba(220, 53, 69, 0.05);
        transition: all 0.2s ease;
        text-decoration: none;
        cursor: pointer;
    }

    .remove-field-btn:hover {
        background-color: #dc3545;
        color: #fff;
        border-color: #dc3545;
        transform: scale(1.05);
        box-shadow: 0 4px 8px rgba(220, 53, 69, 0.2);
    }


    .options-container {
        display: none;
    }

    /* Sortable Placeholder Style */
    .ui-state-highlight {
        height: 100%;
        border: 2px dashed #f1f1f1;
        background-color: #fcfcfc;
        border-radius: 8px;
        margin-bottom: 20px;
        visibility: visible !important;
    }

    .field-wrapper {
        transition: transform 0.2s ease;
    }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
<script>
    function addField(modalType, fieldData = null) {
        let containerId = modalType === 'create' ? 'create-form-fields-container' : 'edit-form-fields-container';
        let container = $('#' + containerId);
        let fieldIndex = container.children().length;

        let label = fieldData ? fieldData.label : '';
        let type = fieldData ? fieldData.type : 'text';
        let options = fieldData ? fieldData.options : '';
        let required = fieldData && fieldData.required ? 'checked' : '';
        let placeholder = fieldData && fieldData.placeholder ? fieldData.placeholder : 'e.g. Resume';

        let optionsStyle = (type === 'select' || type === 'radio' || type === 'checkbox') ? 'display: block;' : 'display: none;';

        let html = `
            <div class="col-md-6 mb-3 field-wrapper" data-index="${fieldIndex}">
                <div class="card h-100 border shadow-sm field-card" style="background-color: #f8f9fa;">
                    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center py-2">
                        <div class="d-flex align-items-center gap-2 field-controls" style="cursor: grab;">
                            <i class="fa fa-grip-vertical text-muted"></i>
                            <span class="fw-bold text-secondary field-sl-no">Field #${fieldIndex + 1}</span>
                        </div>
                        <a href="javascript:void(0)" class="remove-field-btn" onclick="removeField(this)" title="Remove Field">
                            <i class="fa fa-trash-alt"></i>
                        </a>
                    </div>
                    <div class="card-body pt-0">
                        <div class="mb-2">
                            <label class="form-label f-10 text-muted text-uppercase fw-bold mb-1">Label</label>
                            <input type="text" class="form-control form-control-sm field-label" value="${label}" placeholder="${placeholder}" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label f-10 text-muted text-uppercase fw-bold mb-1">Type</label>
                            <select class="form-control form-control-sm field-type" onchange="toggleOptions(this)">
                                <option value="text" ${type === 'text' ? 'selected' : ''}>Text Input</option>
                                <option value="number" ${type === 'number' ? 'selected' : ''}>Number</option>
                                <option value="email" ${type === 'email' ? 'selected' : ''}>Email</option>
                                <option value="date" ${type === 'date' ? 'selected' : ''}>Date</option>
                                <option value="textarea" ${type === 'textarea' ? 'selected' : ''}>Textarea</option>
                                <option value="select" ${type === 'select' ? 'selected' : ''}>Select Dropdown</option>
                                <option value="radio" ${type === 'radio' ? 'selected' : ''}>Radio Buttons</option>
                                <option value="checkbox" ${type === 'checkbox' ? 'selected' : ''}>Checkboxes</option>
                                <option value="file" ${type === 'file' ? 'selected' : ''}>File Upload</option>
                            </select>
                        </div>
                        <div class="mb-2 options-container" style="${optionsStyle}">
                            <label class="form-label f-10 text-muted text-uppercase fw-bold mb-1">Options</label>
                            <input type="text" class="form-control form-control-sm field-options" value="${options}" placeholder="e.g. PHP, JS, Python">
                        </div>
                        <div class="mt-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input field-required" type="checkbox" id="${modalType}_req_${fieldIndex}" ${required}>
                                <label class="form-check-label f-12 ms-1" for="${modalType}_req_${fieldIndex}">Required Field</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        container.append(html);
        updateSerialNumbers(containerId);
    }

    function removeField(btn) {
        let container = $(btn).closest('.row'); // Get container before removing
        let containerId = container.attr('id');
        $(btn).closest('.field-wrapper').remove();
        updateSerialNumbers(containerId);
    }

    function updateSerialNumbers(containerId) {
        $('#' + containerId + ' .field-wrapper').each(function(index) {
            $(this).find('.field-sl-no').text('Field #' + (index + 1));
        });
    }

    function toggleOptions(select) {
        let val = $(select).val();
        let fieldCard = $(select).closest('.field-card');
        let optionsContainer = fieldCard.find('.options-container');

        if (val === 'select' || val === 'radio' || val === 'checkbox') {
            optionsContainer.show();
        } else {
            optionsContainer.hide();
        }
    }

    let currentModalTypeForDefaults = '';

    function loadDefaultFields(modalType) {
        currentModalTypeForDefaults = modalType;
        $('#loadDefaultsConfirmModal').modal('show');
    }

    function getFormFields(containerId) {
        let fields = [];
        $('#' + containerId + ' .field-card').each(function() {
            let label = $(this).find('.field-label').val();
            let type = $(this).find('.field-type').val();
            if (label) {
                let fieldData = {
                    label: label,
                    type: type,
                    required: $(this).find('.field-required').is(':checked')
                };

                if (type === 'select' || type === 'radio' || type === 'checkbox') {
                    fieldData.options = $(this).find('.field-options').val();
                }

                fields.push(fieldData);
            }
        });
        return fields;
    }

    $(document).ready(function() {
        // Confirm Button Logic for Load Defaults
        $('#confirmLoadDefaultsBtn').on('click', function() {
            const defaultFields = [{
                    label: 'Resume / CV',
                    type: 'file',
                    required: true,
                    placeholder: 'Upload your resume'
                },
                {
                    label: 'Cover Letter',
                    type: 'textarea',
                    required: false,
                    placeholder: 'Briefly explain why you are a good fit...'
                },
                {
                    label: 'Portfolio / LinkedIn URL',
                    type: 'text',
                    required: false,
                    placeholder: 'https://...'
                }
            ];

            defaultFields.forEach(field => {
                addField(currentModalTypeForDefaults, field);
            });

            $('#loadDefaultsConfirmModal').modal('hide');
        });

        // Initialize Summernote
        $('#vacancy_description, #edit_vacancy_description').summernote({
            placeholder: 'Enter detailed job description...',
            tabsize: 2,
            height: 250,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'video']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ]
        });

        // Initialize Sortable for Drag & Drop
        $("#create-form-fields-container, #edit-form-fields-container").sortable({
            handle: ".field-controls",
            placeholder: "col-md-6 mb-3 ui-state-highlight",
            forcePlaceholderSize: true,
            tolerance: "pointer",
            revert: false,
            cursor: "grabbing",
            opacity: 0.9,
            start: function(e, ui) {
                ui.placeholder.height(ui.item.height());
            },
            stop: function(e, ui) {
                let containerId = ui.item.parent().attr('id');
                updateSerialNumbers(containerId);
            }
        });

        // Vacancies DataTable
        var vacanciesTable = $('#vacancies-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('job-vacancies.index') }}",
            columns: [{
                    data: 'id',
                    name: 'id'
                },
                {
                    data: 'title',
                    name: 'title'
                },
                {
                    data: 'description',
                    name: 'description',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'status',
                    name: 'status'
                },
                {
                    data: 'views',
                    name: 'views',
                    searchable: false
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                }
            ]
        });

        $('#create-vacancy-form').on('submit', function(e) {
            e.preventDefault();

            if ($('#vacancy_description').summernote('isEmpty')) {
                showToast('Description is required.', 'danger');
                return;
            }

            let fields = getFormFields('create-form-fields-container');
            let data = {
                _token: "{{ csrf_token() }}",
                title: $('#vacancy_title').val(),
                description: $('#vacancy_description').val(),
                status: $('#vacancy_status').val(),
                form_fields: fields
            };

            $.ajax({
                url: "{{ route('job-vacancies.store') }}",
                type: "POST",
                data: data,
                success: function(response) {
                    $('#createVacancyModal').modal('hide');
                    vacanciesTable.draw();
                    showToast(response.success, 'success');
                    $('#create-vacancy-form')[0].reset();
                    $('#vacancy_description').summernote('reset');
                    $('#create-form-fields-container').empty();
                },
                error: function(xhr) {
                    showToast('Error creating vacancy.', 'danger');
                }
            });
        });

        // Edit Vacancy
        $('#vacancies-table').on('click', '.edit-vacancy-btn', function() {
            var id = $(this).data('id');
            $('#edit-form-fields-container').empty();
            $.get('/job-vacancies/' + id, function(data) {
                $('#edit_vacancy_id').val(data.id);
                $('#edit_vacancy_title').val(data.title);
                $('#edit_vacancy_description').summernote('code', data.description);
                $('#edit_vacancy_status').val(data.status);

                if (data.form_fields) {
                    let fields = data.form_fields;
                    if (typeof fields === 'string') {
                        try {
                            fields = JSON.parse(fields);
                        } catch (e) {
                            fields = [];
                        }
                    }

                    if (Array.isArray(fields)) {
                        fields.forEach(field => {
                            addField('edit', field);
                        });
                    }
                }

                $('#editVacancyModal').modal('show');
            });
        });

        $('#edit-vacancy-form').on('submit', function(e) {
            e.preventDefault();

            if ($('#edit_vacancy_description').summernote('isEmpty')) {
                showToast('Description is required.', 'danger');
                return;
            }

            var id = $('#edit_vacancy_id').val();
            let fields = getFormFields('edit-form-fields-container');
            let data = {
                _token: "{{ csrf_token() }}",
                _method: "PUT",
                title: $('#edit_vacancy_title').val(),
                description: $('#edit_vacancy_description').val(),
                status: $('#edit_vacancy_status').val(),
                form_fields: fields
            };

            $.ajax({
                url: '/job-vacancies/' + id,
                type: "POST",
                data: data,
                success: function(response) {
                    $('#editVacancyModal').modal('hide');
                    vacanciesTable.draw();
                    showToast(response.success, 'success');
                },
                error: function(xhr) {
                    showToast('Error updating vacancy.', 'danger');
                }
            });
        });

        // Delete Vacancy
        $('#vacancies-table').on('click', '.delete-vacancy-btn', function() {
            var id = $(this).data('id');
            $('#deleteVacancyModal').modal('show');
            $('#confirmDeleteVacancyBtn').off('click').on('click', function() {
                $.ajax({
                    url: '/job-vacancies/' + id,
                    type: 'POST',
                    data: {
                        _method: 'DELETE',
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        $('#deleteVacancyModal').modal('hide');
                        vacanciesTable.draw();
                        showToast(response.success, 'success');
                    },
                    error: function(xhr) {
                        showToast('Error deleting vacancy.', 'danger');
                        $('#deleteVacancyModal').modal('hide');
                    }
                });
            });
        });

        // Share Vacancy Link
        $('#vacancies-table').on('click', '.share-btn', function(e) {
            e.preventDefault();
            var baseUrl = $(this).data('link');
            var id = $(this).data('id');
            var referrerId = "{{ Auth::id() }}";
            var url = baseUrl + '?ref=' + referrerId;

            $.ajax({
                url: '/job-vacancies/' + id + '/track-copy',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                }
            });

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(url).then(function() {
                    showToast('Link copied to clipboard!', 'success');
                }, function(err) {
                    fallbackCopyTextToClipboard(url);
                });
            } else {
                fallbackCopyTextToClipboard(url);
            }
        });

        function fallbackCopyTextToClipboard(text) {
            var textArea = document.createElement("textarea");
            textArea.value = text;
            textArea.style.position = "fixed";
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            try {
                var successful = document.execCommand('copy');
                if (successful) showToast('Link copied to clipboard!', 'success');
                else showToast('Failed to copy link.', 'danger');
            } catch (err) {
                showToast('Failed to copy link.', 'danger');
            }
            document.body.removeChild(textArea);
        }
    });
</script>
@endpush