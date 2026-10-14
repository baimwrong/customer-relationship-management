<?php

namespace App\Http\Controllers;

use App\Http\Requests\DealRequest;
use App\Models\Activity;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DealController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'stage', 'assigned_user_id', 'company_id']);

        $deals = Deal::query()
            ->with(['company', 'contact', 'assignedUser'])
            ->filter($filters)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $users = User::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $companies = Company::orderBy('name')->get(['id', 'name']);

        $totalOpenAmount = Deal::open()->sum('amount');
        $totalWonAmount = Deal::won()->sum('amount');

        return view('deals.index', [
            'deals' => $deals,
            'filters' => $filters,
            'users' => $users,
            'companies' => $companies,
            'totalOpenAmount' => $totalOpenAmount,
            'totalWonAmount' => $totalWonAmount,
        ]);
    }

    public function kanban(Request $request): View
    {
        $filters = $request->only(['assigned_user_id', 'company_id']);

        $stages = [];
        foreach (Deal::STAGES as $stageKey => $meta) {
            $query = Deal::query()
                ->where('stage', $stageKey)
                ->with(['company', 'contact', 'assignedUser'])
                ->latest();

            if (! empty($filters['assigned_user_id'])) {
                $query->where('assigned_user_id', $filters['assigned_user_id']);
            }
            if (! empty($filters['company_id'])) {
                $query->where('company_id', $filters['company_id']);
            }

            $deals = $query->get();

            $stages[$stageKey] = [
                'meta' => $meta,
                'deals' => $deals,
                'count' => $deals->count(),
                'total_amount' => $deals->sum('amount'),
            ];
        }

        $users = User::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $companies = Company::orderBy('name')->get(['id', 'name']);

        return view('deals.kanban', [
            'stages' => $stages,
            'filters' => $filters,
            'users' => $users,
            'companies' => $companies,
        ]);
    }

    public function updateStage(Request $request, Deal $deal): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'stage' => ['required', 'string', 'in:'.implode(',', array_keys(Deal::STAGES))],
            'loss_reason' => ['nullable', 'string', 'max:255'],
        ]);

        $newStage = $validated['stage'];
        $oldStage = $deal->stage;
        $probability = Deal::STAGES[$newStage]['probability'] ?? $deal->probability;

        $updates = [
            'stage' => $newStage,
            'probability' => $probability,
        ];

        if ($newStage === Deal::STAGE_CLOSED_WON) {
            $updates['closed_at'] = now();
            $updates['loss_reason'] = null;
        } elseif ($newStage === Deal::STAGE_CLOSED_LOST) {
            $updates['closed_at'] = now();
            $updates['loss_reason'] = $validated['loss_reason'] ?? $deal->loss_reason;
        } else {
            $updates['closed_at'] = null;
            $updates['loss_reason'] = null;
        }

        $deal->update($updates);

        // Log an activity for stage transition
        Activity::create([
            'type' => 'note',
            'subject' => 'Deal Stage Updated to '.(Deal::STAGES[$newStage]['label'] ?? $newStage),
            'description' => 'Stage transitioned from '.(Deal::STAGES[$oldStage]['label'] ?? $oldStage).' to '.(Deal::STAGES[$newStage]['label'] ?? $newStage).'.',
            'activity_date' => now(),
            'status' => 'completed',
            'priority' => 'normal',
            'user_id' => $request->user()->id,
            'company_id' => $deal->company_id,
            'contact_id' => $deal->contact_id,
            'deal_id' => $deal->id,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Deal stage updated successfully.',
                'deal' => $deal->fresh(['company', 'contact', 'assignedUser']),
            ]);
        }

        return back()->with('success', "Deal '{$deal->title}' moved to ".(Deal::STAGES[$newStage]['label'] ?? $newStage).'.');
    }

    public function create(Request $request): View
    {
        $companies = Company::orderBy('name')->get(['id', 'name']);
        $contacts = Contact::orderBy('first_name')->get(['id', 'first_name', 'last_name', 'company_id']);
        $users = User::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        $preselectedCompanyId = $request->query('company_id');
        $preselectedContactId = $request->query('contact_id');

        return view('deals.create', [
            'deal' => new Deal([
                'stage' => Deal::STAGE_QUALIFICATION,
                'company_id' => $preselectedCompanyId,
                'contact_id' => $preselectedContactId,
                'probability' => 20,
                'deal_type' => 'new_business',
            ]),
            'companies' => $companies,
            'contacts' => $contacts,
            'users' => $users,
        ]);
    }

    public function store(DealRequest $request): RedirectResponse
    {
        $data = $request->validated();
        if (empty($data['assigned_user_id'])) {
            $data['assigned_user_id'] = $request->user()->id;
        }

        if (! isset($data['probability']) || $data['probability'] === '') {
            $data['probability'] = Deal::STAGES[$data['stage']]['probability'] ?? 20;
        }

        if ($data['stage'] === Deal::STAGE_CLOSED_WON) {
            $data['closed_at'] = now();
        } elseif ($data['stage'] === Deal::STAGE_CLOSED_LOST) {
            $data['closed_at'] = now();
        }

        $deal = Deal::create($data);

        return redirect()->route('deals.show', $deal)
            ->with('success', "Deal '{$deal->title}' created successfully.");
    }

    public function show(Deal $deal): View
    {
        $deal->load([
            'company',
            'contact',
            'assignedUser',
            'activities.user',
            'tasks.assignedUser',
        ]);

        $users = User::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('deals.show', [
            'deal' => $deal,
            'users' => $users,
        ]);
    }

    public function edit(Deal $deal): View
    {
        $companies = Company::orderBy('name')->get(['id', 'name']);
        $contacts = Contact::orderBy('first_name')->get(['id', 'first_name', 'last_name', 'company_id']);
        $users = User::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('deals.edit', [
            'deal' => $deal,
            'companies' => $companies,
            'contacts' => $contacts,
            'users' => $users,
        ]);
    }

    public function update(DealRequest $request, Deal $deal): RedirectResponse
    {
        $data = $request->validated();

        if ($data['stage'] === Deal::STAGE_CLOSED_WON && $deal->stage !== Deal::STAGE_CLOSED_WON) {
            $data['closed_at'] = now();
            $data['loss_reason'] = null;
        } elseif ($data['stage'] === Deal::STAGE_CLOSED_LOST && $deal->stage !== Deal::STAGE_CLOSED_LOST) {
            $data['closed_at'] = now();
        }

        $deal->update($data);

        return redirect()->route('deals.show', $deal)
            ->with('success', "Deal '{$deal->title}' updated successfully.");
    }

    public function destroy(Deal $deal): RedirectResponse
    {
        $title = $deal->title;
        $deal->delete();

        return redirect()->route('deals.index')
            ->with('success', "Deal '{$title}' deleted successfully.");
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $request->only(['search', 'stage', 'assigned_user_id', 'company_id']);
        $deals = Deal::query()->with(['company', 'contact', 'assignedUser'])->filter($filters)->latest()->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="deals_export_'.now()->format('Y-m-d_His').'.csv"',
        ];

        return response()->stream(function () use ($deals) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Deal Title', 'Company', 'Contact', 'Stage', 'Amount', 'Probability', 'Expected Close Date', 'Type', 'Assigned Rep']);

            foreach ($deals as $dl) {
                fputcsv($handle, [
                    $dl->id,
                    $dl->title,
                    $dl->company?->name,
                    $dl->contact?->full_name,
                    Deal::STAGES[$dl->stage]['label'] ?? $dl->stage,
                    $dl->amount,
                    $dl->probability.'%',
                    $dl->expected_close_date?->format('Y-m-d'),
                    $dl->deal_type,
                    $dl->assignedUser?->name,
                ]);
            }
            fclose($handle);
        }, 200, $headers);
    }
}
