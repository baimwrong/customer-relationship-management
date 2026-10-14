<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Enterprise CRM</title>

    <!-- Local Offline CSS Assets (Zero Internet Required) -->
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/crm-custom.css') }}">

    @stack('styles')
</head>
<body>
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <div class="crm-wrapper">
        <!-- Sidebar Navigation -->
        <aside class="crm-sidebar" id="crmSidebar">
            <div class="sidebar-brand">
                <i class="bi bi-buildings-fill"></i>
                <div>
                    <div class="lh-1">Enterprise CRM</div>
                    <div style="font-size: 0.65rem; color: #38bdf8; letter-spacing: 0.05em; text-transform: uppercase;">Enterprise Edition</div>
                </div>
            </div>

            <div class="flex-grow-1 overflow-auto py-2">
                <div class="nav-section-title">Core Operations</div>
                <ul class="nav flex-column mb-2">
                    <li class="nav-item">
                        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="bi bi-speedometer2"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('deals.index') }}" class="nav-link {{ request()->routeIs('deals.index', 'deals.create', 'deals.show', 'deals.edit') ? 'active' : '' }}">
                            <i class="bi bi-briefcase"></i>
                            <span>Deals Pipeline</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('deals.kanban') }}" class="nav-link {{ request()->routeIs('deals.kanban') ? 'active' : '' }}">
                            <i class="bi bi-kanban"></i>
                            <span>Pipeline Kanban</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('leads.index') }}" class="nav-link {{ request()->routeIs('leads.*') ? 'active' : '' }}">
                            <i class="bi bi-funnel"></i>
                            <span>Leads</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('companies.index') }}" class="nav-link {{ request()->routeIs('companies.*') ? 'active' : '' }}">
                            <i class="bi bi-building"></i>
                            <span>Companies</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('contacts.index') }}" class="nav-link {{ request()->routeIs('contacts.*') ? 'active' : '' }}">
                            <i class="bi bi-person-lines-fill"></i>
                            <span>Contacts</span>
                        </a>
                    </li>
                </ul>

                <div class="nav-section-title">Engagements &amp; Follow-up</div>
                <ul class="nav flex-column mb-2">
                    <li class="nav-item">
                        <a href="{{ route('activities.index') }}" class="nav-link {{ request()->routeIs('activities.*') ? 'active' : '' }}">
                            <i class="bi bi-calendar-check"></i>
                            <span>Activities Log</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('tasks.index') }}" class="nav-link {{ request()->routeIs('tasks.*') ? 'active' : '' }}">
                            <i class="bi bi-check2-square"></i>
                            <span>Action Tasks</span>
                        </a>
                    </li>
                </ul>

                @if(Auth::user()->isAdmin() || Auth::user()->isSalesManager())
                    <div class="nav-section-title">Administration &amp; Data</div>
                    <ul class="nav flex-column mb-2">
                        <li class="nav-item">
                            <a href="{{ route('admin.imports.index') }}" class="nav-link {{ request()->routeIs('admin.imports.*') ? 'active' : '' }}">
                                <i class="bi bi-file-earmark-arrow-up"></i>
                                <span>Bulk Data Import</span>
                            </a>
                        </li>
                        @if(Auth::user()->isAdmin())
                            <li class="nav-item">
                                <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                                    <i class="bi bi-people"></i>
                                    <span>Team Members</span>
                                </a>
                            </li>
                        @endif
                    </ul>
                @endif
            </div>

            <!-- User Info in Sidebar Footer -->
            <div class="sidebar-user">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2 overflow-hidden">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px; flex-shrink: 0;">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                        <div class="overflow-hidden">
                            <div class="text-white text-truncate fw-semibold small">{{ Auth::user()->name }}</div>
                            <span class="badge bg-secondary text-uppercase" style="font-size: 0.65rem;">
                                {{ str_replace('_', ' ', Auth::user()->role) }}
                            </span>
                        </div>
                    </div>
                    <a href="{{ route('profile.show') }}" class="text-secondary p-1 hover-white" title="My Profile">
                        <i class="bi bi-gear fs-5"></i>
                    </a>
                </div>
            </div>
        </aside>

        <!-- Main Workspace -->
        <div class="crm-main">
            <!-- Topbar Header -->
            <header class="crm-topbar d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-sm btn-light border d-lg-none" type="button" onclick="toggleSidebar()">
                        <i class="bi bi-list fs-5"></i>
                    </button>
                    <h5 class="mb-0 fw-bold text-dark d-none d-sm-block">@yield('page_title', 'Overview')</h5>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <!-- Quick Create Dropdown -->
                    <div class="dropdown">
                        <button class="btn btn-sm btn-primary dropdown-toggle fw-semibold shadow-sm" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-plus-lg me-1"></i> Quick Action
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                            <li><h6 class="dropdown-header text-uppercase small">Create New Record</h6></li>
                            <li><a class="dropdown-item" href="{{ route('deals.create') }}"><i class="bi bi-briefcase me-2 text-primary"></i> New Deal</a></li>
                            <li><a class="dropdown-item" href="{{ route('leads.create') }}"><i class="bi bi-funnel me-2 text-info"></i> New Lead</a></li>
                            <li><a class="dropdown-item" href="{{ route('companies.create') }}"><i class="bi bi-building me-2 text-success"></i> New Company</a></li>
                            <li><a class="dropdown-item" href="{{ route('contacts.create') }}"><i class="bi bi-person-plus me-2 text-warning"></i> New Contact</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#quickActivityModal"><i class="bi bi-telephone-plus me-2 text-secondary"></i> Log Activity</a></li>
                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#quickTaskModal"><i class="bi bi-check2-circle me-2 text-danger"></i> Schedule Task</a></li>
                        </ul>
                    </div>

                    <!-- User Account Dropdown -->
                    <div class="dropdown">
                        <button class="btn btn-sm btn-light border dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle fs-6 text-primary"></i>
                            <span class="d-none d-md-inline fw-semibold small">{{ Auth::user()->name }}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                            <li class="px-3 py-2 border-bottom">
                                <div class="fw-bold small">{{ Auth::user()->name }}</div>
                                <div class="text-muted small">{{ Auth::user()->email }}</div>
                            </li>
                            <li><a class="dropdown-item" href="{{ route('profile.show') }}"><i class="bi bi-person me-2"></i> Profile &amp; Security</a></li>
                            @if(Auth::user()->isAdmin())
                                <li><a class="dropdown-item" href="{{ route('admin.imports.index') }}"><i class="bi bi-upload me-2"></i> Bulk Import Center</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.users.index') }}"><i class="bi bi-people me-2"></i> Manage Users</a></li>
                            @endif
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="bi bi-box-arrow-right me-2"></i> Sign Out
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </header>

            <!-- Main Content Container -->
            <main class="crm-content">
                <!-- Flash Alert Messages -->
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 d-flex align-items-center" role="alert">
                        <i class="bi bi-check-circle-fill fs-5 me-2 text-success"></i>
                        <div>{{ session('success') }}</div>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if (session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show shadow-sm border-0 d-flex align-items-center" role="alert">
                        <i class="bi bi-exclamation-triangle-fill fs-5 me-2 text-warning"></i>
                        <div>{{ session('warning') }}</div>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 d-flex align-items-center" role="alert">
                        <i class="bi bi-x-circle-fill fs-5 me-2 text-danger"></i>
                        <div>{{ session('error') }}</div>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" role="alert">
                        <div class="fw-bold mb-1"><i class="bi bi-exclamation-circle me-1"></i> Please resolve the following errors:</div>
                        <ul class="mb-0 ps-3 small">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <!-- Quick Log Activity Modal -->
    <div class="modal fade" id="quickActivityModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow">
                <form method="POST" action="{{ route('activities.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold"><i class="bi bi-telephone-plus me-1 text-primary"></i> Log Activity / Interaction</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Activity Type</label>
                                <select name="type" class="form-select form-select-sm" required>
                                    <option value="call">Phone Call</option>
                                    <option value="email">Email</option>
                                    <option value="meeting">Meeting</option>
                                    <option value="note" selected>Note / Memo</option>
                                    <option value="site_visit">Site Visit</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Priority</label>
                                <select name="priority" class="form-select form-select-sm" required>
                                    <option value="low">Low</option>
                                    <option value="normal" selected>Normal</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Subject / Title</label>
                            <input type="text" name="subject" class="form-control form-control-sm" required placeholder="e.g. Call regarding contract terms">
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Date &amp; Time</label>
                                <input type="datetime-local" name="activity_date" class="form-control form-control-sm" value="{{ now()->format('Y-m-d\TH:i') }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Duration (Minutes)</label>
                                <input type="number" name="duration_minutes" class="form-control form-control-sm" placeholder="e.g. 30" min="0">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Description / Notes</label>
                            <textarea name="description" class="form-control form-control-sm" rows="3" placeholder="Key takeaways and next steps..."></textarea>
                        </div>

                        <input type="hidden" name="status" value="completed">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-primary">Save Activity</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Quick Schedule Task Modal -->
    <div class="modal fade" id="quickTaskModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow">
                <form method="POST" action="{{ route('tasks.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold"><i class="bi bi-check2-circle me-1 text-danger"></i> Schedule Follow-up Task</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Task Title</label>
                            <input type="text" name="title" class="form-control form-control-sm" required placeholder="e.g. Send updated pricing proposal">
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Due Date</label>
                                <input type="date" name="due_date" class="form-control form-control-sm" value="{{ now()->addDays(2)->format('Y-m-d') }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Priority</label>
                                <select name="priority" class="form-select form-select-sm" required>
                                    <option value="low">Low</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Task Details / Instructions</label>
                            <textarea name="description" class="form-control form-control-sm" rows="3" placeholder="Additional context for the task..."></textarea>
                        </div>

                        <input type="hidden" name="status" value="pending">
                        <input type="hidden" name="assigned_user_id" value="{{ Auth::id() }}">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-primary">Schedule Task</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Local Offline JS Assets (Zero Internet Required) -->
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('vendor/chartjs/chart.umd.min.js') }}"></script>

    <script>
    function toggleSidebar() {
        document.getElementById('crmSidebar').classList.toggle('show');
        document.getElementById('sidebarOverlay').classList.toggle('show');
    }
    </script>

    @stack('scripts')
</body>
</html>
