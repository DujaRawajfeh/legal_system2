<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArchivedDocument extends Model
{
    protected $fillable = [
        'court_case_id',
        'document_type',
         'file_name',
        'scan_preview',
        'document_number',
    ];

    public function courtCase()
    {
        return $this->belongsTo(CourtCase::class);
    }
}