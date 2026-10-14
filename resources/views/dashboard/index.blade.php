@extends('layouts.app')

@section('title', 'Executive Sales Dashboard')
@section('page_title', 'Sales & Revenue Analytics')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1 text-dark">Executive CRM Dashboard</h3>
        <p class="text-muted small mb-0">Real-time pipeline performance, lead conversion analytics, and sales velocity.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('deals.kanban') }}" class="btn btn-sm btn-outline-primary shadow-sm fw-semibold">
            <i class="bi bi-kanban me-1"></i> View Kanban Pipeline
        </a>
        <a href="{{ route('deals.create') }}" class="btn btn-sm btn-primary shadow-sm fw-semibold">
            <i class="bi bi-plus-lg me-1"></i> New Opportunity
        </a>
    </div>
</div>

<!-- KPI Cards -->
<div class="row g-3 mb-4">
    <!-- Total Pipeline Value -->
    <div class="col-sm-6 col-xl-3">
        <div class="card kpi-card h-100 border-0 shadow-sm">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">Total Pipeline</span>
                    <div class="kpi-icon-box bg-primary-subtle text-primary">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                </div>
                <h3 class="fw-bold text-dark mb-1">${{ number_format($metrics['total_pipeline_value'], 2) }}</h3>
                <div class="small text-muted">
                    <span class="text-dark fw-bold">{{ $metrics['open_deals_count'] }}</span> active open opportunities
                </div>
            </div>
        </div>
    </div>

    <!-- Weighted Forecast -->
    <div class="col-sm-6 col-xl-3">
        <div class="card kpi-card h-100 border-0 shadow-sm">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">Weighted Forecast</span>
                    <div class="kpi-icon-box bg-info-subtle text-info">
                        <i class="bi bi-pie-chart-fill"></i>
                    </div>
                </div>
                <h3 class="fw-bold text-dark mb-1">${{ number_format($metrics['weighted_pipeline_value'], 2) }}</h3>
                <div class="small text-muted">
                    Based on stage probability weights
                </div>
            </div>
        </div>
    </div>

    <!-- Won Revenue -->
    <div class="col-sm-6 col-xl-3">
        <div class="card kpi-card h-100 border-0 shadow-sm">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">Closed Won Revenue</span>
                    <div class="kpi-icon-box bg-success-subtle text-success">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                </div>
                <h3 class="fw-bold text-dark mb-1">${{ number_format($metrics['total_won_revenue'], 2) }}</h3>
                <div class="small text-muted">
                    Win rate: <span class="badge bg-success-subtle text-success fw-bold">{{ $metrics['win_rate'] }}%</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Lead Conversion Rate -->
    <div class="col-sm-6 col-xl-3">
        <div class="card kpi-card h-100 border-0 shadow-sm">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">Lead Conversion</span>
                    <div class="kpi-icon-box bg-warning-subtle text-warning">
                        <i class="bi bi-funnel-fill"></i>
                    </div>
                </div>
                <h3 class="fw-bold text-dark mb-1">{{ $metrics['conversion_rate'] }}%</h3>
                <div class="small text-muted">
                    <span class="text-dark fw-bold">{{ $metrics['active_leads'] }}</span> active / {{ $metrics['converted_leads'] }} converted
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="row g-3 mb-4">
    <!-- Pipeline Stage Distribution Chart -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-bar-chart-fill me-2 text-primary"></i> Pipeline Distribution by Stage</h6>
                <span class="badge bg-light text-muted border">Deal Value ($)</span>
            </div>
            <div class="card-body p-3">
                <div style="height: 290px;">
                    <canvas id="pipelineStageChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Lead Source Doughnut Chart -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-pie-chart me-2 text-info"></i> Leads by Acquisition Source</h6>
                <span class="badge bg-light text-muted border">Lead Count</span>
            </div>
            <div class="card-body p-3 d-flex align-items-center justify-content-center">
                <div style="height: 250px; width: 100%;">
                    <canvas id="leadSourceChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Lower Section: Monthly Revenue Trends, Activities Timeline & Tasks -->
<div class="row g-3">
    <!-- Revenue Trend Chart -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-graph-up me-2 text-success"></i> Monthly Closed Won Revenue (Last 6 Months)</h6>
            </div>
            <div class="card-body p-3">
                <div style="height: 220px;">
                    <canvas id="monthlyRevenueChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Sales Leaderboard -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-trophy-fill me-2 text-warning"></i> Top Sales Performers</h6>
                <span class="small text-muted">Closed Revenue</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Rep Name</th>
                            <th>Role</th>
                            <th class="text-center">Won Deals</th>
                            <th class="text-end">Total Won ($)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($metrics['sales_reps'] as $rep)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-primary-subtle text-primary fw-bold d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                            {{ substr($rep->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-dark">{{ $rep->name }}</div>
                                            <small class="text-muted">{{ $rep->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ ucfirst(str_replace('_', ' ', $rep->role)) }}</span></td>
                                <td class="text-center fw-bold text-dark">{{ $rep->won_deals_count }}</td>
                                <td class="text-end fw-bold text-success">${{ number_format($rep->won_amount ?? 0, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">No sales activity recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Engagement Feed & Urgent Tasks -->
    <div class="col-lg-6">
        <!-- Upcoming Tasks -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-check2-square me-2 text-danger"></i> Urgent Follow-up Tasks</h6>
                <a href="{{ route('tasks.index') }}" class="btn btn-sm btn-link text-decoration-none p-0">View All Tasks</a>
            </div>
            <div class="list-group list-group-flush">
                @forelse($metrics['pending_tasks'] as $task)
                    <div class="list-group-item d-flex align-items-center justify-content-between p-3">
                        <div class="d-flex align-items-center gap-3">
                            <form method="POST" action="{{ route('tasks.toggle', $task) }}" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-outline-secondary rounded-circle p-1" title="Mark as Completed" style="width: 28px; height: 28px;">
                                    <i class="bi bi-check"></i>
                                </button>
                            </form>
                            <div>
                                <div class="fw-semibold text-dark">{{ $task->title }}</div>
                                <div class="small text-muted">
                                    <i class="bi bi-clock me-1"></i> Due: {{ $task->due_date ? $task->due_date->format('M d, Y') : 'No due date' }}
                                    @if($task->company) &bull; <i class="bi bi-building me-1"></i> {{ $task->company->name }} @endif
                                </div>
                            </div>
                        </div>
                        <span class="badge bg-{{ $task->priority_meta['color'] }}-subtle text-{{ $task->priority_meta['color'] }} fw-semibold text-uppercase" style="font-size: 0.7rem;">
                            {{ $task->priority_meta['label'] }}
                        </span>
                    </div>
                @empty
                    <div class="p-4 text-center text-muted small">No upcoming pending tasks. You're all caught up!</div>
                @endforelse
            </div>
        </div>

        <!-- Recent Activities Feed -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-clock-history me-2 text-secondary"></i> Recent Customer Interactions</h6>
                <a href="{{ route('activities.index') }}" class="btn btn-sm btn-link text-decoration-none p-0">All Logs</a>
            </div>
            <div class="card-body p-3">
                <div class="activity-timeline">
                    @forelse($metrics['recent_activities'] as $act)
                        <div class="timeline-item">
                            <div class="timeline-icon bg-{{ $act->type_meta['color'] }}">
                                <i class="bi {{ $act->type_meta['icon'] }}"></i>
                            </div>
                            <div class="ps-2">
                                <div class="d-flex justify-content-between align-items-start">
                                    <h6 class="fw-bold text-dark mb-0">{{ $act->subject }}</h6>
                                    <span class="text-muted small">{{ $act->activity_date ? $act->activity_date->diffForHumans() : $act->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="text-muted small mb-1">{{ Str::limit($act->description, 90) }}</p>
                                <div class="small text-muted">
                                    <span class="fw-semibold text-dark">{{ $act->user?->name }}</span>
                                    @if($act->company) &bull; <span>{{ $act->company->name }}</span> @endif
                                    @if($act->contact) &bull; <span>{{ $act->contact->full_name }}</span> @endif
                                </div>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Pipeline Stage Bar Chart
    const stageCtx = document.getElementById('pipelineStageChart').getContext('2d');
    new Chart(stageCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($metrics['charts']['stage_labels']) !!},
            datasets: [{
                label: 'Pipeline Value ($)',
                data: {!! json_encode($metrics['charts']['stage_amounts']) !!},
                backgroundColor: [
                    '#64748b', '#0ea5e9', '#3b82f6', '#f59e0b', '#10b981', '#ef4444'
                ],
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(val) { return '$' + val.toLocaleString(); }
                    }
                }
            }
        }
    });

    // 2. Leads by Source Doughnut Chart
    const sourceCtx = document.getElementById('leadSourceChart').getContext('2d');
    new Chart(sourceCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($metrics['charts']['source_labels']) !!},
            datasets: [{
                data: {!! json_encode($metrics['charts']['source_counts']) !!},
                backgroundColor: [
                    '#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#06b6d4', '#64748b', '#6366f1'
                ],
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } }
            }
        }
    });

    // 3. Monthly Won Revenue Line Chart
    const monthlyCtx = document.getElementById('monthlyRevenueChart').getContext('2d');
    new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($metrics['charts']['monthly_labels']) !!},
            datasets: [{
                label: 'Won Revenue ($)',
                data: {!! json_encode($metrics['charts']['monthly_revenue']) !!},
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                tension: 0.35,
                fill: true,
                pointRadius: 4,
                pointBackgroundColor: '#10b981',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(val) { return '$' + val.toLocaleString(); }
                    }
                }
            }
        }
    });
});
</script>
@endpush
@endsection
