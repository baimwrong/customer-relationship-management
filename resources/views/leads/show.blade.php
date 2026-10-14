@extends('layouts.app')

@section('title', $lead->full_name)
@section('page_title', 'Lead Details')

@section('content')
<!-- Lead Header Card -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-{{ $lead->status_badge_color }}-subtle text-{{ $lead->status_badge_color }} border border-{{ $lead->status_badge_color }}-subtle fs-6">
                        {{ $lead->status_label }}
                    </span>
                    <span class="badge bg-light text-muted border">{{ \App\Models\Lead::SOURCES[$lead->source] ?? $lead->source }}</span>
                </div>
                <h3 class="fw-bold text-dark mb-1">{{ $lead->full_name }}</h3>
                <div class="text-muted small">
                    @if($lead->company_name)
                        <i class="bi bi-building me-1"></i> <strong class="text-dark">{{ $lead->company_name }}</strong> &bull;
                    @endif
                    @if($lead->job_title)
                        <span>{{ $lead->job_title }}</span> &bull;
                    @endif
                    <i class="bi bi-person-badge me-1"></i> Assigned Rep: <strong>{{ $lead->assignedUser?->name ?? 'Unassigned' }}</strong>
                </div>
            </div>

            <div class="text-end">
                <div class="small text-muted">Estimated Opportunity</div>
                <h2 class="fw-bold text-primary mb-1">${{ number_format($lead->estimated_value, 2) }}</h2>
                <div class="small text-muted mb-2">
                    Lead Score: <span class="fw-bold text-dark">{{ $lead->score }}/100</span>
                </div>
                <div class="d-flex gap-2 justify-content-end">
                    @if(!$lead->isConverted())
                        <button type="button" class="btn btn-sm btn-success fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#convertLeadModal">
                            <i class="bi bi-arrow-repeat me-1"></i> Convert Lead
                        </button>
                    @endif
                    <a href="{{ route('leads.edit', $lead) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-pencil me-1"></i> Edit
                    </a>
                </div>
            </div>
        </div>

        @if($lead->isConverted())
            <div class="alert alert-success-subtle text-success border border-success-subtle mt-3 mb-0 p-3 rounded-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                    <strong>Lead Converted!</strong> This prospect was successfully converted on {{ $lead->converted_at?->format('M d, Y H:i') }}.
                </div>
                <div class="d-flex gap-2">
                    @if($lead->convertedCompany)
                        <a href="{{ route('companies.show', $lead->convertedCompany) }}" class="btn btn-sm btn-outline-success">
                            <i class="bi bi-building me-1"></i> View Account
                        </a>
                    @endif
                    @if($lead->convertedContact)
                        <a href="{{ route('contacts.show', $lead->convertedContact) }}" class="btn btn-sm btn-outline-success">
                            <i class="bi bi-person me-1"></i> View Contact
                        </a>
                    @endif
                    @if($lead->convertedDeal)
                        <a href="{{ route('deals.show', $lead->convertedDeal) }}" class="btn btn-sm btn-success">
                            <i class="bi bi-briefcase me-1"></i> View Deal
                        </a>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Contact & Qualification Info -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-person-lines-fill me-2 text-primary"></i> Prospect Information</h6>
            </div>
            <div class="card-body p-3">
                <div class="mb-3">
                    <span class="small text-muted d-block">Work Email</span>
                    @if($lead->email)
                        <a href="mailto:{{ $lead->email }}" class="text-decoration-none fw-semibold text-dark">{{ $lead->email }}</a>
                    @else
                        <span class="text-muted small">None</span>
                    @endif
                </div>
                <div class="mb-3">
                    <span class="small text-muted d-block">Phone Number</span>
                    @if($lead->phone)
                        <a href="tel:{{ $lead->phone }}" class="text-decoration-none fw-semibold text-dark">{{ $lead->phone }}</a>
                    @else
                        <span class="text-muted small">None</span>
                    @endif
                </div>
                <div class="mb-3">
                    <span class="small text-muted d-block">Qualification Score</span>
                    <div class="d-flex align-items-center gap-2 mt-1">
                        <div class="progress flex-grow-1" style="height: 8px;">
                            <div class="progress-bar bg-{{ $lead->score >= 70 ? 'success' : ($lead->score >= 40 ? 'warning' : 'secondary') }}" style="width: {{ $lead->score }}%"></div>
                        </div>
                        <span class="small fw-bold">{{ $lead->score }}/100</span>
                    </div>
                </div>
                <div class="mb-0">
                    <span class="small text-muted d-block">Acquisition Source</span>
                    <span class="badge bg-light text-dark border mt-1">{{ \App\Models\Lead::SOURCES[$lead->source] ?? $lead->source }}</span>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-file-text me-2 text-secondary"></i> Background &amp; Notes</h6>
            </div>
            <div class="card-body p-3">
                <p class="small text-muted mb-0" style="white-space: pre-line;">{{ $lead->notes ?: 'No notes provided.' }}</p>
            </div>
        </div>
    </div>

    <!-- Right Column: Timeline & Tasks -->
    <div class="col-lg-8">
        <!-- Quick Action Bar -->
        <div class="card border-0 shadow-sm mb-4 bg-light">
            <div class="card-body p-3 d-flex gap-2">
                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#logLeadActivity">
                    <i class="bi bi-telephone me-1"></i> Log Interaction
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="collapse" data-bs-target="#createLeadTask">
                    <i class="bi bi-check2-circle me-1"></i> Schedule Task
                </button>
            </div>

            <!-- Log Activity Collapse -->
            <div class="collapse p-3 pt-0 border-top mt-2" id="logLeadActivity">
                <form method="POST" action="{{ route('activities.store') }}">
                    @csrf
                    <input type="hidden" name="lead_id" value="{{ $lead->id }}">
                    <input type="hidden" name="status" value="completed">

                    <div class="row g-2 mb-2">
                        <div class="col-sm-4">
                            <select name="type" class="form-select form-select-sm" required>
                                <option value="call">Phone Call</option>
                                <option value="email">Email</option>
                                <option value="meeting">Meeting</option>
                                <option value="note" selected>Note</option>
                            </select>
                        </div>
                        <div class="col-sm-8">
                            <input type="text" name="subject" class="form-control form-control-sm" placeholder="Subject..." required>
                        </div>
                    </div>
                    <div class="mb-2">
                        <textarea name="description" class="form-control form-control-sm" rows="2" placeholder="Interaction details..."></textarea>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <select name="priority" class="form-select form-select-sm w-auto">
                            <option value="normal">Normal</option>
                            <option value="high">High</option>
                        </select>
                        <button type="submit" class="btn btn-sm btn-primary">Save Activity</button>
                    </div>
                </form>
            </div>

            <!-- Create Task Collapse -->
            <div class="collapse p-3 pt-0 border-top mt-2" id="createLeadTask">
                <form method="POST" action="{{ route('tasks.store') }}">
                    @csrf
                    <input type="hidden" name="lead_id" value="{{ $lead->id }}">
                    <input type="hidden" name="status" value="pending">

                    <div class="row g-2 mb-2">
                        <div class="col-sm-8">
                            <input type="text" name="title" class="form-control form-control-sm" placeholder="Follow-up task title..." required>
                        </div>
                        <div class="col-sm-4">
                            <input type="date" name="due_date" class="form-control form-control-sm" value="{{ now()->addDays(2)->format('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <select name="priority" class="form-select form-select-sm w-auto">
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                        <button type="submit" class="btn btn-sm btn-danger">Schedule Task</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Follow-up Tasks -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-check2-square me-2 text-danger"></i> Follow-up Tasks</h6>
            </div>
            <div class="list-group list-group-flush">
                @forelse($lead->tasks as $tsk)
                    <div class="list-group-item d-flex align-items-center justify-content-between p-3">
                        <div class="d-flex align-items-center gap-2">
                            <form method="POST" action="{{ route('tasks.toggle', $tsk) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-outline-secondary rounded-circle p-1" style="width: 26px; height: 26px;">
                                    <i class="bi {{ $tsk->status === 'completed' ? 'bi-check-circle-fill text-success' : 'bi-circle' }}"></i>
                                </button>
                            </form>
                            <div>
                                <span class="{{ $tsk->status === 'completed' ? 'text-decoration-line-through text-muted' : 'fw-semibold text-dark' }}">
                                    {{ $tsk->title }}
                                </span>
                                <div class="small text-muted">Due: {{ $tsk->due_date ? $tsk->due_date->format('M d, Y') : 'No due date' }}</div>
                            </div>
                        </div>
                        <span class="badge bg-{{ $tsk->priority_meta['color'] }}-subtle text-{{ $tsk->priority_meta['color'] }}">
                            {{ $tsk->priority_meta['label'] }}
                        </span>
                    </div>
                @empty
                    <div class="p-3 text-center text-muted small">No tasks assigned to this lead.</div>
                @endforelse
            </div>
        </div>

        <!-- Activity Timeline -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-clock-history me-2 text-secondary"></i> Interaction History</h6>
            </div>
            <div class="card-body p-4">
                <div class="activity-timeline">
                    @forelse($lead->activities as $act)
                        <div class="timeline-item">
                            <div class="timeline-icon bg-{{ $act->type_meta['color'] }}">
                                <i class="bi {{ $act->type_meta['icon'] }}"></i>
                            </div>
                            <div class="ps-2">
                                <div class="d-flex justify-content-between align-items-start">
                                    <h6 class="fw-bold text-dark mb-0">{{ $act->subject }}</h6>
                                    <span class="text-muted small">{{ $act->activity_date ? $act->activity_date->format('M d, Y H:i') : $act->created_at->format('M d, Y') }}</span>
                                </div>
                                <p class="text-muted small mb-1">{{ $act->description }}</p>
                                <span class="badge bg-light text-dark border">{{ $act->user?->name }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted small py-3">No activity logs recorded yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Lead Conversion Workflow Modal -->
@if(!$lead->isConverted())
<div class="modal fade" id="convertLeadModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="{{ route('leads.convert', $lead) }}">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fw-bold"><i class="bi bi-arrow-repeat me-2"></i> Convert Lead to Account &amp; Opportunity</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">
                        Converting this lead will automatically generate a verified <strong>Contact ({{ $lead->full_name }})</strong> and allows you to establish a new <strong>Company Account</strong> and <strong>Pipeline Deal</strong>.
                    </p>

                    <!-- Account / Company Section -->
                    <div class="card border mb-3 bg-light">
                        <div class="card-body p-3">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" name="create_company" id="createCompanySwitch" value="1" checked onchange="toggleCompanyInput(this)">
                                <label class="form-check-label fw-bold text-dark" for="createCompanySwitch">Create New Company Account</label>
                            </div>
                            <div id="newCompanySection">
                                <label class="form-label small fw-semibold text-muted">Company Name</label>
                                <input type="text" name="new_company_name" class="form-control form-control-sm" value="{{ $lead->company_name ?: $lead->full_name . '\'s Org' }}" placeholder="Company Name">
                            </div>
                            <div id="existingCompanySection" style="display: none;" class="mt-2">
                                <label class="form-label small fw-semibold text-muted">Or Attach to Existing Company</label>
                                <select name="company_id" class="form-select form-select-sm">
                                    <option value="">-- Choose Existing Company --</option>
                                    @foreach($companies as $comp)
                                        <option value="{{ $comp->id }}">{{ $comp->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Deal / Opportunity Section -->
                    <div class="card border mb-3 bg-light">
                        <div class="card-body p-3">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" name="create_deal" id="createDealSwitch" value="1" checked onchange="toggleDealInput(this)">
                                <label class="form-check-label fw-bold text-dark" for="createDealSwitch">Create Pipeline Deal / Opportunity</label>
                            </div>
                            <div id="dealDetailsSection">
                                <div class="row g-2 mb-2">
                                    <div class="col-sm-8">
                                        <label class="form-label small fw-semibold text-muted">Deal Title</label>
                                        <input type="text" name="deal_title" class="form-control form-control-sm" value="{{ ($lead->company_name ?: $lead->full_name) . ' - New Business Deal' }}">
                                    </div>
                                    <div class="col-sm-4">
                                        <label class="form-label small fw-semibold text-muted">Amount ($)</label>
                                        <input type="number" step="0.01" name="deal_amount" class="form-control form-control-sm" value="{{ $lead->estimated_value }}">
                                    </div>
                                </div>
                                <div class="row g-2">
                                    <div class="col-sm-6">
                                        <label class="form-label small fw-semibold text-muted">Initial Stage</label>
                                        <select name="deal_stage" class="form-select form-select-sm">
                                            @foreach(\App\Models\Deal::STAGES as $stageKey => $meta)
                                                <option value="{{ $stageKey }}" {{ $stageKey === \App\Models\Deal::STAGE_QUALIFICATION ? 'selected' : '' }}>
                                                    {{ $meta['label'] }} ({{ $meta['probability'] }}%)
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="form-label small fw-semibold text-muted">Expected Close Date</label>
                                        <input type="date" name="expected_close_date" class="form-control form-control-sm" value="{{ now()->addMonth()->format('Y-m-d') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Assigned Rep -->
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Assigned Sales Representative</label>
                        <select name="assigned_user_id" class="form-select form-select-sm">
                            @foreach($users as $usr)
                                <option value="{{ $usr->id }}" {{ old('assigned_user_id', $lead->assigned_user_id) == $usr->id ? 'selected' : '' }}>
                                    {{ $usr->name }} ({{ ucfirst(str_replace('_', ' ', $usr->role)) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-success fw-semibold">
                        <i class="bi bi-check2-circle me-1"></i> Finalize Conversion
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function toggleCompanyInput(checkbox) {
    document.getElementById('newCompanySection').style.display = checkbox.checked ? 'block' : 'none';
    document.getElementById('existingCompanySection').style.display = checkbox.checked ? 'none' : 'block';
}

function toggleDealInput(checkbox) {
    document.getElementById('dealDetailsSection').style.display = checkbox.checked ? 'block' : 'none';
}
</script>
@endpush
@endif
@endsection
