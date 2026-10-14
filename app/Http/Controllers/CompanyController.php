<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompanyRequest;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CompanyController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'status', 'industry', 'assigned_user_id']);

        $companies = Company::query()
            ->with(['assignedUser'])
            ->withCount(['contacts', 'deals'])
            ->filter($filters)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $users = User::where('is_active', true)->orderBy('name')->get();
        $industries = Company::distinct()->whereNotNull('industry')->pluck('industry');

        return view('companies.index', [
            'companies' => $companies,
            'filters' => $filters,
            'users' => $users,
            'industries' => $industries,
        ]);
    }

    public function create(): View
    {
        $users = User::where('is_active', true)->orderBy('name')->get();

        return view('companies.create', [
            'company' => new Company,
            'users' => $users,
        ]);
    }

    public function store(CompanyRequest $request): RedirectResponse
    {
        $data = $request->validated();
        if (empty($data['assigned_user_id'])) {
            $data['assigned_user_id'] = $request->user()->id;
        }

        $company = Company::create($data);

        return redirect()->route('companies.show', $company)
            ->with('success', "Company '{$company->name}' was created successfully.");
    }

    public function show(Company $company): View
    {
        $company->load([
            'assignedUser',
            'contacts' => fn ($q) => $q->latest(),
            'deals' => fn ($q) => $q->latest(),
            'activities.user',
            'tasks.assignedUser',
        ]);

        $users = User::where('is_active', true)->orderBy('name')->get();

        return view('companies.show', [
            'company' => $company,
            'users' => $users,
        ]);
    }

    public function edit(Company $company): View
    {
        $users = User::where('is_active', true)->orderBy('name')->get();

        return view('companies.edit', [
            'company' => $company,
            'users' => $users,
        ]);
    }

    public function update(CompanyRequest $request, Company $company): RedirectResponse
    {
        $company->update($request->validated());

        return redirect()->route('companies.show', $company)
            ->with('success', "Company '{$company->name}' updated successfully.");
    }

    public function destroy(Company $company): RedirectResponse
    {
        $name = $company->name;
        $company->delete();

        return redirect()->route('companies.index')
            ->with('success', "Company '{$name}' deleted successfully.");
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $request->only(['search', 'status', 'industry', 'assigned_user_id']);
        $companies = Company::query()->with('assignedUser')->filter($filters)->latest()->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="companies_export_'.now()->format('Y-m-d_His').'.csv"',
        ];

        return response()->stream(function () use ($companies) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Name', 'Industry', 'Website', 'Email', 'Phone', 'City', 'Country', 'Annual Revenue', 'Employees', 'Status', 'Assigned Rep']);

            foreach ($companies as $comp) {
                fputcsv($handle, [
                    $comp->id,
                    $comp->name,
                    $comp->industry,
                    $comp->website,
                    $comp->email,
                    $comp->phone,
                    $comp->city,
                    $comp->country,
                    $comp->annual_revenue,
                    $comp->employees_count,
                    $comp->status,
                    $comp->assignedUser?->name,
                ]);
            }
            fclose($handle);
        }, 200, $headers);
    }
}
