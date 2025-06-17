<?php

namespace App\Models;

use App\Models\Traits\TaskAttributes;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Task extends Model
{
    use HasFactory, Searchable, SoftDeletes, TaskAttributes;

    protected $fillable = [
        'title',
        'description',
        'status',
        'priority',
        'due_date',
        'notification_sent_at',
        'user_id',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'notification_sent_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function canBeCompleted(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    public function isOverdue(): bool
    {
        return $this->due_date->isPast() && $this->status !== self::STATUS_COMPLETED;
    }

    public function updateOverdueStatus(): void
    {
        if ($this->isOverdue() && $this->status !== self::STATUS_OVERDUE) {
            $this->update(['status' => self::STATUS_OVERDUE]);
        }
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', Carbon::now())
            ->where('status', '!=', self::STATUS_COMPLETED);
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'priority' => $this->priority,
        ];
    }

    public function scopeNeedsNotification($query)
    {
        return $query->where('notification_sent_at', null)
            ->where('due_date', '<=', Carbon::now()->addDay())
            ->where('due_date', '>', Carbon::now())
            ->whereIn('status', [self::STATUS_PENDING, self::STATUS_IN_PROGRESS]);
    }
}
