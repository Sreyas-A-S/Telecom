<?php

namespace App\Http\Controllers;

use App\Models\Settlement;
use App\Models\SettlementRemark;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use App\Exports\SettlementsExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\Notification;
use App\Models\User;



class SettlementsController extends Controller
{
    public function exportExcel(Request $request)
    {
        Log::info('Settlements exportExcel hit', ['user_id' => auth()->id(), 'filters' => $request->all()]);

        if (!checkMenu(Session::get('role_id'), 24, 'read')) {
            Log::warning('Settlements exportExcel unauthorized access');
            abort(403);
        }

        $filters = $request->only(['from_date', 'to_date']);
        Log::info('Proceeding with export download');
        return Excel::download(new SettlementsExport($filters), 'settlements_' . date('Y_m_d_H_i_s') . '.xlsx');
    }

    public function index()
    {
        if (!checkMenu(Session::get('role_id'), 24, 'read')) {
            abort(403);
        }
        $settlements = Settlement::all();
        $departments = [
            'Service Department',
            'Parts Department',
            'Sales Department',
            'Accounts Department',
            'HR Department',
            'Work Shop',
            'Business Head',
            'General Manager'
        ];
        $noticePeriodDuration = \App\Models\Setting::where('key', 'notice_period_duration')->value('value') ?? 0;
        return view('settlements.index', compact('settlements', 'departments', 'noticePeriodDuration'));
    }

    public function getDataForDatatable(Request $request)
    {
        if (!checkMenu(Session::get('role_id'), 24, 'read')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        if ($request->ajax()) {
            $data = Settlement::select([
                'id',
                'employee_code',
                'employee_name',
                'department',
                'date_of_joining',
                'date_of_resignation',
                'dealership_id' // Include dealership_id in select
            ]);

            $user = auth()->user();
            $dealershipId = null;
            if ($user && $user->employee && $user->employee->dealership_id) {
                $dealershipId = $user->employee->dealership_id;
            }

            if ($dealershipId) {
                $data->where('dealership_id', $dealershipId);
            }

            return datatables()->of($data)
                ->addIndexColumn()
                ->addColumn('actions', function ($row) {
                    $btn = '<ul class="action d-flex justify-content-around list-unstyled gap-2">';
                    $btn .= '<li class="view"><a title="View" href="' . route('settlements.show', $row->id) . '" class="btn btn-sm btn-link text-info"><i class="icon-eye"></i></a></li>';

                    if (checkMenu(Session::get('role_id'), 24, 'delete')) {
                        $btn .= '<li class="delete"><button type="button" class="btn btn-sm btn-link text-danger delete-settlement-btn" data-id="' . $row->id . '" data-name="' . $row->employee_name . '" title="Delete"><i class="icon-trash"></i></button></li>';
                    }
                    $btn .= '<li class="export-pdf"><a title="Export to PDF" href="' . route('settlements.exportPdf', $row->id) . '" class="btn btn-sm btn-link text-success"><i class="icon-download"></i></a></li>';
                    $btn .= '</ul>';
                    return $btn;
                })
                ->rawColumns(['actions'])
                ->make(true);
        }
    }



    public function store(Request $request)
    {
        if (!checkMenu(Session::get('role_id'), 24, 'create')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $validatedData = $request->validate([
            'employee_code' => 'required|string|unique:settlements',
            'employee_name' => 'required|string',
            'age' => 'nullable|integer',
            'department' => 'nullable|string',
            'head_office_branch' => 'nullable|string',
            'designation' => 'nullable|string',
            'date_of_joining' => 'required|date',
            'date_of_resignation' => 'nullable|date|after_or_equal:date_of_joining',
            'reason_for_resignation' => 'nullable|string',
            'remarks' => 'nullable|array',
            'remarks.*.department' => 'required|string',
            'remarks.*.manager_id' => 'nullable|exists:employees,id',
            'remarks.*.remark' => 'nullable|string',
            'remarks.*.signature' => 'nullable|string',
            'remarks.*.file' => 'nullable|file|mimes:jpeg,png,jpg,pdf,doc,docx|max:2048', // Validate file
        ]);

        $dealershipId = null;
        if (auth()->user()->employee && auth()->user()->employee->dealership_id) {
            $dealershipId = auth()->user()->employee->dealership_id;
        }
        $validatedData['dealership_id'] = $dealershipId;

        $settlement = Settlement::create($validatedData);

        if (isset($validatedData['remarks'])) {
            foreach ($validatedData['remarks'] as $index => $remarkData) {
                // Since validation doesn't return the file object directly in nested arrays in the same way sometimes,
                // we should access the request file directly using the index.
                $file = $request->file("remarks.$index.file");
                $filePath = null;

                if ($file) {
                    $filePath = $file->store('settlements/attachments', 'public');
                }

                $department = $remarkData['department'];
                $manager = $this->getDepartmentManager($department, $dealershipId);
                $managerId = $manager ? $manager->id : null;

                $settlement->remarks()->create([
                    'department' => $department,
                    'manager_id' => $managerId,
                    'remark' => $remarkData['remark'] ?? null,
                    'signature' => $remarkData['signature'] ?? null,
                    'file_path' => $filePath,
                ]);

                if ($manager && $manager->user) {
                    $this->sendNotification(
                        $manager->user,
                        "Settlement Notification",
                        "A new settlement for {$validatedData['employee_name']} requires your attention.",
                        ['settlement_id' => $settlement->id, 'type' => 'settlement_created']
                    );
                }
            }
        }

        return response()->json(['success' => true, 'message' => 'Settlement created successfully!', 'settlement_id' => $settlement->id]);
    }

    private function getDepartmentManager($department, $dealershipId = null)
    {
        $departmentRoleMap = [
            'Service Department' => ['service manager'],
            'Parts Department' => ['parts manager'],
            'Sales Department' => ['sales manager'],
            'Accounts Department' => ['accounts manager'],
            'HR Department' => ['assistant hr manager'],
            'Work Shop' => ['workshop manager', 'service center manager'],
            'Business Head' => ['business head'],
            'General Manager' => ['general manager'],
        ];

        $roleNames = $departmentRoleMap[$department] ?? [];

        if (empty($roleNames)) {
            return null;
        }

        $query = \App\Models\Employee::query();

        if ($dealershipId) {
            $query->where('dealership_id', $dealershipId);
        }

        return $query->where(function ($query) use ($roleNames) {
                foreach ($roleNames as $roleName) {
                    $query->orWhere(function ($subQuery) use ($roleName) {
                        $subQuery->whereRaw('LOWER(designation) = ?', [strtolower($roleName)])
                            ->orWhereHas('role', function ($q) use ($roleName) {
                                $q->whereRaw('LOWER(role) = ?', [strtolower($roleName)]);
                            });
                    });
                }
            })
            ->first();
    }

    public function show(Settlement $settlement)
    {
        if (!checkMenu(Session::get('role_id'), 24, 'read')) {
            abort(403);
        }
        $settlement->load('remarks.manager');
        return view('settlements.show', compact('settlement'));
    }

    public function storeRemark(Request $request, Settlement $settlement)
    {
        if (!checkMenu(Session::get('role_id'), 24, 'edit')) {
            return redirect()->back()->with('error', 'Unauthorized');
        }
        $validatedData = $request->validate([
            'department' => 'required|string',
            'remark' => 'nullable|string',
            'signature' => 'nullable|string',
        ]);

        $settlement->remarks()->create($validatedData);

        return redirect()->route('settlements.show', $settlement)->with('success', 'Remark added successfully!');
    }

    public function updateFilledStatus(Request $request, SettlementRemark $settlementRemark)
    {
        if (!checkMenu(Session::get('role_id'), 24, 'edit')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $request->validate([
            'is_filled' => 'required|boolean',
        ]);

        $settlementRemark->is_filled = $request->is_filled;
        $settlementRemark->save();

        return response()->json(['success' => true, 'message' => 'Remark status updated successfully.']);
    }

    public function uploadRemarkFile(Request $request, SettlementRemark $settlementRemark)
    {
        if (!checkMenu(Session::get('role_id'), 24, 'edit')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'file' => 'required|file|mimes:jpeg,png,jpg,pdf,doc,docx|max:2048',
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filePath = $file->store('settlements/attachments', 'public');
            $settlementRemark->update(['file_path' => $filePath]);
        }

        return response()->json(['success' => true, 'message' => 'File uploaded successfully', 'file_path' => asset('storage/' . $filePath)]);
    }

    public function updateDepartmentRemarksStatus(Request $request, Settlement $settlement)
    {
        if (!checkMenu(Session::get('role_id'), 24, 'edit')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $validatedData = $request->validate([
            'department' => 'required|string',
            'is_filled' => 'required|boolean',
        ]);

        $settlement->remarks()
            ->where('department', $validatedData['department'])
            ->update(['is_filled' => $validatedData['is_filled']]);

        return response()->json(['success' => true, 'message' => 'Department remarks status updated successfully.']);
    }

    public function edit(Settlement $settlement)
    {
        if (!checkMenu(Session::get('role_id'), 24, 'edit')) {
            return redirect()->back()->with('error', 'Unauthorized');
        }
        return view('settlements.edit', compact('settlement'));
    }

    public function editData(Settlement $settlement)
    {
        if (!checkMenu(Session::get('role_id'), 24, 'edit')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        return response()->json($settlement);
    }

    public function update(Request $request, Settlement $settlement)
    {
        if (!checkMenu(Session::get('role_id'), 24, 'edit')) {
            return redirect()->back()->with('error', 'Unauthorized');
        }
        $validatedData = $request->validate([
            'employee_code' => 'required|string|unique:settlements,employee_code,' . $settlement->id,
            'employee_name' => 'required|string',
            'age' => 'nullable|integer',
            'department' => 'nullable|string',
            'head_office_branch' => 'nullable|string',
            'designation' => 'nullable|string',
            'date_of_joining' => 'required|date',
            'date_of_resignation' => 'nullable|date|after_or_equal:date_of_joining',
            'reason_for_resignation' => 'nullable|string',
        ]);

        $settlement->update($validatedData);

        return redirect()->route('settlements.index')->with('success', 'Settlement updated successfully!');
    }

    public function destroy(Settlement $settlement)
    {
        if (!checkMenu(Session::get('role_id'), 24, 'delete')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        // Manually delete all related remarks
        $settlement->remarks()->delete();

        // Then delete the settlement itself
        $settlement->delete();

        return response()->json(['success' => true, 'message' => 'Settlement and all its remarks have been deleted successfully!']);
    }

    public function notifications()
    {
        if (!checkMenu(Session::get('role_id'), 24, 'read')) {
            abort(403);
        }

        $notifications = Notification::where('data->type', 'settlement_created')
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        // Fetch related settlements efficiently
        $settlementIds = $notifications->pluck('data.settlement_id')->filter()->unique();
        $settlements = Settlement::whereIn('id', $settlementIds)->get()->keyBy('id');

        return view('settlements.notifications', compact('notifications', 'settlements'));
    }

    public function exportPdf(Settlement $settlement)
    {
        $pdf = \App::make('dompdf.wrapper');
        $pdf->loadView('settlements.pdf', compact('settlement'));
        return $pdf->download('settlement_' . $settlement->id . '.pdf');
    }

    private function sendNotification(User $recipient, string $title, string $message, array $data = [])
    {
        try {
            if (empty($recipient->player_id)) {
                return;
            }

            do {
                $notificationId = (string) Str::uuid();
            } while (Notification::where('notification_id', $notificationId)->exists());

            $payloadData = array_merge([
                'type' => 'settlement_created', // Default type
                'notification_id' => $notificationId,
            ], $data);

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

            $status = $response->successful() ? 'sent' : 'failed';
            $payloadData['status'] = $status;
            $payloadData['onesignal_response'] = $response->json();

            Notification::create([
                'notification_id' => $notificationId,
                'user_id' => $recipient->id,
                'title' => $title,
                'message' => $message,
                'data' => $payloadData,
            ]);

            Log::info("OneSignal notification sent to {$recipient->email}.", [
                'recipient_id' => $recipient->id,
                'response' => $response->json(),
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send OneSignal notification to {$recipient->email}.", [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
