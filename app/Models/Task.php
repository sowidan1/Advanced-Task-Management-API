<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;
use Carbon\Carbon;
use App\Models\Traits\TaskAttributes;

class Task extends Model
{
    use HasFactory, SoftDeletes, Searchable, TaskAttributes;

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
}
