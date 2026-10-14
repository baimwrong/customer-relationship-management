@extends('layouts.app')

@section('title', 'Edit Contact: ' . $contact->full_name)
@section('page_title', 'Edit Contact Person')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-pencil-square me-2 text-warning"></i> Edit Contact: {{ $contact->full_name }}</h5>
                <a href="{{ route('contacts.show', $contact) }}" class="btn btn-sm btn-outline-secondary">Cancel</a>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('contacts.update', $contact) }}">
                    @csrf
                    @method('PUT')

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">First Name <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name', $contact->first_name) }}" required>
                            @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Last Name <span class="text-danger">*</span></label>
                            <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror" value="{{ old('last_name', $contact->last_name) }}" required>
                            @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Associated Company</label>
                            <select name="company_id" class="form-select @error('company_id') is-invalid @enderror">
                                <option value="">-- No Company Attached --</option>
                                @foreach($companies as $comp)
                                    <option value="{{ $comp->id }}" {{ old('company_id', $contact->company_id) == $comp->id ? 'selected' : '' }}>
                                        {{ $comp->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('company_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="active" {{ old('status', $contact->status) === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="vip" {{ old('status', $contact->status) === 'vip' ? 'selected' : '' }}>VIP / Executive Stakeholder</option>
                                <option value="lead" {{ old('status', $contact->status) === 'lead' ? 'selected' : '' }}>Lead Contact</option>
                                <option value="inactive" {{ old('status', $contact->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Job Title</label>
                            <input type="text" name="job_title" class="form-control" value="{{ old('job_title', $contact->job_title) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Department</label>
                            <input type="text" name="department" class="form-control" value="{{ old('department', $contact->department) }}">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Email Address</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $contact->email) }}">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Office Phone</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $contact->phone) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Mobile Phone</label>
                            <input type="text" name="mobile" class="form-control" value="{{ old('mobile', $contact->mobile) }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Assigned Account Representative</label>
                        <select name="assigned_user_id" class="form-select">
                            @foreach($users as $usr)
                                <option value="{{ $usr->id }}" {{ old('assigned_user_id', $contact->assigned_user_id) == $usr->id ? 'selected' : '' }}>
                                    {{ $usr->name }} ({{ ucfirst(str_replace('_', ' ', $usr->role)) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold">Contact Notes</label>
                        <textarea name="notes" rows="3" class="form-control">{{ old('notes', $contact->notes) }}</textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('contacts.show', $contact) }}" class="btn btn-light border">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4 fw-semibold">
                            <i class="bi bi-check-lg me-1"></i> Update Contact
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
