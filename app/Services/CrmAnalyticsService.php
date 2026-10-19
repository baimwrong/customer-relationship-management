<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class CrmAnalyticsService
{
    /**
     * Get aggregated CRM metrics and chart data.
     *
     * @return array<string, mixed>
     */
    public function getDashboardMetrics(?User $scopedUser = null): array
    {
        if (app()->runningUnitTests()) {
            return $this->computeMetrics($scopedUser);
        }

        $cacheKey = 'crm_dashboard_metrics_'.($scopedUser && $scopedUser->isSalesRep() ? $scopedUser->id : 'all');

        return Cache::remember($cacheKey, now()->addSeconds(60), function () use ($scopedUser) {
            return $this->computeMetrics($scopedUser);
        });
    }

    /**
     * Compute CRM dashboard metrics via efficient SQL aggregates.
     *
     * @return array<string, mixed>
     */
    private function computeMetrics(?User $scopedUser = null): array
    {
        $dealsQuery = Deal::query();
        $leadsQuery = Lead::query();
        $companiesQuery = Company::query();
        $contactsQuery = Contact::query();
        $tasksQuery = Task::query();
        $activitiesQuery = Activity::query();

        // Scope by sales rep if requested and user is a sales rep
        if ($scopedUser && $scopedUser->isSalesRep()) {
            $dealsQuery->where('assigned_user_id', $scopedUser->id);
            $leadsQuery->where('assigned_user_id', $scopedUser->id);
            $companiesQuery->where('assigned_user_id', $scopedUser->id);
            $contactsQuery->where('assigned_user_id', $scopedUser->id);
            $tasksQuery->where('assigned_user_id', $scopedUser->id);
            $activitiesQuery->where('user_id', $scopedUser->id);
        }

        // 1. Pipeline & Financial KPIs (Aggregated in SQL)
        $openDealsQuery = (clone $dealsQuery)->open();
        $totalPipelineValue = (float) (clone $openDealsQuery)->sum('amount');
        $openDealsCount = (clone $openDealsQuery)->count();
        $weightedPipelineValue = (float) (clone $openDealsQuery)
            ->selectRaw('COALESCE(SUM(amount * probability / 100), 0) as val')
            ->value('val');

        $wonDealsQuery = (clone $dealsQuery)->where('stage', Deal::STAGE_CLOSED_WON);
        $totalWonRevenue = (float) (clone $wonDealsQuery)->sum('amount');
        $wonCount = (clone $wonDealsQuery)->count();

        $lostDealsCount = (clone $dealsQuery)->where('stage', Deal::STAGE_CLOSED_LOST)->count();
        $closedCount = $wonCount + $lostDealsCount;
        $winRate = $closedCount > 0 ? round(($wonCount / $closedCount) * 100, 1) : 0;

        // 2. Lead Metrics
        $totalLeads = (clone $leadsQuery)->count();
        $activeLeads = (clone $leadsQuery)->where('status', '!=', Lead::STATUS_CONVERTED)->count();
        $convertedLeads = (clone $leadsQuery)->where('status', Lead::STATUS_CONVERTED)->count();
        $conversionRate = $totalLeads > 0 ? round(($convertedLeads / $totalLeads) * 100, 1) : 0;

        // 3. Stage Breakdown for Funnel Chart (Single Grouped Query)
        $stageStats = (clone $dealsQuery)
            ->selectRaw('stage, COUNT(*) as count, COALESCE(SUM(amount), 0) as total_amount')
            ->groupBy('stage')
            ->get()
            ->keyBy('stage');

        $stagesData = [];
        $stageLabels = [];
        $stageCounts = [];
        $stageAmounts = [];

        foreach (Deal::STAGES as $stageKey => $meta) {
            $stat = $stageStats->get($stageKey);
            $count = (int) ($stat?->count ?? 0);
            $sum = (float) ($stat?->total_amount ?? 0);

            $stagesData[$stageKey] = [
                'label' => $meta['label'],
                'color' => $meta['color'],
                'count' => $count,
                'amount' => $sum,
            ];

            $stageLabels[] = $meta['label'];
            $stageCounts[] = $count;
            $stageAmounts[] = $sum;
        }

        // 4. Monthly Won Revenue for Last 6 Months (Single Date Range Query)
        $sixMonthsAgo = Carbon::now()->subMonths(5)->startOfMonth();
        $wonDealsInRange = (clone $dealsQuery)
            ->where('stage', Deal::STAGE_CLOSED_WON)
            ->where('closed_at', '>=', $sixMonthsAgo)
            ->selectRaw('closed_at, amount')
            ->get();

        $months = [];
        $monthlyRevenue = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthKey = $date->format('Y-m');
            $monthLabel = $date->format('M Y');
            $months[] = $monthLabel;

            $rev = $wonDealsInRange->filter(function ($d) use ($monthKey) {
                return $d->closed_at && $d->closed_at->format('Y-m') === $monthKey;
            })->sum('amount');

            $monthlyRevenue[] = (float) $rev;
        }

        // 5. Leads by Source for Doughnut Chart (Single Grouped Query)
        $sourceStats = (clone $leadsQuery)
            ->selectRaw('source, COUNT(*) as count')
            ->groupBy('source')
            ->pluck('count', 'source');

        $sourceLabels = [];
        $sourceCounts = [];
        foreach (Lead::SOURCES as $sourceKey => $sourceLabel) {
            $count = (int) ($sourceStats[$sourceKey] ?? 0);
            if ($count > 0 || count($sourceLabels) < 6) {
                $sourceLabels[] = $sourceLabel;
                $sourceCounts[] = $count;
            }
        }

        // 6. Recent Activities & Tasks
        $recentActivities = (clone $activitiesQuery)
            ->with(['user', 'company', 'contact', 'deal', 'lead'])
            ->latest()
            ->take(6)
            ->get();

        $pendingTasks = (clone $tasksQuery)
            ->with(['assignedUser', 'company', 'deal'])
            ->pending()
            ->orderBy('due_date', 'asc')
            ->take(5)
            ->get();

        // 7. Sales Leaderboard
        $salesReps = User::whereIn('role', [User::ROLE_SALES_REP, User::ROLE_SALES_MANAGER])
            ->withCount(['assignedDeals as won_deals_count' => fn ($q) => $q->where('stage', Deal::STAGE_CLOSED_WON)])
            ->withSum(['assignedDeals as won_amount' => fn ($q) => $q->where('stage', Deal::STAGE_CLOSED_WON)], 'amount')
            ->orderByDesc('won_amount')
            ->take(5)
            ->get();

        return [
            'total_pipeline_value' => $totalPipelineValue,
            'weighted_pipeline_value' => round($weightedPipelineValue, 2),
            'total_won_revenue' => $totalWonRevenue,
            'win_rate' => $winRate,
            'total_deals' => (clone $dealsQuery)->count(),
            'open_deals_count' => $openDealsCount,
            'total_leads' => $totalLeads,
            'active_leads' => $activeLeads,
            'converted_leads' => $convertedLeads,
            'conversion_rate' => $conversionRate,
            'total_companies' => (clone $companiesQuery)->count(),
            'total_contacts' => (clone $contactsQuery)->count(),
            'pending_tasks_count' => (clone $tasksQuery)->pending()->count(),
            'stages_breakdown' => $stagesData,
            'charts' => [
                'stage_labels' => $stageLabels,
                'stage_counts' => $stageCounts,
                'stage_amounts' => $stageAmounts,
                'monthly_labels' => $months,
                'monthly_revenue' => $monthlyRevenue,
                'source_labels' => $sourceLabels,
                'source_counts' => $sourceCounts,
            ],
            'recent_activities' => $recentActivities,
            'pending_tasks' => $pendingTasks,
            'sales_reps' => $salesReps,
        ];
    }
}
