<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Borrowing extends Model
{
    protected $fillable = ['user_id', 'status', 'borrowed_at', 'due_at', 'returned_at', 'renewed_at'];

    protected function casts(): array
    {
        return ['borrowed_at' => 'date', 'due_at' => 'date', 'returned_at' => 'date', 'renewed_at' => 'date'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(BorrowingDetail::class);
    }

    public function fine()
    {
        return $this->hasOne(Fine::class);
    }
}
