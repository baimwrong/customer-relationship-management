@extends('layouts.app')

@section('title', 'Action Tasks')
@section('page_title', 'Tasks Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1 text-dark">Action Tasks &amp; Reminders</h3>
        <p class="text-muted small mb-0">Follow-up deadlines, procurement tasks, and sales deliverables.</p>
    </div>
    <div>
        <button type="button" class="btn btn-sm btn-primary shadow-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#quickTaskModal">
            <i class="bi bi-plus-lg me-1"></i> Schedule Task
        </button>
    </div>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('tasks.index') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted">Search Task Title</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Title..." value="{{ $filters['search'] ?? '' }}">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    @foreach(\App\Models\Task::STATUSES as $key => $meta)
                        <option value="{{ $key }}" {{ ($filters['status'] ?? '') === $key ? 'selected' : '' }}>
                            {{ $meta['label'] }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted">Priority</label>
                <select name="priority" class="form-select form-select-sm">
                    <option value="">All Priorities</option>
                    @foreach(\App\Models\Task::PRIORITIES as $key => $meta)
                        <option value="{{ $key }}" {{ ($filters['priority'] ?? '') === $key ? 'selected' : '' }}>
                            {{ $meta['label'] }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary w-100 fw-semibold">
                    <i class="bi bi-filter me-1"></i> Filter
                </button>
                <a href="{{ route('tasks.index') }}" class="btn btn-sm btn-light border" title="Reset">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Tasks Table / List -->
<div class="card border-0 shadow-sm">
    <div class="list-group list-group-flush">
        @forelse($tasks as $task)
            <div class="list-group-item d-flex align-items-center justify-content-between p-3 flex-wrap gap-2 {{ $task->status === 'completed' ? 'bg-light' : '' }}">
                <div class="d-flex align-items-start gap-3">
                    <form method="POST" action="{{ route('tasks.toggle', $task) }}" class="mt-1">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-sm btn-outline-secondary rounded-circle p-1" style="width: 28px; height: 28px;" title="{{ $task->status === 'completed' ? 'Reopen Task' : 'Mark Completed' }}">
                            <i class="bi {{ $task->status === 'completed' ? 'bi-check-circle-fill text-success' : 'bi-circle' }} fs-6"></i>
                        </button>
                    </form>
                    <div>
                        <h6 class="fw-bold mb-1 {{ $task->status === 'completed' ? 'text-decoration-line-through text-muted' : 'text-dark' }}">
                            {{ $task->title }}
                        </h6>
                        @if($task->description)
                            <p class="text-secondary small mb-1">{{ $task->description }}</p>
                        @endif
                        <div class="small text-muted d-flex align-items-center gap-2 flex-wrap">
                            @php
                                $isOverdue = $task->due_date && $task->due_date->isPast() && $task->status !== 'completed';
                            @endphp
                            <span class="{{ $isOverdue ? 'text-danger fw-bold' : '' }}">
                                <i class="bi bi-clock me-1"></i> Due: {{ $task->due_date ? $task->due_date->format('M d, Y') : 'No due date' }}
                                @if($isOverdue) (Overdue) @endif
                            </span>
                            &bull; <span>Assigned: <strong>{{ $task->assignedUser?->name }}</strong></span>
                            @if($task->company)
                                &bull; <a href="{{ route('companies.show', $task->company) }}" class="text-decoration-none text-muted"><i class="bi bi-building me-1"></i>{{ $task->company->name }}</a>
                            @endif
                            @if($task->deal)
                                &bull; <a href="{{ route('deals.show', $task->deal) }}" class="text-decoration-none text-muted"><i class="bi bi-briefcase me-1"></i>{{ $task->deal->title }}</a>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2 ms-auto">
                    <span class="badge bg-{{ $task->priority_meta['color'] }}-subtle text-{{ $task->priority_meta['color'] }} text-uppercase" style="font-size: 0.7rem;">
                        {{ $task->priority_meta['label'] }} Priority
                    </span>
                    <span class="badge bg-light text-dark border text-capitalize" style="font-size: 0.7rem;">
                        {{ $task->status }}
                    </span>
                    <form method="POST" action="{{ route('tasks.destroy', $task) }}" onsubmit="return confirm('Delete this task?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-link text-danger p-1" title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="p-4 text-center text-muted">
                <i class="bi bi-check2-circle fs-2 d-block text-secondary mb-2"></i>
                No tasks found.
            </div>
        @endforelse
    </div>
    @if($tasks->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $tasks->links() }}
        </div>
    @endif
</div>
@endsection
