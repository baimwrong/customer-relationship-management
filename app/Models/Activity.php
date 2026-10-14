<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Activity extends Model
{
    use HasFactory;

    public const TYPES = [
        'call' => ['label' => 'Phone Call', 'icon' => 'bi-telephone', 'color' => 'primary'],
        'email' => ['label' => 'Email', 'icon' => 'bi-envelope', 'color' => 'info'],
        'meeting' => ['label' => 'Meeting', 'icon' => 'bi-people', 'color' => 'warning'],
        'note' => ['label' => 'Note / Memo', 'icon' => 'bi-file-text', 'color' => 'secondary'],
        'site_visit' => ['label' => 'Site Visit', 'icon' => 'bi-geo-alt', 'color' => 'success'],
        'task' => ['label' => 'Task', 'icon' => 'bi-check2-square', 'color' => 'dark'],
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'type',
        'subject',
        'description',
        'activity_date',
        'duration_minutes',
        'status',
        'priority',
        'user_id',
        'company_id',
        'contact_id',
        'lead_id',
        'deal_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'activity_date' => 'datetime',
            'duration_minutes' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class);
    }

    public function getTypeMetaAttribute(): array
    {
        return self::TYPES[$this->type] ?? ['label' => ucfirst($this->type), 'icon' => 'bi-activity', 'color' => 'secondary'];
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['type'] ?? null, function (Builder $query, string $type) {
                $query->where('type', $type);
            })
            ->when($filters['user_id'] ?? null, function (Builder $query, $userId) {
                $query->where('user_id', $userId);
            })
            ->when($filters['search'] ?? null, function (Builder $query, string $search) {
                $query->where(function (Builder $query) use ($search) {
                    $query->where('subject', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            });
    }
}
