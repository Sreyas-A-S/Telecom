<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body>
<table>
    <thead>
        <tr>
            <th colspan="7" style="font-weight: bold; font-size: 14pt;">Client Profile</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="font-weight: bold;">Salutation:</td>
            <td colspan="6">{{ $client->salutation }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Name:</td>
            <td colspan="6">{{ $client->name }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Phone:</td>
            <td colspan="6">{{ $client->phone_number }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Email:</td>
            <td colspan="6">{{ $client->email ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Address:</td>
            <td colspan="6">
                {{ $client->address ?? '' }}
                @if($client->district || $client->state)
                    ({{ $client->district->name ?? '' }}{{ $client->district && $client->state ? ', ' : '' }}{{ $client->state->name ?? '' }})
                @endif
                @if(!$client->address && !$client->district && !$client->state)
                    N/A
                @endif
            </td>
        </tr>

        <tr><td colspan="7"></td></tr>

        <tr>
            <th colspan="7" style="font-weight: bold; font-size: 12pt; background-color: #f2f2f2;">Summary Statistics</th>
        </tr>
        <tr>
            <td style="font-weight: bold;">Total Leads:</td>
            <td colspan="6">{{ $client->leads->count() }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Total Services:</td>
            <td colspan="6">{{ $services->count() }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Total Products:</td>
            <td colspan="6">{{ $uniqueProducts->count() }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Total Interactions:</td>
            <td colspan="6">{{ $totalInteractions }}</td>
        </tr>

        <tr><td colspan="7"></td></tr>

        <tr>
            <th colspan="7" style="font-weight: bold; font-size: 12pt; background-color: #f2f2f2;">Leads History &amp; Interactions</th>
        </tr>

        @foreach($client->leads as $index => $lead)
            <tr>
                <th style="font-weight: bold; background-color: #d9e1f2;">Sl No</th>
                <th style="font-weight: bold; background-color: #d9e1f2;">Lead ID</th>
                <th style="font-weight: bold; background-color: #d9e1f2;">Date</th>
                <th style="font-weight: bold; background-color: #d9e1f2;">Product/Model</th>
                <th style="font-weight: bold; background-color: #d9e1f2;">Status</th>
                <th style="font-weight: bold; background-color: #d9e1f2;">Value</th>
                <th style="font-weight: bold; background-color: #d9e1f2;">Agent</th>
            </tr>
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>#{{ $lead->id }}</td>
                <td>{{ $lead->created_at->format('d M Y') }}</td>
                <td>{{ $lead->product->name ?? 'N/A' }} {{ $lead->productModel ? '-' . $lead->productModel->name : '' }}</td>
                <td>{{ str_replace('_', ' ', $lead->status) }}</td>
                <td>INR {{ number_format($lead->lead_value, 2) }}</td>
                <td>{{ $lead->agent->name ?? 'N/A' }}</td>
            </tr>

            @if($lead->followups->count() > 0)
                <tr>
                    <td colspan="7" style="font-weight: bold; font-style: italic; background-color: #f8f9fa;">-- Follow-up History --</td>
                </tr>
                <tr>
                    <th colspan="2" style="font-weight: bold; background-color: #e9ecef;">Date</th>
                    <th style="font-weight: bold; background-color: #e9ecef;">Status</th>
                    <th colspan="3" style="font-weight: bold; background-color: #e9ecef;">Remarks</th>
                    <th style="font-weight: bold; background-color: #e9ecef;">By</th>
                </tr>
                @foreach($lead->followups as $followup)
                <tr>
                    <td colspan="2">{{ $followup->created_at->format('d M Y H:i') }}</td>
                    <td>{{ str_replace('_', ' ', $followup->new_status) }}</td>
                    <td colspan="3">{{ $followup->remarks ?? 'No remarks' }}</td>
                    <td>{{ $followup->user->name ?? 'N/A' }}</td>
                </tr>
                @endforeach
            @endif

            @if($lead->tasks->count() > 0)
                <tr>
                    <td colspan="7" style="font-weight: bold; font-style: italic; background-color: #f8f9fa;">-- Associated Tasks --</td>
                </tr>
                <tr>
                    <th colspan="2" style="font-weight: bold; background-color: #fff3cd;">Task Title</th>
                    <th style="font-weight: bold; background-color: #fff3cd;">Type</th>
                    <th style="font-weight: bold; background-color: #fff3cd;">Assigned To</th>
                    <th style="font-weight: bold; background-color: #fff3cd;">Status</th>
                    <th style="font-weight: bold; background-color: #fff3cd;">Due Date</th>
                    <th style="font-weight: bold; background-color: #fff3cd;">Time Spent</th>
                </tr>
                @foreach($lead->tasks as $task)
                <tr>
                    <td colspan="2">{{ $task->title }}</td>
                    <td>{{ ucfirst($task->type) }}</td>
                    <td>{{ $task->assignedEmployee->name ?? 'N/A' }}</td>
                    <td>{{ ucfirst($task->status) }}</td>
                    <td>{{ $task->due_date ? $task->due_date->format('d M Y') : 'N/A' }}</td>
                    <td>{{ $task->getFormattedElapsedTime() }}</td>
                </tr>
                @endforeach
            @endif
            <tr><td colspan="7" style="height: 10px;"></td></tr>
        @endforeach

        <tr><td colspan="7"></td></tr>

        <tr>
            <th colspan="7" style="font-weight: bold; font-size: 12pt; background-color: #f2f2f2;">Services History &amp; Tasks</th>
        </tr>

        @foreach($services as $index => $service)
            <tr>
                <th style="font-weight: bold; background-color: #e2efda;">Sl No</th>
                <th style="font-weight: bold; background-color: #e2efda;">Service ID</th>
                <th style="font-weight: bold; background-color: #e2efda;">Date</th>
                <th style="font-weight: bold; background-color: #e2efda;">Product</th>
                <th style="font-weight: bold; background-color: #e2efda;">Service Type</th>
                <th style="font-weight: bold; background-color: #e2efda;">Engineer</th>
                <th style="font-weight: bold; background-color: #e2efda;">Price</th>
            </tr>
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>#{{ $service->id }}</td>
                <td>{{ $service->created_at->format('d M Y') }}</td>
                <td>{{ $service->product->name ?? 'N/A' }} {{ $service->productModel ? '-' . $service->productModel->name : '' }}</td>
                <td>{{ str_replace('_', ' ', $service->type_of_service) }}</td>
                <td>{{ $service->serviceEngineer->name ?? 'N/A' }}</td>
                <td>INR {{ number_format($service->price, 2) }}</td>
            </tr>
            <tr><td colspan="7" style="height: 10px;"></td></tr>
        @endforeach

        <tr><td colspan="7"></td></tr>

        <tr>
            <th colspan="7" style="font-weight: bold; font-size: 12pt; background-color: #f2f2f2;">Owned Products</th>
        </tr>
        <tr>
            <th style="font-weight: bold; background-color: #fff2cc;">Product Name</th>
            <th style="font-weight: bold; background-color: #fff2cc;">Model</th>
            <th style="font-weight: bold; background-color: #fff2cc;">Series</th>
            <th style="font-weight: bold; background-color: #fff2cc;">Engine Model</th>
            <th style="font-weight: bold; background-color: #fff2cc;">DOC</th>
            <th style="font-weight: bold; background-color: #fff2cc;">Source</th>
            <th style="font-weight: bold; background-color: #fff2cc;">Date Acquired</th>
        </tr>
        @foreach($uniqueProducts as $p)
        <tr>
            <td>{{ $p['product']->name }}</td>
            <td>{{ $p['model']->name ?? 'N/A' }}</td>
            <td>{{ $p['series']->name ?? 'N/A' }}</td>
            <td>{{ $p['engine_model'] ?? 'N/A' }}</td>
            <td>{{ $p['doc'] ? (\Carbon\Carbon::parse($p['doc'])->format('d M Y')) : 'N/A' }}</td>
            <td>{{ $p['source'] }}</td>
            <td>{{ $p['date']->format('d M Y') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
</body>
</html>