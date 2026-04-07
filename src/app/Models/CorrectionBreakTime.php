<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorrectionBreakTime extends Model
{
    protected $fillable = [
        'correction_id',
        'break_start',
        'break_end',
    ];

    public function correction()
    {
        return $this->belongsTo(StampCorrectionRequest::class, 'correction_id');
    }
}
