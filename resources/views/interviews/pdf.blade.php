@extends('layouts.pdf')

@section('title', 'Interview Details')

@section('header-right')
Interview Evaluation Sheet
@endsection

@push('styles')
<style>
    .details, .evaluation, .recommendation, .offer { margin-bottom: 15px; }
    .details table, .evaluation table, .offer table { width: 100%; border-collapse: collapse; }
    .details th, .details td, .evaluation th, .evaluation td, .offer th, .offer td { border: 1px solid #ddd; padding: 4px; text-align: left; }
    .details th { width: 30%; }
    .evaluation th { background-color: #f2f2f2; }
    .recommendation .badge { display: inline-block; padding: 8px; color: #fff; border-radius: 5px; font-size: 10px; }
    .badge-success { background-color: #28a745; }
    .badge-primary { background-color: #007bff; }
    .badge-info { background-color: #17a2b8; }
    .badge-danger { background-color: #dc3545; }
    .badge-secondary { background-color: #6c757d; }
</style>
@endpush

@section('content')
    @include('pdf.partials.report-header', ['title' => 'Interview Evaluation Sheet'])

    <div class="details">
        <h2 style="font-size: 14px; color: #1d3557;">Candidate Details</h2>
        <table>
            <tr><th>Post Applied For:</th><td>{{ $interview->post_applied_for }}</td></tr>
            <tr><th>Candidate Name:</th><td>{{ $interview->candidate_name }}</td></tr>
            <tr><th>Contact Number:</th><td>{{ $interview->contact_number }}</td></tr>
            <tr><th>Email Id:</th><td>{{ $interview->email_id }}</td></tr>
            <tr><th>Educational Qualification:</th><td>{{ $interview->educational_qualification }}</td></tr>
        </table>
    </div>

    <div class="details">
        <h2 style="font-size: 14px; color: #1d3557;">Professional Background</h2>
        <table>
            <tr><th>Years of Experience:</th><td>{{ $interview->years_of_experience }}</td></tr>
            <tr><th>Current Employer:</th><td>{{ $interview->current_employer }}</td></tr>
            <tr><th>Last/Current CTC:</th><td>{{ $interview->last_current_ctc }}</td></tr>
            <tr><th>Expected CTC:</th><td>{{ $interview->expected_ctc }}</td></tr>
            <tr><th>Notice Period:</th><td>{{ $interview->notice_period }}</td></tr>
        </table>
    </div>

    <div class="evaluation">
        <h2 style="font-size: 14px; color: #1d3557;">Candidate Evaluation</h2>
        <table>
            <thead>
                <tr>
                    <th>Criteria</th>
                    <th>Rating (out of 5)</th>
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
                        <td>{{ $interview->{$field.'_rating'} }}</td>
                        <td>{{ $interview->{$field.'_remarks'} }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="recommendation">
        <h2 style="font-size: 14px; color: #1d3557;">Interviewer Recommendation</h2>
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
        <span class="badge badge-{{ $badgeClass }}">{{ $recommendation ??'N/A' }}</span>
    </div>

    <div class="offer">
        <h2 style="font-size: 14px; color: #1d3557;">Job Offer Details</h2>
        <table>
            <tr><th>Salary Offered:</th><td>{{ $interview->salary_offered ??'N/A' }}</td></tr>
            <tr><th>DA:</th><td>{{ $interview->da ??'N/A' }}</td></tr>
            <tr><th>TA:</th><td>{{ $interview->ta ??'N/A' }}</td></tr>
            <tr><th>Location:</th><td>{{ $interview->location ??'N/A' }}</td></tr>
            <tr><th>Category:</th><td>{{ $interview->category ??'N/A' }}</td></tr>
        </table>
    </div>
@endsection
