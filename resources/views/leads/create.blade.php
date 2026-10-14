@extends('layouts.app')

@section('title', 'Create Lead')
@section('page_title', 'New Sales Prospect')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-funnel me-2 text-info"></i> Create Sales Lead</h5>
                <a href="{{ route('leads.index') }}" class="btn btn-sm btn-outline-secondary">Cancel</a>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('leads.store') }}">
                    @csrf

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">First Name <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name') }}" required placeholder="Jane">
                            @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Last Name <span class="text-danger">*</span></label>
                            <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror" value="{{ old('last_name') }}" required placeholder="Doe">
                            @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Company / Organization Name</label>
                            <input type="text" name="company_name" class="form-control" value="{{ old('company_name') }}" placeholder="Acme Global Inc.">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Job Title</label>
                            <input type="text" name="job_title" class="form-control" value="{{ old('job_title') }}" placeholder="VP of Procurement">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Email Address</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="jane.doe@example.com">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Phone Number</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="+1 555-0199">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Acquisition Source <span class="text-danger">*</span></label>
                            <select name="source" class="form-select @error('source') is-invalid @enderror" required>
                                @foreach(\App\Models\Lead::SOURCES as $key => $label)
                                    <option value="{{ $key }}" {{ old('source', 'website') === $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('source')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                @foreach(\App\Models\Lead::STATUSES as $key => $meta)
                                    <option value="{{ $key }}" {{ old('status', 'new') === $key ? 'selected' : '' }}>
                                        {{ $meta['label'] }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Lead Qualification Score (0-100)</label>
                            <input type="number" name="score" class="form-control" value="{{ old('score', 50) }}" min="0" max="100">
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Estimated Potential Deal Value ($)</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" name="estimated_value" class="form-control" value="{{ old('estimated_value', 0) }}" min="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Assigned Sales Representative</label>
                            <select name="assigned_user_id" class="form-select">
                                @foreach($users as $usr)
                                    <option value="{{ $usr->id }}" {{ old('assigned_user_id', Auth::id()) == $usr->id ? 'selected' : '' }}>
                                        {{ $usr->name }} ({{ ucfirst(str_replace('_', ' ', $usr->role)) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold">Prospect Notes &amp; Background</label>
                        <textarea name="notes" rows="4" class="form-control" placeholder="Context, how they reached out, pain points...">{{ old('notes') }}</textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('leads.index') }}" class="btn btn-light border">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4 fw-semibold">
                            <i class="bi bi-check-lg me-1"></i> Save Lead
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
