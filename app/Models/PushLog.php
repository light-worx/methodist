<?php
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
 
class PushLog extends Model
{
    protected $fillable = [
        'push_message_id',
        'user_preference_id',
        'status',
        'error',
        'delivered_at',
    ];
 
    protected $casts = [
        'delivered_at' => 'datetime',
    ];
 
    public function message(): BelongsTo
    {
        return $this->belongsTo(PushMessage::class, 'push_message_id');
    }
 
    public function preference(): BelongsTo
    {
        return $this->belongsTo(UserPreference::class, 'user_preference_id');
    }
}