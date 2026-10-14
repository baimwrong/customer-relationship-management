@extends('layouts.app')

@section('title', 'Import Batch #' . $import->id)
@section('page_title', 'Import Batch Inspection')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1 text-dark">Batch Inspection #{{ $import->id }}</h3>
        <p class="text-muted small mb-0">Detailed results and diagnostic log for bulk ingestion batch.</p>
    </div>
    <div>
        <a href="{{ route('admin.imports.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Import Center
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-info-circle me-2 text-primary"></i> Batch Metadata</h6>
            </div>
            <div class="card-body p-3">
                <div class="mb-3">
                    <span class="small text-muted d-block">Entity Type</span>
                    <span class="badge bg-light text-dark border text-uppercase">{{ $import->entity_type }}</span>
                </div>
                <div class="mb-3">
                    <span class="small text-muted d-block">Source Filename</span>
                    <strong class="text-dark">{{ $import->filename }}</strong>
                </div>
                <div class="mb-3">
                    <span class="small text-muted d-block">Executed By</span>
                    <span>{{ $import->user?->name }}</span>
                </div>
                <div class="mb-3">
                    <span class="small text-muted d-block">Execution Date</span>
                    <span>{{ $import->created_at->format('M d, Y H:i:s') }}</span>
                </div>
                <div class="mb-0">
                    <span class="small text-muted d-block">Final Batch Status</span>
                    <span class="badge bg-{{ $import->status === 'completed' ? 'success' : 'warning' }}-subtle text-{{ $import->status === 'completed' ? 'success' : 'warning' }} text-capitalize">
                        {{ $import->status }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <div class="row text-center g-3">
                    <div class="col-4 border-end">
                        <span class="text-muted small fw-semibold text-uppercase">Total Rows Processed</span>
                        <h3 class="fw-bold text-dark mt-1 mb-0">{{ number_format($import->total_rows) }}</h3>
                    </div>
                    <div class="col-4 border-end">
                        <span class="text-muted small fw-semibold text-uppercase">Records Ingested</span>
                        <h3 class="fw-bold text-success mt-1 mb-0">{{ number_format($import->success_count) }}</h3>
                    </div>
                    <div class="col-4">
                        <span class="text-muted small fw-semibold text-uppercase">Rejected Rows</span>
                        <h3 class="fw-bold text-danger mt-1 mb-0">{{ number_format($import->error_count) }}</h3>
                    </div>
                </div>

                @if($import->summary)
                    <div class="mt-4 p-3 bg-light rounded text-secondary small">
                        <strong>Summary:</strong> {{ $import->summary }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Diagnostic Error Log -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-exclamation-triangle me-2 text-danger"></i> Row-by-Row Diagnostic Error Log</h6>
                <span class="badge bg-danger-subtle text-danger">{{ $import->error_count }} Issues</span>
            </div>
            <div class="card-body p-3">
                @if(!empty($import->errors) && count($import->errors) > 0)
                    <div class="table-responsive" style="max-height: 380px;">
                        <table class="table table-sm table-striped align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Diagnostic Issue Message</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($import->errors as $idx => $err)
                                    <tr>
                                        <td class="text-muted small" style="width: 50px;">{{ $idx + 1 }}</td>
                                        <td class="text-danger small font-monospace">{{ $err }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-4 text-center text-success">
                        <i class="bi bi-check-circle-fill fs-3 d-block mb-2"></i>
                        No validation errors occurred during this ingestion! 100% of rows were imported cleanly.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
