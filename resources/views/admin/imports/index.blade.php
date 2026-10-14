@extends('layouts.app')

@section('title', 'Admin Bulk Data Import')
@section('page_title', 'Bulk Data Import Center')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1 text-dark">Enterprise Bulk Data Import Center</h3>
        <p class="text-muted small mb-0">High-performance CSV bulk data ingestion with automated validation, mapping, and audit trails.</p>
    </div>
</div>

<!-- Import Statistics Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3 text-center">
                <span class="text-muted small fw-semibold text-uppercase">Total Batches Executed</span>
                <h3 class="fw-bold text-dark mt-1 mb-0">{{ number_format($stats['total_batches']) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3 text-center">
                <span class="text-muted small fw-semibold text-uppercase">Records Ingested Successfully</span>
                <h3 class="fw-bold text-success mt-1 mb-0">{{ number_format($stats['total_rows_imported']) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3 text-center">
                <span class="text-muted small fw-semibold text-uppercase">Total Ingestion Errors</span>
                <h3 class="fw-bold text-danger mt-1 mb-0">{{ number_format($stats['total_errors']) }}</h3>
            </div>
        </div>
    </div>
</div>

<!-- Import Action Center -->
<div class="row g-4 mb-4">
    <!-- Left: Upload Form -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-cloud-arrow-up me-2 text-primary"></i> Upload CSV Dataset</h6>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('admin.imports.process') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark">Target Entity Type <span class="text-danger">*</span></label>
                        <select name="entity_type" id="entityTypeSelector" class="form-select @error('entity_type') is-invalid @enderror" required onchange="updateTemplatePreview()">
                            <option value="leads">Sales Leads (Leads Dataset)</option>
                            <option value="contacts">Contacts (Decision Makers &amp; People)</option>
                            <option value="companies">Companies (Accounts &amp; Organizations)</option>
                            <option value="deals">Opportunities &amp; Deals (Pipeline Data)</option>
                        </select>
                        @error('entity_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold text-dark">Select CSV File <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control @error('file') is-invalid @enderror" accept=".csv,text/csv,text/plain" required>
                        <small class="text-muted d-block mt-1">Accepts UTF-8 standard comma or semicolon delimited CSV files (Max: 10MB).</small>
                        @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                        <span class="small text-muted"><i class="bi bi-shield-check me-1 text-success"></i> Transactional Safe Processing</span>
                        <button type="submit" class="btn btn-primary px-4 fw-semibold shadow-sm">
                            <i class="bi bi-upload me-1"></i> Start Bulk Ingestion
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Right: Sample CSV Template Downloads -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-filetype-csv me-2 text-success"></i> Download Sample CSV Templates</h6>
            </div>
            <div class="card-body p-4">
                <p class="small text-muted mb-3">
                    Download pre-formatted template files with header definitions and dummy rows to prepare your enterprise data:
                </p>

                <div class="d-grid gap-2 mb-3">
                    <a href="{{ route('admin.imports.template', 'leads') }}" class="btn btn-sm btn-outline-primary d-flex align-items-center justify-content-between p-2">
                        <span><i class="bi bi-funnel me-2"></i> Sample Leads Template</span>
                        <i class="bi bi-download"></i>
                    </a>
                    <a href="{{ route('admin.imports.template', 'contacts') }}" class="btn btn-sm btn-outline-info d-flex align-items-center justify-content-between p-2">
                        <span><i class="bi bi-person-lines-fill me-2"></i> Sample Contacts Template</span>
                        <i class="bi bi-download"></i>
                    </a>
                    <a href="{{ route('admin.imports.template', 'companies') }}" class="btn btn-sm btn-outline-success d-flex align-items-center justify-content-between p-2">
                        <span><i class="bi bi-building me-2"></i> Sample Companies Template</span>
                        <i class="bi bi-download"></i>
                    </a>
                    <a href="{{ route('admin.imports.template', 'deals') }}" class="btn btn-sm btn-outline-warning d-flex align-items-center justify-content-between p-2">
                        <span><i class="bi bi-briefcase me-2"></i> Sample Deals Template</span>
                        <i class="bi bi-download"></i>
                    </a>
                </div>

                <div class="bg-light p-3 rounded small text-muted">
                    <strong>Tip:</strong> Headers are auto-mapped (e.g. <code>First Name</code> or <code>first_name</code>). Blank rows are ignored.
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Past Bulk Import Audit History -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-clock-history me-2 text-secondary"></i> Bulk Import Audit Trail</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Batch ID</th>
                    <th>Entity Type</th>
                    <th>Filename</th>
                    <th>Uploaded By</th>
                    <th class="text-center">Total Rows</th>
                    <th class="text-center">Success</th>
                    <th class="text-center">Errors</th>
                    <th>Status</th>
                    <th>Timestamp</th>
                    <th class="text-end">Details</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentImports as $import)
                    <tr>
                        <td class="fw-bold text-dark">#{{ $import->id }}</td>
                        <td>
                            <span class="badge bg-light text-dark border text-uppercase" style="font-size: 0.7rem;">
                                {{ $import->entity_type }}
                            </span>
                        </td>
                        <td class="small fw-semibold text-truncate" style="max-width: 180px;" title="{{ $import->filename }}">
                            {{ $import->filename }}
                        </td>
                        <td class="small text-muted">{{ $import->user?->name }}</td>
                        <td class="text-center fw-semibold text-dark">{{ $import->total_rows }}</td>
                        <td class="text-center text-success fw-bold">{{ $import->success_count }}</td>
                        <td class="text-center text-danger fw-bold">{{ $import->error_count }}</td>
                        <td>
                            <span class="badge bg-{{ $import->status === 'completed' ? 'success' : ($import->status === 'partial' ? 'warning' : 'danger') }}-subtle text-{{ $import->status === 'completed' ? 'success' : ($import->status === 'partial' ? 'warning' : 'danger') }} text-capitalize">
                                {{ $import->status }}
                            </span>
                        </td>
                        <td class="small text-muted">{{ $import->created_at->format('M d, Y H:i') }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.imports.show', $import) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-journal-text me-1"></i> Report
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox fs-2 d-block text-secondary mb-2"></i>
                            No bulk import batches recorded yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($recentImports->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $recentImports->links() }}
        </div>
    @endif
</div>
@endsection
