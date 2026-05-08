<table class="table table-bordernone">
    <thead>
        <tr>
            <th>Employee</th>
            @if($category === 'sales')
                <th>Won Leads</th>
            @elseif($category === 'service')
                <th>Tasks Completed</th>
            @elseif($category === 'parts')
                <th>Parts Sold</th>
            @endif
        </tr>
    </thead>
    <tbody>
        @forelse ($contributors as $contributor)
            <tr>
                <td class="d-flex align-items-center">
                    <img src="{{ $contributor->user->profile_pic ?? ($contributor->user->employee->profile_pic ?? asset('admin/assets/images/user/avatar-9.jpg')) }}" alt="{{ $contributor->user->name }}" class="rounded-circle me-2" width="30" height="30">
                    <span>{{ $contributor->user->name }}</span>
                </td>
                @if($category === 'sales')
                    <td>{{ $contributor->leads_count }}</td>
                @elseif($category === 'service')
                    <td>{{ $contributor->tasks_count }}</td>
                @elseif($category === 'parts')
                    <td>{{ $contributor->parts_sold_count }}</td>
                @endif
            </tr>
        @empty
            <tr>
                <td colspan="2" class="text-center">No data available</td>
            </tr>
        @endforelse
    </tbody>
</table>
