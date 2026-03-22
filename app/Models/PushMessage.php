<?php
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
 
class PushMessage extends Model
{
    protected $fillable = [
        'sent_by',
        'type',
        'circuit_id',
        'user_preference_id',
        'title',
        'body',
        'url',
        'icon',
        'recipient_count',
        'sent_at',
    ];
 
    protected $casts = [
        'sent_at' => 'datetime',
    ];
 
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
 
    public function preference(): BelongsTo
    {
        return $this->belongsTo(UserPreference::class, 'user_preference_id');
    }
 
    public function logs(): HasMany
    {
        return $this->hasMany(PushLog::class);
    }
}