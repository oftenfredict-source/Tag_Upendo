<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'church_service_id',
        'member_id',
        'status',
    ];

    public function churchService()
    {
        return $this->belongsTo(ChurchService::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
