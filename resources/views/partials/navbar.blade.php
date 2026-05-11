<div class="page-header">
    <div class="header-wrapper row m-0">
        <form class="form-inline search-full col" action="#" method="get">
            <div class="form-group w-100">
                <div class="Typeahead Typeahead--twitterUsers">
                    <div class="u-posRelative">
                        <input class="demo-input Typeahead-input form-control-plaintext w-100" type="text"
                            placeholder="Search Riho .." name="q" title="" autofocus>
                        <div class="spinner-border Typeahead-spinner" role="status"><span class="sr-only">Loading...
                            </span></div><i class="close-search" data-feather="x"></i>
                    </div>
                    <div class="Typeahead-menu"> </div>
                </div>
            </div>
        </form>
        <div class="header-logo-wrapper col-auto p-0">
            <div class="logo-wrapper"> <a href="index.html"><img class="img-fluid for-light"
                        src="{{ asset('admin/assets/images/logo/logo_dark.png') }}" alt="logo-light"><img
                        class="img-fluid for-dark" src="{{ asset('admin/assets/images/logo/logo.png') }}"
                        alt="logo-dark"></a></div>
            <div class="toggle-sidebar"> <i class="status_toggle middle sidebar-toggle" data-feather="align-center"></i>
            </div>
        </div>
        <div class="left-header col-xxl-5 col-xl-6 col-lg-5 col-md-4 col-sm-3 p-0">
            <div> <a class="toggle-sidebar" href="#"> <i class="iconly-Category icli"> </i></a>
                @auth
                <div class="d-flex align-items-center gap-2 ">
                    <h4 class="f-w-600">Welcome {{ Auth::user()->name }}</h4><img class="mt-0"
                        src="{{ asset('admin/assets/images/hand.gif') }}" alt="hand-gif">
                </div>
                @endauth
            </div>
            <div class="welcome-content d-xl-block d-none"><span class="text-truncate col-12"> </span></div>
        </div>
        <div class="nav-right col-xxl-7 col-xl-6 col-md-7 col-8 pull-right right-header p-0 ms-auto">
            <ul class="nav-menus">
                <li class="d-md-block d-none">
                    <div class="form search-form mb-0">
                        <div class="input-group"><span class="input-icon">
                                <svg>
                                    <use href="{{ asset('admin/assets/svg/icon-sprite.svg#search-header') }}"></use>
                                </svg>
                                <input class="w-100" type="search" placeholder="Search pages... (Ctrl+K)" id="navbar-search-desktop"></span></div>
                        <div id="search-results-desktop" class="search-results-dropdown" style="display: none;"></div>
                    </div>
                </li>
                <li class="d-md-none d-block">
                    <div class="form search-form mb-0">
                        <div class="input-group"> <span class="input-show">
                                <svg id="searchIcon">
                                    <use href="{{ asset('admin/assets/svg/icon-sprite.svg#search-header') }}"></use>
                                </svg>
                                <div id="searchInput">
                                    <input type="search" placeholder="Search pages... (Ctrl+K)" id="navbar-search-mobile">
                                </div>
                            </span></div>
                        <div id="search-results-mobile" class="search-results-dropdown" style="display: none;"></div>
                    </div>
                </li>
                <li id="global-task-timer" style="display: none;">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa fa-clock-o"></i>
                        <span id="timer-display">00:00:00</span>
                    </div>
                </li>
                <!-- Call Centre Status -->
                <li class="onhover-dropdown">
                    <div class="d-flex align-items-center gap-2" style="cursor: pointer;">
                        <span id="agent-status-dot" class="rounded-circle bg-secondary" style="width: 10px; height: 10px; display: inline-block;"></span>
                        <div id="agent-status-loader" class="spinner-border spinner-border-sm text-primary" role="status" style="display: none; width: 12px; height: 12px;"></div>
                        <span id="agent-status-text" class="f-w-600 f-12 text-muted">Checking...</span>
                    </div>
                    <div class="onhover-show-div p-3" style="width: 200px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
                        <h6 class="f-w-700 mb-3">Call Centre Status</h6>
                        <button id="availability-toggle-btn" class="btn btn-success btn-sm w-100" onclick="window.exotelService.toggleAvailability()">
                            <span id="btn-loader" class="spinner-border spinner-border-sm me-2" role="status" style="display: none;"></span>
                            <span id="btn-text">Go Online</span>
                        </button>
                        <div class="text-center mt-3 border-top pt-2">
                            <a href="javascript:void(0)" onclick="window.exotelService.openConsole()" class="text-primary f-w-600" style="font-size: 11px;">
                                <i class="fa fa-external-link-alt me-1"></i>Pop-out Console
                            </a>
                        </div>
                    </div>
                </li>
                <!-- <li class="onhover-dropdown">
                <svg>
                  <use href="{{ asset('admin/assets/svg/icon-sprite.svg#star') }}"></use>
                </svg>
                <div class="onhover-show-div bookmark-flip">
                  <div class="flip-card">
                    <div class="flip-card-inner">
                      <div class="front">
                        <h6 class="f-18 mb-0 dropdown-title">Bookmark</h6>
                        <ul class="bookmark-dropdown">
                          <li>
                            <div class="row">
                              <div class="col-4 text-center">
                                <div class="bookmark-content">
                                  <div class="bookmark-icon"><i data-feather="file-text"></i></div><span>Forms</span>
                                </div>
                              </div>
                              <div class="col-4 text-center">
                                <div class="bookmark-content">
                                  <div class="bookmark-icon"><i data-feather="user"></i></div><span>Profile</span>
                                </div>
                              </div>
                              <div class="col-4 text-center">
                                <div class="bookmark-content">
                                  <div class="bookmark-icon"><i data-feather="server"></i></div><span>Tables</span>
                                </div>
                              </div>
                            </div>
                          </li>
                          <li class="text-center"><a class="flip-btn f-w-700" id="flip-btn" href="javascript:void(0)">Add New Bookmark</a></li>
                        </ul>
                      </div>
                      <div class="back">
                        <ul>
                          <li>
                            <div class="bookmark-dropdown flip-back-content">
                              <input type="text" placeholder="search...">
                            </div>
                          </li>
                          <li><a class="f-w-700 d-block flip-back" id="flip-back" href="javascript:void(0)">Back</a></li>
                        </ul>
                      </div>
                    </div>
                  </div>
                </div>
              </li> -->
                <li style="display: none;">
                    <div class="mode"><i class="moon" data-feather="moon"> </i></div>
                </li>
                <li class="onhover-dropdown notification-down">
                    <div class="notification-box">
                        <svg>
                            <use href="{{ asset('admin/assets/svg/icon-sprite.svg#notification-header') }}"></use>
                        </svg><span class="badge rounded-pill badge-primary" id="unread-notification-count">0</span>
                    </div>
                    <div class="onhover-show-div notification-dropdown custom-notification-dropdown">
                        <div class="card mb-0">
                            <div class="card-header p-3 border-bottom">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="m-0 f-w-600">Notifications</h6>
                                    <span class="f-12 text-muted" id="unread-notification-text">0 New</span>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="notitications-bar" style="max-height: 300px; overflow-y: auto;">
                                    <ul class="list-group list-group-flush" id="recent-notifications-list">
                                        <!-- Recent notifications will be loaded here via AJAX -->
                                        <li class="list-group-item text-center text-muted py-5"
                                            id="no-recent-notifications" style="display: none;">
                                            <div class="text-center">
                                                <i data-feather="bell-off" class="mb-3 text-muted" style="width: 32px; height: 32px;"></i>
                                                <p class="mb-0 f-13">No recent notifications</p>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="card-footer p-2 text-center border-top">
                                <a class="f-w-600 f-12 text-primary" href="{{ route('notifications.index') }}">View All Notifications</a>
                            </div>
                        </div>
                    </div>
                    <style>
                        .custom-notification-dropdown,
                        .custom-profile-dropdown {
                            width: 360px !important;
                            border-radius: 16px !important;
                            overflow: hidden;
                            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.08) !important;
                            border: 1px solid rgba(0, 0, 0, 0.04) !important;
                            right: 0 !important;
                            left: auto !important;
                            top: 55px !important;
                            background: #fff !important;
                        }

                        .custom-profile-dropdown {
                            width: 240px !important;
                            padding: 10px !important;
                        }

                        .custom-notification-dropdown .card {
                            box-shadow: none !important;
                            border: none !important;
                            margin-bottom: 0 !important;
                        }

                        .notification-item {
                            padding: 16px 20px;
                            border-bottom: 1px solid #f1f5f9;
                            transition: all 0.2s ease;
                            cursor: pointer;
                            display: flex;
                            align-items: flex-start;
                            text-decoration: none !important;
                            position: relative;
                        }

                        .notification-item:hover {
                            background-color: #f8fafc;
                        }

                        .notification-item.unread {
                            background-color: #fcfaff;
                            /* Very subtle violet tint */
                        }

                        /* Premium Unread Indicator */
                        .notification-item.unread::before {
                            content: '';
                            position: absolute;
                            left: 0;
                            top: 0;
                            bottom: 0;
                            width: 4px;
                            background: #7366ff;
                        }

                        .notification-item:last-child {
                            border-bottom: none;
                        }

                        .notification-icon-box {
                            width: 42px;
                            height: 42px;
                            border-radius: 12px;
                            /* Squircle */
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            flex-shrink: 0;
                            margin-right: 16px;
                            background-color: #eef2ff;
                            color: #6366f1;
                        }

                        .notification-content {
                            flex: 1;
                            min-width: 0;
                        }

                        .notification-content h6 {
                            font-size: 14px;
                            font-weight: 700;
                            margin-bottom: 4px;
                            color: #1e293b;
                            line-height: 1.4;
                        }

                        .notification-content p {
                            font-size: 13px;
                            color: #64748b;
                            margin-bottom: 6px;
                            font-weight: 500;
                            line-height: 1.5;
                            display: -webkit-box;
                            -webkit-line-clamp: 2;
                            line-clamp: 2;
                            -webkit-box-orient: vertical;
                            overflow: hidden;
                        }

                        .notification-time {
                            font-size: 11px;
                            color: #94a3b8;
                            display: block;
                            font-weight: 600;
                        }

                        .unread-dot {
                            display: none;
                            /* Replaced by left border indicator */
                        }

                        .notitications-bar {
                            scrollbar-width: thin;
                            scrollbar-color: #cbd5e1 #ffffff;
                        }
                    </style>
                </li>
                @auth
                <li class="profile-nav onhover-dropdown">
                    <!-- Show the profile picture if it is available -->

                    @if(Auth::user()->profile_pic && file_exists(public_path('storage/' . Auth::user()->profile_pic)))
                    <div class="media profile-media"><img style="width: 41px; height: 41px;" class="b-r-10"
                            src="{{ asset('storage/'. Auth::user()->profile_pic) }}" alt="">
                        @else
                        <div class="media profile-media"><img class="b-r-10" width="41" height="41"
                                src="{{ asset('admin/assets/images/dashboard/profile.png') }}" alt="">
                            @endif
                            <div class="media-body d-xxl-block d-none box-col-none">
                                <div class="d-flex align-items-center gap-2"> <span>{{ Auth::user()->name }} </span><i
                                        class="middle fa fa-angle-down"> </i></div>
                                <p class="mb-0 font-roboto">{{ ucfirst(Auth::user()->user_type) }}</p>
                            </div>

                        </div>
                        <ul class="profile-dropdown onhover-show-div custom-profile-dropdown">
                            <!-- Premium User Header -->
                            <li class="p-0 border-bottom mb-2">
                                <div class="d-flex align-items-center p-3">
                                    <div class="flex-shrink-0">
                                        @if(Auth::user()->profile_pic && file_exists(public_path('storage/' . Auth::user()->profile_pic)))
                                        <img style="width: 44px; height: 44px; object-fit: cover;" class="rounded-circle" src="{{ asset('storage/'. Auth::user()->profile_pic) }}" alt="Profile">
                                        @else
                                        <img style="width: 44px; height: 44px;" class="rounded-circle" src="{{ asset('admin/assets/images/dashboard/profile.png') }}" alt="Profile">
                                        @endif
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <span class="d-block f-w-700 text-dark">{{ Auth::user()->name }}</span>
                                        <span class="d-block f-12 text-muted">{{ ucfirst(Auth::user()->user_type) }}</span>
                                    </div>
                                </div>
                            </li>
                            <!-- Menu Links -->
                            <li>
                                <a href="{{ route('my-profile') }}" class="d-flex align-items-center py-2 px-3 rounded-2 hover-bg-light text-dark">
                                    <i data-feather="user" style="width: 16px; height: 16px; margin-right: 10px; color: #64748b;"></i>
                                    <span class="f-w-500">My Profile</span>
                                </a>
                            </li>
                            <li>
                                <a id="logout-link" href="{{ route('logout') }}" class="d-flex align-items-center py-2 px-3 rounded-2 hover-bg-light text-danger">
                                    <i data-feather="log-out" style="width: 16px; height: 16px; margin-right: 10px;"></i>
                                    <span class="f-w-500">Log Out</span>
                                </a>
                            </li>
                        </ul>
                </li>
                @endauth
            </ul>
        </div>
        <script class="result-template" type="text/x-handlebars-template">
            <div class="ProfileCard u-cf">                        
                                      <div class="ProfileCard-avatar"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-airplay m-0"><path d="M5 17H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-1"></path><polygon points="12 15 17 21 7 21 12 15"></polygon></svg></div>
                                      <div class="ProfileCard-details"> 
                                      <div class="ProfileCard-realName">ABCD</div>
                                      </div> 
                                      </div>
                                    </script>
        <script class="empty-template" type="text/x-handlebars-template"><div class="EmptyMessage">Your search turned up 0 results. This most likely means the backend is down, yikes!</div></script>
    </div>
</div>

<!-- Logout Loader Overlay -->
<div id="logout-loader" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,255,255,0.8); z-index: 10000; justify-content: center; align-items: center; flex-direction: column;">
    <img src="{{ asset('admin/assets/images/logo/logo_dark.png') }}" alt="Loading..." class="img-fluid" style="height: 50px; animation: breathing 1.5s ease-in-out infinite;">
    <h5 class="mt-3 text-muted">Logging out...</h5>
</div>

<style>
    @keyframes breathing {
        0% {
            transform: scale(0.95);
            opacity: 0.8;
        }

        50% {
            transform: scale(1.05);
            opacity: 1;
        }

        100% {
            transform: scale(0.95);
            opacity: 0.8;
        }
    }
</style>
<!-- Page Header Ends  -->

@push('scripts')
<script>
    (function($) { // Pass jQuery as $
        $(function() { // jQuery document ready shorthand
            // Logout Loader Logic
            $('#logout-link').on('click', function(e) {
                e.preventDefault();
                $('#logout-loader').css('display', 'flex').hide().fadeIn(200); // Smooth fade in

                var logoutUrl = $(this).attr('href');

                // Slight delay to ensure the loader renders before the browser starts navigation
                setTimeout(function() {
                    window.location.href = logoutUrl;
                }, 100);
            });

            const unreadNotificationCount = $('#unread-notification-count');
            const unreadNotificationText = $('#unread-notification-text');
            const recentNotificationsList = $('#recent-notifications-list');
            const noRecentNotifications = $('#no-recent-notifications');
            const notificationBox = $('.notification-box');

            function fetchNotifications() {
                $.ajax({
                    url: "{{ route('notifications.recent') }}",
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        let notifications = response.notifications;
                        let unreadCount = response.unreadCount;

                        unreadNotificationCount.text(unreadCount);
                        if (unreadCount === 0) {
                            unreadNotificationText.text('All caught up!');
                        } else {
                            unreadNotificationText.text(`${unreadCount} New`);
                        }

                        recentNotificationsList.empty();

                        if (notifications.length === 0) {
                            recentNotificationsList.append(`
                                <li class="list-group-item text-center text-muted py-5" id="no-recent-notifications">
                                    <div class="text-center">
                                        <i data-feather="bell-off" class="mb-3 text-muted" style="width: 32px; height: 32px;"></i>
                                        <p class="mb-0 f-13">No recent notifications</p>
                                    </div>
                                </li>
                            `);
                            if (typeof feather !== 'undefined') {
                                feather.replace();
                            }
                        } else {
                            notifications.forEach(function(notification) {
                                const MAX_MESSAGE_LENGTH = 80; // Reduced length for cleaner look
                                let displayMessage = notification.message;
                                if (displayMessage.length > MAX_MESSAGE_LENGTH) {
                                    displayMessage = displayMessage.substring(0, MAX_MESSAGE_LENGTH) + '...';
                                }

                                let isRead = notification.read_at !== null;
                                let notificationClass = isRead ? '' : 'unread'; // Changed class name to match CSS

                                // Determine icon background color based on type if available, else default
                                let iconBgClass = 'bg-light-primary';
                                let iconColorClass = 'text-primary';

                                let notificationItem = `
                                    <li class="notification-item ${notificationClass}" data-id="${notification.id}">
                                        <div class="notification-icon-box ${iconBgClass}">
                                            <i class="fa fa-${notification.data && notification.data.icon ? notification.data.icon : 'bell'} ${iconColorClass}"></i>
                                        </div>
                                        <div class="notification-content">
                                            <h6>${notification.title}</h6>
                                            <p>${displayMessage}</p>
                                            <span class="notification-time">${moment(notification.created_at).fromNow()}</span>
                                        </div>
                                        ${!isRead ? '<div class="unread-dot"></div>' : ''}
                                    </li>
                                `;
                                recentNotificationsList.append(notificationItem);
                            });
                        }
                    },
                    error: function(xhr) {
                        console.error('Error fetching notifications:', xhr);
                    }
                });
            }

            function markNotificationAsRead(notificationId) {
                $.ajax({
                    url: `/notifications/${notificationId}/mark-as-read`,
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            let item = $(`#recent-notifications-list li[data-id="${notificationId}"]`);
                            item.removeClass('unread');
                            item.find('.unread-dot').remove();

                            let currentUnreadCount = parseInt(unreadNotificationCount.text());
                            if (currentUnreadCount > 0) {
                                unreadNotificationCount.text(currentUnreadCount - 1);
                                unreadNotificationText.text(`${currentUnreadCount - 1} New`);
                            }
                        }
                    },
                    error: function(xhr) {
                        console.error('Error marking notification as read:', xhr);
                    }
                });
            }

            fetchNotifications();
            setInterval(fetchNotifications, 6000);

            recentNotificationsList.on('click', 'li', function() { // Changed selector to 'li' to catch all clicks
                let notificationId = $(this).data('id');
                if ($(this).hasClass('unread')) { // Only mark as read if it's currently unread
                    markNotificationAsRead(notificationId);
                }
                window.location.href = "{{ route('notifications.index') }}"; // Redirect to notifications page
            });

            notificationBox.parent().on('show.bs.dropdown', function() {
                fetchNotifications();
            });
        });
    })(jQuery); // Invoke with jQuery
</script>

<!-- Exotel Ringing Modal -->
<div class="modal fade" id="exotel-ringing-modal" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; background: linear-gradient(135deg, #7366ff 0%, #a066ff 100%);">
            <div class="modal-body text-center p-5 text-white">
                <div class="ringing-animation mb-4">
                    <i class="fa fa-phone fa-4x mb-3 animate-ringing"></i>
                </div>
                <h3 class="f-w-700 mb-2">Incoming Call</h3>
                <p class="f-20 mb-4" id="caller-number">Unknown Number</p>
                
                <div class="d-flex justify-content-center gap-4">
                    <button class="btn btn-success btn-lg rounded-circle p-4" onclick="window.exotelService.answerCall()">
                        <i class="fa fa-phone fa-2x"></i>
                    </button>
                    <button class="btn btn-danger btn-lg rounded-circle p-4" onclick="window.exotelService.endCall()">
                        <i class="fa fa-phone fa-rotate-135 fa-2x"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Active Call Bar -->
<div id="exotel-active-call-bar" style="display: none; position: fixed; bottom: 30px; left: 50%; transform: translateX(-50%); z-index: 10000;">
    <div class="d-flex align-items-center gap-4 bg-dark text-white px-4 py-3 rounded-pill shadow-lg border border-secondary">
        <div class="d-flex align-items-center gap-2">
            <span class="rounded-circle bg-success pulse-animation" style="width: 10px; height: 10px;"></span>
            <span class="f-w-600">On Call: <span id="active-call-number">...</span></span>
        </div>
        <div class="divider bg-secondary" style="width: 1px; height: 20px;"></div>
        <button class="btn btn-danger btn-xs rounded-pill px-3" onclick="window.exotelService.endCall()">
            <i class="fa fa-phone fa-rotate-135 me-2"></i> End Call
        </button>
    </div>
</div>

<!-- Confirm Call Modal -->
<div class="modal fade" id="confirmCallModal" tabindex="-1" aria-labelledby="confirmCallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmCallModalLabel">Confirm Call</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-dark">
                Are you sure you want to call <strong id="confirm-call-phone"></strong>?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="window.exotelService.confirmDialLead()">Call</button>
            </div>
        </div>
    </div>
</div>

<!-- Confirm Hangup Modal -->
<div class="modal fade" id="confirmHangupModal" tabindex="-1" aria-labelledby="confirmHangupModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmHangupModalLabel">Confirm Hang Up</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-dark">
                Are you sure you want to end the active call?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="window.exotelService.confirmEndCall()">Hang Up</button>
            </div>
        </div>
    </div>
</div>

<style>
    .animate-ringing {
        animation: ringing 1s infinite;
    }
    @keyframes ringing {
        0% { transform: rotate(0) scale(1); }
        15% { transform: rotate(15deg) scale(1.1); }
        30% { transform: rotate(-15deg) scale(1.1); }
        45% { transform: rotate(10deg) scale(1.1); }
        60% { transform: rotate(-10deg) scale(1.1); }
        75% { transform: rotate(5deg) scale(1.1); }
        85% { transform: rotate(-5deg) scale(1.1); }
        100% { transform: rotate(0) scale(1); }
    }
    .pulse-animation {
        animation: pulse 1.5s infinite;
    }
    @keyframes pulse {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(81, 187, 37, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(81, 187, 37, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(81, 187, 37, 0); }
    }
</style>
<script src="{{ asset('admin/assets/js/exotelsdk.js') }}"></script>
<script src="{{ asset('admin/assets/js/exotel-service.js') }}"></script>

<!-- Realtime Subscriptions -->
<script src="https://js.pusher.com/8.0.1/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.3/dist/echo.iife.js"></script>

<script>
    (function() {
        const pusherKey = "{{ env('PUSHER_APP_KEY') }}";
        
        if (!pusherKey) {
            console.warn('PUSHER_APP_KEY is missing. Real-time updates disabled, falling back to polling.');
        } else {
            // Initialize Echo
            window.Pusher = Pusher;
            window.Echo = new Echo({
                broadcaster: 'pusher',
                key: pusherKey,
                cluster: "{{ env('PUSHER_APP_CLUSTER', 'mt1') }}",
                forceTLS: true,
                wsHost: "{{ env('PUSHER_HOST', 'api.pusher.com') }}",
                wsPort: {{ env('PUSHER_PORT', 443) }},
                wssPort: {{ env('PUSHER_PORT', 443) }},
                enabledTransports: ['ws', 'wss'],
            });

            const currentEmployeeId = {{ Auth::user()->employee->id ?? 'null' }};

            // Listen for status updates
            window.Echo.channel('agent-status')
                .listen('.status.updated', (e) => {
                    console.log('Realtime Status Update received:', e);
                    if (e.employeeId == currentEmployeeId && window.exotelService) {
                        window.exotelService.status = e.status;
                        window.exotelService.updateUIStatus(e.status);
                    }
                });
        }

        // Polling Fallback (Interval of 30 seconds to sync state if WebSockets miss an event)
        setInterval(() => {
            if (window.exotelService) {
                console.log('Running polling fallback sync...');
                window.exotelService.init();
            }
        }, 30000);
    })();
</script>

@endpush