<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\BulkImportRequest;
use App\Models\BulkImport;
use App\Services\BulkImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

class BulkImportController extends Controller
{
    public function index(): View
    {
        $recentImports = BulkImport::with('user')
            ->latest()
            ->paginate(10);

        $stats = [
            'total_batches' => BulkImport::count(),
            'total_rows_imported' => (int) BulkImport::sum('success_count'),
            'total_errors' => (int) BulkImport::sum('error_count'),
        ];

        return view('admin.imports.index', [
            'recentImports' => $recentImports,
            'stats' => $stats,
        ]);
    }

    public function import(BulkImportRequest $request, BulkImportService $importService): RedirectResponse
    {
        $entityType = $request->validated('entity_type');
        $file = $request->file('file');

        try {
            $result = $importService->import($file, $entityType, $request->user());

            if ($result['errors_count'] > 0) {
                return redirect()->route('admin.imports.index')
                    ->with('warning', "Import completed with warnings: {$result['imported']} records created, {$result['errors_count']} rows had issues.")
                    ->with('import_result', $result);
            }

            return redirect()->route('admin.imports.index')
                ->with('success', "Bulk import successful! Created {$result['imported']} {$entityType} records.")
                ->with('import_result', $result);
        } catch (\Throwable $e) {
            return redirect()->route('admin.imports.index')
                ->with('error', 'Import failed: '.$e->getMessage());
        }
    }

    public function downloadTemplate(string $type, BulkImportService $importService): Response
    {
        if (! in_array($type, ['leads', 'contacts', 'companies', 'deals'], true)) {
            abort(404, 'Invalid template type');
        }

        $content = $importService->generateTemplate($type);

        return response($content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"sample_{$type}_template.csv\"",
        ]);
    }

    public function show(BulkImport $bulkImport): View
    {
        $bulkImport->load('user');

        return view('admin.imports.show', [
            'import' => $bulkImport,
        ]);
    }
}
