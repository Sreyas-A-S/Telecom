<?php

namespace App\Http\Controllers;

use App\Models\Interview;
use App\Models\InterviewComment;
use App\Models\Employee;
use App\Models\Client;
use App\Models\Dealership; // Added
use App\Models\JobApplication;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

use App\Exports\InterviewsExport;
use Maatwebsite\Excel\Facades\Excel;

class InterviewController extends Controller
{
    public function exportExcel(Request $request)
    {
        if (!checkMenu(Session::get('role_id'), 23, 'read')) {
            return redirect()->back()->with('error', 'Unauthorized');
        }

        $filters = $request->only(['from_date', 'to_date']);
        return Excel::download(new InterviewsExport($filters), 'interviews_' . date('Y_m_d_H_i_s') . '.xlsx');
    }

    public function index(Request $request)
    {
        if (!checkMenu(Session::get('role_id'), 23, 'read')) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to view interviews.');
        }
        if ($request->ajax()) {
            $data = Interview::with(['employee', 'client', 'dealership', 'jobVacancy'])->select('interviews.*')->orderBy('id', 'desc');

            // Filter by dealership_id if provided in the request
            if ($request->has('dealership_id') && $request->dealership_id != '') {
                $data->where('dealership_id', $request->dealership_id);
            }

            // Filter by date range if provided
            if ($request->has('start_date') && $request->start_date != '') {
                $data->whereDate('created_at', '>=', $request->start_date);
            }
            if ($request->has('end_date') && $request->end_date != '') {
                $data->whereDate('created_at', '<=', $request->end_date);
            }

            if ($request->has('search') && !empty($request->input('search.value'))) {
                $searchValue = $request->input('search.value');
                $data->where(function ($query) use ($searchValue) {
                    $query->where('interviews.candidate_name', 'like', "%{$searchValue}%")
                        ->orWhere('interviews.post_applied_for', 'like', "%{$searchValue}%")
                        ->orWhere('interviews.contact_number', 'like', "%{$searchValue}%")
                        ->orWhere('interviews.email_id', 'like', "%{$searchValue}%")
                        ->orWhere('interviews.educational_qualification', 'like', "%{$searchValue}%")
                        ->orWhere('interviews.current_employer', 'like', "%{$searchValue}%")
                        ->orWhere('interviews.notice_period', 'like', "%{$searchValue}%")
                        ->orWhere('interviews.interviewer_recommendation', 'like', "%{$searchValue}%")
                        ->orWhere('interviews.location', 'like', "%{$searchValue}%")
                        ->orWhere('interviews.category', 'like', "%{$searchValue}%")
                        ->orWhere('interviews.communication_skills_remarks', 'like', "%{$searchValue}%")
                        ->orWhere('interviews.technical_knowledge_remarks', 'like', "%{$searchValue}%")
                        ->orWhere('interviews.problem_solving_ability_remarks', 'like', "%{$searchValue}%")
                        ->orWhere('interviews.knowledge_of_heavy_equipments_remarks', 'like', "%{$searchValue}%")
                        ->orWhere('interviews.relevant_work_experience_remarks', 'like', "%{$searchValue}%")
                        ->orWhere('interviews.attitude_and_confidence_remarks', 'like', "%{$searchValue}%")
                        ->orWhere('interviews.adaptability_flexibility_remarks', 'like', "%{$searchValue}%")
                        ->orWhere('interviews.teamwork_collaboration_remarks', 'like', "%{$searchValue}%")
                        ->orWhere('interviews.leadership_potential_remarks', 'like', "%{$searchValue}%")
                        ->orWhere('interviews.willingness_to_travel_relocate_remarks', 'like', "%{$searchValue}%")
                        ->orWhereHas('jobVacancy', function ($q) use ($searchValue) {
                            $q->where('title', 'like', "%{$searchValue}%");
                        });
                });
            }

            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('candidate_name', function (Interview $interview) {
                    return $interview->candidate_name ?? 'N/A';
                })
                ->addColumn('dealership.name', function (Interview $interview) {
                    return $interview->dealership ? $interview->dealership->name : 'N/A';
                })
                ->editColumn('post_applied_for', function (Interview $interview) {
                    return $interview->post_applied_for ?? 'N/A';
                })
                ->editColumn('expected_ctc', function (Interview $interview) {
                    return $interview->expected_ctc ?? 'N/A';
                })
                ->editColumn('contact_number', function (Interview $interview) {
                    $contactNumber = $interview->contact_number ?? 'N/A';
                    return $contactNumber !== 'N/A' ? '<a href="tel:' . $contactNumber . '">' . $contactNumber . '</a>' : 'N/A';
                })
                ->editColumn('salary_offered', function (Interview $interview) {
                    return $interview->salary_offered ?? 'N/A';
                })
                ->addColumn('average_rating', function (Interview $interview) {
                    $ratings = [
                        $interview->communication_skills_rating,
                        $interview->technical_knowledge_rating,
                        $interview->problem_solving_ability_rating,
                        $interview->knowledge_of_heavy_equipments_rating,
                        $interview->relevant_work_experience_rating,
                        $interview->attitude_and_confidence_rating,
                        $interview->adaptability_flexibility_rating,
                        $interview->teamwork_collaboration_rating,
                        $interview->leadership_potential_rating,
                        $interview->willingness_to_travel_relocate_rating,
                    ];
                    $validRatings = array_filter($ratings, function ($value) {
                        return !is_null($value);
                    });
                    if (count($validRatings) > 0) {
                        return round(array_sum($validRatings) / count($validRatings), 2);
                    }
                    return 'N/A';
                })
                ->addColumn('job_vacancy', function (Interview $interview) {
                    return $interview->jobVacancy ? $interview->jobVacancy->title : 'N/A';
                })
                ->addColumn('created_at', function (Interview $interview) {
                    return $interview->created_at->format('M d, Y h:i A');
                })
                ->addColumn('action', function ($row) {
                    $btn = '<ul class="action d-flex justify-content-around list-unstyled gap-2">';

                    $btn .= '<li class="view"><a title="View" href="' . route('interviews.show', $row->id) . '"><i class="icon-eye"></i></a></li>';
                    $btn .= '<li class="edit"><a href="#" data-id="' . $row->id . '" class="editButton" title="Edit"><i class="icon-pencil-alt"></i></a></li>';
                    $btn .= '<li class="delete"><a title="Delete" href="#" data-id="' . $row->id . '" class="deleteButton"><i class="icon-trash"></i></a></li>';
                    $btn .= '</ul>';
                    return $btn;
                })
                ->rawColumns(['action', 'contact_number', 'created_at'])
                ->make(true);
        }
        $dealerships = Dealership::all();
        $employees = Employee::all();
        $clients = Client::all();
        $canUpdate = checkMenu(Session::get('role_id'), 23, 'update');
        $canDelete = checkMenu(Session::get('role_id'), 23, 'delete');
        return view('interviews.index', compact('dealerships', 'employees', 'clients', 'canUpdate', 'canDelete'));
    }

    public function store(Request $request)
    {
        if (!checkMenu(Session::get('role_id'), 23, 'create')) {
            return redirect()->route('interviews.index')->with('error', 'You do not have permission to create interviews.');
        }
        $validatedData = $request->validate([
            'post_applied_for' => 'nullable|string|max:255',
            'candidate_name' => 'required|string|max:255',
            'contact_number' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],
            'email_id' => 'required|email|max:255',
            'educational_qualification' => 'nullable|string|max:255',
            'years_of_experience' => 'nullable|integer|min:0|max:60',
            'current_employer' => 'nullable|string|max:255',
            'last_current_ctc' => 'nullable|numeric|min:0',
            'expected_ctc' => 'nullable|numeric|min:0',
            'notice_period' => 'nullable|string|max:255',
            'communication_skills_rating' => 'nullable|integer|min:1|max:5',
            'communication_skills_remarks' => 'nullable|string',
            'technical_knowledge_rating' => 'nullable|integer|min:1|max:5',
            'technical_knowledge_remarks' => 'nullable|string',
            'problem_solving_ability_rating' => 'nullable|integer|min:1|max:5',
            'problem_solving_ability_remarks' => 'nullable|string',
            'knowledge_of_heavy_equipments_rating' => 'nullable|integer|min:1|max:5',
            'knowledge_of_heavy_equipments_remarks' => 'nullable|string',
            'relevant_work_experience_rating' => 'nullable|integer|min:1|max:5',
            'relevant_work_experience_remarks' => 'nullable|string',
            'attitude_and_confidence_rating' => 'nullable|integer|min:1|max:5',
            'attitude_and_confidence_remarks' => 'nullable|string',
            'adaptability_flexibility_rating' => 'nullable|integer|min:1|max:5',
            'adaptability_flexibility_remarks' => 'nullable|string',
            'teamwork_collaboration_rating' => 'nullable|integer|min:1|max:5',
            'teamwork_collaboration_remarks' => 'nullable|string',
            'leadership_potential_rating' => 'nullable|integer|min:1|max:5',
            'leadership_potential_remarks' => 'nullable|string',
            'willingness_to_travel_relocate_rating' => 'nullable|integer|min:1|max:5',
            'willingness_to_travel_relocate_remarks' => 'nullable|string',
            'interviewer_recommendation' => 'nullable|string|in:Highly Recommended,Recommended,Consider for Other Role,Not Recommended',
            'salary_offered' => 'nullable|numeric|min:0',
            'da' => 'nullable|numeric|min:0',
            'ta' => 'nullable|numeric|min:0',
            'location' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'dealership_id' => 'nullable|exists:dealerships,id',
            'job_vacancy_id' => 'nullable|exists:job_vacancies,id',
            'resume' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
        ], [
            'candidate_name.required' => 'Candidate name is required.',
            'contact_number.required' => 'Contact number is required.',
            'contact_number.regex' => 'Contact number format is invalid.',
            'email_id.required' => 'Email is required.',
        ]);

        // Automatically assign dealership_id if the user has one and it's not provided in the request
        if (!isset($validatedData['dealership_id']) && Auth::check() && Auth::user()->employee && Auth::user()->employee->dealership_id) {
            $validatedData['dealership_id'] = Auth::user()->employee->dealership_id;
        }

        if ($request->hasFile('resume')) {
            $file = $request->file('resume');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/resumes'), $filename);
            $validatedData['resume'] = 'uploads/resumes/' . $filename;
        }

        Interview::create($validatedData);

        if ($request->ajax()) {
            return response()->json(['success' => 'Interview created successfully.']);
        }

        return redirect()->route('interviews.index')->with('success', 'Interview created successfully.');
    }

    public function show(Interview $interview)
    {
        if (!checkMenu(Session::get('role_id'), 23, 'read')) {
            return redirect()->route('interviews.index')->with('error', 'You do not have permission to view this interview.');
        }
        $interview->load(['employee', 'client', 'dealership', 'comments.user.employee.department', 'jobVacancy']);
        return view('interviews.show', compact('interview'));
    }

    public function edit(Interview $interview)
    {
        if (request()->ajax()) {
            if (!checkMenu(Session::get('role_id'), 23, 'read')) {
                return response()->json(['error' => 'You do not have permission to edit interviews.'], 403);
            }
            return response()->json(['interview' => $interview]);
        }

        if (!checkMenu(Session::get('role_id'), 23, 'read')) {
            return redirect()->route('interviews.index')->with('error', 'You do not have permission to edit interviews.');
        }
        $employees = Employee::all();
        $clients = Client::all();
        $dealerships = Dealership::all();
        return view('interviews.edit', compact('interview', 'employees', 'clients', 'dealerships'));
    }

    public function update(Request $request, Interview $interview)
    {
        if (!checkMenu(Session::get('role_id'), 23, 'update')) {
            return redirect()->route('interviews.index')->with('error', 'You do not have permission to update interviews.');
        }
        $validatedData = $request->validate([
            'post_applied_for' => 'nullable|string|max:255',
            'candidate_name' => 'required|string|max:255',
            'contact_number' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],
            'email_id' => 'required|email|max:255',
            'educational_qualification' => 'nullable|string|max:255',
            'years_of_experience' => 'nullable|integer|min:0|max:60',
            'current_employer' => 'nullable|string|max:255',
            'last_current_ctc' => 'nullable|numeric|min:0',
            'expected_ctc' => 'nullable|numeric|min:0',
            'notice_period' => 'nullable|string|max:255',
            'communication_skills_rating' => 'nullable|integer|min:1|max:5',
            'communication_skills_remarks' => 'nullable|string',
            'technical_knowledge_rating' => 'nullable|integer|min:1|max:5',
            'technical_knowledge_remarks' => 'nullable|string',
            'problem_solving_ability_rating' => 'nullable|integer|min:1|max:5',
            'problem_solving_ability_remarks' => 'nullable|string',
            'knowledge_of_heavy_equipments_rating' => 'nullable|integer|min:1|max:5',
            'knowledge_of_heavy_equipments_remarks' => 'nullable|string',
            'relevant_work_experience_rating' => 'nullable|integer|min:1|max:5',
            'relevant_work_experience_remarks' => 'nullable|string',
            'attitude_and_confidence_rating' => 'nullable|integer|min:1|max:5',
            'attitude_and_confidence_remarks' => 'nullable|string',
            'adaptability_flexibility_rating' => 'nullable|integer|min:1|max:5',
            'adaptability_flexibility_remarks' => 'nullable|string',
            'teamwork_collaboration_rating' => 'nullable|integer|min:1|max:5',
            'teamwork_collaboration_remarks' => 'nullable|string',
            'leadership_potential_rating' => 'nullable|integer|min:1|max:5',
            'leadership_potential_remarks' => 'nullable|string',
            'willingness_to_travel_relocate_rating' => 'nullable|integer|min:1|max:5',
            'willingness_to_travel_relocate_remarks' => 'nullable|string',
            'interviewer_recommendation' => 'nullable|string|in:Highly Recommended,Recommended,Consider for Other Role,Not Recommended',
            'salary_offered' => 'nullable|numeric|min:0',
            'da' => 'nullable|numeric|min:0',
            'ta' => 'nullable|numeric|min:0',
            'location' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'dealership_id' => 'nullable|exists:dealerships,id',
            'job_vacancy_id' => 'nullable|exists:job_vacancies,id',
            'resume' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
        ], [
            'candidate_name.required' => 'Candidate name is required.',
            'contact_number.required' => 'Contact number is required.',
            'contact_number.regex' => 'Contact number format is invalid.',
            'email_id.required' => 'Email is required.',
        ]);

        // Automatically assign dealership_id if the user has one and it's not provided in the request
        if (!isset($validatedData['dealership_id']) && Auth::check() && Auth::user()->employee && Auth::user()->employee->dealership_id) {
            $validatedData['dealership_id'] = Auth::user()->employee->dealership_id;
        }

        if ($request->hasFile('resume')) {
            if ($interview->resume && file_exists(public_path($interview->resume))) {
                unlink(public_path($interview->resume));
            }
            $file = $request->file('resume');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/resumes'), $filename);
            $validatedData['resume'] = 'uploads/resumes/' . $filename;
        }

        $interview->update($validatedData);

        if ($request->ajax()) {
            return response()->json(['success' => 'Interview updated successfully.']);
        }

        return redirect()->route('interviews.index')->with('success', 'Interview updated successfully.');
    }

    public function destroy(Interview $interview)
    {
        if (!checkMenu(Session::get('role_id'), 23, 'delete')) {
            return response()->json(['error' => 'You do not have permission to delete interviews.'], 403);
        }
        $interview->delete();
        return response()->json(['success' => 'Interview deleted successfully.']);
    }

    public function storeComment(Request $request, Interview $interview)
    {
        if (!checkMenu(Session::get('role_id'), 23, 'create')) {
            if ($request->ajax()) {
                return response()->json(['error' => 'You do not have permission to add comments.'], 403);
            }
            return back()->with('error', 'You do not have permission to add comments.');
        }
        $request->validate([
            'comment' => 'required|string',
        ]);

        $comment = $interview->comments()->create([
            'user_id' => Auth::id(),
            'comment' => $request->comment,
        ]);

        $comment->load('user');

        if ($request->ajax()) {
            return response()->json(['success' => 'Comment added successfully.', 'comment' => $comment]);
        }

        return back()->with('success', 'Comment added successfully.');
    }

    public function updateComment(Request $request, InterviewComment $comment)
    {
        if (!checkMenu(Session::get('role_id'), 23, 'update')) {
            if ($request->ajax()) {
                return response()->json(['error' => 'You do not have permission to update comments.'], 403);
            }
            return back()->with('error', 'You do not have permission to update comments.');
        }
        $this->authorize('update', $comment);

        $request->validate([
            'comment' => 'required|string',
        ]);

        $comment->update([
            'comment' => $request->comment,
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => 'Comment updated successfully.']);
        }

        return back()->with('success', 'Comment updated successfully.');
    }

    public function destroyComment(Request $request, InterviewComment $comment)
    {
        if (!checkMenu(Session::get('role_id'), 23, 'delete')) {
            if ($request->ajax()) {
                return response()->json(['error' => 'You do not have permission to delete comments.'], 403);
            }
            return back()->with('error', 'You do not have permission to delete comments.');
        }
        $this->authorize('delete', $comment);

        $comment->delete();

        if ($request->ajax()) {
            return response()->json(['success' => 'Comment deleted successfully.']);
        }

        return back()->with('success', 'Comment deleted successfully.');
    }

    /**
     * Display the specified comment.
     *
     * @param  \App\Models\InterviewComment  $comment
     * @return \Illuminate\Http\JsonResponse
     */
    public function showComment(InterviewComment $comment)
    {
        if (!checkMenu(Session::get('role_id'), 23, 'read')) {
            return response()->json(['error' => 'You do not have permission to view comments.'], 403);
        }

        return response()->json($comment);
    }


    public function debugInterview()
    {
        return response()->json(Interview::first());
    }

    public function exportPdf(Interview $interview)
    {
        $interview->load(['employee', 'client', 'dealership', 'comments.user.employee.department']);
        $pdf = app('dompdf.wrapper');
        $pdf->loadView('interviews.pdf', compact('interview'));
        return $pdf->download('interview-' . $interview->id . '.pdf');
    }

    public function getApplicationsByVacancy($jobVacancyId)
    {
        if (!checkMenu(Session::get('role_id'), 23, 'read')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $applications = JobApplication::where('job_vacancy_id', $jobVacancyId)
            ->where('status', 'Applied')
            ->orderByDesc('created_at')
            ->get([
                'id',
                'candidate_name',
                'email_id',
                'contact_number',
                'educational_qualification',
                'years_of_experience',
                'current_employer',
                'last_current_ctc',
                'expected_ctc',
                'notice_period',
                'location',
                'post_applied_for',
                'created_at',
            ]);

        return response()->json($applications);
    }

    public function publicForm($uuid)
    {
        $interview = Interview::where('uuid', $uuid)->firstOrFail();
        return view('interviews.public_form', compact('interview'));
    }

    public function publicUpdate(Request $request, $uuid)
    {
        $interview = Interview::where('uuid', $uuid)->firstOrFail();

        $validatedData = $request->validate([
            'candidate_name' => 'required|string|max:255',
            'contact_number' => 'required|string|max:255',
            'email_id' => 'required|email|max:255',
            'educational_qualification' => 'nullable|string|max:255',
            'years_of_experience' => 'nullable|integer',
            'current_employer' => 'nullable|string|max:255',
            'last_current_ctc' => 'nullable|numeric',
            'expected_ctc' => 'nullable|numeric',
            'notice_period' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'resume' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
        ]);

        if ($request->hasFile('resume')) {
            if ($interview->resume && file_exists(public_path($interview->resume))) {
                unlink(public_path($interview->resume));
            }
            $file = $request->file('resume');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/resumes'), $filename);
            $validatedData['resume'] = 'uploads/resumes/' . $filename;
        }

        $interview->update($validatedData);

        return redirect()->back()->with('success', 'Application submitted successfully.');
    }
}
