<?php

namespace App\Http\Controllers;

use App\Http\Requests\ActivityRequest;
use App\Models\Activity;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->only(['type', 'user_id', 'search']);

        $activities = Activity::query()
            ->with(['user', 'company', 'contact', 'deal', 'lead'])
            ->filter($filters)
            ->latest('activity_date')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $users = User::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('activities.index', [
            'activities' => $activities,
            'filters' => $filters,
            'users' => $users,
        ]);
    }

    public function store(ActivityRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;

        if (empty($data['activity_date'])) {
            $data['activity_date'] = now();
        }

        Activity::create($data);

        return back()->with('success', 'Activity logged successfully.');
    }

    public function destroy(Activity $activity): RedirectResponse
    {
        $this->authorize('delete', $activity);

        $activity->delete();

        return back()->with('success', 'Activity removed.');
    }
}
