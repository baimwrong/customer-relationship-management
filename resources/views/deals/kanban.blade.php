@extends('layouts.app')

@section('title', 'Pipeline Kanban Board')
@section('page_title', 'Pipeline Kanban')

@push('styles')
<style>
.kanban-item {
    cursor: grab;
    transition: transform 0.15s ease, box-shadow 0.15s ease, opacity 0.15s ease;
}
.kanban-item:active {
    cursor: grabbing;
}
.kanban-item.dragging {
    opacity: 0.4;
    transform: scale(0.98);
}
.kanban-card-container.drag-over {
    background-color: rgba(59, 130, 246, 0.08);
    border: 2px dashed #3b82f6 !important;
    border-radius: 8px;
}
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1 text-dark">Visual Pipeline Kanban</h3>
        <p class="text-muted small mb-0">Drag and drop opportunities to advance through sales lifecycle stages.</p>
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

<!-- Toast Notification Container -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1080">
    <div id="kanbanToast" class="toast align-items-center text-white bg-dark border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center gap-2" id="kanbanToastBody">
                <i class="bi bi-check-circle-fill text-success"></i>
                <span id="kanbanToastMessage">Stage updated successfully</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<!-- Kanban Columns Container -->
<div class="kanban-board">
    @foreach($stages as $stageKey => $stageData)
        <div class="kanban-col" data-col-stage="{{ $stageKey }}">
            <div class="kanban-col-header">
                <div>
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <span class="badge rounded-circle bg-{{ $stageData['meta']['color'] }}" style="width: 10px; height: 10px; padding: 0;">&nbsp;</span>
                        {{ $stageData['meta']['label'] }}
                    </h6>
                    <small class="text-muted" id="stage-amount-{{ $stageKey }}" data-raw-amount="{{ $stageData['total_amount'] }}">
                        ${{ number_format($stageData['total_amount'], 2) }}
                    </small>
                </div>
                <span class="badge bg-white text-dark border" id="stage-count-{{ $stageKey }}">{{ $stageData['count'] }}</span>
            </div>

            <div class="kanban-card-container" data-stage="{{ $stageKey }}">
                @forelse($stageData['deals'] as $deal)
                    <div class="kanban-item"
                        draggable="true"
                        data-deal-id="{{ $deal->id }}"
                        data-deal-amount="{{ $deal->amount }}"
                        data-current-stage="{{ $deal->stage }}">
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
                    <div class="p-3 text-center text-muted small border rounded bg-white empty-placeholder">
                        No opportunities in this stage.
                    </div>
                @endforelse
            </div>
        </div>
    @endforeach
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    let draggedItem = null;
    let sourceStage = null;
    let dealId = null;
    let dealAmount = 0;

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const toastEl = document.getElementById('kanbanToast');
    const toast = toastEl ? new bootstrap.Toast(toastEl) : null;

    function showToast(message, isSuccess = true) {
        if (!toast) return;
        const body = document.getElementById('kanbanToastBody');
        body.innerHTML = (isSuccess
            ? '<i class="bi bi-check-circle-fill text-success fs-5"></i> '
            : '<i class="bi bi-exclamation-triangle-fill text-danger fs-5"></i> ') + message;
        toast.show();
    }

    function recalculateColumn(stage) {
        const container = document.querySelector(`.kanban-card-container[data-stage="${stage}"]`);
        if (!container) return;

        const items = container.querySelectorAll('.kanban-item');
        const count = items.length;
        let sum = 0;

        items.forEach(item => {
            sum += parseFloat(item.getAttribute('data-deal-amount') || 0);
        });

        const countBadge = document.getElementById(`stage-count-${stage}`);
        if (countBadge) countBadge.textContent = count;

        const amountEl = document.getElementById(`stage-amount-${stage}`);
        if (amountEl) {
            amountEl.setAttribute('data-raw-amount', sum);
            amountEl.textContent = '$' + sum.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        const placeholder = container.querySelector('.empty-placeholder');
        if (count === 0 && !placeholder) {
            const emptyDiv = document.createElement('div');
            emptyDiv.className = 'p-3 text-center text-muted small border rounded bg-white empty-placeholder';
            emptyDiv.textContent = 'No opportunities in this stage.';
            container.appendChild(emptyDiv);
        } else if (count > 0 && placeholder) {
            placeholder.remove();
        }
    }

    document.querySelectorAll('.kanban-item').forEach(item => {
        item.addEventListener('dragstart', function (e) {
            draggedItem = this;
            sourceStage = this.getAttribute('data-current-stage');
            dealId = this.getAttribute('data-deal-id');
            dealAmount = parseFloat(this.getAttribute('data-deal-amount') || 0);

            this.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', dealId);
        });

        item.addEventListener('dragend', function () {
            this.classList.remove('dragging');
            document.querySelectorAll('.kanban-card-container').forEach(c => c.classList.remove('drag-over'));
        });
    });

    document.querySelectorAll('.kanban-card-container').forEach(container => {
        container.addEventListener('dragover', function (e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            this.classList.add('drag-over');
        });

        container.addEventListener('dragleave', function () {
            this.classList.remove('drag-over');
        });

        container.addEventListener('drop', function (e) {
            e.preventDefault();
            this.classList.remove('drag-over');

            if (!draggedItem) return;

            const targetStage = this.getAttribute('data-stage');
            if (targetStage === sourceStage) return;

            const originalParent = draggedItem.parentElement;
            const originalStage = sourceStage;

            // Remove placeholder if present
            const placeholder = this.querySelector('.empty-placeholder');
            if (placeholder) placeholder.remove();

            // Optimistically update DOM
            this.appendChild(draggedItem);
            draggedItem.setAttribute('data-current-stage', targetStage);

            recalculateColumn(originalStage);
            recalculateColumn(targetStage);

            // Send AJAX PATCH to backend
            fetch(`/deals/${dealId}/stage`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ stage: targetStage })
            })
            .then(res => {
                if (!res.ok) {
                    throw new Error('Failed to update stage on server.');
                }
                return res.json();
            })
            .then(data => {
                showToast(`Deal moved to ${targetStage.replace('_', ' ')}!`, true);
            })
            .catch(err => {
                // Revert on failure
                originalParent.appendChild(draggedItem);
                draggedItem.setAttribute('data-current-stage', originalStage);
                recalculateColumn(originalStage);
                recalculateColumn(targetStage);
                showToast(err.message || 'Error updating deal stage', false);
            });
        });
    });
});
</script>
@endpush
@endsection
