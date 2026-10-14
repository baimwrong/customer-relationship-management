<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory;

    public const PRIORITIES = [
        'low' => ['label' => 'Low', 'color' => 'secondary'],
        'medium' => ['label' => 'Medium', 'color' => 'info'],
        'high' => ['label' => 'High', 'color' => 'warning'],
        'urgent' => ['label' => 'Urgent', 'color' => 'danger'],
    ];

    public const STATUSES = [
        'pending' => ['label' => 'Pending', 'color' => 'secondary'],
        'in_progress' => ['label' => 'In Progress', 'color' => 'primary'],
        'completed' => ['label' => 'Completed', 'color' => 'success'],
        'deferred' => ['label' => 'Deferred', 'color' => 'dark'],
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'description',
        'due_date',
        'priority',
        'status',
        'assigned_user_id',
        'company_id',
        'contact_id',
        'lead_id',
        'deal_id',
        'completed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
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

    public function getPriorityMetaAttribute(): array
    {
        return self::PRIORITIES[$this->priority] ?? ['label' => ucfirst($this->priority), 'color' => 'secondary'];
    }

    public function getStatusMetaAttribute(): array
    {
        return self::STATUSES[$this->status] ?? ['label' => ucfirst($this->status), 'color' => 'secondary'];
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->whereIn('status', ['pending', 'in_progress']);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['status'] ?? null, function (Builder $query, string $status) {
                $query->where('status', $status);
            })
            ->when($filters['priority'] ?? null, function (Builder $query, string $priority) {
                $query->where('priority', $priority);
            })
            ->when($filters['assigned_user_id'] ?? null, function (Builder $query, $userId) {
                $query->where('assigned_user_id', $userId);
            })
            ->when($filters['search'] ?? null, function (Builder $query, string $search) {
                $query->where(function (Builder $query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            });
    }
}
