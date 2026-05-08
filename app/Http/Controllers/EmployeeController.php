<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Department;
use App\Models\Role;
use App\Models\Dealership;
use App\Models\Zone;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Menu;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\EmployeesExport;
use App\Models\EmployeeImport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule; // Added this line
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        if (!checkMenu(Session::get('role_id'), 4, 'read')) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            } else {
                abort(403);
            }
        }

        if ($request->ajax()) {
            $data = Employee::with(['department', 'role', 'dealership', 'zone', 'reporter', 'user'])->select('employees.*')->orderBy('created_at', 'desc');

            $joiningDateFilter = $request->input('joining_date_filter');
            $joiningDateSortOrder = $request->input('joining_date_sort_order');

            if ($joiningDateFilter === 'today') {
                $data->whereDate('joining_date', today());
            } elseif ($joiningDateFilter === 'this_week') {
                $data->whereBetween('joining_date', [now()->startOfWeek(), now()->endOfWeek()]);
            } elseif ($joiningDateFilter === 'this_month') {
                $data->whereMonth('joining_date', now()->month)->whereYear('joining_date', now()->year);
            } elseif ($joiningDateFilter === 'this_year') {
                $data->whereYear('joining_date', now()->year);
            }

            if ($joiningDateSortOrder && $joiningDateSortOrder !== 'any') {
                $data->orderByRaw('CASE WHEN joining_date IS NULL OR joining_date = "" THEN 1 ELSE 0 END ASC');
                $data->orderBy('joining_date', $joiningDateSortOrder);
            }

            $joiningDateFilter = $request->input('joining_date_filter');
            $joiningDateSortOrder = $request->input('joining_date_sort_order');

            if ($joiningDateFilter === 'today') {
                $data->whereDate('joining_date', today());
            } elseif ($joiningDateFilter === 'this_week') {
                $data->whereBetween('joining_date', [now()->startOfWeek(), now()->endOfWeek()]);
            } elseif ($joiningDateFilter === 'this_month') {
                $data->whereMonth('joining_date', now()->month)->whereYear('joining_date', now()->year);
            } elseif ($joiningDateFilter === 'this_year') {
                $data->whereYear('joining_date', now()->year);
            }

            if ($joiningDateSortOrder && $joiningDateSortOrder !== 'any') {
                $data->orderByRaw('CASE WHEN joining_date IS NULL OR joining_date = "" THEN 1 ELSE 0 END ASC');
                $data->orderBy('joining_date', $joiningDateSortOrder);
            }
            try {
                return DataTables::of($data)
                    ->filter(function ($query) use ($request) {
                        if ($request->has('search') && $request->search['value'] != '') {
                            $searchValue = $request->search['value'];
                            $query->where(function ($query) use ($searchValue) {

                                $query->orWhere('employees.name', 'like', "%{$searchValue}%")
                                    ->orWhere('employees.employee_id', 'like', "%{$searchValue}%");

                                $query->orWhere('employees.designation', 'like', "%{$searchValue}%");
                                $query->orWhereHas('role', function ($q) use ($searchValue) {
                                    $q->where('role', 'like', "%{$searchValue}%");
                                });

                                $query->orWhereHas('dealership', function ($q) use ($searchValue) {
                                    $q->where('name', 'like', "%{$searchValue}%");
                                });
                                $query->orWhereHas('department', function ($q) use ($searchValue) {
                                    $q->where('name', 'like', "%{$searchValue}%");
                                });

                                $query->orWhere('employees.mobile', 'like', "%{$searchValue}%")
                                    ->orWhere('employees.email', 'like', "%{$searchValue}%");

                                // joining_date
                                $query->orWhere('employees.joining_date', 'like', "%{$searchValue}%");
                            });
                        }
                    })
                    ->addIndexColumn()
                    ->addColumn('employee_combined', function ($row) {
                        $profilePicHtml = '';
                        if ($row->profile_pic && file_exists(public_path('storage/' . $row->profile_pic))) {
                            $imageUrl = Storage::url($row->profile_pic) . '?v=' . now()->timestamp;
                            $profilePicHtml = '<img src="' . $imageUrl . '" width="40" height="40" class="rounded-circle me-2" style="object-fit: cover;">';
                        } else {
                            $profilePicHtml = '<img src="' . asset("admin/assets/images/blog/12.png") . '" width="40" height="40" class="rounded-circle me-2" style="object-fit: cover;">';
                        }
                        $employeeName = $row->name ?? 'N/A';
                        $employeeIdBadge = '<span class="badge bg-primary">' . ($row->employee_id ?? 'N/A') . '</span>';
                        $nameAndIdHtml = '<div><div>' . $employeeName . '</div><div>' . $employeeIdBadge . '</div></div>';
                        return '<div class="d-flex align-items-center">' . $profilePicHtml . $nameAndIdHtml . '</div>';
                    })
                    ->addColumn('designation_role_combined', function ($row) {
                        $designation = $row->designation ?
                            '<span class="badge bg-primary bg-opacity-50">' . $row->designation . '</span>' :
                            '<span class="badge bg-secondary bg-opacity-50">All</span>';
                        $role = $row->role ? '<span class="badge bg-info bg-opacity-50">' . $row->role->role . '</span>' :
                            '<span class="badge bg-secondary bg-opacity-50">All</span>';
                        return '<div>' . $designation . '</div><div class="mt-1">' . str_replace('_', ' ', $role) . '</div>';
                    })
                    ->addColumn('dealership_department_combined', function ($row) {
                        $dealership = $row->dealership ?
                            '<span class="badge bg-success bg-opacity-50">' . $row->dealership->name . '</span>' :
                            '<span class="badge bg-secondary bg-opacity-50">All</span>';
                        $department = $row->department ?
                            '<span class="badge bg-warning bg-opacity-50">' . $row->department->name . '</span>' :
                            '<span class="badge bg-secondary bg-opacity-50">All';
                        return '<div>' . $dealership . '</div><div class="mt-1">' . $department . '</div>';
                    })
                    ->addColumn('contact_combined', function ($row) {
                        $mobile = $row->mobile ?? 'N/A';
                        $email = $row->email ?? 'N/A';
                        return '<div>' . $mobile . '</div><div><small class="text-muted">' . $email . '</small></div>';
                    })
                    ->addColumn('joining_date_combined', function ($row) {
                        $joiningDate = $row->joining_date ?? 'N/A';
                        $reportingTo = $row->reporter2 ? $row->reporter2->name : '';
                        return '<div>' . $joiningDate . '</div><div><small class="text-muted">' . $reportingTo . '</small></div>';
                    })
                    ->addColumn('actions', function ($row) {
                        $btn = '<ul class="action d-flex justify-content-around list-unstyled gap-2">';
                        $btn .= '<li class="view"><a title="View" href="#" data-bs-toggle="modal" data-bs-target="#viewEmployeeModal" data-id="' . $row->id . '"><i class="icon-eye"></i></a></li>';
                        $btn .= '<li class="edit"><a href="#" title="Edit" data-bs-toggle="modal" data-bs-target="#editEmployeeModal" data-id="' . $row->id . '"><i class="icon-pencil-alt"></i></a></li>';
                        $btn .= '<li class="pdf"><a href="' . route('employees.export.pdf', $row->id) . '" title="Download PDF" target="_blank"><i class="fa fa-file-pdf-o"></i></a></li>';
                        $btn .= '<li class="delete"><a title="Delete" href="#" data-bs-toggle="modal" data-bs-target="#deleteEmployeeModal" data-id="' . $row->id . '" data-employee-name="' . $row->name . '"><i class="icon-trash"></i></a></li>';
                        $btn .= '</ul>';
                        return $btn;
                    })
                    ->addColumn('status', function ($row) {
                        $status = $row->status;
                        $statusLabel = $status ? 'Active' : 'Inactive';
                        $statusClass = $status ? 'bg-success' : 'bg-secondary';
                        return '<span class="badge ' . $statusClass . '">' . $statusLabel . '</span>';
                    })
                    ->rawColumns(['actions', 'employee_combined', 'designation_role_combined', 'dealership_department_combined', 'contact_combined', 'joining_date_combined', 'status'])

                    ->make(true);
            } catch (\Exception $e) {
                Log::error('DataTables error in EmployeeController: ' . $e->getMessage());

                return response()->json([
                    'draw' => $request->input('draw', 1),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                    'error' => 'An error occurred while fetching data: ' . $e->getMessage()
                ], 500);
            }
        }

        $designations = Employee::select('designation')->distinct()->get();
        //remove null designations
        $designations = $designations->filter(function ($designation) {
            return !is_null($designation->designation);
        });

        $departments = Department::all();
        $roles = Role::all();
        $dealerships = Dealership::all();
        $zones = Zone::all();

        $excelColumns = [
            'Sl No',
            'Employee Name',
            'Email ID',
            'Phone No',
            'Gender',
            'Date of Birth',
            'Date of Joining',
            'Address',
            'Designation',
            'Department',
            'Role',
            'Dealership',
            'Zone',
            'Country',
            'Is Agent',
        ];

        return view('employees.index', compact('departments', 'roles', 'dealerships', 'zones', 'designations', 'excelColumns'));
    }

    public function show(Employee $employee)
    {
        $employee->load(['department', 'role', 'dealership', 'zone', 'reporter', 'reporter2']);

        if ($employee->role) {
            $employee->role->role = strtoupper(str_replace('_', ' ', $employee->role->role));
        }
        return response()->json($employee);
    }

    public function store(Request $request)
    {
        if (!checkMenu(Session::get('role_id'), 4, 'create')) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            } else {
                abort(403);
            }
        }

        try {
            DB::beginTransaction();

            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'employee_id' => 'required|string|unique:employees',
                'email' => 'required|string|email|max:255|unique:employees|unique:users,email',
                'password' => 'required|string|min:8|confirmed',
                'profile_pic' => 'nullable|string',
                'designation' => 'nullable|string',
                'role_id' => 'nullable|integer',
                'zone_id' => 'nullable|integer',
                'department_id' => 'sometimes|nullable',
                'address' => 'required|string',
                'marital_status' => 'nullable|string',
                'emergency_contact' => 'nullable|string',
                'father_name' => 'nullable|string',
                'mother_name' => 'nullable|string',
                'spouse_name' => 'nullable|string',
                'shirt_size' => 'nullable|string',
                'tshirt_size' => 'nullable|string',
                'blood_group' => 'nullable|string',
                'bank_name' => 'nullable|string',
                'account_number' => 'nullable|string',
                'ifsc_code' => 'nullable|string',
                'pf_no' => 'nullable|string',
                'esi_no' => 'nullable|string',
                'lwf_no' => 'nullable|string',
                'aadhar_no' => 'nullable|string',
                'pan_no' => 'nullable|string',
                'branch' => 'nullable|string',
            ]);

            if (isset($validatedData['department_id']) && $validatedData['department_id'] !== null) {
                $validatedData['department_id'] = (int) $validatedData['department_id'];
            } else {
                $validatedData['department_id'] = null;
            }

            $employee = new Employee($request->except('profile_pic', 'password', 'password_confirmation'));
            $employee->department_id = $validatedData['department_id'];
            $employee->password = Hash::make($request->password);

            if ($request->profile_pic) {
                $imageData = $request->profile_pic;
                $decoded = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imageData));
                $imageInfo = getimagesizefromstring($decoded);
                $extension = image_type_to_extension($imageInfo[2]);
                $filename = $request->employee_id . $extension;

                $path = public_path('storage/profile_pics');
                if (!\Illuminate\Support\Facades\File::exists($path)) {
                    \Illuminate\Support\Facades\File::makeDirectory($path, 0755, true);
                }

                file_put_contents($path . '/' . $filename, $decoded);
                $employee->profile_pic = 'profile_pics/' . $filename;
            }

            $employee->save();


            $user = User::create([
                'name' => $employee->name,
                'email' => $employee->email,
                'password' => $employee->password,
                'user_type' => 'employee',
                'employee_id' => $employee->employee_id,
                'profile_pic' => $employee->profile_pic,
            ]);

            $userId = $user->id;
            $employee->user_id = $userId;
            $employee->save();

            DB::commit();

            log_action('Employee created: ' . $employee->name . ' (ID: ' . $employee->id . ')');
            return response()->json(['message' => 'Employee created successfully.']);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating employee: ' . $e->getMessage());
            return response()->json(['error' => 'An unexpected error occurred.'], 500);
        }
    }

    public function edit(Employee $employee)
    {
        $employee->load(['department', 'role', 'dealership', 'zone', 'reporter']);
        //format the role names to uppercase and replace underscores with spaces if role exists
        if ($employee->role) {
            $employee->role->role = strtoupper(str_replace('_', ' ', $employee->role->role));
        }

        $dealershipZones = Zone::all();

        return response()->json([
            'employee' => $employee,
            'dealership_zones' => $dealershipZones,
        ]);
    }

    public function update(Request $request, Employee $employee)
    {
        // Authorization check
        if (!checkMenu(Session::get('role_id'), 4, 'update')) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            } else {
                abort(403);
            }
        }

        // Build email validation rules
        $emailRules = [
            'required',
            'string',
            'email',
            'max:255',
            Rule::unique('employees', 'email')->ignore($employee->id, 'id'),
        ];

        if ($employee->user) {
            $emailRules[] = Rule::unique('users', 'email')->ignore($employee->user->id, 'id');
        }
        // Validate request
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'employee_id' => [
                'required',
                'string',
                Rule::unique('employees', 'employee_id')->ignore($employee->id),
            ],
            'email' => $emailRules,
            'password' => 'nullable|string|min:8|confirmed',
            'profile_pic' => 'nullable|string',
            'designation' => 'nullable|string',
            'department_id' => 'sometimes|nullable',
            'role_id' => 'nullable|integer',
            'dealership_id' => 'nullable|integer',
            'zone_id' => 'nullable|integer',
            'country' => 'nullable|string',
            'mobile' => 'nullable|string',
            'gender' => 'nullable|string',
            'joining_date' => 'nullable|date',
            'dob' => 'nullable|date',
            'reporting_to' => 'nullable|integer',
            'address' => 'required|string',
            'is_broker' => 'nullable|boolean',
            'status' => 'nullable|boolean',
            'marital_status' => 'nullable|string',
            'emergency_contact' => 'nullable|string',
            'father_name' => 'nullable|string',
            'mother_name' => 'nullable|string',
            'spouse_name' => 'nullable|string',
            'shirt_size' => 'nullable|string',
            'tshirt_size' => 'nullable|string',
            'blood_group' => 'nullable|string',
            'bank_name' => 'nullable|string',
            'account_number' => 'nullable|string',
            'ifsc_code' => 'nullable|string',
            'pf_no' => 'nullable|string',
            'esi_no' => 'nullable|string',
            'lwf_no' => 'nullable|string',
            'aadhar_no' => 'nullable|string',
            'pan_no' => 'nullable|string',
            'branch' => 'nullable|string',
        ]);

        // Normalize department_id
        $validatedData['department_id'] = isset($validatedData['department_id']) && $validatedData['department_id'] !== null
            ? (int) $validatedData['department_id']
            : null;
        if (array_key_exists('status', $validatedData)) {
            $validatedData['status'] = (int) $validatedData['status'];
        }

        // Handle password
        if (!empty($validatedData['password'])) {
            $validatedData['password'] = Hash::make($validatedData['password']);
        } else {
            unset($validatedData['password']);
        }

        // Handle profile picture (base64)
        if ($request->filled('profile_pic') && strpos($request->profile_pic, 'data:image/') === 0) {
            if ($employee->profile_pic && \Illuminate\Support\Facades\File::exists(public_path('storage/' . $employee->profile_pic))) {
                \Illuminate\Support\Facades\File::delete(public_path('storage/' . $employee->profile_pic));
            }

            $imageData = $request->profile_pic;
            $decodedImage = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imageData));
            $imageInfo = getimagesizefromstring($decodedImage);
            $extension = image_type_to_extension($imageInfo[2]);
            $filename = $request->employee_id . $extension;

            $path = public_path('storage/profile_pics');
            if (!\Illuminate\Support\Facades\File::exists($path)) {
                \Illuminate\Support\Facades\File::makeDirectory($path, 0755, true);
            }

            file_put_contents($path . '/' . $filename, $decodedImage);
            $validatedData['profile_pic'] = 'profile_pics/' . $filename;
        } else {
            unset($validatedData['profile_pic']);
        }

        // Update employee
        $employee->update($validatedData);

        // Update linked user (if exists)
        if ($employee->user) {
            $userData = [];

            if (isset($validatedData['name'])) {
                $userData['name'] = $validatedData['name'];
            }
            if (isset($validatedData['email'])) {
                $userData['email'] = $validatedData['email'];
            }
            if (isset($validatedData['password'])) {
                $userData['password'] = $validatedData['password'];
            }
            if ($employee->isDirty('employee_id')) {
                $userData['employee_id'] = $employee->employee_id;
            }
            if (isset($validatedData['profile_pic'])) {
                $userData['profile_pic'] = $validatedData['profile_pic'];
            }
            if (array_key_exists('status', $validatedData)) {
                $userData['status'] = $validatedData['status'];
            }

            $employee->user->update($userData);
        }

        // Log action
        log_action('Employee updated: ' . $employee->name . ' (ID: ' . $employee->id . ')');

        return response()->json(['message' => 'Employee updated successfully.']);
    }

    public function destroy(Employee $employee)
    {
        if (!checkMenu(Session::get('role_id'), 4, 'delete')) {
            if (request()->ajax()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            } else {
                abort(403);
            }
        }

        DB::beginTransaction();
        try {
            if ($employee->profile_pic) {
                Storage::disk('public')->delete($employee->profile_pic);
            }
            $employeeName = $employee->name;
            $employeeId = $employee->id;

            // Delete associated user
            if ($employee->user_id) {
                User::where('id', $employee->user_id)->delete();
            }

            $employee->delete();
            log_action('Employee deleted: ' . $employeeName . ' (ID: ' . $employeeId . ')');
            DB::commit();
            return response()->json(['message' => 'Employee deleted successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error deleting employee: ' . $e->getMessage()], 500);
        }
    }

    public function getBrokers(Request $request)
    {
        $query = Employee::query()->where('is_broker', 1)->with('dealership');

        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if ($user && $user->user_type === 'employee') {
            $user->load('employee');
            if ($user->employee && $user->employee->dealership_id !== null) {
                $query->where('dealership_id', $user->employee->dealership_id);
            }
        }

        // If a dealership_id is explicitly provided in the request, it overrides the user's dealership_id
        if ($request->filled('dealership_id')) {
            $query->where('dealership_id', $request->input('dealership_id'));
        }

        return response()->json($query->get());
    }

    public function getEmployees(Request $request)
    {
        $query = Employee::query();

        if ($request->filled('dealership_id')) {
            $query->where('dealership_id', $request->input('dealership_id'));
        }

        return response()->json($query->get());
    }

    public function getAssignableEmployees(Request $request)
    {
        $query = Employee::query()->with('dealership');

        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        $userIsEmployee = ($user && $user->user_type === 'employee');
        $userDealershipId = null;

        if ($userIsEmployee) {
            $user->load('employee');
            if ($user->employee && $user->employee->dealership_id !== null) {
                $userDealershipId = $user->employee->dealership_id;
            }
        }

        // Prioritize dealership_id from the request (from the lead)
        if ($request->filled('dealership_id')) {
            $query->where('dealership_id', $request->input('dealership_id'));
        } elseif ($userIsEmployee && $userDealershipId !== null) {
            // If no dealership_id from request, but user is an employee with a dealership, filter by user's dealership
            $query->where('dealership_id', $userDealershipId);
        } else if ($userIsEmployee && $userDealershipId === null) {
            // If an employee has no dealership, they should see all employees (no dealership filter applied)
            // No additional where clause needed here
        } else {
            // If the user is not an employee (e.g., admin), they should see all employees
            // No additional where clause needed here
        }

        $employees = $query->get();

        if ($employees->isEmpty()) {
            // If the user is an employee with a dealership and no employees are found for that dealership
            if ($userIsEmployee && $userDealershipId !== null) {
                return response()->json(['data' => [], 'message' => 'No assignable employees found for your dealership.']);
            } else if ($userIsEmployee && $userDealershipId === null) {
                // If an employee has no dealership and no employees are found (e.g., no employees in system)
                return response()->json(['data' => [], 'message' => 'No assignable employees found.']);
            }
            // For non-employees or other cases where no employees are found
            return response()->json(['data' => [], 'message' => 'No assignable employees found.']);
        }

        return response()->json(['data' => $employees, 'message' => 'Assignable employees retrieved successfully.']);
    }

    public function storeBroker(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:employees,name',
            'employee_id' => 'nullable|string|unique:employees,employee_id',
        ]);

        $employeeId = $request->employee_id ?? 'EMP-' . uniqid();

        $employee = Employee::create([
            'name' => $request->name,
            'employee_id' => $employeeId,
            'is_broker' => 1,
            'email' => $request->name . '@example.com',
            'password' => bcrypt('password'),
            'role_id' => 1,
        ]);

        $user = User::create([
            'name' => $employee->name,
            'email' => $employee->email,
            'password' => $employee->password,
            'user_type' => 'employee',
            'employee_id' => $employee->employee_id,
        ]);

        $employee->user_id = $user->id;
        $employee->save();

        log_action('Broker created: ' . $employee->name . ' (ID: ' . $employee->id . ')');
        return response()->json(['message' => 'Broker created successfully.', 'employee' => $employee]);
    }





    /**
     * Export employees to Excel.
     */
    public function exportExcel()
    {
        return Excel::download(new EmployeesExport, 'employees.xlsx');
    }

    /**
     * Export single employee to PDF.
     */
    public function exportPdf(Employee $employee)
    {
        $employee->load(['department', 'role', 'dealership', 'zone', 'reporter', 'reporter2']);
        $pdf = app('dompdf.wrapper');
        $pdf->loadView('employees.pdf', compact('employee'));
        return $pdf->download('employee-details-' . $employee->id . '.pdf');
    }

    public function search(Request $request)
    {
        $query = Employee::with(['department']);

        // Filter by reporting_to if the authenticated user is an employee
        /** @var \App\Models\User|null $loggedInUser */
        $loggedInUser = Auth::user();
        if ($loggedInUser && $loggedInUser->user_type === 'employee') {
            $loggedInUser->load('employee.subordinates');
            if ($loggedInUser->employee && $loggedInUser->employee->subordinates->isNotEmpty()) {
                $subordinateIds = $loggedInUser->employee->subordinates->pluck('id')->toArray();
                $query->whereIn('id', $subordinateIds);
            } else {
                // If an employee has no subordinates, they shouldn't see any employees in the filter
                $query->whereRaw('1 = 0'); // Return empty result
            }
        } else if ($loggedInUser && $loggedInUser->user_type === 'admin') {
        }

        // Apply search term
        if ($request->has('q') && $request->q !== '') {
            $searchTerm = $request->q;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('email', 'like', '%' . $searchTerm . '%')
                    ->orWhere('employee_id', 'like', '%' . $searchTerm . '%');
            });
        }


        $employees = $query->paginate(10);

        $data = collect($employees->items())->map(function ($employee) {
            return [
                'id' => $employee->user_id,
                'text' => $employee->name,
                'department_name' => $employee->department ? $employee->department->name : 'N/A',
                'profile_pic' => $employee->profile_pic ? \Illuminate\Support\Facades\Storage::url($employee->profile_pic) : null,
            ];
        });

        return response()->json([
            'data' => $data,
            'total' => $employees->total(),
        ]);
    }

    public function searchEmployee(Request $request)
    {
        $query = Employee::with(['department', 'dealership']);
        /** @var \App\Models\User|null $loggedInUser */
        $loggedInUser = Auth::user();
        if ($loggedInUser && $loggedInUser->user_type === 'employee') {
            $loggedInUser->load('employee');
            $dealershipId = $loggedInUser->employee ? $loggedInUser->employee->dealership_id : null;
            if ($dealershipId) {
                $query->where('dealership_id', $dealershipId);
            }
        }
        // Apply search term
        if ($request->has('q') && $request->q !== '') {
            $searchTerm = $request->q;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('email', 'like', '%' . $searchTerm . '%')
                    ->orWhere('employee_id', 'like', '%' . $searchTerm . '%');
            });
        }
        $employees = $query->paginate(10);
        $data = collect($employees->items())->map(function ($employee) {
            return [
                'id' => $employee->employee_id,
                'internal_id' => $employee->id,
                'user_id' => $employee->user_id,
                'text' => $employee->name . ' (' . $employee->employee_id . ')',
                'name' => $employee->name,
                'dob' => $employee->dob,
                'department' => $employee->department,
                'branch' => $employee->branch,
                'dealership' => $employee->dealership,
                'designation' => $employee->designation,
                'joining_date' => $employee->joining_date,

            ];
        });
        return response()->json([
            'results' => $data,
            'pagination' => [
                'more' => $employees->hasMorePages()
            ]
        ]);
    }

    public function getByCode($employee_code)
    {
        $employee = Employee::where('employee_id', $employee_code)->with('department')->first();

        if (!$employee) {
            return response()->json(['error' => 'Employee not found.'], 404);
        }

        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if ($user && $user->user_type === 'employee') {
            $user->load('employee');
            if ($user->employee && $user->employee->dealership_id !== null) {
                if ($employee->dealership_id !== $user->employee->dealership_id) {
                    return response()->json(['error' => 'You are not authorized to view this employee.'], 403);
                }
            }
        }

        return response()->json($employee);
    }

    public function getDepartmentManagers()
    {
        $departmentManagers = [];

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

        foreach ($departmentRoleMap as $department => $roleNames) {

            $managers = Employee::query()
                ->where(function ($query) use ($roleNames) {

                    foreach ($roleNames as $roleName) {

                        // Normalize the search term once
                        $normalizedRole = strtolower(str_replace(' ', '_', $roleName));

                        $query->orWhere(function ($subQuery) use ($normalizedRole, $roleName) {

                            // Match designation (normalize DB value)
                            $subQuery->whereRaw(
                                "LOWER(REPLACE(designation, ' ', '_')) LIKE ?",
                                ["%{$normalizedRole}%"]
                            )

                                // OR match roles table
                                ->orWhereHas('role', function ($q) use ($normalizedRole) {
                                    $q->whereRaw(
                                        "LOWER(REPLACE(role, ' ', '_')) LIKE ?",
                                        ["%{$normalizedRole}%"]
                                    );
                                });
                        });
                    }
                })
                ->get();

            $departmentManagers[$department] = $managers->map(function ($manager) {
                return [
                    'name' => $manager->name,
                    'designation' => $manager->designation,
                    'id' => $manager->id,
                ];
            })->toArray();
        }

        return response()->json($departmentManagers);
    }
}
