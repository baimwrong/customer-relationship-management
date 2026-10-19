<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConvertLeadRequest;
use App\Http\Requests\LeadRequest;
use App\Models\Company;
use App\Models\Lead;
use App\Models\User;
use App\Services\LeadConversionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeadController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'status', 'source', 'assigned_user_id']);

        $leads = Lead::query()
            ->with(['assignedUser', 'convertedCompany', 'convertedContact'])
            ->filter($filters)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $users = User::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('leads.index', [
            'leads' => $leads,
            'filters' => $filters,
            'users' => $users,
        ]);
    }

    public function create(): View
    {
        $users = User::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('leads.create', [
            'lead' => new Lead(['status' => Lead::STATUS_NEW, 'source' => 'website']),
            'users' => $users,
        ]);
    }

    public function store(LeadRequest $request): RedirectResponse
    {
        $data = $request->validated();
        if (empty($data['assigned_user_id'])) {
            $data['assigned_user_id'] = $request->user()->id;
        }

        $lead = Lead::create($data);

        return redirect()->route('leads.show', $lead)
            ->with('success', "Lead '{$lead->full_name}' was created successfully.");
    }

    public function show(Lead $lead): View
    {
        $lead->load([
            'assignedUser',
            'convertedCompany',
            'convertedContact',
            'convertedDeal',
            'activities.user',
            'tasks.assignedUser',
        ]);

        $users = User::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $companies = Company::orderBy('name')->get(['id', 'name']);

        return view('leads.show', [
            'lead' => $lead,
            'users' => $users,
            'companies' => $companies,
        ]);
    }

    public function edit(Lead $lead): View
    {
        $this->authorize('update', $lead);

        $users = User::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('leads.edit', [
            'lead' => $lead,
            'users' => $users,
        ]);
    }

    public function update(LeadRequest $request, Lead $lead): RedirectResponse
    {
        $this->authorize('update', $lead);

        $lead->update($request->validated());

        return redirect()->route('leads.show', $lead)
            ->with('success', "Lead '{$lead->full_name}' updated successfully.");
    }

    public function convert(ConvertLeadRequest $request, Lead $lead, LeadConversionService $converter): RedirectResponse
    {
        $this->authorize('convert', $lead);

        if ($lead->isConverted()) {
            return back()->with('error', 'This lead has already been converted.');
        }

        $result = $converter->convert($lead, $request->validated(), $request->user());

        if ($result['deal']) {
            return redirect()->route('deals.show', $result['deal'])
                ->with('success', "Lead successfully converted into Deal: {$result['deal']->title}!");
        }

        if ($result['company']) {
            return redirect()->route('companies.show', $result['company'])
                ->with('success', "Lead successfully converted into Company: {$result['company']->name}!");
        }

        return redirect()->route('contacts.show', $result['contact'])
            ->with('success', "Lead successfully converted into Contact: {$result['contact']->full_name}!");
    }

    public function destroy(Lead $lead): RedirectResponse
    {
        $this->authorize('delete', $lead);

        $name = $lead->full_name;
        $lead->delete();

        return redirect()->route('leads.index')
            ->with('success', "Lead '{$name}' deleted successfully.");
    }

    public function export(Request $request): StreamedResponse
    {
        $this->authorize('export', Lead::class);

        $filters = $request->only(['search', 'status', 'source', 'assigned_user_id']);
        $leadsQuery = Lead::query()->with('assignedUser')->filter($filters)->latest();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="leads_export_'.now()->format('Y-m-d_His').'.csv"',
        ];

        return response()->stream(function () use ($leadsQuery) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'First Name', 'Last Name', 'Company Name', 'Email', 'Phone', 'Job Title', 'Source', 'Status', 'Estimated Value', 'Score', 'Assigned Rep']);

            foreach ($leadsQuery->cursor() as $ld) {
                fputcsv($handle, [
                    $ld->id,
                    $this->sanitizeCsvCell($ld->first_name),
                    $this->sanitizeCsvCell($ld->last_name),
                    $this->sanitizeCsvCell($ld->company_name),
                    $this->sanitizeCsvCell($ld->email),
                    $this->sanitizeCsvCell($ld->phone),
                    $this->sanitizeCsvCell($ld->job_title),
                    $this->sanitizeCsvCell($ld->source),
                    $ld->status,
                    $ld->estimated_value,
                    $ld->score,
                    $this->sanitizeCsvCell($ld->assignedUser?->name),
                ]);
            }
            fclose($handle);
        }, 200, $headers);
    }

    private function sanitizeCsvCell(mixed $value): mixed
    {
        if (is_string($value) && in_array(substr($value, 0, 1), ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'".$value;
        }

        return $value;
    }
}
