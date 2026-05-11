<div class="sidebar-wrapper" data-layout="stroke-svg">
    <div class="logo-wrapper" style="min-height: 92px;"><a href="dashboard"><img class="img-fluid"
                width="120" src="{{ asset('admin/assets/images/logo/korps-sync-crm-logo-white.png') }}" alt=""></a>
        <div class="back-btn"><i class="fa fa-angle-left"> </i></div>
        <div class="toggle-sidebar"><i class="status_toggle middle sidebar-toggle" data-feather="grid"> </i>
        </div>
    </div>
    <div class="logo-icon-wrapper"><a href="dashboard"><img class="img-fluid" width="100"
                src="{{ asset('admin/assets/images/logo/korps-sync-crm-logo-white.png') }}" alt=""></a></div>
    <nav class="sidebar-main">
        <div class="left-arrow" id="left-arrow"><i data-feather="arrow-left"></i></div>
        <div id="sidebar-menu">
            <ul class="sidebar-links" id="simple-bar">
                <li class="back-btn"><a href="index"><img class="img-fluid" width="100"
                            src="{{ asset('admin/assets/images/logo/logo.png') }}" alt=""></a>
                    <div class="mobile-back text-end"> <span>Back </span><i class="fa fa-angle-right ps-2"
                            aria-hidden="true"></i></div>
                </li>
                <li class="pin-title sidebar-main-title">
                    <div>
                        <h6>Pinned</h6>
                    </div>
                </li>
                <li class="sidebar-main-title">
                    <div>
                        <h6 class="lan-1">General</h6>
                    </div>
                </li>

                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title link-nav"
                        href="{{ route('dashboard') }}">
                        <svg class="stroke-icon">
                            <use href="{{ asset('admin/assets/svg/icon-sprite.svg#stroke-home') }}"></use>
                        </svg>
                        <svg class="fill-icon">
                            <use href="{{ asset("admin/assets/svg/icon-sprite.svg#stroke-user") }}"></use>
                        </svg><span>Dashboard</span>
                        <div class="according-menu"><i class="fa fa-angle-right"></i></div>
                    </a></li>

                @if(checkMenu(Session::get('role_id'), 12, 'read'))
                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title link-nav"
                        href="{{ route('leads.index') }}">
                        <svg class="stroke-icon">
                            <use href="{{ asset('admin/assets/svg/icon-sprite.svg#stroke-task') }}">
                            </use>
                        </svg>
                        <svg class="fill-icon">
                            <use href="{{ asset('admin/assets/svg/icon-sprite.svg#fill-task') }}"></use>
                        </svg><span>Leads</span></a></li>
                @endif

                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title link-nav"
                        href="{{ route('notifications.index') }}">
                        <svg class="stroke-icon">
                            <use href="{{ asset('admin/assets/svg/icon-sprite.svg#stroke-email') }}"></use>
                        </svg>
                        <svg class="fill-icon">
                            <use href="{{ asset("admin/assets/svg/icon-sprite.svg#stroke-user") }}"></use>
                        </svg><span>Notifications</span>
                        <div class="according-menu"><i class="fa fa-angle-right"></i></div>
                    </a></li>

                <li class="sidebar-main-title">
                    <div>
                        <h6 class="">Task Management</h6>
                    </div>
                </li>

                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title link-nav"
                        href="{{ route('tasks.index') }}">
                        <svg class="stroke-icon">
                            <use href="{{ asset('admin/assets/svg/icon-sprite.svg#stroke-task') }}"></use>
                        </svg>
                        <svg class="fill-icon">
                            <use href="{{ asset("admin/assets/svg/icon-sprite.svg#stroke-user") }}"></use>
                        </svg><span>Task Management</span>
                        <div class="according-menu"><i class="fa fa-angle-right"></i></div>
                    </a></li>

                @if(checkMenu(Session::get('role_id'), 22, 'read'))
                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title link-nav"
                        href="{{ route('task_continuation.index') }}">
                        <svg class="stroke-icon">
                            <use href="{{ asset('admin/assets/svg/icon-sprite.svg#stroke-to-do') }}"></use>
                        </svg>
                        <svg class="fill-icon">
                            <use href="{{ asset("admin/assets/svg/icon-sprite.svg#stroke-user") }}"></use>
                        </svg><span>Task Continuation</span>
                        <div class="according-menu"><i class="fa fa-angle-right"></i></div>
                    </a></li>
                @endif

                {{-- Monitoring Section Hidden --}}
                {{--
                {{--
                <li class="sidebar-main-title">
                    <div>
                        <h6 class="">Monitoring</h6>
                    </div>
                </li>


                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title link-nav"
                        href="{{ route('live-location.index') }}">
                <svg class="stroke-icon">
                    <use href="{{ asset('admin/assets/svg/icon-sprite.svg#stroke-maps') }}"></use>
                </svg>
                <svg class="fill-icon">
                    <use href="{{ asset("admin/assets/svg/icon-sprite.svg#stroke-user") }}"></use>
                </svg><span>Live Location</span>
                <div class="according-menu"><i class="fa fa-angle-right"></i></div>
                </a></li>

                @php
                $canViewAllTimeline = checkMenu(Session::get('role_id'), 38, 'read');
                $canViewSubTimeline = checkMenu(Session::get('role_id'), 40, 'read');
                $canViewSelfTimeline = checkMenu(Session::get('role_id'), 29, 'read');
                @endphp

                @if($canViewAllTimeline || $canViewSubTimeline || $canViewSelfTimeline)
                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title link-nav"
                        href="{{ route('timeline.index') }}">
                        <svg class="stroke-icon">
                            <use href="{{ asset('admin/assets/svg/icon-sprite.svg#stroke-calendar') }}"></use>
                        </svg>
                        <svg class="fill-icon">
                            <use href="{{ asset("admin/assets/svg/icon-sprite.svg#stroke-user") }}"></use>
                        </svg><span>Timeline</span>
                        <div class="according-menu"><i class="fa fa-angle-right"></i></div>
                    </a></li>
                @endif

                @if(checkMenu(Session::get('role_id'), 28, 'read'))
                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title link-nav"
                        href="{{ route('location-reports.index') }}">
                        <svg class="stroke-icon">
                            <use href="{{ asset('admin/assets/svg/icon-sprite.svg#stroke-maps') }}"></use>
                        </svg>
                        <svg class="fill-icon">
                            <use href="{{ asset("admin/assets/svg/icon-sprite.svg#stroke-user") }}"></use>
                        </svg><span>Location Reports</span>
                        <div class="according-menu"><i class="fa fa-angle-right"></i></div>
                    </a></li>
                @endif
                --}}
                --}}


                <li class="sidebar-main-title">
                    <div>
                        <h6 class="">Human resources</h6>
                    </div>
                </li>
                @if(checkMenu(Session::get('role_id'), 4, 'read'))
                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title link-nav"
                        href="{{ route('employees.index') }}">
                        <svg class="stroke-icon">
                            <use href="{{ asset('admin/assets/svg/icon-sprite.svg#stroke-user') }}">
                            </use>
                        </svg>
                        <svg class="fill-icon">
                            <use href="{{ asset('admin/assets/svg/icon-sprite.svg#fill-user') }}"></use>
                        </svg><span>Employees</span></a></li>
                </li>
                @endif
                @if(checkMenu(Session::get('role_id'), 23, 'read'))
                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title link-nav"
                        href="{{ route('interviews.index') }}">
                        <svg class="stroke-icon">
                            <use href="{{ asset('admin/assets/svg/icon-sprite.svg#stroke-job-search') }}">
                            </use>
                        </svg>
                        <svg class="fill-icon">
                            <use href="{{ asset('admin/assets/svg/icon-sprite.svg#fill-user') }}"></use>
                        </svg><span>Interview</span>
                    </a>
                </li>
                @endif
                @if(checkMenu(Session::get('role_id'), 33, 'read'))
                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title link-nav"
                        href="{{ route('job-vacancies.index') }}">
                        <svg class="stroke-icon">
                            <use href="{{ asset('admin/assets/svg/icon-sprite.svg#stroke-form') }}">
                            </use>
                        </svg>
                        <svg class="fill-icon">
                            <use href="{{ asset('admin/assets/svg/icon-sprite.svg#fill-user') }}"></use>
                        </svg><span>Job Vacancies</span>
                    </a>
                </li>
                @endif
                {{-- Temporary hardcoded Settlements menu item --}}
                @if(checkMenu(Session::get('role_id'), 24, 'read'))
                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title link-nav"
                        href="{{ route('settlements.index') }}">
                        <svg class="stroke-icon">
                            <use href="{{ asset('admin/assets/svg/icon-sprite.svg#stroke-charts') }}">
                            </use>
                        </svg>
                        <svg class="fill-icon">
                            <use href="{{ asset('admin/assets/svg/icon-sprite.svg#fill-user') }}"></use>
                        </svg><span>Settlements</span>
                    </a>
                </li>
                @endif
                @if(checkMenu(Session::get('role_id'), 25, 'read'))
                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title link-nav"
                        href="{{ route('attendance.index') }}">
                        <svg class="stroke-icon">
                            <use href="{{ asset('admin/assets/svg/icon-sprite.svg#stroke-calendar') }}">
                            </use>
                        </svg>
                        <svg class="fill-icon">
                            <use href="{{ asset('admin/assets/svg/icon-sprite.svg#fill-user') }}"></use>
                        </svg><span>Attendance</span></a></li>
                </li>
                @endif
                @if(checkMenu(Session::get('role_id'), 4, 'read'))
                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title link-nav"
                        href="{{ route('birthdays.index') }}">
                        <svg class="stroke-icon">
                            <use href="{{ asset('admin/assets/svg/icon-sprite.svg#stroke-calendar') }}">
                            </use>
                        </svg>
                        <svg class="fill-icon">
                            <use href="{{ asset('admin/assets/svg/icon-sprite.svg#fill-user') }}"></use>
                        </svg><span>Birthdays</span></a></li>
                </li>
                @endif

                @if(checkMenu(Session::get('role_id'), 26, 'read'))
                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title link-nav"
                        href="{{ route('performance-review.index') }}">
                        <svg class="stroke-icon">
                            <use href="{{ asset('admin/assets/svg/icon-sprite.svg#stroke-learning') }}">
                            </use>
                        </svg>
                        <svg class="fill-icon">
                            <use href="{{ asset('admin/assets/svg/icon-sprite.svg#fill-user') }}"></use>
                        </svg><span>Performance Review</span></a></li>
                </li>
                @endif


                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title link-nav"
                        href="{{ route('organization.index') }}">
                        <svg class="stroke-icon">
                            <use href="{{ asset('admin/assets/svg/icon-sprite.svg#stroke-social') }}">
                            </use>
                        </svg>
                        <svg class="fill-icon">
                            <use href="{{ asset('admin/assets/svg/icon-sprite.svg#fill-user') }}"></use>
                        </svg><span>Organization</span></a></li>
                {{-- <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a
                        class="sidebar-link sidebar-title link-nav" href="{{ route('teams.index') }}">
                <svg class="stroke-icon">
                    <use href="{{ asset('admin/assets/svg/icon-sprite.svg#stroke-user') }}">
                    </use>
                </svg>
                <svg class="fill-icon">
                    <use href="{{ asset('admin/assets/svg/icon-sprite.svg#fill-user') }}"></use>
                </svg><span>Teams</span></a></li> --}}
                {{-- <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a
                        class="sidebar-link sidebar-title link-nav" href="{{ route('hierarchy-map.index') }}">
                <svg class="stroke-icon">
                    <use href="{{ asset('admin/assets/svg/icon-sprite.svg#stroke-user') }}">
                    </use>
                </svg>
                <svg class="fill-icon">
                    <use href="{{ asset('admin/assets/svg/icon-sprite.svg#fill-user') }}"></use>
                </svg><span>Hierarchy Map</span></a></li> --}}



                <li class="sidebar-main-title">
                    <div>
                        <h6 class="">Requests</h6>
                    </div>
                </li>
                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title link-nav"
                        href="{{ route('leave-requests.index') }}">
                        <svg class="stroke-icon">
                            <use href="{{ asset('admin/assets/svg/icon-sprite.svg#stroke-email') }}"></use>
                        </svg>
                        <svg class="fill-icon">
                            <use href="{{ asset("admin/assets/svg/icon-sprite.svg#stroke-user") }}"></use>
                        </svg><span>Leave Requests</span>
                        <div class="according-menu"><i class="fa fa-angle-right"></i></div>
                    </a></li>
                @if(checkMenu(Session::get('role_id'), 30, 'read'))
                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title link-nav"
                        href="{{ route('expense-requests.index') }}">
                        <svg class="stroke-icon">
                            <use href="{{ asset('admin/assets/svg/icon-sprite.svg#stroke-ecommerce') }}"></use>
                        </svg>
                        <svg class="fill-icon">
                            <use href="{{ asset("admin/assets/svg/icon-sprite.svg#stroke-user") }}"></use>
                        </svg><span>Expense Requests</span>
                        <div class="according-menu"><i class="fa fa-angle-right"></i></div>
                    </a></li>
                @endif
                @if(checkMenu(Session::get('role_id'), 31, 'read'))
                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title link-nav"
                        href="{{ route('document-requests.index') }}">
                        <svg class="stroke-icon">
                            <use href="{{ asset('admin/assets/svg/icon-sprite.svg#stroke-file') }}"></use>
                        </svg>
                        <svg class="fill-icon">
                            <use href="{{ asset("admin/assets/svg/icon-sprite.svg#stroke-user") }}"></use>
                        </svg><span>Document Requests</span>
                        <div class="according-menu"><i class="fa fa-angle-right"></i></div>
                    </a></li>
                @endif
                @if(checkMenu(Session::get('role_id'), 32, 'read'))
                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title link-nav"
                        href="{{ route('loan-requests.index') }}">
                        <svg class="stroke-icon">
                            <use href="{{ asset('admin/assets/svg/icon-sprite.svg#stroke-ecommerce') }}"></use>
                        </svg>
                        <svg class="fill-icon">
                            <use href="{{ asset("admin/assets/svg/icon-sprite.svg#stroke-user") }}"></use>
                        </svg><span>Loan Requests</span>
                        <div class="according-menu"><i class="fa fa-angle-right"></i></div>
                    </a></li>
                @endif

                @if(checkMenu(Session::get('role_id'), 34, 'read') || checkMenu(Session::get('role_id'), 35, 'read'))
                <li class="sidebar-main-title">
                    <div>
                        <h6 class="">Reports</h6>
                    </div>
                </li>
                @if(checkMenu(Session::get('role_id'), 34, 'read'))
                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title link-nav"
                        href="{{ route('general-reports.index') }}">
                        <svg class="stroke-icon">
                            <use href="{{ asset('admin/assets/svg/icon-sprite.svg#stroke-file') }}"></use>
                        </svg>
                        <svg class="fill-icon">
                            <use href="{{ asset("admin/assets/svg/icon-sprite.svg#stroke-user") }}"></use>
                        </svg><span>General Report</span>
                    </a></li>
                @endif
                @if(checkMenu(Session::get('role_id'), 35, 'read'))
                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title link-nav"
                        href="{{ route('task-reports.index') }}">
                        <svg class="stroke-icon">
                            <use href="{{ asset('admin/assets/svg/icon-sprite.svg#stroke-task') }}"></use>
                        </svg>
                        <svg class="fill-icon">
                            <use href="{{ asset("admin/assets/svg/icon-sprite.svg#stroke-user") }}"></use>
                        </svg><span>Task Reports</span>
                    </a></li>
                @endif
                @endif




                {{-- Parts Section Hidden --}}
                {{--
                {{--
                @if(checkMenuGroup(Session::get('role_id'), 6))
                <li class="sidebar-main-title">
                    <div>
                        <h6 class="">Parts</h6>
                    </div>
                </li>
                @endif

                @if(checkMenu(Session::get('role_id'), 20, 'read'))
                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title link-nav"
                        href="{{ route('parts.index') }}">
                <svg class="stroke-icon">
                    <use href="{{ asset('admin/assets/svg/icon-sprite.svg#stroke-builders') }}"></use>
                </svg>
                <svg class="fill-icon">
                    <use href="{{ asset("admin/assets/svg/icon-sprite.svg#stroke-user") }}"></use>
                </svg><span>Parts Management</span>
                <div class="according-menu"><i class="fa fa-angle-right"></i></div>
                </a></li>

                @endif
                @if(checkMenu(Session::get('role_id'), 21, 'read'))
                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title link-nav"
                        href="{{ route('fsr-quotations.review.index') }}">
                        <svg class="stroke-icon">
                            <use href="{{ asset('admin/assets/svg/icon-sprite.svg#stroke-file') }}"></use>
                        </svg>
                        <svg class="fill-icon">
                            <use href="{{ asset("admin/assets/svg/icon-sprite.svg#stroke-user") }}"></use>
                        </svg><span>FSR Quotations</span>
                        <div class="according-menu"><i class="fa fa-angle-right"></i></div>
                    </a></li>
                @endif
                --}}
                --}}

                {{-- Service Section Hidden --}}
                {{--
                {{--
                @if(checkMenuGroup(Session::get('role_id'), 5))
                <li class="sidebar-main-title">
                    <div>
                        <h6 class="">Service</h6>
                    </div>
                </li>
                @endif
                @if(checkMenu(Session::get('role_id'), 17, 'read') || checkMenu(Session::get('role_id'), 18, 'read'))
                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title link-nav"
                        href="{{ route('entries.index') }}">
                <svg class="stroke-icon">
                    <use href="{{ asset('admin/assets/svg/icon-sprite.svg#stroke-support-tickets') }}"></use>
                </svg>
                <svg class="fill-icon">
                    <use href="{{ asset("admin/assets/svg/icon-sprite.svg#stroke-user") }}"></use>
                </svg><span>Services</span>
                <div class="according-menu"><i class="fa fa-angle-right"></i></div>
                </a></li>
                @endif
                @if(checkMenu(Session::get('role_id'), 19, 'read'))
                <li class="sidebar-list d-none"><i class="fa fa-thumb-tack"></i><a
                        class="sidebar-link sidebar-title link-nav" href="{{ route('service-kits.index') }}">
                        <svg class="stroke-icon">
                            <use href="{{ asset('admin/assets/svg/icon-sprite.svg#stroke-builders') }}"></use>
                        </svg>
                        <svg class="fill-icon">
                            <use href="{{ asset("admin/assets/svg/icon-sprite.svg#stroke-user") }}"></use>
                        </svg><span>Service Kits</span>
                        <div class="according-menu"><i class="fa fa-angle-right"></i></div>
                    </a></li>
                @endif
                --}}
                --}}

                @if(checkMenuGroup(Session::get('role_id'), 3))
                <li class="sidebar-main-title">
                    <div>
                        <h6 class="lan-8">Sales </h6>
                    </div>
                </li>

                {{-- Leads moved to General --}}

                @if(checkMenu(Session::get('role_id'), 6, 'read'))
                <li id="sidebar-products" class="sidebar-list">
                    <i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title link-nav"
                        href="{{ route('products.index') }}">
                        <svg class="stroke-icon">
                            <use href="{{ asset('admin/assets/svg/icon-sprite.svg#stroke-ecommerce') }}">
                            </use>
                        </svg>
                        <svg class="fill-icon">
                            <use href="{{ asset('admin/assets/svg/icon-sprite.svg#fill-ecommerce') }}"></use>
                        </svg><span>Products</span></a>
                </li>
                @endif
                @if(checkMenu(Session::get('role_id'), 7, 'read'))
                <li id="sidebar-about-products" class="sidebar-list">
                    <i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title link-nav"
                        href="{{ route('about-products.index') }}">
                        <svg class="stroke-icon">
                            <use href="{{ asset('admin/assets/svg/icon-sprite.svg#stroke-widget') }}">
                            </use>
                        </svg>
                        <svg class="fill-icon">
                            <use href="{{ asset('admin/assets/svg/icon-sprite.svg#fill-widget') }}"></use>
                        </svg><span>Product Settings</span></a>
                </li>
                @endif

                {{-- Lost Orders Hidden --}}
                {{--
                {{--
                @if(checkMenu(Session::get('role_id'), 9, 'read'))
                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title link-nav"
                        href="{{ route('loss-orders.index') }}">
                <svg class="stroke-icon">
                    <use href="{{ asset('admin/assets/svg/icon-sprite.svg#stroke-charts') }}"></use>
                </svg>
                <svg class="fill-icon">
                    <use href="{{ asset('admin/assets/svg/icon-sprite.svg#fill-task') }}"></use>
                </svg><span>Lost Orders</span></a></li>


                @endif
                --}}
                --}}

                {{-- Pipeline Hidden --}}
                {{--
                {{--
                @if(checkMenu(Session::get('role_id'), 10, 'read'))
                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title link-nav"
                        href="{{ route('pipelines.index') }}">
                <svg class="stroke-icon">
                    <use href="{{ asset('admin/assets/svg/icon-sprite.svg#stroke-layout') }}"></use>
                </svg>
                <svg class="fill-icon">
                    <use href="{{ asset('admin/assets/svg/icon-sprite.svg#fill-task') }}"></use>
                </svg><span>Pipeline</span></a></li>
                @endif
                --}}
                --}}

                @endif
                @if(checkMenuGroup(Session::get('role_id'), 4))
                <li class="sidebar-main-title">
                    <div>
                        <h6 class="">Clients</h6>
                    </div>
                </li>
                @if(checkMenu(Session::get('role_id'), 8, 'read'))
                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title link-nav"
                        href="{{ route('clients.index') }}">
                        <svg class="stroke-icon">

                            <use href="{{ asset('admin/assets/svg/icon-sprite.svg#stroke-user') }}"></use>
                        </svg>
                        <svg class="fill-icon">
                            <use href="{{ asset('admin/assets/svg/icon-sprite.svg#fill-user') }}"></use>
                        </svg><span>Manage Clients </span>
                        <div class="according-menu"><i class="fa fa-angle-right"></i></div>
                    </a></li>
                @endif
                @endif

                <li class="sidebar-main-title">
                    <div>
                        <h6 class="lan-9">Settings</h6>
                    </div>
                </li>
                @if(checkMenuGroup(Session::get('role_id'), 1))
                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title" href="#">
                        <svg class="stroke-icon">
                            <use href="{{ asset('admin/assets/svg/icon-sprite.svg#stroke-project') }}"></use>
                        </svg>
                        <svg class="fill-icon">
                            <use href="{{ asset('admin/assets/svg/icon-sprite.svg#fill-project') }}"></use>
                        </svg><span>Master Settings</span>
                        <div class="according-menu"><i class="fa fa-angle-right"></i></div>
                    </a>
                    <ul class="sidebar-submenu">
                        @if(checkMenu(Session::get('role_id'), 3, 'read'))
                        <li><a href="{{ route('roles.index') }}">User Roles</a></li>
                        @endif
                        @if(checkMenu(Session::get('role_id'), 1, 'read'))
                        <li><a href="{{ route('dealerships.index') }}">Dealerships</a></li>
                        @endif
                        @if(checkMenu(Session::get('role_id'), 2, 'read'))
                        <li><a href="{{ route('zones.index') }}">Zones</a></li>
                        @endif
                        @if(checkMenu(Session::get('role_id'), 27, 'read'))
                        <li><a href="{{ route('brand-settings.index') }}">Task Settings</a></li>
                        @endif
                        <li><a href="{{ route('organization-settings.index') }}">Organization Settings</a></li>
                        <!-- <li><a href="{{ route('backups.index') }}">Backups</a></li> -->
                    </ul>
                </li>
                @endif
                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title link-nav"
                        href="{{ route('settings.index') }}">
                        <svg class="stroke-icon">
                            <use href="{{ asset('admin/assets/svg/icon-sprite.svg#stroke-widget') }}"></use>
                        </svg>
                        <svg class="fill-icon">
                            <use href="{{ asset('admin/assets/svg/icon-sprite.svg#fill-widget') }}"></use>
                        </svg><span>Application Settings</span></a></li>
                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title link-nav"
                        href="/my-profile#edit-profile-tab">
                        <svg class="stroke-icon">
                            <use href="{{ asset('admin/assets/svg/icon-sprite.svg#stroke-user') }}"></use>
                        </svg>
                        <svg class="fill-icon">
                            <use href="{{ asset('admin/assets/svg/icon-sprite.svg#fill-user') }}"></use>
                        </svg><span>Profile Settings</span></a></li>
            </ul>


            <div class="right-arrow" id="right-arrow"><i data-feather="arrow-right"></i></div>
        </div>
    </nav>
</div>
<script>
    // Make sure jQuery is already loaded before this script
    document.addEventListener('DOMContentLoaded', function() {
        var path = window.location.pathname.replace(/\/$/, ''); // normalize path


        if (path === '/products') {

            document.getElementById('sidebar-products')?.classList.add('active');
        } else if (path === '/about-products') {

            document.getElementById('sidebar-about-products')?.classList.add('active');

            // Use vanilla JS or ensure jQuery is loaded
            const sidebarProducts = document.getElementById('sidebar-products');
            if (sidebarProducts) {
                sidebarProducts.classList.remove('active');

            }
        } else {
            console.warn('#sidebar-products not found');
        }
    });
</script>

<!-- Page Sidebar Ends-->

@push('styles')
<style>
    /* 1. Lock the outer wrappers - STRICT FIXED DIMENSIONS */
    /* 1. Lock the outer wrappers - STRICT FIXED DIMENSIONS */
    .logo-wrapper,
    .logo-icon-wrapper {
        height: 91.77px !important;
        /* Absolute fixed height, prevents expansion */
        width: 100% !important;
        /* Fill available width */
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        padding: 0;
        margin: 0;
        box-sizing: border-box;
    }

    /* 2. Lock the anchor tag to be a rigid frame using Flexbox */
    .logo-wrapper a,
    .logo-icon-wrapper a {
        display: flex;
        /* Use flexbox for centering */
        align-items: center;
        /* Vertical center */
        justify-content: center;
        /* Horizontal center */
        width: 100%;
        height: 50px;
        /* Exact height limit for the logo area */
        overflow: hidden;
        position: relative;
    }

    /* 3. The Image itself - flexible but contained */
    .slideshow-logo {
        max-height: 100%;
        /* Fit within the 50px parent */
        max-width: 100%;
        /* Fit within width */
        height: auto !important;
        /* Maintain aspect ratio */
        width: auto !important;
        object-fit: contain;
        display: block;
    }

    /* Sidebar Hover Effect - All Screens */
    .sidebar-wrapper .sidebar-list {
        transition: transform 0.3s ease;
    }

    .sidebar-wrapper .sidebar-list:hover {
        transform: translateY(-5px);
    }

    /* Minimalist Sidebar Scrollbar - Surgical Hiding */

    /* 1. Only the actual content area or SimpleBar container should scroll */
    #sidebar-menu,
    .simplebar-content-wrapper {
        scrollbar-width: none !important;
        -ms-overflow-style: none !important;
        overflow-y: hidden !important;
        /* Block scroll by default */
        overflow-x: hidden !important;
    }

    /* Prevent redundant scrolling on the list itself */
    .sidebar-links {
        overflow: visible !important;
        height: auto !important;
        /* Let content dictate height */
    }

    /* 2. Outer wrappers should NOT scroll to prevent "beyond items" effect */
    .sidebar-wrapper,
    .sidebar-main {
        overflow: hidden !important;
        height: 100vh !important;
    }

    /* 3. Revert SimpleBar mask/offset to their intended overflow: hidden to hide ghost space */
    .simplebar-mask,
    .simplebar-offset {
        overflow: hidden !important;
        height: auto !important;
    }

    /* Allow scrolling ONLY on the deepest content container if it overflows */
    .simplebar-content {
        overflow-y: auto !important;
        height: 100% !important;
    }

    /* 4. Target all possible scrollbars within sidebar for webkit hiding */
    .sidebar-wrapper ::-webkit-scrollbar,
    .sidebar-main ::-webkit-scrollbar,
    .sidebar-links ::-webkit-scrollbar,
    #sidebar-menu ::-webkit-scrollbar,
    .simplebar-content-wrapper::-webkit-scrollbar {
        display: none !important;
        width: 0 !important;
        height: 0 !important;
    }

    /* 5. Hide SimpleBar tracks/scrollbars entirely */
    .sidebar-wrapper .simplebar-track,
    .sidebar-wrapper .simplebar-scrollbar,
    .sidebar-wrapper .simplebar-track.simplebar-vertical,
    .sidebar-wrapper .simplebar-track.simplebar-horizontal {
        display: none !important;
        opacity: 0 !important;
        visibility: hidden !important;
        width: 0 !important;
        height: 0 !important;
    }

    /* 6. Ensure the last item doesn't have excessive bottom space */
    .sidebar-links {
        padding-bottom: 0px !important;
        /* Stop exactly at the last item */
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Sidebar logic (if any)
    });
</script>
@endpush