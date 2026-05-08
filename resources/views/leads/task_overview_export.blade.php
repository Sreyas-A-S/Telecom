<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body>
<table>
    <thead>
        <tr>
            <th colspan="4" style="font-weight: bold; font-size: 14pt;">Task Overview: {{ $task->title }}</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="font-weight: bold;">Task ID:</td>
            <td colspan="3">#{{ $task->id }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Type:</td>
            <td colspan="3">{{ ucfirst($task->type) }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Assigned To:</td>
            <td colspan="3">{{ $task->assignedEmployee->name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Status:</td>
            <td colspan="3">{{ ucfirst($task->status) }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Due Date:</td>
            <td colspan="3">{{ $task->due_date ? $task->due_date->format('d M Y') : 'N/A' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Total Time Spent:</td>
            <td colspan="3">{{ $totalTime }}</td>
        </tr>

        <tr><td colspan="4"></td></tr>

        <tr>
            <th colspan="4" style="font-weight: bold; font-size: 12pt; background-color: #f2f2f2;">Task Logs</th>
        </tr>
        <tr>
            <th style="font-weight: bold; background-color: #e9ecef;">Date</th>
            <th style="font-weight: bold; background-color: #e9ecef;">Employee</th>
            <th style="font-weight: bold; background-color: #e9ecef;">Action</th>
            <th style="font-weight: bold; background-color: #e9ecef;">Time</th>
        </tr>
        @foreach($taskLogs as $log)
        <tr>
            <td>{{ $log->created_at->format('d M Y H:i') }}</td>
            <td>{{ $log->employee->name ?? 'N/A' }}</td>
            <td>{{ ucfirst($log->action_type) }}</td>
            <td>{{ $log->action_time }}</td>
        </tr>
        @endforeach

        <tr><td colspan="4"></td></tr>

        <tr>
            <th colspan="4" style="font-weight: bold; font-size: 12pt; background-color: #f2f2f2;">Follow-ups</th>
        </tr>
        <tr>
            <th style="font-weight: bold; background-color: #e9ecef;">Date</th>
            <th style="font-weight: bold; background-color: #e9ecef;">User</th>
            <th colspan="2" style="font-weight: bold; background-color: #e9ecef;">Notes</th>
        </tr>
        @foreach($task->followups as $followup)
        <tr>
            <td>{{ $followup->created_at->format('d M Y H:i') }}</td>
            <td>{{ $followup->user->name ?? 'N/A' }}</td>
            <td colspan="2">{{ $followup->notes }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
</body>
</html>
