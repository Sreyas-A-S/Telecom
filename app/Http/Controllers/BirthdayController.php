<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Setting;
use App\Models\Notification;

use App\Exports\BirthdaysExport;
use Maatwebsite\Excel\Facades\Excel;

class BirthdayController extends Controller
{
    public function exportExcel(Request $request)
    {
        $filters = $request->only(['from_date', 'to_date']);
        return Excel::download(new BirthdaysExport($filters), 'birthdays_' . date('Y_m_d_H_i_s') . '.xlsx');
    }

    public function index()
    {
        $employees = Employee::whereNotNull('dob')->get();

        $events = [];
        $upcomingBirthdays = [];
        $today = Carbon::today();
        $currentYear = $today->year;

        foreach ($employees as $employee) {
            if (!$employee->dob) continue;

            try {
                $dob = Carbon::parse($employee->dob);

                // For Calendar: Create an event for the current year
                // We'll also add one for next year to cover year-end rollovers if needed, 
                // but for a simple view, current year is the main focus.
                // Actually, let's just pass the MD (Month-Day) and let JS handle the year assignment 
                // or just pass a recurring event if using FullCalendar resource fields.
                // Standard approach: Pass strict dates for the view.

                $birthdayThisYear = Carbon::create($currentYear, $dob->month, $dob->day);

                $events[] = [
                    'title' => $employee->name,
                    'start' => $birthdayThisYear->format('Y-m-d'),
                    'allDay' => true,
                    'className' => 'bg-primary',
                    'extendedProps' => [
                        'profile_pic' => $employee->profile_pic ? asset('storage/' . $employee->profile_pic) : asset('admin/assets/images/dashboard/profile.png'),
                        'designation' => $employee->designation ?? 'Employee',
                        'department' => $employee->department ? $employee->department->name : 'N/A',
                        'dob' => $employee->dob
                    ]
                ];

                // Check for upcoming birthdays (e.g., next 30 days)
                // We need to handle year wrap-around for upcoming check
                $birthdayNext = $birthdayThisYear->copy();
                if ($birthdayNext->isPast() && !$birthdayNext->isToday()) {
                    $birthdayNext->addYear();
                }

                if ($birthdayNext->diffInDays($today) <= 30 && $birthdayNext->gte($today)) {
                    $upcomingBirthdays[] = [
                        'name' => $employee->name,
                        'date' => $birthdayNext,
                        'profile_pic' => $employee->profile_pic,
                        'designation' => $employee->designation,
                        'department' => $employee->department ? $employee->department->name : '',
                        'age' => $dob->age + 1 // Turning age
                    ];
                }
            } catch (\Exception $e) {
                // Handle invalid dates if any
                continue;
            }
        }

        // Sort upcoming birthdays
        usort($upcomingBirthdays, function ($a, $b) {
            return $a['date'] <=> $b['date'];
        });

        return view('hr.birthdays.index', compact('events', 'upcomingBirthdays'));
    }

    public function settings()
    {
        $settings = [
            'birthday_notification_enabled' => Setting::where('key', 'birthday_notification_enabled')->value('value') ?? '1',
            'birthday_notification_time' => Setting::where('key', 'birthday_notification_time')->value('value') ?? '09:00',
            'birthday_notification_message' => Setting::where('key', 'birthday_notification_message')->value('value') ?? 'Happy Birthday, {name}! Wishing you a wonderful year ahead.',
            'birthday_hr_notification_message' => Setting::where('key', 'birthday_hr_notification_message')->value('value') ?? 'Today is {name}\'s birthday (Turning {age}).',
        ];

        return view('hr.birthdays.settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'birthday_notification_time' => 'required|date_format:H:i',
            'birthday_notification_message' => 'required|string',
            'birthday_hr_notification_message' => 'required|string',
        ]);

        Setting::updateOrCreate(['key' => 'birthday_notification_enabled'], ['value' => $request->has('birthday_notification_enabled') ? '1' : '0']);
        Setting::updateOrCreate(['key' => 'birthday_notification_time'], ['value' => $request->birthday_notification_time]);
        Setting::updateOrCreate(['key' => 'birthday_notification_message'], ['value' => $request->birthday_notification_message]);
        Setting::updateOrCreate(['key' => 'birthday_hr_notification_message'], ['value' => $request->birthday_hr_notification_message]);

        return redirect()->route('birthdays.settings')->with('success', 'Settings updated successfully.');
    }

    public function logs()
    {
        $logs = Notification::whereIn('data->type', ['birthday_self', 'birthday_hr'])
            ->with(['user.employee'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Fetch related employees efficiently
        $employeeIds = $logs->pluck('data.employee_id')->filter()->unique();
        $employees = Employee::whereIn('id', $employeeIds)->get()->keyBy('id');

        return view('hr.birthdays.logs', compact('logs', 'employees'));
    }
}
