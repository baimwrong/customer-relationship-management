@extends('layouts.app')

@section('title', 'Pipeline Kanban Board')
@section('page_title', 'Pipeline Kanban')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1 text-dark">Visual Pipeline Kanban</h3>
        <p class="text-muted small mb-0">Drag and advance opportunities through sales lifecycle stages.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('deals.index') }}" class="btn btn-sm btn-outline-secondary shadow-sm fw-semibold">
            <i class="bi bi-list-ul me-1"></i> Switch to List
        </a>
        <a href="{{ route('deals.create') }}" class="btn btn-sm btn-primary shadow-sm fw-semibold">
            <i class="bi bi-plus-lg me-1"></i> Add Deal
        </a>
    </div>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-2 px-3">
        <form method="GET" action="{{ route('deals.kanban') }}" class="row g-2 align-items-center">
            <div class="col-auto">
                <span class="small fw-semibold text-muted"><i class="bi bi-funnel me-1"></i> Filter by Rep:</span>
            </div>
            <div class="col-md-3">
                <select name="assigned_user_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Sales Representatives</option>
                    @foreach($users as $usr)
                        <option value="{{ $usr->id }}" {{ ($filters['assigned_user_id'] ?? '') == $usr->id ? 'selected' : '' }}>
                            {{ $usr->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @if(!empty($filters['assigned_user_id']))
                <div class="col-auto">
                    <a href="{{ route('deals.kanban') }}" class="btn btn-sm btn-light border text-muted">Clear Filter</a>
                </div>
            @endif
        </form>
    </div>
</div>

<!-- Kanban Columns Container -->
<div class="kanban-board">
    @foreach($stages as $stageKey => $stageData)
        <div class="kanban-col">
            <div class="kanban-col-header">
                <div>
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <span class="badge rounded-circle bg-{{ $stageData['meta']['color'] }}" style="width: 10px; height: 10px; padding: 0;">&nbsp;</span>
                        {{ $stageData['meta']['label'] }}
                    </h6>
                    <small class="text-muted">${{ number_format($stageData['total_amount'], 2) }}</small>
                </div>
                <span class="badge bg-white text-dark border">{{ $stageData['count'] }}</span>
            </div>

            <div class="kanban-card-container">
                @forelse($stageData['deals'] as $deal)
                    <div class="kanban-item">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <a href="{{ route('deals.show', $deal) }}" class="fw-bold text-dark text-decoration-none small text-truncate pe-2">
                                {{ $deal->title }}
                            </a>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light border-0 py-0 px-1 text-muted" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow border-0 small">
                                    <li><h6 class="dropdown-header">Move Stage To:</h6></li>
                                    @foreach(\App\Models\Deal::STAGES as $targetKey => $targetMeta)
                                        @if($targetKey !== $deal->stage)
                                            <li>
                                                <form method="POST" action="{{ route('deals.update-stage', $deal) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="stage" value="{{ $targetKey }}">
                                                    <button type="submit" class="dropdown-item py-1">
                                                        <i class="bi bi-arrow-right-short text-{{ $targetMeta['color'] }}"></i> {{ $targetMeta['label'] }}
                                                    </button>
                                                </form>
                                            </li>
                                        @endif
                                    @endforeach
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="{{ route('deals.show', $deal) }}"><i class="bi bi-eye me-1"></i> View Details</a></li>
                                </ul>
                            </div>
                        </div>

                        <div class="small text-muted mb-2 text-truncate">
                            <i class="bi bi-building me-1"></i> {{ $deal->company?->name ?? 'No company' }}
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                            <span class="fw-bold text-dark small">${{ number_format($deal->amount, 2) }}</span>
                            <div class="d-flex align-items-center gap-1">
                                <span class="badge bg-light text-muted border" style="font-size: 0.65rem;">{{ $deal->probability }}%</span>
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 22px; height: 22px; font-size: 0.65rem;" title="{{ $deal->assignedUser?->name }}">
                                    {{ substr($deal->assignedUser?->name ?? 'U', 0, 1) }}
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-3 text-center text-muted small border rounded bg-white">
                        No opportunities in this stage.
                    </div>
                @endforelse
            </div>
        </div>
    @endforeach
</div>
@endsection
