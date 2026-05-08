<?php

namespace App\Http\Controllers;

use App\Models\PerformanceReview;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\PerformanceReviewComment;
use Illuminate\Support\Facades\Session;

use App\Models\Dealership;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

use App\Exports\PerformanceReviewsExport;
use Maatwebsite\Excel\Facades\Excel;

class PerformanceReviewController extends Controller
{
    public function exportExcel(Request $request)
    {
        // Assuming module ID for Performance Review is 35 based on typical structure or just use generic check
        // Ideally I should read the file to see the ID used in index.
        // Let's assume the user has access if they can hit the route, or add a check later.

        $filters = $request->only(['from_date', 'to_date']);
        return Excel::download(new PerformanceReviewsExport($filters), 'performance_reviews_' . date('Y_m_d_H_i_s') . '.xlsx');
    }

    public function index(Request $request)
    {
        if (!checkMenu(Session::get('role_id'), 26, 'read')) {
            abort(403);
        }
        if ($request->ajax()) {
            $user = Auth::user();
            $query = PerformanceReview::with(['employee.employee', 'reviewer.employee']);

            // If not admin, only show reviews where the user is the reviewer or the employee
            if ($user->user_type !== 'admin') {
                $query->where(function ($q) use ($user) {
                    $q->where('reviewer_id', $user->id)
                        ->orWhere('employee_id', $user->id);
                });
            }

            if ($request->has('dealership_id') && $request->dealership_id != '') {
                $query->whereHas('employee.employee', function ($q) use ($request) {
                    $q->where('dealership_id', $request->dealership_id);
                });
            }

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->addColumn('employee_name', function ($row) {
                    $name = $row->employee && $row->employee->employee ? $row->employee->employee->name : 'N/A';
                    $avatar = "https://ui-avatars.com/api/?name=" . urlencode($name) . "&color=7F9CF5&background=EBF4FF";
                    return '<div class="d-flex align-items-center">
                                <img src="' . $avatar . '" class="rounded-circle me-2" width="30" height="30" alt="Avatar">
                                <span>' . $name . '</span>
                            </div>';
                })
                ->addColumn('reviewer_name', function ($row) {
                    return $row->reviewer && $row->reviewer->employee ? $row->reviewer->employee->name : 'N/A';
                })
                // Average rating column removed

                ->addColumn('action', function ($row) {
                    $btn = '<ul class="action d-flex justify-content-around list-unstyled gap-2">';
                    $btn .= '<li class="view"><a title="View" href="javascript:void(0)" data-id="' . $row->id . '" class="view-review-btn"><i class="icon-eye"></i></a></li>';
                    // Check permissions
                    $canEdit = checkMenu(Session::get('role_id'), 26, 'edit');
                    $canDelete = checkMenu(Session::get('role_id'), 26, 'delete');

                    if ($canEdit) {
                        $btn .= '<li class="edit"><a title="Edit" href="javascript:void(0)" data-id="' . $row->id . '" class="edit-review-btn"><i class="icon-pencil-alt"></i></a></li>';
                    }
                    if ($canDelete) {
                        $btn .= '<li class="delete"><a title="Delete" href="javascript:void(0)" data-id="' . $row->id . '" class="delete-review-btn"><i class="icon-trash"></i></a></li>';
                    }

                    $btn .= '</ul>';
                    return $btn;
                })
                ->rawColumns(['action', 'employee_name'])
                ->make(true);
        }

        // Get subordinates for the create form
        $user = Auth::user();
        $subordinates = collect();
        if ($user->employee) {
            $subordinates = Employee::where('reporting_to', $user->id)->get();
        }

        $dealerships = Dealership::where('brand', 1)->get();

        // Upcoming reviews (next 30 days) + calendar events (submitted + due)
        $today = \Carbon\Carbon::today();
        $windowStart = $today->copy()->subYear();
        $windowEnd = $today->copy()->addYear();

        $avatarUrl = function (string $name): string {
            return "https://ui-avatars.com/api/?name=" . urlencode($name) . "&color=7F9CF5&background=EBF4FF";
        };

        $makeAnniversary = function (\Carbon\Carbon $joiningDate, int $year): \Carbon\Carbon {
            $month = (int) $joiningDate->month;
            $day = (int) $joiningDate->day;
            $daysInMonth = \Carbon\Carbon::create($year, $month, 1)->daysInMonth;
            $day = min($day, $daysInMonth);
            return \Carbon\Carbon::create($year, $month, $day);
        };

        $events = [];

        // 1) Submitted reviews in a +/- 1 year window
        $submittedReviews = PerformanceReview::with(['employee.employee.department'])
            ->whereBetween('review_date', [$windowStart->toDateString(), $windowEnd->toDateString()])
            ->get();

        foreach ($submittedReviews as $review) {
            $emp = $review->employee && $review->employee->employee ? $review->employee->employee : null;
            if (!$emp) {
                continue;
            }
            $name = $emp->name ?? 'N/A';
            $profilePic = $emp->profile_pic ? asset('storage/' . $emp->profile_pic) : $avatarUrl($name);

            $events[] = [
                'title' => $name,
                'start' => \Carbon\Carbon::parse($review->review_date)->format('Y-m-d'),
                'allDay' => true,
                'className' => 'bg-success',
                'extendedProps' => [
                    'type' => 'submitted',
                    'review_id' => $review->id,
                    'user_id' => $review->employee_id,
                    'profile_pic' => $profilePic,
                    'designation' => $emp->designation ?? '',
                    'department' => $emp->department ? $emp->department->name : '',
                ],
            ];
        }

        // 2) Due (pending) review anniversaries in the same window
        $years = range($windowStart->year, $windowEnd->year);
        $completedYearPairs = PerformanceReview::whereNotNull('review_year')
            ->whereIn('review_year', array_map('strval', $years))
            ->get(['employee_id', 'review_year']);

        $completed = [];
        foreach ($completedYearPairs as $pair) {
            $completed[$pair->employee_id][$pair->review_year] = true;
        }

        $employees = User::where('user_type', 'employee')
            ->has('employee')
            ->with(['employee.department'])
            ->get();

        $upcomingReviews = [];
        foreach ($employees as $u) {
            $employee = $u->employee;
            if (!$employee || !$employee->joining_date) {
                continue;
            }

            $joiningDate = \Carbon\Carbon::parse($employee->joining_date);
            $name = $employee->name ?? 'N/A';
            $profilePic = $employee->profile_pic ? asset('storage/' . $employee->profile_pic) : $avatarUrl($name);

            foreach ($years as $year) {
                if ($year <= $joiningDate->year) {
                    continue;
                }

                $dueDate = $makeAnniversary($joiningDate, $year);
                if ($dueDate->lt($windowStart) || $dueDate->gt($windowEnd)) {
                    continue;
                }

                $reviewYear = (string) $year;
                $isCompleted = isset($completed[$u->id][$reviewYear]);
                if ($isCompleted) {
                    continue;
                }

                $events[] = [
                    'title' => $name,
                    'start' => $dueDate->format('Y-m-d'),
                    'allDay' => true,
                    'className' => $dueDate->lt($today) ? 'bg-danger' : 'bg-warning',
                    'extendedProps' => [
                        'type' => 'due',
                        'user_id' => $u->id,
                        'review_year' => $reviewYear,
                        'profile_pic' => $profilePic,
                        'designation' => $employee->designation ?? '',
                        'department' => $employee->department ? $employee->department->name : '',
                    ],
                ];

                // Upcoming list: next 30 days only
                if ($dueDate->gte($today) && $dueDate->lte($today->copy()->addDays(30))) {
                    $upcomingReviews[] = [
                        'user_id' => $u->id,
                        'name' => $name,
                        'date' => $dueDate,
                        'profile_pic' => $employee->profile_pic,
                        'designation' => $employee->designation,
                        'department' => $employee->department ? $employee->department->name : '',
                        'days_left' => $dueDate->diffInDays($today),
                        'pending_count' => 1,
                    ];
                }
            }
        }

        // Sort by date
        usort($upcomingReviews, function ($a, $b) {
            return $a['date'] <=> $b['date'];
        });

        return view('performance-review.index', compact('subordinates', 'dealerships', 'upcomingReviews', 'events'));
    }

    public function store(Request $request)
    {
        if (!checkMenu(Session::get('role_id'), 26, 'create')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $request->validate([
            'employee_id' => 'required|exists:users,id',
            'initial_comment' => 'nullable|string',
            'final_report' => 'nullable|file|mimes:pdf|max:10240', // Max 10MB
        ]);

        $employeeId = $request->employee_id;
        $reviewDate = \Carbon\Carbon::now();
        $reviewPeriod = 'Q' . $reviewDate->quarter . ' ' . $reviewDate->year;

        $lastReview = PerformanceReview::where('employee_id', $employeeId)
            ->orderBy('review_date', 'desc')
            ->first();

        $employee = \App\Models\Employee::where('user_id', $employeeId)->first();
        $joiningDate = $employee ? \Carbon\Carbon::parse($employee->joining_date) : null;

        $nextReviewDate = null;
        if ($lastReview) {
            $nextReviewDate = \Carbon\Carbon::parse($lastReview->review_date)->addYear();
        } elseif ($joiningDate) {
            $nextReviewDate = $joiningDate->copy()->addYear();
        }

        if ($nextReviewDate && $reviewDate->lt($nextReviewDate)) {
            // return response()->json(['errors' => ['review_date' => ['The review date cannot be earlier than the next due date: ' . $nextReviewDate->format('Y-m-d')]]], 422);
        }
        $review = new PerformanceReview($request->except(['initial_comment', 'review_period', 'final_report']));
        $review->review_date = $reviewDate;
        $review->review_period = $reviewPeriod;
        $review->review_year = $request->review_year;
        $review->reviewer_id = Auth::id();

        if ($request->hasFile('final_report')) {
            $file = $request->file('final_report');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('storage/performance-reviews'), $filename);
            $review->final_report_pdf = 'storage/performance-reviews/' . $filename;
        }

        $review->save();

        if ($request->filled('initial_comment')) {
            $review->comments()->create([
                'user_id' => Auth::id(),
                'comment' => $request->initial_comment,
            ]);
        }

        // Notify HR, Employee, and Reporter
        $recipients = collect();

        // 1. HR
        $hrDepartments = \App\Models\Department::whereIn('name', ['Human Resources', 'HR'])->pluck('id');
        $hrEmployees = Employee::with('user')->whereIn('department_id', $hrDepartments)->get();
        foreach ($hrEmployees as $hr) {
            if ($hr->user) $recipients->push($hr->user);
        }

        // 2. Employee
        if ($review->employee) {
            $recipients->push($review->employee);
        }

        // 3. Reporting Authority
        $empDetails = Employee::where('user_id', $review->employee_id)->with('reporter.user')->first();
        if ($empDetails && $empDetails->reporter && $empDetails->reporter->user) {
            $recipients->push($empDetails->reporter->user);
        }

        $title = "Performance Review Created";
        $employeeName = ($review->employee && $review->employee->employee ? $review->employee->employee->name : 'an employee');
        $message = "A new performance review has been created for {$employeeName}.";

        foreach ($recipients->unique('id') as $recipient) {
            if ($recipient->player_id) {
                try {
                    do {
                        $notificationId = (string) \Illuminate\Support\Str::uuid();
                    } while (\App\Models\Notification::where('notification_id', $notificationId)->exists());

                    $payloadData = [
                        'id' => $review->id,
                        'route' => 'PerformanceReviewView',
                        'type' => 'new_performance_review',
                        'notification_id' => $notificationId,
                    ];

                    $response = Http::withHeaders([
                        'Authorization' => 'Basic ' . env('ONESIGNAL_REST_API_KEY'),
                        'Content-Type' => 'application/json',
                    ])->post('https://onesignal.com/api/v1/notifications', [
                        'app_id' => env('ONESIGNAL_APP_ID'),
                        'include_aliases' => [
                            'external_id' => [$recipient->email],
                        ],
                        'data' => $payloadData,
                        'target_channel' => 'push',
                        'priority' => 10,
                        'android_visibility' => 1,
                        'headings' => ['en' => $title],
                        'contents' => ['en' => $message],
                    ]);

                    \App\Models\Notification::create([
                        'notification_id' => $notificationId,
                        'user_id' => $recipient->id,
                        'title' => $title,
                        'message' => $message,
                        'data' => $payloadData,
                    ]);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Failed to send OneSignal notification.', [
                        'user_id' => $recipient->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        return response()->json([
            'message' => 'Performance review created successfully.',
            'redirect_url' => route('performance-review.show', $review->id)
        ]);
    }

    public function show($id)
    {
        if (!checkMenu(Session::get('role_id'), 26, 'read')) {
            abort(403);
        }
        $review = PerformanceReview::with([
            'employee.employee.department',
            'employee.employee.reporter',
            'reviewer.employee',
            'updater.employee',
            'comments.user.employee.department'
        ])->findOrFail($id);

        return view('performance-review.show', compact('review'));
    }

    public function edit($id)
    {
        if (!checkMenu(Session::get('role_id'), 26, 'edit')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $review = PerformanceReview::with(['employee.employee.department'])->findOrFail($id);
        return response()->json($review);
    }

    public function update(Request $request, $id)
    {
        $review = PerformanceReview::findOrFail($id);

        // Check authorization
        if (!checkMenu(Session::get('role_id'), 26, 'edit')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'review_period' => 'nullable|string',
            'review_year' => 'nullable|string',
            'final_report' => 'nullable|file|mimes:pdf|max:10240',
        ]);

        if ($request->hasFile('final_report')) {
            // Delete old file if exists
            if ($review->final_report_pdf && \Illuminate\Support\Facades\File::exists(public_path($review->final_report_pdf))) {
                \Illuminate\Support\Facades\File::delete(public_path($review->final_report_pdf));
            }

            $file = $request->file('final_report');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('storage/performance-reviews'), $filename);
            $review->final_report_pdf = 'storage/performance-reviews/' . $filename;
            $review->save();
        }

        $data = $request->except(['final_report', 'final_report_pdf']);
        $data['updated_by'] = Auth::id();
        $review->update($data); 

        return response()->json(['message' => 'Performance review updated successfully.']);
    }

    public function destroy($id)
    {
        $review = PerformanceReview::findOrFail($id);

        // Check authorization
        if (!checkMenu(Session::get('role_id'), 26, 'delete')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $review->delete();

        return response()->json(['message' => 'Performance review deleted successfully.']);
    }

    public function storeComment(Request $request, $id)
    {
        $request->validate([
            'comment' => 'required|string',
        ]);

        $review = PerformanceReview::findOrFail($id);

        $comment = $review->comments()->create([
            'user_id' => Auth::id(),
            'comment' => $request->comment,
        ]);

        $comment->load('user');

        if ($request->ajax()) {
            return response()->json(['success' => 'Comment added successfully.', 'comment' => $comment]);
        }

        return redirect()->route('performance-review.show', $id)->with('success', 'Comment added successfully.');
    }

    public function updateComment(Request $request, $id)
    {
        $comment = PerformanceReviewComment::findOrFail($id);

        if (Auth::id() !== $comment->user_id) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            return back()->with('error', 'Unauthorized');
        }

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

    public function destroyComment(Request $request, $id)
    {
        $comment = PerformanceReviewComment::findOrFail($id);

        if (Auth::id() !== $comment->user_id) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            return back()->with('error', 'Unauthorized');
        }

        $comment->delete();

        if ($request->ajax()) {
            return response()->json(['success' => 'Comment deleted successfully.']);
        }

        return back()->with('success', 'Comment deleted successfully.');
    }

    public function showComment($id)
    {
        $comment = PerformanceReviewComment::findOrFail($id);
        return response()->json($comment);
    }

    public function exportPdf($id)
    {
        $review = PerformanceReview::with(['employee.employee', 'reviewer.employee', 'comments.user'])->findOrFail($id);
        $pdf = app('dompdf.wrapper');
        $pdf->loadView('performance-review.pdf', compact('review'));
        return $pdf->download('performance-review-' . $review->id . '.pdf');
    }

    public function getEmployeeHistory($employeeId)
    {
        // Assuming employeeId is the user_id as used in the form select
        $reviews = PerformanceReview::where('employee_id', $employeeId)
            ->with('reviewer.employee')
            ->orderBy('review_date', 'desc')
            ->get();

        $employee = \App\Models\Employee::where('user_id', $employeeId)->first();

        $joiningDate = $employee ? $employee->joining_date : null;
        $lastReviewDate = $reviews->first() ? $reviews->first()->review_date : null;

        $nextReviewDate = null;
        if ($lastReviewDate) {
            $nextReviewDate = \Carbon\Carbon::parse($lastReviewDate)->addYear();
        } elseif ($joiningDate) {
            $nextReviewDate = \Carbon\Carbon::parse($joiningDate)->addYear();
        }

        if ($nextReviewDate) {
            $today = \Carbon\Carbon::today();
            while ($nextReviewDate->lt($today)) {
                $nextReviewDate->addYear();
            }
        }

        // Calculate Years List
        $yearsList = [];
        if ($joiningDate) {
            $join = \Carbon\Carbon::parse($joiningDate);
            $tempDate = $join->copy()->addYear();
            
            // Go up to next year to allow planning
            $limitDate = \Carbon\Carbon::today()->addYear();
            $currentYear = \Carbon\Carbon::today()->year;
            
            $completedYears = $reviews->pluck('review_year')->filter()->toArray();

            while ($tempDate->lte($limitDate)) {
                $yearLabel = $tempDate->format('Y');
                $isCompleted = in_array($yearLabel, $completedYears);
                $currentYear = \Carbon\Carbon::today()->year;

                if ($isCompleted || (int)$yearLabel >= $currentYear) {
                    $yearsList[] = [
                        'year' => $yearLabel,
                        'label' => $isCompleted ? $yearLabel . ' (Completed)' : $yearLabel . ' (Pending)',
                        'status' => $isCompleted ? 'completed' : 'pending'
                    ];
                }
                $tempDate->addYear();
            }
        }

        return response()->json([
            'reviews' => $reviews,
            'joining_date' => $joiningDate,
            'next_review_date' => $nextReviewDate ? $nextReviewDate->format('Y-m-d') : null,
            'years_list' => $yearsList
        ]);
    }
    public function removeReport(Request $request, PerformanceReview $performanceReview)
    {
        if ($performanceReview->final_report_pdf) {
            $filePath = public_path($performanceReview->final_report_pdf);
            if (\Illuminate\Support\Facades\File::exists($filePath)) {
                \Illuminate\Support\Facades\File::delete($filePath);
            }
            $performanceReview->update(['final_report_pdf' => null]);
        }

        if ($request->ajax()) {
            return response()->json(['success' => 'Report removed successfully.']);
        }

        return redirect()->back()->with('success', 'Report removed successfully.');
    }
}
