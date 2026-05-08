<?php

namespace App\Imports;

use App\Models\Dealership;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Role;
use App\Models\Zone;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash; // Added for Hash::make
use Illuminate\Support\Facades\Log; // Added for logging
use PhpOffice\PhpSpreadsheet\Shared\Date;


class EmployeesImport implements ToModel, WithChunkReading, WithHeadingRow, WithEvents, WithCalculatedFormulas
{
    use RegistersEventListeners;

    private $import_id;
    private $totalRows;
    private $processedRows = 0;
    private $headerRow; // To store the header row if needed for validation or mapping

    public function __construct($import_id)
    {
        $this->import_id = $import_id;
        // Initialize progress in cache
        Cache::put('import_progress:' . $this->import_id, [
            'total_rows' => 0,
            'processed_rows' => 0,
            'percentage' => 0,
            'status' => 'pending',
            'results' => []
        ], now()->addHours(2)); // Cache for 2 hours
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function headingRow(): int
    {
        return 1; // Assuming the first row is the header
    }

    public static function beforeImport(BeforeImport $event)
    {
        // This event is fired before the import starts
        // We can't get total rows here reliably with chunk reading
    }



    public static function afterImport(AfterImport $event)
    {
        $import = $event->getConcernable(); // Get the EmployeesImport instance
        $progress = Cache::get('import_progress:' . $import->import_id);
        if ($progress) {
            $progress['status'] = 'completed';
            $progress['percentage'] = 100;
            Cache::put('import_progress:' . $import->import_id, $progress, now()->addHours(2));
        }
    }

    public function afterSheet(AfterSheet $event)
    {
        $this->totalRows = $event->getDelegate()->getHighestRow() - 1; // Subtract header row
        $progress = Cache::get('import_progress:' . $this->import_id);
        if ($progress) {
            $progress['total_rows'] = $this->totalRows;
            Cache::put('import_progress:' . $this->import_id, $progress, now()->addHours(2));
        }
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $this->processedRows++;
        $progress = Cache::get('import_progress:' . $this->import_id);

        try {
            if (isset($row['no']) && strtolower($row['no']) === 'no') {
                return null;
            }

            $name = trim($row['employee_name'] ?? '');
            $email = trim($row['email_id'] ?? $row['email'] ?? '');
            $employee_id_input = trim($row['employee_id'] ?? '');

            if (empty($name) || empty($email)) {
                $progress['results'][] = [
                    'row_number' => $this->processedRows,
                    'status' => 'skipped',
                    'reason' => 'Missing name or email.',
                ];
                Cache::put('import_progress:' . $this->import_id, $progress, now()->addHours(2));
                return null;
            }

            // Validate email format
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $progress['results'][] = [
                    'row_number' => $this->processedRows,
                    'status' => 'failed',
                    'reason' => "Invalid email format: '$email'",
                ];
                Cache::put('import_progress:' . $this->import_id, $progress, now()->addHours(2));
                return null;
            }

            $mobile = trim($row['phone_no'] ?? '');
            $gender = trim($row['gender'] ?? '');
            if (strtolower($gender) === 'm') {
                $gender = 'Male';
            } elseif (strtolower($gender) === 'f') {
                $gender = 'Female';
            } else {
                $gender = 'Other';
            }
            $dob = trim($row['date_of_birth'] ?? '');
            $joining_date = trim($row['date_of_joining'] ?? '');
            $address = trim($row['address'] ?? '');
            $designation = trim($row['designation'] ?? '');
            $department_name = trim($row['department'] ?? '');
            // $role_name = trim($row['role'] ?? ''); // Removed as per template update
            $dealership_name = trim($row['dealership'] ?? '');
            $zone_name = trim($row['zone'] ?? '');
            $country = trim($row['country'] ?? '');
            $is_broker = filter_var($row['is_agent'] ?? $row['is_broker'] ?? false, FILTER_VALIDATE_BOOLEAN);

            $department = Department::firstOrCreate(['name' => $department_name]);

            // Priority to designation for role creation as per request
            $role_to_use = $designation;

            $role = !empty($role_to_use) ? Role::firstOrCreate(
                ['role' => $role_to_use],
                ['is_active' => true]
            ) : null;

            $dealership = !empty($dealership_name) ? Dealership::where(DB::raw('lower(name)'), strtolower($dealership_name))->first() : null;
            $zone = !empty($zone_name) ? Zone::where(DB::raw('lower(name)'), strtolower($zone_name))->first() : null;

            // Handle Reporting Authority
            $reporting_email = trim($row['reporting_authority_email'] ?? '');
            $reporting_to_id = null;
            if (!empty($reporting_email)) {
                // Find employee with this email
                $manager = Employee::where('email', $reporting_email)->first();
                if ($manager) {
                    $reporting_to_id = $manager->id;
                }
            }

            $dobParsed = null;
            if (!empty($dob)) {
                // Check for common Excel error strings
                if (in_array(strtoupper($dob), ['#REF!', '#NAME?', '#VALUE!', '#DIV/0!', '#N/A', '#NULL!', '#NUM!'])) {
                    // Treat as invalid date, will be null
                } elseif (is_numeric($dob)) {
                    $dobParsed = Carbon::instance(Date::excelToDateTimeObject($dob))->format('Y-m-d');
                } else {
                    try {
                        $dobParsed = Carbon::parse($dob)->format('Y-m-d');
                    } catch (\Exception $e) {
                        // Log or handle parsing error, dobParsed remains null
                    }
                }
            }

            $joiningDateParsed = null;
            if (!empty($joining_date)) {
                // Check for common Excel error strings
                if (in_array(strtoupper($joining_date), ['#REF!', '#NAME?', '#VALUE!', '#DIV/0!', '#N/A', '#NULL!', '#NUM!'])) {
                    // Treat as invalid date, will be null
                } elseif (is_numeric($joining_date)) {
                    $joiningDateParsed = Carbon::instance(Date::excelToDateTimeObject($joining_date))->format('Y-m-d');
                } else {
                    try {
                        $joiningDateParsed = Carbon::parse($joining_date)->format('Y-m-d');
                    } catch (\Exception $e) {
                        // Log or handle parsing error, joiningDateParsed remains null
                    }
                }
            }

            $employee_id = !empty($employee_id_input) ? $employee_id_input : uniqid();

            // Double check uniqueness if provided
            if (!empty($employee_id_input)) {
                $exists = Employee::where('employee_id', $employee_id)->exists() || User::where('employee_id', $employee_id)->exists();
                if ($exists) {
                    $employee_id = $employee_id_input . '_' . uniqid();
                }
            }

            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make('password'),
                'user_type' => 'employee',
                'employee_id' => $employee_id,
            ]);

            $employee = new Employee([
                'name' => $name,
                'email' => $email,
                'mobile' => $mobile,
                'gender' => $gender,
                'dob' => $dobParsed,
                'joining_date' => $joiningDateParsed,
                'address' => $address,
                'designation' => $designation,
                'department_id' => $department ? $department->id : null,
                'role_id' => $role ? $role->id : null,
                'dealership_id' => $dealership ? $dealership->id : null,
                'zone_id' => $zone ? $zone->id : null,
                'country' => $country,
                'is_broker' => $is_broker,
                'reporting_to' => $reporting_to_id,
                'password' => Hash::make('password'),
                'employee_id' => $employee_id,
                'import_id' => $this->import_id,
                'user_id' => $user->id,
                // Assign new fields
                'marital_status' => trim($row['marital_status'] ?? ''),
                'father_name' => trim($row['fathers_name'] ?? ''),
                'mother_name' => trim($row['mothers_name'] ?? ''),
                'spouse_name' => trim($row['spouses_name'] ?? ''),
                'shirt_size' => trim($row['shirt_size'] ?? ''),
                'tshirt_size' => trim($row['t_shirt_size'] ?? ''),
                'blood_group' => trim($row['blood_group'] ?? ''),
                'bank_name' => trim($row['bank_name'] ?? ''),
                'account_number' => trim($row['account_number'] ?? ''),
                'ifsc_code' => trim($row['ifsc_code'] ?? ''),
                'pf_no' => trim($row['pf_no'] ?? ''),
                'esi_no' => trim($row['esi_no'] ?? ''),
                'lwf_no' => trim($row['lwf_no'] ?? ''),
                'aadhar_no' => trim($row['aadhar_no'] ?? ''),
                'pan_no' => trim($row['pan_no'] ?? ''),
                'branch' => trim($row['branch'] ?? ''),
                'emergency_contact' => trim($row['emergency_contact'] ?? ''),
            ]);

            $progress['results'][] = [
                'row_number' => $this->processedRows,
                'status' => 'success',
                'reason' => 'Successfully imported.',
            ];

            return $employee;
        } catch (\Exception $e) {
            Log::error('Error processing row ' . $this->processedRows . ' for import ID ' . $this->import_id . ': ' . $e->getMessage());
            $progress['results'][] = [
                'row_number' => $this->processedRows,
                'status' => 'failed',
                'reason' => $e->getMessage(),
            ];
            return null;
        } finally {
            if ($progress && $this->totalRows > 0) {
                $progress['processed_rows'] = $this->processedRows;
                $progress['percentage'] = min(100, round(($this->processedRows / $this->totalRows) * 100));
            }
            Cache::put('import_progress:' . $this->import_id, $progress, now()->addHours(2));
        }
    }
}
