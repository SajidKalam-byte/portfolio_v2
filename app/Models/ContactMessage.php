<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'email', 'subject', 'message', 'status', 'ip_hash'])]
class ContactMessage extends Model
{
    public const STATUSES = [
        'pending' => 'New',
        'read' => 'Read',
        'replied' => 'Replied',
        'archived' => 'Archived',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function markAsRead(): void
    {
        if ($this->status === 'pending') {
            $this->forceFill(['status' => 'read', 'read_at' => now()])->save();
        }
    }
}
