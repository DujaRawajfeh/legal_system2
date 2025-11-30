<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Participant extends Model
{
    use HasFactory;

    protected $fillable = [
        'court_case_id',
        'type',
        'name',
        'national_id',
        'residence',
        'job',
        'phone',
        'charge',
        'fingerprint',
    ];

    // العلاقة مع القضية
    public function courtCase()
    {
        return $this->belongsTo(CourtCase::class);
    }




    public function sessionReports()
{
    return $this->hasMany(CourtSessionReport::class, 'participant_id');
}
}
