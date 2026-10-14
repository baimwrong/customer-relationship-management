@extends('layouts.app')

@section('title', 'Edit Company: ' . $company->name)
@section('page_title', 'Edit Company Account')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-pencil-square me-2 text-success"></i> Edit Company: {{ $company->name }}</h5>
                <a href="{{ route('companies.show', $company) }}" class="btn btn-sm btn-outline-secondary">Cancel</a>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('companies.update', $company) }}">
                    @csrf
                    @method('PUT')

                    <div class="row g-3 mb-3">
                        <div class="col-md-8">
                            <label class="form-label small fw-semibold">Company Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $company->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Industry Sector</label>
                            <input type="text" name="industry" class="form-control" value="{{ old('industry', $company->industry) }}">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Website URL</label>
                            <input type="url" name="website" class="form-control @error('website') is-invalid @enderror" value="{{ old('website', $company->website) }}">
                            @error('website')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Company Email</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $company->email) }}">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Primary Phone Number</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $company->phone) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Street Address</label>
                            <input type="text" name="address" class="form-control" value="{{ old('address', $company->address) }}">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">City</label>
                            <input type="text" name="city" class="form-control" value="{{ old('city', $company->city) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">State / Province</label>
                            <input type="text" name="state" class="form-control" value="{{ old('state', $company->state) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Country</label>
                            <input type="text" name="country" class="form-control" value="{{ old('country', $company->country) }}">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Annual Revenue ($)</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" name="annual_revenue" class="form-control" value="{{ old('annual_revenue', $company->annual_revenue) }}" min="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Employee Headcount</label>
                            <input type="number" name="employees_count" class="form-control" value="{{ old('employees_count', $company->employees_count) }}" min="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Account Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="active" {{ old('status', $company->status) === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="customer" {{ old('status', $company->status) === 'customer' ? 'selected' : '' }}>Customer</option>
                                <option value="prospect" {{ old('status', $company->status) === 'prospect' ? 'selected' : '' }}>Prospect</option>
                                <option value="partner" {{ old('status', $company->status) === 'partner' ? 'selected' : '' }}>Partner</option>
                                <option value="inactive" {{ old('status', $company->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Assigned Account Representative</label>
                        <select name="assigned_user_id" class="form-select">
                            @foreach($users as $usr)
                                <option value="{{ $usr->id }}" {{ old('assigned_user_id', $company->assigned_user_id) == $usr->id ? 'selected' : '' }}>
                                    {{ $usr->name }} ({{ ucfirst(str_replace('_', ' ', $usr->role)) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold">Account Notes</label>
                        <textarea name="notes" rows="3" class="form-control">{{ old('notes', $company->notes) }}</textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('companies.show', $company) }}" class="btn btn-light border">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4 fw-semibold">
                            <i class="bi bi-check-lg me-1"></i> Update Company
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
