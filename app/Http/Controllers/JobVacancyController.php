<?php

namespace App\Http\Controllers;

use App\Models\JobVacancy;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Session;

use App\Models\JobVacancyAnalytic;
use Illuminate\Support\Facades\Auth;
use App\Exports\JobVacanciesExport;
use Maatwebsite\Excel\Facades\Excel;

class JobVacancyController extends Controller
{
    private const MENU_ID = 33;

    // Fetch vacancies for DataTables
    public function index(Request $request)
    {
        if (!checkMenu(Session::get('role_id'), self::MENU_ID, 'read')) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            return redirect()->back()->with('error', 'Unauthorized');
        }

        $canRead = checkMenu(Session::get('role_id'), self::MENU_ID, 'read');
        $canUpdate = checkMenu(Session::get('role_id'), self::MENU_ID, 'update');
        $canDelete = checkMenu(Session::get('role_id'), self::MENU_ID, 'delete');

        if ($request->ajax()) {
            $data = JobVacancy::query()->orderBy('created_at', 'desc');

            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('description', function ($row) {
                    // Strip tags for table view, limit length
                    return \Illuminate\Support\Str::limit(strip_tags($row->description), 50);
                })
                ->addColumn('action', function ($row) use ($canRead, $canUpdate, $canDelete) {
                    $slug = $row->slug ?? $row->uuid;
                    $btn = '<ul class="action d-flex justify-content-around list-unstyled gap-2">';
                    if ($canRead) {
                        $btn .= '<li class="share"><a href="#" data-link="' . route('job-vacancies.public', $slug) . '" data-id="' . $row->id . '" class="share-btn" title="Copy Link"><i class="icon-link"></i></a></li>';
                        $btn .= '<li class="view"><a href="' . route('job-vacancies.analytics', $row->id) . '" class="view-btn" title="View Analytics"><i class="icon-eye"></i></a></li>';
                    }
                    if ($canUpdate) {
                        $btn .= '<li class="edit"><a href="#" data-id="' . $row->id . '" class="edit-vacancy-btn" title="Edit"><i class="icon-pencil-alt"></i></a></li>';
                    }
                    if ($canDelete) {
                        $btn .= '<li class="delete"><a href="#" data-id="' . $row->id . '" class="delete-vacancy-btn" title="Delete"><i class="icon-trash"></i></a></li>';
                    }
                    $btn .= '</ul>';
                    return $btn;
                })
                ->addColumn('views', function ($row) {
                    return $row->views_count;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('job_vacancies.index');
    }

    use \App\Traits\OneSignalNotificationTrait;

    public function store(Request $request)
    {
        if (!checkMenu(Session::get('role_id'), self::MENU_ID, 'create')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string', // HTML content
            'status' => 'required|in:Open,Closed',
            'form_fields' => 'nullable|array'
        ]);

        $validated['created_by'] = Auth::id();

        $vacancy = JobVacancy::create($validated);

        // Send notifications to all employees
        if ($vacancy->status === 'Open') {
            $employees = \App\Models\User::whereIn('user_type', ['employee', 'admin'])
                ->whereNotNull('email')
                ->get();
            $this->sendOneSignalNotification(
                $employees,
                "New Job Vacancy: " . $vacancy->title,
                "A new job opening has been posted. Check it out!",
                [
                    'type' => 'job_vacancy_created',
                    'vacancy_id' => $vacancy->id,
                    'route' => 'Job Vacancies',
                ]
            );
        }

        return response()->json(['success' => 'Job Vacancy created successfully.']);
    }

    public function update(Request $request, JobVacancy $jobVacancy)
    {
        if (!checkMenu(Session::get('role_id'), self::MENU_ID, 'update')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'required|in:Open,Closed',
            'form_fields' => 'nullable|array'
        ]);

        $jobVacancy->update($validated);

        return response()->json(['success' => 'Job Vacancy updated successfully.']);
    }

    public function destroy(JobVacancy $jobVacancy)
    {
        if (!checkMenu(Session::get('role_id'), self::MENU_ID, 'delete')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $jobVacancy->delete();
        return response()->json(['success' => 'Job Vacancy deleted successfully.']);
    }

    public function show(JobVacancy $jobVacancy)
    {
        if (!checkMenu(Session::get('role_id'), self::MENU_ID, 'read')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        return response()->json($jobVacancy);
    }

    public function publicView(Request $request, $slug)
    {
        $vacancy = JobVacancy::where('slug', $slug)->first();

        if (!$vacancy) {
            return view('job_vacancies.not_found');
        }

        // Analytics: Increment view count and log
        $vacancy->increment('views_count');

        // Prevent self-referral counting (don't count if referrer is the current viewer)
        $referrerId = $request->query('ref');
        if (Auth::check() && $referrerId == Auth::id()) {
            $referrerId = null;
        }

        JobVacancyAnalytic::create([
            'job_vacancy_id' => $vacancy->id,
            'user_id' => Auth::id(), // If logged in (optional)
            'referrer_id' => $referrerId,
            'action' => 'view',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return view('job_vacancies.public', compact('vacancy'));
    }

    public function trackCopy(Request $request, $id)
    {
        $vacancy = JobVacancy::findOrFail($id);

        JobVacancyAnalytic::create([
            'job_vacancy_id' => $vacancy->id,
            'user_id' => Auth::id(),
            'action' => 'copy_link',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return response()->json(['success' => true]);
    }

    public function analytics($id)
    {
        if (!checkMenu(Session::get('role_id'), self::MENU_ID, 'read')) {
            return redirect()->back()->with('error', 'Unauthorized');
        }

        $vacancy = JobVacancy::findOrFail($id);

        // Stats
        // Unique Views based on IP address
        $uniqueViews = $vacancy->analytics()
            ->where('action', 'view')
            ->distinct('ip_address')
            ->count('ip_address');

        $totalCopies = $vacancy->analytics()->where('action', 'copy_link')->count();

        // Top Referrers (People who shared the link and got views)
        $topReferrers = JobVacancyAnalytic::where('job_vacancy_id', $id)
            ->where('action', 'view')
            ->whereNotNull('referrer_id')
            ->select('referrer_id', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
            ->groupBy('referrer_id')
            ->with('referrer')
            ->orderByDesc('count')
            ->get();

        // Users who copied the link
        $copiers = JobVacancyAnalytic::where('job_vacancy_id', $id)
            ->where('action', 'copy_link')
            ->with('user')
            ->orderByDesc('created_at')
            ->get();

        // Submitted Applications (Job Applications)
        $applications = $vacancy->applications()->orderByDesc('created_at')->get();

        return view('job_vacancies.analytics', compact('vacancy', 'uniqueViews', 'totalCopies', 'topReferrers', 'copiers', 'applications'));
    }

    // For dropdowns
    public function list()
    {
        if (!checkMenu(Session::get('role_id'), self::MENU_ID, 'read') && !checkMenu(Session::get('role_id'), 23, 'read')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $vacancies = JobVacancy::where('status', 'Open')->get(['id', 'title']);
        return response()->json($vacancies);
    }

    public function showApplyForm($slug)
    {
        $vacancy = JobVacancy::where('slug', $slug)->firstOrFail();
        if ($vacancy->status !== 'Open') {
            return view('job_vacancies.not_found', ['message' => 'This position is no longer open.']);
        }
        return view('job_vacancies.apply', compact('vacancy'));
    }

    public function submitApplication(Request $request, $slug)
    {
        $vacancy = JobVacancy::where('slug', $slug)->firstOrFail();

        $rules = [
            'candidate_name' => 'required|string|max:255',
            'email_id' => 'required|email',
            'contact_number' => 'required',
            'location' => 'nullable|string',
            'educational_qualification' => 'nullable|string',
            'years_of_experience' => 'nullable|numeric',
            'current_employer' => 'nullable|string',
            'last_current_ctc' => 'nullable|numeric',
            'expected_ctc' => 'nullable|numeric',
            'notice_period' => 'nullable|string',
        ];
        $normalizeLabel = function ($label) {
            $label = strtolower((string) $label);
            $label = preg_replace('/[^a-z0-9]+/', ' ', $label);
            return trim(preg_replace('/\s+/', ' ', $label));
        };

        // Helper to map standard fields
        $standardFieldMap = [
            'full name' => 'candidate_name',
            'candidate name' => 'candidate_name',
            'name' => 'candidate_name',
            'your name' => 'candidate_name',
            'email' => 'email_id',
            'email address' => 'email_id',
            'email id' => 'email_id',
            'contact' => 'contact_number',
            'contact number' => 'contact_number',
            'phone' => 'contact_number',
            'mobile' => 'contact_number',
            'phone number' => 'contact_number',
            'qualification' => 'educational_qualification',
            'highest qualification' => 'educational_qualification',
            'education' => 'educational_qualification',
            'degree' => 'educational_qualification',
            'experience' => 'years_of_experience',
            'total experience' => 'years_of_experience',
            'years of experience' => 'years_of_experience',
            'current employer' => 'current_employer',
            'employer' => 'current_employer',
            'company' => 'current_employer',
            'current company' => 'current_employer'
        ];

        // Dynamic validation for ALL fields based on configuration
        if ($vacancy->form_fields) {
            foreach ($vacancy->form_fields as $field) {
                $label = $normalizeLabel($field['label'] ?? '');

                // Determine the input name used in the view
                if (array_key_exists($label, $standardFieldMap)) {
                    $fieldName = $standardFieldMap[$label];
                } else {
                    $fieldName = 'custom_' . \Illuminate\Support\Str::slug($field['label'], '_');
                }

                $fieldRules = [];
                if (isset($field['required']) && $field['required']) {
                    $fieldRules[] = 'required';
                } else {
                    $fieldRules[] = 'nullable';
                }

                if ($field['type'] === 'email') $fieldRules[] = 'email';
                if ($field['type'] === 'number') $fieldRules[] = 'numeric';
                if ($field['type'] === 'date') $fieldRules[] = 'date';

                // File validation
                if ($field['type'] === 'file') {
                    $fieldRules[] = 'file';
                    // Max 1MB = 1024KB. Use 'max:1024'
                    $fieldRules[] = 'max:1024';
                    // Optional: Restrict mimes if needed, e.g., mimes:pdf,doc,docx,jpg,png
                }

                $rules[$fieldName] = implode('|', $fieldRules);
            }
        }

        $validated = $request->validate($rules);

        // Standard DB columns we populate directly
        $dbColumns = [
            'candidate_name',
            'email_id',
            'contact_number',
            'educational_qualification',
            'years_of_experience',
            'current_employer',
            'last_current_ctc',
            'expected_ctc',
            'notice_period',
            'location'
        ];

        $interviewData = [];
        $customResponses = [];

        // Distribute validated data
        foreach ($validated as $key => $value) {
            // Check if this key maps to a DB column
            if (in_array($key, $dbColumns)) {
                $interviewData[$key] = $value;
            } else {
                // Must be a custom field. Find original label to store response.
                foreach ($vacancy->form_fields as $field) {
                    $label = $normalizeLabel($field['label'] ?? '');
                    // If it was a standard field, we already handled it above via DB column check (e.g. candidate_name)
                    // So this else block is ONLY for true custom fields (custom_slug)

                    if (array_key_exists($label, $standardFieldMap)) {
                        continue; // Skip standard mapped fields here
                    }

                    $fieldName = 'custom_' . \Illuminate\Support\Str::slug($field['label'], '_');

                    if ($key === $fieldName) {
                        // Handle File Upload
                        if ($request->hasFile($key)) {
                            $file = $request->file($key);
                            $filename = time() . '_' . $file->getClientOriginalName();
                            $file->move(public_path('uploads/applications'), $filename);
                            $value = 'uploads/applications/' . $filename;
                        }

                        $customResponses[] = [
                            'label' => $field['label'],
                            'value' => $value
                        ];
                    }
                }
            }
        }

        // Ensure candidate_name is always present before DB insert.
        if (empty($interviewData['candidate_name'])) {
            $fallbackName = trim((string) ($request->input('candidate_name')
                ?? $request->input('full_name')
                ?? $request->input('name')
                ?? ''));

            if ($fallbackName === '' && !empty($customResponses)) {
                foreach ($customResponses as $response) {
                    $normalizedCustomLabel = $normalizeLabel($response['label'] ?? '');
                    if (in_array($normalizedCustomLabel, ['full name', 'candidate name', 'name', 'your name'])) {
                        $fallbackName = trim((string) ($response['value'] ?? ''));
                        if ($fallbackName !== '') {
                            break;
                        }
                    }
                }
            }

            if ($fallbackName === '') {
                return response()->json([
                    'message' => 'Candidate name is required.',
                    'errors' => ['candidate_name' => ['Candidate name is required.']],
                ], 422);
            }

            $interviewData['candidate_name'] = $fallbackName;
        }

        $interviewData['job_vacancy_id'] = $vacancy->id;
        $interviewData['custom_form_responses'] = $customResponses; // Cast to JSON automatically via model attribute casting? Or manually access. 
        // Assuming Interview model casts this or we pass array. 
        // Best to verify Interview model casting, but for now passing array.
        // Update: Interview::create expects array for json column if cast array, or json string.
        // Let's assume standard Laravel casting is in place or Pass standard array.

        $interviewData['post_applied_for'] = $vacancy->title;
        $interviewData['status'] = 'Applied';

        // Track referrer if provided
        if ($request->has('ref')) {
            $interviewData['referrer_id'] = $request->ref;
        }

        \App\Models\JobApplication::create($interviewData);

        // Return JSON for AJAX
        return response()->json(['success' => 'Your application has been submitted successfully!']);
    }

    public function convertApplication($id)
    {
        if (!checkMenu(Session::get('role_id'), self::MENU_ID, 'update')) {
            return redirect()->back()->with('error', 'Unauthorized');
        }

        $application = \App\Models\JobApplication::findOrFail($id);

        if ($application->status === 'Applied') {

            // Map JobApplication to Interview
            $interview = \App\Models\Interview::create([
                'job_vacancy_id' => $application->job_vacancy_id,
                'candidate_name' => $application->candidate_name,
                'email_id' => $application->email_id,
                'contact_number' => $application->contact_number,
                'educational_qualification' => $application->educational_qualification,
                'years_of_experience' => $application->years_of_experience,
                'current_employer' => $application->current_employer,
                'last_current_ctc' => $application->last_current_ctc,
                'expected_ctc' => $application->expected_ctc,
                'notice_period' => $application->notice_period,
                'location' => $application->location,
                'post_applied_for' => $application->post_applied_for,
                'custom_form_responses' => $application->custom_form_responses,
                'referrer_id' => $application->referrer_id,
                'status' => 'Pending', // Initial status for interview
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
            ]);

            $application->status = 'Shortlisted';
            $application->save();

            // Redirect to edit page to add interview details
            return redirect()->route('interviews.edit', $interview->id)->with('success', 'Application converted to Interview. Please schedule it.');
        }

        return redirect()->back()->with('info', 'Application already converted.');
    }

    public function showApplication($id)
    {
        if (!checkMenu(Session::get('role_id'), self::MENU_ID, 'read')) {
            return redirect()->back()->with('error', 'Unauthorized');
        }
        $application = \App\Models\JobApplication::with('jobVacancy')->findOrFail($id);
        return view('job_vacancies.application_show', compact('application'));
    }

    public function updateApplicationStatus(Request $request, $id)
    {
        if (!checkMenu(Session::get('role_id'), self::MENU_ID, 'update')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $application = \App\Models\JobApplication::findOrFail($id);
        $oldStatus = $application->status;
        $newStatus = $request->status;

        $application->status = $newStatus;
        $application->save();

        if ($newStatus === 'Shortlisted' && $oldStatus !== 'Shortlisted') {
            $exists = \App\Models\Interview::where('candidate_name', $application->candidate_name)
                ->where('email_id', $application->email_id)
                ->where('job_vacancy_id', $application->job_vacancy_id)
                ->exists();

            if (!$exists) {
                \App\Models\Interview::create([
                    'job_vacancy_id' => $application->job_vacancy_id,
                    'candidate_name' => $application->candidate_name,
                    'email_id' => $application->email_id,
                    'contact_number' => $application->contact_number,
                    'educational_qualification' => $application->educational_qualification,
                    'years_of_experience' => $application->years_of_experience,
                    'current_employer' => $application->current_employer,
                    'last_current_ctc' => $application->last_current_ctc,
                    'expected_ctc' => $application->expected_ctc,
                    'notice_period' => $application->notice_period,
                    'location' => $application->location,
                    'post_applied_for' => $application->post_applied_for,
                    'custom_form_responses' => $application->custom_form_responses,
                    'referrer_id' => $application->referrer_id,
                    'status' => 'Pending',
                    'uuid' => (string) \Illuminate\Support\Str::uuid(),
                ]);
            }
        }

        return response()->json(['success' => 'Status updated successfully.']);
    }

    public function exportExcel(Request $request)
    {
        if (!checkMenu(Session::get('role_id'), self::MENU_ID, 'read')) {
            return redirect()->back()->with('error', 'Unauthorized');
        }

        $filters = $request->only(['from_date', 'to_date']);
        return Excel::download(new JobVacanciesExport($filters), 'job_vacancies_' . date('Y_m_d_H_i_s') . '.xlsx');
    }
}
