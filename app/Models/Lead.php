<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lead extends Model
{
    use HasFactory;

    public const STATUS_NEW = 'new';

    public const STATUS_CONTACTED = 'contacted';

    public const STATUS_QUALIFIED = 'qualified';

    public const STATUS_UNQUALIFIED = 'unqualified';

    public const STATUS_CONVERTED = 'converted';

    public const STATUSES = [
        self::STATUS_NEW => ['label' => 'New', 'color' => 'primary'],
        self::STATUS_CONTACTED => ['label' => 'Contacted', 'color' => 'info'],
        self::STATUS_QUALIFIED => ['label' => 'Qualified', 'color' => 'success'],
        self::STATUS_UNQUALIFIED => ['label' => 'Unqualified', 'color' => 'secondary'],
        self::STATUS_CONVERTED => ['label' => 'Converted', 'color' => 'dark'],
    ];

    public const SOURCES = [
        'website' => 'Website Form',
        'referral' => 'Referral',
        'cold_call' => 'Cold Call',
        'advertising' => 'Advertising',
        'partner' => 'Partner',
        'inbound' => 'Inbound Phone/Email',
        'event' => 'Trade Show / Event',
        'other' => 'Other',
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'company_name',
        'email',
        'phone',
        'job_title',
        'source',
        'status',
        'estimated_value',
        'score',
        'assigned_user_id',
        'converted_at',
        'converted_company_id',
        'converted_contact_id',
        'converted_deal_id',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'estimated_value' => 'decimal:2',
            'score' => 'integer',
            'converted_at' => 'datetime',
        ];
    }

    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn () => trim("{$this->first_name} {$this->last_name}"),
        );
    }

    public function isConverted(): bool
    {
        return $this->status === self::STATUS_CONVERTED || $this->converted_at !== null;
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function convertedCompany(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'converted_company_id');
    }

    public function convertedContact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'converted_contact_id');
    }

    public function convertedDeal(): BelongsTo
    {
        return $this->belongsTo(Deal::class, 'converted_deal_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status]['label'] ?? ucfirst($this->status);
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return self::STATUSES[$this->status]['color'] ?? 'secondary';
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', '!=', self::STATUS_CONVERTED);
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['search'] ?? null, function (Builder $query, string $search) {
                $query->where(function (Builder $query) use ($search) {
                    $query->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('company_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, function (Builder $query, string $status) {
                $query->where('status', $status);
            })
            ->when($filters['source'] ?? null, function (Builder $query, string $source) {
                $query->where('source', $source);
            })
            ->when($filters['assigned_user_id'] ?? null, function (Builder $query, $userId) {
                $query->where('assigned_user_id', $userId);
            });
    }
}
