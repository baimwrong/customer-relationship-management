@extends('layouts.guest')

@section('title', 'Sign In')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-3 p-3 shadow-sm mb-3">
                <i class="bi bi-buildings-fill fs-2"></i>
            </div>
            <h3 class="fw-bold text-dark">Enterprise CRM</h3>
            <p class="text-muted small">Production-ready Customer Relationship Management Platform</p>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-4 p-md-5">
                <h4 class="card-title fw-bold text-dark mb-4 text-center">Account Sign In</h4>

                @if (session('info'))
                    <div class="alert alert-info alert-dismissible fade show small" role="alert">
                        <i class="bi bi-info-circle me-1"></i> {{ session('info') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show small" role="alert">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form method="POST" action="{{ route('login.submit') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label small fw-semibold text-secondary">Work Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
                            <input id="email" type="email" name="email" value="{{ old('email', 'admin@crm.test') }}" required autofocus
                                   class="form-control border-start-0 @error('email') is-invalid @enderror" placeholder="name@company.com">
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <label for="password" class="form-label small fw-semibold text-secondary mb-0">Password</label>
                        </div>
                        <div class="input-group mt-1">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-lock"></i></span>
                            <input id="password" type="password" name="password" value="password" required
                                   class="form-control border-start-0 @error('password') is-invalid @enderror" placeholder="••••••••">
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" checked>
                            <label class="form-check-label small text-muted" for="remember">
                                Keep me signed in
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold shadow-sm">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Sign In to CRM
                    </button>
                </form>

                <hr class="my-4 text-muted">

                <div>
                    <p class="text-center small text-muted fw-semibold mb-2">Quick Demo Credentials (One-Click):</p>
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-sm btn-outline-primary text-start" onclick="fillCredentials('admin@crm.test', 'password')">
                            <i class="bi bi-shield-lock-fill me-1"></i> <strong>Admin:</strong> admin@crm.test (Full access & Bulk Import)
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-info text-start" onclick="fillCredentials('manager@crm.test', 'password')">
                            <i class="bi bi-person-badge-fill me-1"></i> <strong>Sales Manager:</strong> manager@crm.test (Team & Pipeline)
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary text-start" onclick="fillCredentials('rep1@crm.test', 'password')">
                            <i class="bi bi-person-fill me-1"></i> <strong>Sales Rep:</strong> rep1@crm.test (Deals & Activities)
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mt-4">
            <small class="text-muted">
                Running locally &bull; Powered by Laravel 13 &amp; PostgreSQL 18 &bull; Offline Ready
            </small>
        </div>
    </div>
</div>

<script>
function fillCredentials(email, password) {
    document.getElementById('email').value = email;
    document.getElementById('password').value = password;
}
</script>
@endsection
