@extends('layouts.admin')

@section('title', 'Interview Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <ul class="nav nav-tabs tab-card-header" role="tablist">
                        <li class="nav-item"><a class="nav-link active" id="overview-tab" data-bs-toggle="tab" href="#overview" role="tab" aria-controls="overview" aria-selected="true">Overview</a></li>
                        @if(checkMenu(Session::get('role_id'), 23, 'create'))
                        <li class="nav-item"><a class="nav-link" id="create-tab" data-bs-toggle="tab" href="#create" role="tab" aria-controls="create" aria-selected="false">Create New Interview</a></li>
                        @endif
                    </ul>
                    @if(checkMenu(Session::get('role_id'), 23, 'read'))
                    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#exportInterviewModal">Export to Excel</button>
                    @endif
                </div>
                <div class="card-body">
                    <div class="tab-content" id="myTabContent">
                        <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview-tab">
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="start_date">Start Date</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date">
                                </div>
                                <div class="col-md-4">
                                    <label for="end_date">End Date</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date">
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button class="btn btn-primary" id="filter">Filter</button>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="display datatables" id="interviews-table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Candidate Name</th>
                                            <th>Job Vacancy</th>
                                            <th>Dealership</th>
                                            <th>Post Applied For</th>
                                            <th>Expected CTC</th>
                                            <th>Contact Number</th>
                                            <th>Salary Offered</th>
                                            <th>Average Rating</th>
                                            <th>Created At</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @if(checkMenu(Session::get('role_id'), 23, 'create'))
                        <div class="tab-pane fade" id="create" role="tabpanel" aria-labelledby="create-tab">
                            <form action="{{ route('interviews.store') }}" method="POST" id="create-interview-form" enctype="multipart/form-data">
                                @csrf
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h6>Candidate Details</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="form-group col-md-6">
                                                <label for="job_vacancy_id">Job Vacancy</label>
                                                <select class="form-control" id="job_vacancy_id" name="job_vacancy_id">
                                                    <option value="">Select Job Vacancy</option>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label for="job_application_id">Applied Candidate</label>
                                                <select class="form-control" id="job_application_id">
                                                    <option value="">Select Applied Candidate</option>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label for="post_applied_for">Post Applied For</label>
                                                <input type="text" class="form-control" id="post_applied_for" name="post_applied_for">
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label for="candidate_name">Candidate Name</label>
                                                <input type="text" class="form-control" id="candidate_name" name="candidate_name">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="form-group col-md-6">
                                                <label for="contact_number">Contact Number</label>
                                                <input type="text" class="form-control" id="contact_number" name="contact_number">
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label for="email_id">Email Id</label>
                                                <input type="email" class="form-control" id="email_id" name="email_id">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="form-group col-md-6">
                                                <label for="educational_qualification">Educational Qualification</label>
                                                <input type="text" class="form-control" id="educational_qualification" name="educational_qualification">
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label for="years_of_experience">Years of Experience</label>
                                                <input type="number" class="form-control" id="years_of_experience" name="years_of_experience">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="form-group col-md-6">
                                                <label for="dealership_id">Dealership</label>
                                                <select class="form-control" id="dealership_id" name="dealership_id">
                                                    <option value="">Select Dealership</option>
                                                    @foreach($dealerships as $dealership)
                                                    <option value="{{ $dealership->id }}">{{ $dealership->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="form-group col-md-6">
                                                <label for="current_employer">Current Employer</label>
                                                <input type="text" class="form-control" id="current_employer" name="current_employer">
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label for="last_current_ctc">Last/Current CTC</label>
                                                <input type="number" step="0.01" class="form-control" id="last_current_ctc" name="last_current_ctc">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="form-group col-md-6">
                                                <label for="expected_ctc">Expected CTC</label>
                                                <input type="number" step="0.01" class="form-control" id="expected_ctc" name="expected_ctc">
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label for="notice_period">Notice Period</label>
                                                <input type="text" class="form-control" id="notice_period" name="notice_period">
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label for="resume">Resume Attachment</label>
                                                <input type="file" class="form-control" id="resume" name="resume" accept=".pdf,.doc,.docx">
                                                <small class="text-muted">Allowed types: PDF, DOC, DOCX (Max 2MB)</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h6>Candidate Evaluation</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Criteria</th>
                                                        <th>Rating (1-5)</th>
                                                        <th>Remarks</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td><strong>Communication Skills</strong></td>
                                                        <td><input type="number" class="form-control" id="communication_skills_rating" name="communication_skills_rating" min="1" max="5" style="border: 2px solid #ced4da;"></td>
                                                        <td><textarea class="form-control" id="communication_skills_remarks" name="communication_skills_remarks" rows="1" style="border: 2px solid #ced4da;"></textarea></td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Technical Knowledge</strong></td>
                                                        <td><input type="number" class="form-control" id="technical_knowledge_rating" name="technical_knowledge_rating" min="1" max="5" style="border: 2px solid #ced4da;"></td>
                                                        <td><textarea class="form-control" id="technical_knowledge_remarks" name="technical_knowledge_remarks" rows="1" style="border: 2px solid #ced4da;"></textarea></td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Problem Solving Ability</strong></td>
                                                        <td><input type="number" class="form-control" id="problem_solving_ability_rating" name="problem_solving_ability_rating" min="1" max="5" style="border: 2px solid #ced4da;"></td>
                                                        <td><textarea class="form-control" id="problem_solving_ability_remarks" name="problem_solving_ability_remarks" rows="1" style="border: 2px solid #ced4da;"></textarea></td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Knowledge of Heavy Equipments</strong></td>
                                                        <td><input type="number" class="form-control" id="knowledge_of_heavy_equipments_rating" name="knowledge_of_heavy_equipments_rating" min="1" max="5" style="border: 2px solid #ced4da;"></td>
                                                        <td><textarea class="form-control" id="knowledge_of_heavy_equipments_remarks" name="knowledge_of_heavy_equipments_remarks" rows="1" style="border: 2px solid #ced4da;"></textarea></td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Relevant Work Experience</strong></td>
                                                        <td><input type="number" class="form-control" id="relevant_work_experience_rating" name="relevant_work_experience_rating" min="1" max="5" style="border: 2px solid #ced4da;"></td>
                                                        <td><textarea class="form-control" id="relevant_work_experience_remarks" name="relevant_work_experience_remarks" rows="1" style="border: 2px solid #ced4da;"></textarea></td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Attitude and Confidence</strong></td>
                                                        <td><input type="number" class="form-control" id="attitude_and_confidence_rating" name="attitude_and_confidence_rating" min="1" max="5" style="border: 2px solid #ced4da;"></td>
                                                        <td><textarea class="form-control" id="attitude_and_confidence_remarks" name="attitude_and_confidence_remarks" rows="1" style="border: 2px solid #ced4da;"></textarea></td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Adaptability/Flexibility</strong></td>
                                                        <td><input type="number" class="form-control" id="adaptability_flexibility_rating" name="adaptability_flexibility_rating" min="1" max="5" style="border: 2px solid #ced4da;"></td>
                                                        <td><textarea class="form-control" id="adaptability_flexibility_remarks" name="adaptability_flexibility_remarks" rows="1" style="border: 2px solid #ced4da;"></textarea></td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Teamwork and Collaboration</strong></td>
                                                        <td><input type="number" class="form-control" id="teamwork_collaboration_rating" name="teamwork_collaboration_rating" min="1" max="5" style="border: 2px solid #ced4da;"></td>
                                                        <td><textarea class="form-control" id="teamwork_collaboration_remarks" name="teamwork_collaboration_remarks" rows="1" style="border: 2px solid #ced4da;"></textarea></td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Leadership Potential</strong></td>
                                                        <td><input type="number" class="form-control" id="leadership_potential_rating" name="leadership_potential_rating" min="1" max="5" style="border: 2px solid #ced4da;"></td>
                                                        <td><textarea class="form-control" id="leadership_potential_remarks" name="leadership_potential_remarks" rows="1" style="border: 2px solid #ced4da;"></textarea></td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Willingness to Travel/Relocate</strong></td>
                                                        <td><input type="number" class="form-control" id="willingness_to_travel_relocate_rating" name="willingness_to_travel_relocate_rating" min="1" max="5" style="border: 2px solid #ced4da;"></td>
                                                        <td><textarea class="form-control" id="willingness_to_travel_relocate_remarks" name="willingness_to_travel_relocate_remarks" rows="1" style="border: 2px solid #ced4da;"></textarea></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h6>Interviewer Recommendation</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="interviewer_recommendation" id="highly_recommended" value="Highly Recommended">
                                                <label class="form-check-label" for="highly_recommended">
                                                    Highly Recommended
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="interviewer_recommendation" id="recommended" value="Recommended">
                                                <label class="form-check-label" for="recommended">
                                                    Recommended
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="interviewer_recommendation" id="consider_for_other_role" value="Consider for Other Role">
                                                <label class="form-check-label" for="consider_for_other_role">
                                                    Consider for Other Role
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="interviewer_recommendation" id="not_recommended" value="Not Recommended">
                                                <label class="form-check-label" for="not_recommended">
                                                    Not Recommended
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h6>Job Offer Details</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="form-group col-md-6">
                                                <label for="salary_offered">Salary Offered</label>
                                                <input type="number" step="0.01" class="form-control" id="salary_offered" name="salary_offered">
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label for="da">DA</label>
                                                <input type="number" step="0.01" class="form-control" id="da" name="da">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="form-group col-md-6">
                                                <label for="ta">TA</label>
                                                <input type="number" step="0.01" class="form-control" id="ta" name="ta">
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label for="location">Location</label>
                                                <input type="text" class="form-control" id="location" name="location">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="form-group col-md-6">
                                                <label for="category">Category</label>
                                                <input type="text" class="form-control" id="category" name="category">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary mt-3">Submit</button>
                                <button type="reset" class="btn btn-secondary mt-3">Reset</button>
                            </form>
                        </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmationModal" tabindex="-1" role="dialog" aria-labelledby="deleteConfirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteConfirmationModalLabel">Confirm Delete</h5>
            </div>
            @if(!checkMenu(Session::get('role_id'), 23, 'delete'))
            <div class="modal-body">
                <p class="text-danger">You are not authorized to delete interviews.</p>
            </div>
            @else
            <div class="modal-body">
                Are you sure you want to delete this interview?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Edit Interview Modal -->
<div class="modal fade" id="editInterviewModal" tabindex="-1" role="dialog" aria-labelledby="editInterviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editInterviewModalLabel">Edit Interview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="loading-indicator" style="display: none;">
                    <p>Loading interview data...</p>
                </div>
                <div class="error-message text-danger" style="display: none;"></div>
                <form action="" method="POST" id="edit-interview-form" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="interview_id" id="edit_interview_id">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6>Candidate Details</h6>
                        </div>
                        <div class="card-body">
                            @if(!checkMenu(Session::get('role_id'), 23, 'update'))

                            <p class="text-danger">You are not authorized to edit interviews.</p>
                            @else
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="edit_post_applied_for">Post Applied For</label>
                                    <input type="text" class="form-control" id="edit_post_applied_for" name="post_applied_for">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="edit_candidate_name">Candidate Name</label>
                                    <input type="text" class="form-control" id="edit_candidate_name" name="candidate_name">
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="edit_contact_number">Contact Number</label>
                                    <input type="text" class="form-control" id="edit_contact_number" name="contact_number">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="edit_email_id">Email Id</label>
                                    <input type="email" class="form-control" id="edit_email_id" name="email_id">
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="edit_educational_qualification">Educational Qualification</label>
                                    <input type="text" class="form-control" id="edit_educational_qualification" name="educational_qualification">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="edit_years_of_experience">Years of Experience</label>
                                    <input type="number" class="form-control" id="edit_years_of_experience" name="years_of_experience">
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="edit_dealership_id">Dealership</label>
                                    <select class="form-control" id="edit_dealership_id" name="dealership_id">
                                        <option value="">Select Dealership</option>
                                        @foreach($dealerships as $dealership)
                                        <option value="{{ $dealership->id }}">{{ $dealership->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="edit_current_employer">Current Employer</label>
                                    <input type="text" class="form-control" id="edit_current_employer" name="current_employer">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="edit_last_current_ctc">Last/Current CTC</label>
                                    <input type="number" step="0.01" class="form-control" id="edit_last_current_ctc" name="last_current_ctc">
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="edit_expected_ctc">Expected CTC</label>
                                    <input type="number" step="0.01" class="form-control" id="edit_expected_ctc" name="expected_ctc">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="edit_notice_period">Notice Period</label>
                                    <input type="text" class="form-control" id="edit_notice_period" name="notice_period">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="edit_resume">Resume Attachment</label>
                                    <input type="file" class="form-control" id="edit_resume" name="resume" accept=".pdf,.doc,.docx">
                                    <small class="text-muted">Upload new to replace. Allowed: PDF, DOC, DOCX (Max 2MB)</small>
                                    <div id="current_resume_display" class="mt-2"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-header">
                            <h6>Candidate Evaluation</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Criteria</th>
                                            <th>Rating (1-5)</th>
                                            <th>Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><strong>Communication Skills</strong></td>
                                            <td><input type="number" class="form-control" id="edit_communication_skills_rating" name="communication_skills_rating" min="1" max="5" style="border: 2px solid #ced4da;"></td>
                                            <td><textarea class="form-control" id="edit_communication_skills_remarks" name="communication_skills_remarks" rows="1" style="border: 2px solid #ced4da;"></textarea></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Technical Knowledge</strong></td>
                                            <td><input type="number" class="form-control" id="edit_technical_knowledge_rating" name="technical_knowledge_rating" min="1" max="5" style="border: 2px solid #ced4da;"></td>
                                            <td><textarea class="form-control" id="edit_technical_knowledge_remarks" name="technical_knowledge_remarks" rows="1" style="border: 2px solid #ced4da;"></textarea></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Problem Solving Ability</strong></td>
                                            <td><input type="number" class="form-control" id="edit_problem_solving_ability_rating" name="problem_solving_ability_rating" min="1" max="5" style="border: 2px solid #ced4da;"></td>
                                            <td><textarea class="form-control" id="edit_problem_solving_ability_remarks" name="problem_solving_ability_remarks" rows="1" style="border: 2px solid #ced4da;"></textarea></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Knowledge of Heavy Equipments</strong></td>
                                            <td><input type="number" class="form-control" id="edit_knowledge_of_heavy_equipments_rating" name="knowledge_of_heavy_equipments_rating" min="1" max="5" style="border: 2px solid #ced4da;"></td>
                                            <td><textarea class="form-control" id="edit_knowledge_of_heavy_equipments_remarks" name="knowledge_of_heavy_equipments_remarks" rows="1" style="border: 2px solid #ced4da;"></textarea></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Relevant Work Experience</strong></td>
                                            <td><input type="number" class="form-control" id="edit_relevant_work_experience_rating" name="relevant_work_experience_rating" min="1" max="5" style="border: 2px solid #ced4da;"></td>
                                            <td><textarea class="form-control" id="edit_relevant_work_experience_remarks" name="relevant_work_experience_remarks" rows="1" style="border: 2px solid #ced4da;"></textarea></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Attitude and Confidence</strong></td>
                                            <td><input type="number" class="form-control" id="edit_attitude_and_confidence_rating" name="attitude_and_confidence_rating" min="1" max="5" style="border: 2px solid #ced4da;"></td>
                                            <td><textarea class="form-control" id="edit_attitude_and_confidence_remarks" name="attitude_and_confidence_remarks" rows="1" style="border: 2px solid #ced4da;"></textarea></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Adaptability/Flexibility</strong></td>
                                            <td><input type="number" class="form-control" id="edit_adaptability_flexibility_rating" name="adaptability_flexibility_rating" min="1" max="5" style="border: 2px solid #ced4da;"></td>
                                            <td><textarea class="form-control" id="edit_adaptability_flexibility_remarks" name="adaptability_flexibility_remarks" rows="1" style="border: 2px solid #ced4da;"></textarea></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Teamwork and Collaboration</strong></td>
                                            <td><input type="number" class="form-control" id="edit_teamwork_collaboration_rating" name="teamwork_collaboration_rating" min="1" max="5" style="border: 2px solid #ced4da;"></td>
                                            <td><textarea class="form-control" id="edit_teamwork_collaboration_remarks" name="teamwork_collaboration_remarks" rows="1" style="border: 2px solid #ced4da;"></textarea></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Leadership Potential</strong></td>
                                            <td><input type="number" class="form-control" id="edit_leadership_potential_rating" name="leadership_potential_rating" min="1" max="5" style="border: 2px solid #ced4da;"></td>
                                            <td><textarea class="form-control" id="edit_leadership_potential_remarks" name="leadership_potential_remarks" rows="1" style="border: 2px solid #ced4da;"></textarea></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Willingness to Travel/Relocate</strong></td>
                                            <td><input type="number" class="form-control" id="edit_willingness_to_travel_relocate_rating" name="willingness_to_travel_relocate_rating" min="1" max="5" style="border: 2px solid #ced4da;"></td>
                                            <td><textarea class="form-control" id="edit_willingness_to_travel_relocate_remarks" name="willingness_to_travel_relocate_remarks" rows="1" style="border: 2px solid #ced4da;"></textarea></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-header">
                            <h6>Interviewer Recommendation</h6>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="interviewer_recommendation" id="edit_highly_recommended" value="Highly Recommended">
                                    <label class="form-check-label" for="edit_highly_recommended">
                                        Highly Recommended
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="interviewer_recommendation" id="edit_recommended" value="Recommended">
                                    <label class="form-check-label" for="edit_recommended">
                                        Recommended
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="interviewer_recommendation" id="edit_consider_for_other_role" value="Consider for Other Role">
                                    <label class="form-check-label" for="edit_consider_for_other_role">
                                        Consider for Other Role
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="interviewer_recommendation" id="edit_not_recommended" value="Not Recommended">
                                    <label class="form-check-label" for="edit_not_recommended">
                                        Not Recommended
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-header">
                            <h6>Job Offer Details</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="edit_salary_offered">Salary Offered</label>
                                    <input type="number" step="0.01" class="form-control" id="edit_salary_offered" name="salary_offered">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="edit_da">DA</label>
                                    <input type="number" step="0.01" class="form-control" id="edit_da" name="da">
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="edit_ta">TA</label>
                                    <input type="number" step="0.01" class="form-control" id="edit_ta" name="ta">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="edit_location">Location</label>
                                    <input type="text" class="form-control" id="edit_location" name="location">
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="edit_category">Category</label>
                                    <input type="text" class="form-control" id="edit_category" name="category">
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>

                    <button type="submit" class="btn btn-primary mt-3">Update Interview</button>
                    <button type="button" class="btn btn-secondary mt-3" data-bs-dismiss="modal">Cancel</button>
                </form>
            </div>
        </div>
    </div>
</div>

</div>

<!-- Export Interview Modal -->
<div class="modal fade" id="exportInterviewModal" tabindex="-1" role="dialog" aria-labelledby="exportInterviewModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exportInterviewModalLabel">Export Interviews</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('interviews.export') }}" method="GET">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="export_from_date" class="form-label">From Date</label>
                                <input type="date" class="form-control" id="export_from_date" name="from_date">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="export_to_date" class="form-label">To Date</label>
                                <input type="date" class="form-control" id="export_to_date" name="to_date">
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
@endpush

@push('scripts')
<script>
    $(document).ready(function() {


        // Initialize Interviews Table
        var table = $('#interviews-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('interviews.index') }}",
                data: function(d) {
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
                }
            },
            columns: [{
                    data: null,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    },
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'candidate_name',
                    name: 'candidate_name'
                },
                {
                    data: 'job_vacancy',
                    name: 'job_vacancy',
                    defaultContent: 'N/A'
                },
                {
                    data: 'dealership.name',
                    name: 'dealership.name',
                    defaultContent: 'N/A'
                },
                {
                    data: 'post_applied_for',
                    name: 'post_applied_for'
                },
                {
                    data: 'expected_ctc',
                    name: 'expected_ctc'
                },
                {
                    data: 'contact_number',
                    name: 'contact_number'
                },
                {
                    data: 'salary_offered',
                    name: 'salary_offered'
                },
                {
                    data: 'average_rating',
                    name: 'average_rating',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'created_at',
                    name: 'created_at'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
            ],
            rawColumns: ['contact_number', 'action', 'created_at']
        });

        $('#filter').click(function() {
            table.draw();
        });

        // Delete functionality
        $('#interviews-table').on('click', '.deleteButton', function() {
            var interviewId = $(this).data('id');
            $('#deleteConfirmationModal').modal('show');

            $('#confirmDeleteBtn').off('click').on('click', function() {
                $.ajax({
                    url: '/interviews/' + interviewId,
                    type: 'POST', // Laravel uses POST for DELETE with a _method field
                    data: {
                        _method: 'DELETE',
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        $('#deleteConfirmationModal').modal('hide');
                        table.draw();
                        showToast(response.success, 'success');
                    },
                    error: function(xhr) {
                        $('#deleteConfirmationModal').modal('hide');
                        showToast('Error deleting interview.', 'danger');
                    }
                });
            });
        });

        // Edit functionality
        $('#interviews-table').on('click', '.editButton', function() {
            var interviewId = $(this).data('id');
            $('#editInterviewModal').modal('show'); // Show modal immediately

            // Show loading indicator and hide the form
            $('#editInterviewModal .modal-body').find('.loading-indicator').show();
            $('#edit-interview-form').hide();

            $.ajax({
                url: '/interviews/' + interviewId + '/edit',
                method: 'GET',
                success: function(data) {
                    // Populate form fields
                    $('#edit_interview_id').val(data.interview.id);
                    $('#edit_post_applied_for').val(data.interview.post_applied_for);
                    $('#edit_candidate_name').val(data.interview.candidate_name);
                    $('#edit_contact_number').val(data.interview.contact_number);
                    $('#edit_email_id').val(data.interview.email_id);
                    $('#edit_educational_qualification').val(data.interview.educational_qualification);
                    $('#edit_years_of_experience').val(data.interview.years_of_experience);
                    $('#edit_dealership_id').val(data.interview.dealership_id);
                    $('#edit_current_employer').val(data.interview.current_employer);
                    $('#edit_last_current_ctc').val(data.interview.last_current_ctc);
                    $('#edit_expected_ctc').val(data.interview.expected_ctc);
                    $('#edit_notice_period').val(data.interview.notice_period);

                    $('#edit_communication_skills_rating').val(data.interview.communication_skills_rating);
                    $('#edit_communication_skills_remarks').val(data.interview.communication_skills_remarks);
                    $('#edit_technical_knowledge_rating').val(data.interview.technical_knowledge_rating);
                    $('#edit_technical_knowledge_remarks').val(data.interview.technical_knowledge_remarks);
                    $('#edit_problem_solving_ability_rating').val(data.interview.problem_solving_ability_rating);
                    $('#edit_problem_solving_ability_remarks').val(data.interview.problem_solving_ability_remarks);
                    $('#edit_knowledge_of_heavy_equipments_rating').val(data.interview.knowledge_of_heavy_equipments_rating);
                    $('#edit_knowledge_of_heavy_equipments_remarks').val(data.interview.knowledge_of_heavy_equipments_remarks);
                    $('#edit_relevant_work_experience_rating').val(data.interview.relevant_work_experience_rating);
                    $('#edit_relevant_work_experience_remarks').val(data.interview.relevant_work_experience_remarks);
                    $('#edit_attitude_and_confidence_rating').val(data.interview.attitude_and_confidence_rating);
                    $('#edit_attitude_and_confidence_remarks').val(data.interview.attitude_and_confidence_remarks);
                    $('#edit_adaptability_flexibility_rating').val(data.interview.adaptability_flexibility_rating);
                    $('#edit_adaptability_flexibility_remarks').val(data.interview.adaptability_flexibility_remarks);
                    $('#edit_teamwork_collaboration_rating').val(data.interview.teamwork_collaboration_rating);
                    $('#edit_teamwork_collaboration_remarks').val(data.interview.teamwork_collaboration_remarks);
                    $('#edit_leadership_potential_rating').val(data.interview.leadership_potential_rating);
                    $('#edit_leadership_potential_remarks').val(data.interview.leadership_potential_remarks);
                    $('#edit_willingness_to_travel_relocate_rating').val(data.interview.willingness_to_travel_relocate_rating);
                    $('#edit_willingness_to_travel_relocate_remarks').val(data.interview.willingness_to_travel_relocate_remarks);

                    // Set radio button for interviewer_recommendation
                    $('input[name="interviewer_recommendation"][value="' + data.interview.interviewer_recommendation + '"]').prop('checked', true);

                    $('#edit_salary_offered').val(data.interview.salary_offered);
                    $('#edit_da').val(data.interview.da);
                    $('#edit_ta').val(data.interview.ta);
                    $('#edit_location').val(data.interview.location);
                    $('#edit_category').val(data.interview.category);

                    // Display current resume if exists
                    if (data.interview.resume) {
                        $('#current_resume_display').html('<a href="/storage/' + data.interview.resume + '" target="_blank" class="btn btn-sm btn-info text-white"><i class="fa fa-download"></i> View Current Resume</a>');
                    } else {
                        $('#current_resume_display').html('<span class="text-muted">No resume attached</span>');
                    }

                    // Set form action
                    $('#edit-interview-form').attr('action', '/interviews/' + interviewId);

                    // Hide loading indicator and show the form
                    $('#editInterviewModal .modal-body').find('.loading-indicator').hide();
                    $('#edit-interview-form').show();
                },
                error: function(xhr) {
                    let errorMessage = 'Error fetching interview data.';
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        errorMessage = xhr.responseJSON.error;
                    }
                    // Hide loading indicator and show error message
                    $('#editInterviewModal .modal-body').find('.loading-indicator').hide();
                    $('#editInterviewModal .modal-body').find('.error-message').text(errorMessage).show();
                    showToast(errorMessage, 'danger');
                    $('#edit-interview-form').hide(); // Ensure form remains hidden
                }
            });
        });

        // Submit handler for edit form
        $('#edit-interview-form').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            var url = form.attr('action');
            var formData = new FormData(this);
            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#editInterviewModal').modal('hide');
                    table.draw();
                    showToast(response.success, 'success');
                },
                error: function(xhr) {
                    showToast('Error updating interview.', 'danger');
                }
            });
        });

        $('#create-interview-form').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            var url = form.attr('action');
            var formData = new FormData(this);
            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    table.draw();
                    showToast(response.success, 'success');
                    form[0].reset();
                    resetAppliedCandidatesDropdown();
                    $('#create-tab').removeClass('active');
                    $('#overview-tab').addClass('active');
                    $('#create').removeClass('show active');
                    $('#overview').addClass('show active');
                },
                error: function(xhr) {
                    showToast('Error creating interview.', 'danger');
                }
            });
        });




        // Load Job Vacancies for Dropdown
        var jobApplicationsMap = {};

        function resetAppliedCandidatesDropdown() {
            jobApplicationsMap = {};
            var select = $('#job_application_id');
            select.empty();
            select.append('<option value="">Select Applied Candidate</option>');
        }

        function loadAppliedCandidates(jobVacancyId) {
            resetAppliedCandidatesDropdown();

            if (!jobVacancyId) {
                return;
            }

            $.get("{{ route('interviews.applications.by-vacancy', ['jobVacancy' => '__ID__']) }}".replace('__ID__', jobVacancyId), function(data) {
                var select = $('#job_application_id');

                if (!Array.isArray(data) || data.length === 0) {
                    select.append('<option value="">No applied candidates found</option>');
                    return;
                }

                $.each(data, function(index, application) {
                    jobApplicationsMap[String(application.id)] = application;
                    var candidateText = (application.candidate_name || 'Unnamed Candidate');
                    var contactText = application.contact_number ? (' - ' + application.contact_number) : '';
                    select.append('<option value="' + application.id + '">' + candidateText + contactText + '</option>');
                });
            });
        }

        function loadVacancies() {
            $.get("{{ route('job-vacancies.list') }}", function(data) {
                var select = $('#job_vacancy_id');
                select.empty();
                select.append('<option value="">Select Job Vacancy</option>');
                $.each(data, function(key, value) {
                    select.append('<option value="' + value.id + '">' + value.title + '</option>');
                });

                // Initialize Select2
                select.select2({
                    placeholder: "Select Job Vacancy",
                    allowClear: true,
                    width: '100%'
                });
            });
        }

        loadVacancies();

        // Auto-fill Post Applied For
        $('#job_vacancy_id').on('select2:select change', function() {
            var data = $(this).select2('data');
            // Select2 'data' returns an array, we want the first selection
            if (data && data.length > 0) {
                var text = data[0].text;
                // If placeholder is selected (value is empty), we might get the placeholder text depending on how select2 handles it, 
                // but typically we check the value.
                if ($(this).val() !== '') {
                    $('#post_applied_for').val(text);
                    loadAppliedCandidates($(this).val());
                } else {
                    $('#post_applied_for').val('');
                    resetAppliedCandidatesDropdown();
                }
            } else {
                // Fallback for normal change event if select2 is not active or for initial clear
                var text = $(this).find("option:selected").text();
                if ($(this).val() !== '') {
                    $('#post_applied_for').val(text);
                    loadAppliedCandidates($(this).val());
                } else {
                    $('#post_applied_for').val('');
                    resetAppliedCandidatesDropdown();
                }
            }
        });

        $('#job_application_id').on('change', function() {
            var applicationId = $(this).val();
            if (!applicationId || !jobApplicationsMap[String(applicationId)]) {
                return;
            }

            var application = jobApplicationsMap[String(applicationId)];
            $('#candidate_name').val(application.candidate_name || '');
            $('#contact_number').val(application.contact_number || '');
            $('#email_id').val(application.email_id || '');
            $('#educational_qualification').val(application.educational_qualification || '');
            $('#years_of_experience').val(application.years_of_experience || '');
            $('#current_employer').val(application.current_employer || '');
            $('#last_current_ctc').val(application.last_current_ctc || '');
            $('#expected_ctc').val(application.expected_ctc || '');
            $('#notice_period').val(application.notice_period || '');
            $('#location').val(application.location || '');

            if (application.post_applied_for) {
                $('#post_applied_for').val(application.post_applied_for);
            }
        });
    });
</script>
@endpush
