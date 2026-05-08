@extends('layouts.admin')

@section('title', 'Notifications')

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3>Notifications</h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"> <i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item">Notifications</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<!-- Container-fluid starts-->
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>All Notifications</h5>
                    <button id="mark-all-read-btn" class="btn btn-primary btn-sm"><i class="fa fa-check-double"></i> Mark All as Read</button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="display datatables" id="notifications-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Message</th>
                                    <th>Sent To</th>
                                    <th>Created At</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Container-fluid Ends-->

<!-- View Notification Modal -->
<div class="modal fade" id="viewNotificationModal" tabindex="-1" role="dialog" aria-labelledby="viewNotificationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewNotificationModalLabel">Notification Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6 id="modal-notification-title"></h6><br>
                <p id="modal-notification-message"></p>
                <hr>
                <p class="mb-1"><strong>Sent To:</strong> <span id="modal-notification-sent-to"></span></p>
                <p class="mb-1"><strong>Created At:</strong> <span id="modal-notification-created-at"></span></p>
                <p class="mb-0"><strong>Status:</strong> <span id="modal-notification-status"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>



<!-- Mark All as Read Confirmation Modal -->
<div class="modal fade" id="markAllReadConfirmationModal" tabindex="-1" role="dialog" aria-labelledby="markAllReadConfirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="markAllReadConfirmationModalLabel">Confirm Mark All as Read</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to mark all notifications as read?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmMarkAllReadBtn">Mark All as Read</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete/Remove Confirmation Modal -->
<div class="modal fade" id="deleteRemoveConfirmationModal" tabindex="-1" role="dialog" aria-labelledby="deleteRemoveConfirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteRemoveConfirmationModalLabel">Confirm Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="delete-remove-modal-message"></p>
                <input type="hidden" id="delete-remove-notification-id">
                <input type="hidden" id="delete-remove-action-type">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteRemoveBtn">Confirm</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
<script>
    $(document).ready(function() {
        var notificationsTable = $('#notifications-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('notifications.index') }}",
            columns: [
                { data: 'id', name: 'id' },
                { data: 'title', name: 'title' },
                { data: 'message', name: 'message' },
                { data: 'sent_to', name: 'sent_to', orderable: false, searchable: false },
                { data: 'created_at', name: 'created_at' },
                { data: 'read_at', name: 'read_at', orderable: false, searchable: false },
                { data: 'actions', name: 'actions', orderable: false, searchable: false },
            ],
            rawColumns: ['read_at', 'actions', 'message']
        });

        setInterval(function() {
            notificationsTable.ajax.reload(null, false); // Reloads the table without resetting pagination
        }, 5000);

        // Handle "Read More" click
        $('#notifications-table').on('click', '.read-more-btn', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            $('.truncated-message-' + id).toggleClass('d-none');
            $('.full-message-' + id).toggleClass('d-none');
            $(this).text($(this).text() === '...Read More' ? 'Show Less' : '...Read More');
        });

        // Handle "View Details" button click
        $('#notifications-table').on('click', '.view-notification', function() {
            var notificationId = $(this).data('id');
            $.ajax({
                url: '/notifications/' + notificationId,
                type: 'GET',
                success: function(response) {
                    $('#modal-notification-title').text(response.title);
                    $('#modal-notification-message').html(response.message); // Use .html() for potentially rich content
                    $('#modal-notification-sent-to').text(response.user.employee ? response.user.employee.name : (response.user.name || 'N/A'));
                    $('#modal-notification-created-at').text(moment(response.created_at).format('MMM D, YYYY h:mm A'));
                    
                    // Mark as read automatically if not already read
                    if (!response.read_at) {
                         $.ajax({
                            url: '/notifications/' + notificationId,
                            type: 'PUT',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(markResponse) {
                                // Update status in modal
                                $('#modal-notification-status').html('<span class="badge bg-success">Read</span>');
                                // Refresh table to show updated status
                                notificationsTable.ajax.reload(null, false);
                            }
                        });
                        $('#modal-notification-status').html('<span class="badge bg-primary">Unread</span>'); // Initially unread until success
                    } else {
                        $('#modal-notification-status').html('<span class="badge bg-success">Read</span>');
                    }
                    
                    $('#viewNotificationModal').modal('show');
                },
                error: function(xhr) {
                    showToast('Error fetching notification details.', 'danger');
                }
            });
        });



        // Handle "Remove" button click (for non-admin users) - show confirmation modal
        $('#notifications-table').on('click', '.remove-notification-btn', function() {
            var notificationId = $(this).data('id');
            $('#delete-remove-notification-id').val(notificationId);
            $('#delete-remove-action-type').val('hide');
            $('#delete-remove-modal-message').text('Are you sure you want to remove this notification?');
            $('#confirmDeleteRemoveBtn').removeClass('btn-danger').addClass('btn-warning').text('Remove');
            $('#deleteRemoveConfirmationModal').modal('show');
        });

        // Handle "Delete" button click (for admin users) - show confirmation modal
        $('#notifications-table').on('submit', 'form', function(e) {
            e.preventDefault();
            var form = $(this);
            var notificationId = form.find('button[type="submit"]').closest('.d-flex').find('.view-notification').data('id'); // Get ID from a sibling button
            if (!notificationId) { // Fallback if ID not found this way
                notificationId = form.attr('action').split('/').pop();
            }

            $('#delete-remove-notification-id').val(notificationId);
            $('#delete-remove-action-type').val('delete');
            $('#delete-remove-modal-message').text('Are you sure you want to delete this notification permanently?');
            $('#confirmDeleteRemoveBtn').removeClass('btn-warning').addClass('btn-danger').text('Delete');
            $('#deleteRemoveConfirmationModal').modal('show');
        });

        // Confirm Delete/Remove
        $('#confirmDeleteRemoveBtn').on('click', function() {
            var notificationId = $('#delete-remove-notification-id').val();
            var actionType = $('#delete-remove-action-type').val();
            var url = '';
            var type = '';
            var data = { _token: '{{ csrf_token() }}' };

            if (actionType === 'hide') {
                url = '/notifications/' + notificationId + '/hide';
                type = 'POST';
            } else if (actionType === 'delete') {
                url = '/notifications/' + notificationId;
                type = 'POST'; // Laravel uses POST for DELETE with _method
                data._method = 'DELETE';
            }

            $.ajax({
                url: url,
                type: type,
                data: data,
                success: function(response) {
                    showToast(response.success, 'success');
                    notificationsTable.ajax.reload();
                    $('#deleteRemoveConfirmationModal').modal('hide');
                },
                error: function(xhr) {
                    showToast('Error performing action.', 'danger');
                    $('#deleteRemoveConfirmationModal').modal('hide');
                }
            });
        });


        // Handle "Mark All as Read" button click
        $('#mark-all-read-btn').on('click', function() {
            $('#markAllReadConfirmationModal').modal('show');
        });

        // Confirm Mark All as Read
        $('#confirmMarkAllReadBtn').on('click', function() {
            $.ajax({
                url: '{{ route("notifications.markAllAsRead") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    showToast(response.message, 'success');
                    notificationsTable.ajax.reload();
                    $('#markAllReadConfirmationModal').modal('hide');
                },
                error: function(xhr) {
                    showToast('Error marking all notifications as read.', 'danger');
                    $('#markAllReadConfirmationModal').modal('hide');
                }
            });
        });
    });
</script>
@endpush