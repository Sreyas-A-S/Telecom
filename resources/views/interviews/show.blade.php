@extends('layouts.admin')

@section('title', 'Interview Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Left Column -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Interview Details: {{ $interview->title }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="mb-3">Candidate Details</h6>
                            <dl class="row">
                                <dt class="col-sm-5">Job Vacancy:</dt>
                                <dd class="col-sm-7">
                                    @if($interview->jobVacancy)
                                    <a href="{{ route('job-vacancies.show', $interview->job_vacancy_id) }}" target="_blank">
                                        {{ $interview->jobVacancy->title }}
                                    </a>
                                    @else
                                    N/A
                                    @endif
                                </dd>

                                <dt class="col-sm-5">Post Applied For:</dt>
                                <dd class="col-sm-7">{{ $interview->post_applied_for }}</dd>

                                <dt class="col-sm-5">Candidate Name:</dt>
                                <dd class="col-sm-7">{{ $interview->candidate_name }}</dd>

                                <dt class="col-sm-5">Contact Number:</dt>
                                <dd class="col-sm-7">{{ $interview->contact_number }}</dd>

                                <dt class="col-sm-5">Email Id:</dt>
                                <dd class="col-sm-7">{{ $interview->email_id }}</dd>

                                <dt class="col-sm-5">Educational Qualification:</dt>
                                <dd class="col-sm-7">{{ $interview->educational_qualification }}</dd>

                                <dt class="col-sm-5">Resume Attachment:</dt>
                                <dd class="col-sm-7">
                                    @if($interview->resume)
                                        @php
                                            $isManual = Str::startsWith($interview->resume, 'uploads/');
                                            $fileExists = $isManual ? file_exists(public_path($interview->resume)) : Storage::disk('public')->exists($interview->resume);
                                            $url = $isManual ? asset($interview->resume) : Storage::url($interview->resume);
                                        @endphp
                                        
                                        @if($fileExists)
                                            <a href="{{ $url }}" target="_blank" class="btn btn-xs btn-primary">
                                                <i class="fa fa-download"></i> Download Resume
                                            </a>
                                        @else
                                            <span class="text-danger"><i class="fa fa-exclamation-triangle"></i> File not found on server</span>
                                        @endif
                                    @else
                                        <span class="text-muted">No resume attached</span>
                                    @endif
                                </dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-3">Professional Background</h6>
                            <dl class="row">
                                <dt class="col-sm-5">Years of Experience:</dt>
                                <dd class="col-sm-7">{{ $interview->years_of_experience }}</dd>

                                <dt class="col-sm-5">Current Employer:</dt>
                                <dd class="col-sm-7">{{ $interview->current_employer }}</dd>

                                <dt class="col-sm-5">Last/Current CTC:</dt>
                                <dd class="col-sm-7">{{ $interview->last_current_ctc }}</dd>

                                <dt class="col-sm-5">Expected CTC:</dt>
                                <dd class="col-sm-7">{{ $interview->expected_ctc }}</dd>

                                <dt class="col-sm-5">Notice Period:</dt>
                                <dd class="col-sm-7">{{ $interview->notice_period }}</dd>
                            </dl>
                        </div>
                    </div>

                    @if($interview->custom_form_responses && count($interview->custom_form_responses) > 0)
                    <hr>
                    <div class="row">
                        <div class="col-12">
                            <h6 class="mb-3 text-primary">Custom Application Data</h6>
                            <div class="row">
                                @foreach($interview->custom_form_responses as $response)
                                <div class="col-md-6 mb-2">
                                    <div class="bg-light p-2 px-3 rounded">
                                        <small class="text-muted d-block">{{ $response['label'] }}</small>
                                        <span class="fw-bold">
                                            @if(is_array($response['value']))
                                            {{ implode(', ', $response['value']) }}
                                            @else
                                            {{ $response['value'] ?? 'N/A' }}
                                            @endif
                                        </span>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                    <hr>

                    <h6 class="mb-3">Candidate Evaluation</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="thead-light">
                                <tr>
                                    <th>Criteria</th>
                                    <th class="text-center">Rating (out of 5)</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                $ratings = [
                                'Communication Skills' => 'communication_skills',
                                'Technical Knowledge' => 'technical_knowledge',
                                'Problem Solving Ability' => 'problem_solving_ability',
                                'Knowledge of Heavy Equipments' => 'knowledge_of_heavy_equipments',
                                'Relevant Work Experience' => 'relevant_work_experience',
                                'Attitude and Confidence' => 'attitude_and_confidence',
                                'Adaptability/Flexibility' => 'adaptability_flexibility',
                                'Teamwork and Collaboration' => 'teamwork_collaboration',
                                'Leadership Potential' => 'leadership_potential',
                                'Willingness to Travel/Relocate' => 'willingness_to_travel_relocate',
                                ];
                                @endphp
                                @foreach ($ratings as $label => $field)
                                <tr>
                                    <td>{{ $label }}</td>
                                    <td class="text-center">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <i class="fa fa-star {{ $i <= $interview->{$field.'_rating'} ?'text-warning':'text-muted' }}"></i>
                                            @endfor
                                    </td>
                                    <td>{{ $interview->{$field.'_remarks'} }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Interviewer Recommendation</h6>
                </div>
                <div class="card-body text-center">
                    @php
                    $recommendation = $interview->interviewer_recommendation;
                    $badgeClass = 'secondary';
                    if ($recommendation == 'Highly Recommended') {
                    $badgeClass = 'success';
                    } elseif ($recommendation == 'Recommended') {
                    $badgeClass = 'primary';
                    } elseif ($recommendation == 'Consider for Other Role') {
                    $badgeClass = 'info';
                    } elseif ($recommendation == 'Not Recommended') {
                    $badgeClass = 'danger';
                    }
                    @endphp
                    <span class="badge badge-lg bg-{{ $badgeClass }}">{{ $recommendation ??'N/A' }}</span>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="mb-0">Job Offer Details</h6>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-6">Salary Offered:</dt>
                        <dd class="col-sm-6">{{ $interview->salary_offered ??'N/A' }}</dd>

                        <dt class="col-sm-6">DA:</dt>
                        <dd class="col-sm-6">{{ $interview->da ??'N/A' }}</dd>

                        <dt class="col-sm-6">TA:</dt>
                        <dd class="col-sm-6">{{ $interview->ta ??'N/A' }}</dd>

                        <dt class="col-sm-6">Location:</dt>
                        <dd class="col-sm-6">{{ $interview->location ??'N/A' }}</dd>

                        <dt class="col-sm-6">Category:</dt>
                        <dd class="col-sm-6">{{ $interview->category ??'N/A' }}</dd>
                    </dl>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-body">
                    <a href="{{ route('interviews.index') }}" class="btn btn-secondary w-100 mb-2">Back to Interviews</a>
                    <a href="{{ route('interviews.export.pdf', $interview->id) }}" class="btn btn-info w-100">Export to PDF</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="mb-0">Comments</h6>
                </div>
                <div class="card-body">
                    <div class="comments-section">
                        @forelse ($interview->comments as $comment)
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
                                <div class="mt-2">
                                    @can('update', $comment)
                                    <button class="btn btn-xs btn-outline-primary edit-comment me-1" data-comment-id="{{ $comment->id }}" data-comment-text="{{ $comment->comment }}" title="Edit Comment">
                                        <i class="fa fa-edit"></i> Edit
                                    </button>
                                    @endcan
                                    @can('delete', $comment)
                                    <button class="btn btn-xs btn-outline-danger delete-comment" data-comment-id="{{ $comment->id }}" title="Delete Comment">
                                        <i class="fa fa-trash"></i> Delete
                                    </button>
                                    @endcan
                                </div>
                            </div>
                        </div>
                        @empty
                        <p id="no-comments-yet">No comments yet.</p>
                        @endforelse
                    </div>
                    <hr>
                    <div class="mt-3">
                        <h6 class="mb-3">Add a Comment</h6>
                        <form id="add-comment-form" action="{{ route('interviews.storeComment', $interview->id) }}" method="POST">
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

<!-- Edit Comment Modal -->
<div class="modal fade" id="editCommentModal" tabindex="-1" role="dialog" aria-labelledby="editCommentModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCommentModalLabel">Edit Comment</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
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
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
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

@push('scripts')
<script>
    $(document).ready(function() {
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
                                            <button class="btn btn-xs btn-outline-primary edit-comment me-1" data-comment-id="${comment.id}" data-comment-text="${comment.comment}" title="Edit Comment">
                                                <i class="fa fa-edit"></i> Edit
                                            </button>
                                            <button class="btn btn-xs btn-outline-danger delete-comment" data-comment-id="${comment.id}" title="Delete Comment">
                                                <i class="fa fa-trash"></i> Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>`;
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
                url: '{{ url("interviews/comments") }}/' + commentId, // Use url() helper for dynamic ID
                type: 'GET',
                success: function(response) {
                    $('#edit-comment-text').val(response.comment);
                    $('#edit-comment-form').attr('action', '{{ url("interviews/comments") }}/' + commentId);
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
                url: '/interviews/comments/' + commentId,
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
    });
</script>
@endpush
@endsection