<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Str;

class SendPerformanceReviewNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'performance:send-review-notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send OneSignal notifications for employee work anniversaries to HR, Reporting Person, and Business Head';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();
        $this->info("Checking for performance review reminders on " . $today->toDateString());

        // Get all employees with a joining date
        $employees = Employee::whereNotNull('joining_date')->with(['user', 'reporter.user'])->get();

        foreach ($employees as $employee) {
            try {
                $joiningDate = Carbon::parse($employee->joining_date);
                
                // Determine the next anniversary date
                $nextAnniversary = $joiningDate->copy()->year($today->year);
                if ($nextAnniversary->isPast() && !$nextAnniversary->isToday()) {
                    $nextAnniversary->addYear();
                }

                $isFirstOfMonth = $today->day === 1 && $nextAnniversary->month === $today->month && $nextAnniversary->year === $today->year;
                $isThreeDaysBefore = $today->diffInDays($nextAnniversary, false) === 3;
                $isSameDay = $today->isSameDay($nextAnniversary);

                if ($isFirstOfMonth || $isThreeDaysBefore || $isSameDay) {
                    $years = $nextAnniversary->year - $joiningDate->year;
                    
                    $type = "";
                    if ($isFirstOfMonth) $type = "Monthly Reminder";
                    if ($isThreeDaysBefore) $type = "3-Day Reminder";
                    if ($isSameDay) $type = "Due Today";

                    $this->info("Sending {$type} for {$employee->name} (Anniversary: {$nextAnniversary->toDateString()})");
                    $this->sendNotifications($employee, $years, $type);
                }
            } catch (\Exception $e) {
                Log::error("Error processing employee {$employee->id} for performance review notification: " . $e->getMessage());
                $this->error("Error processing employee {$employee->id}: " . $e->getMessage());
            }
        }

        $this->info('Performance review notifications check completed.');
    }

    private function sendNotifications(Employee $employee, int $years, string $reminderType)
    {
        $recipients = collect();

        // 1. Employee themselves
        if ($employee->user) {
            $recipients->push($employee->user);
        }

        // 2. Reporting Person (Manager)
        if ($employee->reporter && $employee->reporter->user) {
            $recipients->push($employee->reporter->user);
        }

        // 3. HR Manager(s)
        $hrDepartments = \App\Models\Department::whereIn('name', ['Human Resources', 'HR'])->pluck('id');
        $hrEmployees = Employee::with('user')->whereIn('department_id', $hrDepartments)->get();
        foreach ($hrEmployees as $hr) {
            if ($hr->user) {
                $recipients->push($hr->user);
            }
        }

        // 4. Business Head
        $businessHeads = Employee::with('user')
            ->where(function ($query) {
                $query->whereRaw('LOWER(designation) = ?', ['business head'])
                    ->orWhereHas('role', function ($q) {
                        $q->whereRaw('LOWER(role) = ?', ['business head']);
                    });
            })->get();

        foreach ($businessHeads as $bh) {
            if ($bh->user) {
                $recipients->push($bh->user);
            }
        }

        // Remove duplicates and filter users with player_id (or just all for in-app logs)
        $uniqueRecipients = $recipients->unique('id');

        if ($uniqueRecipients->isEmpty()) {
            $this->info("No valid recipients found for {$employee->name}.");
            return;
        }

        $title = "Performance Review: {$reminderType}";
        $message = "{$employee->name} is completing {$years} years of service. Performance review is due on " . Carbon::parse($employee->joining_date)->year(Carbon::today()->year + (Carbon::parse($employee->joining_date)->month < Carbon::today()->month ? 1 : 0))->format('d M, Y') . ".";
        
        // Refined message based on type
        if ($reminderType === "Due Today") {
            $message = "Today is {$employee->name}'s work anniversary ({$years} years). Please complete the performance review.";
        }

        foreach ($uniqueRecipients as $recipient) {
            $this->sendOneSignalNotification($recipient, $title, $message, $employee);
        }
    }

    private function sendOneSignalNotification(User $recipient, string $title, string $message, Employee $subjectEmployee)
    {
        try {
            do {
                $notificationId = (string) Str::uuid();
            } while (Notification::where('notification_id', $notificationId)->exists());

            $payloadData = [
                'employee_id' => $subjectEmployee->user_id, // or employee->id depending on what the app expects
                'route' => 'PerformanceReviewView', // Assuming this is the correct route/screen
                'type' => 'performance_review_due',
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

            // Log the notification in the database
            Notification::create([
                'notification_id' => $notificationId,
                'user_id' => $recipient->id,
                'title' => $title,
                'message' => $message,
                'data' => $payloadData,
            ]);

            Log::info("OneSignal notification sent to {$recipient->email} for {$subjectEmployee->name}'s anniversary.", [
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
