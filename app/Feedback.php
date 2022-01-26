<?php

namespace App;

use App\Events\FeedbackSent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feedback extends Model
{
    protected $table = 'feedbacks';
    protected $fillable = ['message', 'user_id'];

    protected $casts = [
        "id" => "integer",
        "user_id" => "integer"
    ];


    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }
}
