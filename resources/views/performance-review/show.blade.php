@extends('layouts.admin')

@section('title', 'View Performance Review')

@section('breadcrumb')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3>Performance Review Details</h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">
                            <svg class="stroke-icon">
                                <use href="{{ asset('admin/assets/svg/icon-sprite.svg#stroke-home') }}"></use>
                            </svg></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('performance-review.index') }}">Performance Review</a></li>
                    <li class="breadcrumb-item active">View</li>
                </ol>
            </div>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <!-- Employee Profile Card -->
            <div class="card mb-4 shadow-sm border-0">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-4">
                            <img src="{{ $review->employee->employee->profile_pic ? asset($review->employee->employee->profile_pic) :'https://ui-avatars.com/api/?name='. urlencode($review->employee->employee->name) }}"
                                alt="Profile Picture"
                                class="rounded-circle border border-3 border-light shadow-sm"
                                width="100" height="100"
                                style="object-fit: cover;">
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h3 class="mb-1 fw-bold text-dark">
                                        {{ $review->employee->employee->name }}
                                        @if($review->review_year)
                                        <span class="badge bg-light text-primary border border-primary ms-2" style="font-size: 0.9rem;">Review Year: {{ $review->review_year }}</span>
                                        @endif
                                    </h3>
                                    <div class="d-flex align-items-center text-muted">
                                        <span class="badge bg-primary me-2">{{ $review->employee->employee->designation ??'N/A' }}</span>
                                        <span class="me-2">|</span>
                                        <i class="fa fa-building-o me-1"></i> {{ $review->employee->employee->department->name ??'N/A' }}
                                    </div>
                                </div>
                                @if($review->final_report_pdf)
                                <a href="{{ asset($review->final_report_pdf) }}" target="_blank" class="btn btn-outline-success rounded-pill px-4 me-2">
                                    <i class="fa fa-download me-2"></i> Final Report
                                </a>
                                @endif
                                <a href="{{ route('performance-review.export.pdf', $review->id) }}" class="btn btn-outline-primary rounded-pill px-4">
                                    <i class="fa fa-file-pdf-o me-2"></i> Export PDF
                                </a>
                            </div>

                            <div class="p-3 bg-light rounded-3">
                                <div class="row g-3">
                                    <div class="col-md-3 col-6 border-end">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3 text-primary">
                                                <i class="fa fa-id-card fa-2x"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">Employee ID</small>
                                                <span class="fw-bold text-dark">{{ $review->employee->employee->employee_id ??'N/A' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-6 border-end">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3 text-success">
                                                <i class="fa fa-calendar fa-2x"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">Joining Date</small>
                                                <span class="fw-bold text-dark">{{ $review->employee->employee && $review->employee->employee->joining_date ? \Carbon\Carbon::parse($review->employee->employee->joining_date)->format('d M, Y') :'N/A' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-6 border-end">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3 text-info">
                                                <i class="fa fa-user fa-2x"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">Reporting To</small>
                                                <span class="fw-bold text-dark">{{ $review->employee->employee->reporter->name ??'N/A' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3 text-warning">
                                                <i class="fa fa-clock-o fa-2x"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">Review Period</small>
                                                <span class="fw-bold text-dark">{{ $review->review_period }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Performance Evaluation</h5>
                    <div class="text-end">
                        <small class="text-muted d-block">Reviewer: <strong>{{ $review->reviewer && $review->reviewer->employee ? $review->reviewer->employee->name : 'N/A' }}</strong> on {{ \Carbon\Carbon::parse($review->review_date)->format('d M, Y') }}</small>
                        @if($review->updated_by)
                        <small class="text-info d-block">Last updated by: <strong>{{ $review->updater && $review->updater->employee ? $review->updater->employee->name : 'N/A' }}</strong> on {{ $review->updated_at->format('d M, Y H:i') }}</small>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        @if($review->final_report_pdf)
                        <div class="btn-group me-2" role="group">
                            <a href="{{ asset($review->final_report_pdf) }}" target="_blank" class="btn btn-sm btn-outline-info" title="View PDF">
                                <i class="fa fa-eye me-1"></i> View
                            </a>
                            <a href="{{ asset($review->final_report_pdf) }}" download class="btn btn-sm btn-outline-success" title="Download PDF">
                                <i class="fa fa-download me-1"></i> Download
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-danger" id="delete-report-btn" title="Remove PDF">
                                <i class="fa fa-trash me-1"></i> Remove
                            </button>
                        </div>
                        @endif

                        <form id="upload-report-form" class="m-0">
                            <label for="inline_final_report" id="upload-report-label" class="btn btn-sm btn-outline-primary mb-0" style="cursor: pointer;">
                                <i class="fa fa-upload me-1"></i> {{ $review->final_report_pdf ?'Change':'Upload Report' }}
                            </label>
                            <input type="file" id="inline_final_report" name="final_report" accept="application/pdf" style="display: none;">
                        </form>
                    </div>

                    <!-- Delete Report Confirmation Modal -->
                    <div class="modal fade" id="deleteReportModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Confirm Removal</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    Are you sure you want to remove the Final Report PDF?
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="button" class="btn btn-danger" id="confirm-delete-report">Remove</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Evaluation Criteria Removed -->


                {{-- Comments Section --}}
                <div class="card mt-4">
                    <div class="card-header">
                        <h6>Comments</h6>
                    </div>
                    <div class="card-body">
                        <div class="comments-section">
                            @forelse($review->comments as $comment)
                            <div class="d-flex mb-3" id="comment-{{ $comment->id }}">
                                <div class="flex-shrink-0">
                                    <img class="rounded-circle" src="https://ui-avatars.com/api/?name={{ urlencode($comment->user->name) }}" alt="User avatar" width="50">
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mt-0">{{ $comment->user->name }}
                                        @if($comment->user->employee && $comment->user->employee->department)
                                        <small class="text-muted">({{ $comment->user->employee->department->name }})</small>
                                        @endif
                                    </h6>
                                    <p>{{ $comment->comment }}</p>
                                    <small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
                                    @if(Auth::id() === $comment->user_id)
                                    <div class="mt-2">
                                        <button class="btn btn-primary btn-xs edit-comment me-1" data-comment-id="{{ $comment->id }}" data-comment-text="{{ $comment->comment }}" title="Edit Comment"><i class="fa fa-edit"></i></button>
                                        <button class="btn btn-danger btn-xs delete-comment" data-comment-id="{{ $comment->id }}" title="Delete Comment"><i class="fa fa-trash"></i></button>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @empty
                            <p id="no-comments-yet">No comments yet.</p>
                            @endforelse
                        </div>
                        <hr>
                        <div class="mt-3">
                            <h6 class="mb-3">Add a Comment</h6>
                            <form id="add-comment-form" action="{{ route('performance-review.comments.store', $review->id) }}" method="POST">
                                @csrf
                                <div class="form-group">
                                    <textarea class="form-control" name="comment" rows="3" placeholder="Enter your comment" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary mt-2">Add Comment</button>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
</div>

<!-- Edit Comment Modal -->
<div class="modal fade" id="editCommentModal" tabindex="-1" role="dialog" aria-labelledby="editCommentModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCommentModalLabel">Edit Comment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="edit-comment-form">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <textarea class="form-control" id="edit-comment-text" name="comment" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Comment Confirmation Modal -->
<div class="modal fade" id="deleteCommentConfirmationModal" tabindex="-1" role="dialog" aria-labelledby="deleteCommentConfirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteCommentConfirmationModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this comment?
                <input type="hidden" id="delete-comment-id">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteCommentBtn">Delete</button>
            </div>
        </div>
    </div>
</div>



@endsection

@push('scripts')
<script>
    $(document).ready(function() {

        $('#upload-report-form').on('submit', function(e) {
            e.preventDefault();

            // Show loader
            var $label = $('#upload-report-label');
            var originalContent = $label.html();
            $label.html('<i class="fa fa-spinner fa-spin me-1"></i> Uploading...').addClass('disabled').css('pointer-events', 'none');

            var formData = new FormData(this);
            formData.append('_method', 'PUT');
            formData.append('_token', '{{ csrf_token() }}');

            // We use the update route which handles file upload
            $.ajax({
                url: "{{ route('performance-review.update', $review->id) }}",
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    location.reload(); // Reload to show the new file
                },
                error: function(xhr) {
                    // Revert loader on error
                    $label.html(originalContent).removeClass('disabled').css('pointer-events', 'auto');
                    alert('Upload failed: ' + (xhr.responseJSON.message || 'Unknown error'));
                }
            });
        });

        // Add comment
        $('#add-comment-form').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            var url = form.attr('action');
            $.ajax({
                url: url,
                type: 'POST',
                data: form.serialize(),
                success: function(response) {
                    var comment = response.comment;
                    var newCommentHtml = `
                            <div class="d-flex mb-3" id="comment-${comment.id}">
                                <div class="flex-shrink-0">
                                    <img class="rounded-circle" src="https://ui-avatars.com/api/?name=${encodeURIComponent(comment.user.name)}" alt="User avatar" width="50">
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mt-0">${comment.user.name}</h6>
                                    <p>${comment.comment}</p>
                                    <small class="text-muted">Just now</small>
                                    <div class="mt-2">
                                        <button class="btn btn-primary btn-xs edit-comment me-1" data-comment-id="${comment.id}" data-comment-text="${comment.comment}" title="Edit Comment"><i class="fa fa-edit"></i></button>
                                        <button class="btn btn-danger btn-xs delete-comment" data-comment-id="${comment.id}" title="Delete Comment"><i class="fa fa-trash"></i></button>
                                    </div>
                                </div>
                            </div>
                        `;
                    $('.comments-section').append(newCommentHtml);
                    $('#no-comments-yet').hide();
                    form[0].reset();
                    showToast(response.success, 'success');
                },
                error: function(xhr) {
                    showToast('Error adding comment.', 'danger');
                }
            });
        });

        // Edit comment - show modal
        $('.comments-section').on('click', '.edit-comment', function() {
            var commentId = $(this).data('comment-id');
            // Fetch the latest comment data from the server
            $.ajax({
                url: '/performance-review/comments/' + commentId,
                type: 'GET',
                success: function(response) {
                    $('#edit-comment-text').val(response.comment);
                    $('#edit-comment-form').attr('action', '/performance-review/comments/' + commentId);
                    $('#editCommentModal').modal('show');
                },
                error: function(xhr) {
                    showToast('Error fetching comment data.', 'danger');
                }
            });
        });

        // Edit comment - submit form
        $('#edit-comment-form').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            var url = form.attr('action');
            $.ajax({
                url: url,
                type: 'POST', // Using POST with _method: 'PUT'
                data: form.serialize(),
                success: function(response) {
                    var commentId = url.split('/').pop();
                    var newCommentText = $('#edit-comment-text').val();
                    $('#comment-' + commentId).find('p').text(newCommentText);
                    // Update the data-comment-text attribute of the edit button
                    $('#comment-' + commentId).find('.edit-comment').attr('data-comment-text', newCommentText);
                    $('#editCommentModal').modal('hide');
                    showToast(response.success, 'success');
                },
                error: function(xhr) {
                    showToast('Error updating comment.', 'danger');
                }
            });
        });

        // Delete comment - show confirmation modal
        $('.comments-section').on('click', '.delete-comment', function() {
            var commentId = $(this).data('comment-id');
            $('#delete-comment-id').val(commentId); // Store comment ID in hidden input
            $('#deleteCommentConfirmationModal').modal('show');
        });

        // Confirm Delete Comment
        $('#confirmDeleteCommentBtn').on('click', function() {
            var commentId = $('#delete-comment-id').val(); // Retrieve comment ID
            $.ajax({
                url: '/performance-review/comments/' + commentId,
                type: 'POST', // Using POST with _method: 'DELETE'
                data: {
                    _method: 'DELETE',
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    $('#comment-' + commentId).remove();
                    $('#deleteCommentConfirmationModal').modal('hide');
                    showToast(response.success, 'success');
                    // If no comments left, show "No comments yet."
                    if ($('.comments-section').children('.d-flex').length === 0) {
                        $('#no-comments-yet').show();
                    }
                },
                error: function(xhr) {
                    $('#deleteCommentConfirmationModal').modal('hide');
                    showToast('Error deleting comment.', 'danger');
                }
            });
        });
        // Auto-upload when file is selected
        $('#inline_final_report').on('change', function() {
            if (this.files && this.files.length > 0) {
                $('#upload-report-form').submit();
            }
        });

        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });

        // Handle Report Deletion
        $('#delete-report-btn').on('click', function() {
            $('#deleteReportModal').modal('show');
        });

        $('#confirm-delete-report').on('click', function() {
            $.ajax({
                url: "{{ route('performance-review.remove-report', $review->id) }}",
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    _method: 'PUT',
                    remove_report: true
                },
                success: function(response) {
                    location.reload();
                },
                error: function(xhr) {
                    $('#deleteReportModal').modal('hide');
                    alert('Error removing report: ' + (xhr.responseJSON.message || 'Unknown error'));
                }
            });
        });
    });
</script>
@endpush