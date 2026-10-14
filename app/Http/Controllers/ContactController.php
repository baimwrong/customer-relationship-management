<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Models\Company;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ContactController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'status', 'company_id', 'assigned_user_id']);

        $contacts = Contact::query()
            ->with(['company', 'assignedUser'])
            ->filter($filters)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $companies = Company::orderBy('name')->get(['id', 'name']);
        $users = User::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('contacts.index', [
            'contacts' => $contacts,
            'filters' => $filters,
            'companies' => $companies,
            'users' => $users,
        ]);
    }

    public function create(Request $request): View
    {
        $companies = Company::orderBy('name')->get(['id', 'name']);
        $users = User::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $preselectedCompanyId = $request->query('company_id');

        return view('contacts.create', [
            'contact' => new Contact(['company_id' => $preselectedCompanyId]),
            'companies' => $companies,
            'users' => $users,
        ]);
    }

    public function store(ContactRequest $request): RedirectResponse
    {
        $data = $request->validated();
        if (empty($data['assigned_user_id'])) {
            $data['assigned_user_id'] = $request->user()->id;
        }

        $contact = Contact::create($data);

        return redirect()->route('contacts.show', $contact)
            ->with('success', "Contact '{$contact->full_name}' was created successfully.");
    }

    public function show(Contact $contact): View
    {
        $contact->load([
            'company',
            'assignedUser',
            'deals' => fn ($q) => $q->latest(),
            'activities.user',
            'tasks.assignedUser',
        ]);

        $users = User::where('is_active', true)->orderBy('name')->get();

        return view('contacts.show', [
            'contact' => $contact,
            'users' => $users,
        ]);
    }

    public function edit(Contact $contact): View
    {
        $companies = Company::orderBy('name')->get(['id', 'name']);
        $users = User::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('contacts.edit', [
            'contact' => $contact,
            'companies' => $companies,
            'users' => $users,
        ]);
    }

    public function update(ContactRequest $request, Contact $contact): RedirectResponse
    {
        $contact->update($request->validated());

        return redirect()->route('contacts.show', $contact)
            ->with('success', "Contact '{$contact->full_name}' updated successfully.");
    }

    public function destroy(Contact $contact): RedirectResponse
    {
        $name = $contact->full_name;
        $contact->delete();

        return redirect()->route('contacts.index')
            ->with('success', "Contact '{$name}' deleted successfully.");
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $request->only(['search', 'status', 'company_id', 'assigned_user_id']);
        $contacts = Contact::query()->with(['company', 'assignedUser'])->filter($filters)->latest()->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="contacts_export_'.now()->format('Y-m-d_His').'.csv"',
        ];

        return response()->stream(function () use ($contacts) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'First Name', 'Last Name', 'Email', 'Phone', 'Mobile', 'Job Title', 'Department', 'Company', 'Status', 'Assigned Rep']);

            foreach ($contacts as $ct) {
                fputcsv($handle, [
                    $ct->id,
                    $ct->first_name,
                    $ct->last_name,
                    $ct->email,
                    $ct->phone,
                    $ct->mobile,
                    $ct->job_title,
                    $ct->department,
                    $ct->company?->name,
                    $ct->status,
                    $ct->assignedUser?->name,
                ]);
            }
            fclose($handle);
        }, 200, $headers);
    }
}
