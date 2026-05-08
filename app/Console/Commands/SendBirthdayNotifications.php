<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;
use App\Models\User;
use App\Models\Notification;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Str;

class SendBirthdayNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'birthday:send-notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send OneSignal notifications for employee birthdays to HR and the Employee';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $enabled = Setting::where('key', 'birthday_notification_enabled')->value('value') ?? '1';
        if ($enabled !== '1') {
            return;
        }

        $notificationTime = Setting::where('key', 'birthday_notification_time')->value('value') ?? '09:00';

        // Check if current time matches the scheduled time
        if (Carbon::now()->format('H:i') !== $notificationTime) {
            return;
        }

        $today = Carbon::today();
        $this->info("Checking for birthdays on " . $today->toDateString());

        // Get all employees with a date of birth
        $employees = Employee::whereNotNull('dob')->with(['user'])->get();

        foreach ($employees as $employee) {
            try {
                $dob = Carbon::parse($employee->dob);

                // Check if today is the birthday (ignoring year)
                if ($dob->month === $today->month && $dob->day === $today->day) {
                    $age = $today->year - $dob->year;
                    $this->info("It is {$employee->name}'s birthday! Turning {$age}.");

                    $this->sendNotifications($employee, $age);
                }
            } catch (\Exception $e) {
                Log::error("Error processing employee {$employee->id} for birthday notification: " . $e->getMessage());
                $this->error("Error processing employee {$employee->id}: " . $e->getMessage());
            }
        }

        $this->info('Birthday notifications check completed.');
    }

    private function sendNotifications(Employee $birthdayEmployee, int $age)
    {
        $messageTemplate = Setting::where('key', 'birthday_notification_message')->value('value') ?? 'Happy Birthday, {name}! Wishing you a fantastic birthday and a wonderful year ahead!';
        $hrMessageTemplate = Setting::where('key', 'birthday_hr_notification_message')->value('value') ?? 'Today is {name}\'s birthday (Turning {age}).';

        // 1. Send to the Birthday Employee
        if ($birthdayEmployee->user && !empty($birthdayEmployee->user->player_id)) {
            // Check if already sent today
            $alreadySent = Notification::where('data->employee_id', $birthdayEmployee->id)
                ->where('data->type', 'birthday_self')
                ->whereDate('created_at', Carbon::today())
                ->exists();

            if (!$alreadySent) {
                $title = "Happy Birthday, " . $birthdayEmployee->name . "!";
                $message = str_replace(['{name}', '{age}'], [$birthdayEmployee->name, $age], $messageTemplate);

                $this->sendOneSignalNotification(
                    $birthdayEmployee->user,
                    $title,
                    $message,
                    $birthdayEmployee,
                    'birthday_self'
                );
            }
        }

        // 2. Send to HR Manager(s)
        $hrDepartments = \App\Models\Department::whereIn('name', ['Human Resources', 'HR'])->pluck('id');
        $hrEmployees = Employee::with('user')->whereIn('department_id', $hrDepartments)->get();

        $hrRecipients = collect();
        foreach ($hrEmployees as $hr) {
            if ($hr->user && !empty($hr->user->player_id)) {
                $hrRecipients->push($hr->user);
            }
        }

        // Remove duplicates (just in case)
        $hrRecipients = $hrRecipients->unique('id');

        if ($hrRecipients->isNotEmpty()) {
            $hrTitle = "Birthday Alert: " . $birthdayEmployee->name;
            $hrMessage = str_replace(['{name}', '{age}'], [$birthdayEmployee->name, $age], $hrMessageTemplate);

            foreach ($hrRecipients as $recipient) {
                // Check if already sent today for this recipient
                $alreadySentHr = Notification::where('user_id', $recipient->id)
                    ->where('data->employee_id', $birthdayEmployee->id)
                    ->where('data->type', 'birthday_hr')
                    ->whereDate('created_at', Carbon::today())
                    ->exists();

                if (!$alreadySentHr) {
                    $this->sendOneSignalNotification(
                        $recipient,
                        $hrTitle,
                        $hrMessage,
                        $birthdayEmployee,
                        'birthday_hr'
                    );
                }
            }
        }
    }

    private function sendOneSignalNotification(User $recipient, string $title, string $message, Employee $subjectEmployee, string $type)
    {
        try {
            do {
                $notificationId = (string) Str::uuid();
            } while (Notification::where('notification_id', $notificationId)->exists());

            $payloadData = [
                'employee_id' => $subjectEmployee->id,
                'type' => $type,
                'notification_id' => $notificationId,
                'route' => 'Birthdays', // Assuming a route to the birthday calendar exists or similar
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
                // Optional: Add small icon or other UI customization here matching project style
            ]);

            $status = $response->successful() ? 'sent' : 'failed';
            $payloadData['status'] = $status;
            $payloadData['onesignal_response'] = $response->json();

            // Log the notification in the database
            Notification::create([
                'notification_id' => $notificationId,
                'user_id' => $recipient->id,
                'title' => $title,
                'message' => $message,
                'data' => $payloadData,
            ]);

            Log::info("OneSignal {$type} notification sent to {$recipient->email}.", [
                'recipient_id' => $recipient->id,
                'response' => $response->json(),
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send OneSignal {$type} notification to {$recipient->email}.", [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
