<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FollowUp extends Model
{
    protected $fillable = ['member_id', 'message', 'status', 'scheduled_at'];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
