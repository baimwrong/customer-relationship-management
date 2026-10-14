<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Deal extends Model
{
    use HasFactory;

    public const STAGE_QUALIFICATION = 'qualification';

    public const STAGE_NEEDS_ANALYSIS = 'needs_analysis';

    public const STAGE_PROPOSAL = 'proposal';

    public const STAGE_NEGOTIATION = 'negotiation';

    public const STAGE_CLOSED_WON = 'closed_won';

    public const STAGE_CLOSED_LOST = 'closed_lost';

    public const STAGES = [
        self::STAGE_QUALIFICATION => [
            'label' => 'Qualification',
            'color' => 'secondary',
            'probability' => 20,
        ],
        self::STAGE_NEEDS_ANALYSIS => [
            'label' => 'Needs Analysis',
            'color' => 'info',
            'probability' => 40,
        ],
        self::STAGE_PROPOSAL => [
            'label' => 'Proposal / Quote',
            'color' => 'primary',
            'probability' => 60,
        ],
        self::STAGE_NEGOTIATION => [
            'label' => 'Negotiation',
            'color' => 'warning',
            'probability' => 80,
        ],
        self::STAGE_CLOSED_WON => [
            'label' => 'Closed Won',
            'color' => 'success',
            'probability' => 100,
        ],
        self::STAGE_CLOSED_LOST => [
            'label' => 'Closed Lost',
            'color' => 'danger',
            'probability' => 0,
        ],
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'company_id',
        'contact_id',
        'assigned_user_id',
        'stage',
        'amount',
        'expected_close_date',
        'probability',
        'deal_type',
        'loss_reason',
        'closed_at',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'expected_close_date' => 'date',
            'closed_at' => 'datetime',
            'probability' => 'integer',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function getStageLabelAttribute(): string
    {
        return self::STAGES[$this->stage]['label'] ?? ucfirst(str_replace('_', ' ', $this->stage));
    }

    public function getStageBadgeColorAttribute(): string
    {
        return self::STAGES[$this->stage]['color'] ?? 'secondary';
    }

    public function getWeightedAmountAttribute(): float
    {
        return round(($this->amount * $this->probability) / 100, 2);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNotIn('stage', [self::STAGE_CLOSED_WON, self::STAGE_CLOSED_LOST]);
    }

    public function scopeWon(Builder $query): Builder
    {
        return $query->where('stage', self::STAGE_CLOSED_WON);
    }

    public function scopeLost(Builder $query): Builder
    {
        return $query->where('stage', self::STAGE_CLOSED_LOST);
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['search'] ?? null, function (Builder $query, string $search) {
                $query->where(function (Builder $query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhereHas('company', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('contact', fn ($q) => $q->where('first_name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%"));
                });
            })
            ->when($filters['stage'] ?? null, function (Builder $query, string $stage) {
                $query->where('stage', $stage);
            })
            ->when($filters['assigned_user_id'] ?? null, function (Builder $query, $userId) {
                $query->where('assigned_user_id', $userId);
            })
            ->when($filters['company_id'] ?? null, function (Builder $query, $companyId) {
                $query->where('company_id', $companyId);
            });
    }
}
