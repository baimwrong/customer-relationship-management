@extends('layouts.app')

@section('title', 'Create Deal')
@section('page_title', 'New Deal Opportunity')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-briefcase me-2 text-primary"></i> Create Deal / Opportunity</h5>
                <a href="{{ route('deals.index') }}" class="btn btn-sm btn-outline-secondary">Cancel</a>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('deals.store') }}">
                    @csrf

                    <div class="row g-3 mb-3">
                        <div class="col-md-8">
                            <label class="form-label small fw-semibold">Opportunity Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required placeholder="e.g. Acme Corp - Cloud ERP Implementation">
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Deal Amount ($) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" name="amount" class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount', 0) }}" required placeholder="0.00">
                            </div>
                            @error('amount')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Associated Company / Account</label>
                            <select name="company_id" class="form-select @error('company_id') is-invalid @enderror">
                                <option value="">-- Select Company (Optional) --</option>
                                @foreach($companies as $comp)
                                    <option value="{{ $comp->id }}" {{ old('company_id', $deal->company_id) == $comp->id ? 'selected' : '' }}>
                                        {{ $comp->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('company_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Primary Contact</label>
                            <select name="contact_id" class="form-select @error('contact_id') is-invalid @enderror">
                                <option value="">-- Select Contact (Optional) --</option>
                                @foreach($contacts as $ct)
                                    <option value="{{ $ct->id }}" {{ old('contact_id', $deal->contact_id) == $ct->id ? 'selected' : '' }}>
                                        {{ $ct->first_name }} {{ $ct->last_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('contact_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Pipeline Stage <span class="text-danger">*</span></label>
                            <select name="stage" class="form-select @error('stage') is-invalid @enderror" required id="dealStageSelect">
                                @foreach(\App\Models\Deal::STAGES as $key => $meta)
                                    <option value="{{ $key }}" data-prob="{{ $meta['probability'] }}" {{ old('stage', $deal->stage) === $key ? 'selected' : '' }}>
                                        {{ $meta['label'] }} ({{ $meta['probability'] }}%)
                                    </option>
                                @endforeach
                            </select>
                            @error('stage')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Probability (%)</label>
                            <input type="number" name="probability" id="probabilityInput" class="form-control @error('probability') is-invalid @enderror" value="{{ old('probability', $deal->probability) }}" min="0" max="100">
                            @error('probability')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Expected Close Date</label>
                            <input type="date" name="expected_close_date" class="form-control @error('expected_close_date') is-invalid @enderror" value="{{ old('expected_close_date', now()->addMonth()->format('Y-m-d')) }}">
                            @error('expected_close_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Deal Type <span class="text-danger">*</span></label>
                            <select name="deal_type" class="form-select @error('deal_type') is-invalid @enderror" required>
                                <option value="new_business" {{ old('deal_type') === 'new_business' ? 'selected' : '' }}>New Business</option>
                                <option value="existing_business" {{ old('deal_type') === 'existing_business' ? 'selected' : '' }}>Existing Business / Expansion</option>
                                <option value="renewal" {{ old('deal_type') === 'renewal' ? 'selected' : '' }}>Renewal Contract</option>
                            </select>
                            @error('deal_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Assigned Sales Representative</label>
                            <select name="assigned_user_id" class="form-select @error('assigned_user_id') is-invalid @enderror">
                                @foreach($users as $usr)
                                    <option value="{{ $usr->id }}" {{ old('assigned_user_id', Auth::id()) == $usr->id ? 'selected' : '' }}>
                                        {{ $usr->name }} ({{ ucfirst(str_replace('_', ' ', $usr->role)) }})
                                    </option>
                                @endforeach
                            </select>
                            @error('assigned_user_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold">Deal Notes &amp; Scope Summary</label>
                        <textarea name="notes" rows="4" class="form-control" placeholder="Key deliverables, buyer requirements, budget discussions...">{{ old('notes') }}</textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('deals.index') }}" class="btn btn-light border">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4 fw-semibold">
                            <i class="bi bi-check-lg me-1"></i> Save Deal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('dealStageSelect')?.addEventListener('change', function() {
    const selected = this.options[this.selectedIndex];
    const prob = selected.getAttribute('data-prob');
    if (prob !== null) {
        document.getElementById('probabilityInput').value = prob;
    }
});
</script>
@endpush
@endsection
