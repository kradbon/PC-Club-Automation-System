<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClubReview extends Model
{
    protected $fillable = ['user_id', 'rating', 'body', 'status', 'moderated_by', 'submitted_at', 'moderated_at'];

    protected function casts(): array
    {
        return ['rating' => 'integer', 'submitted_at' => 'datetime', 'moderated_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }
}
