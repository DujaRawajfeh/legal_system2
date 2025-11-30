<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JudgeUser extends Model
{
    protected $table = 'judge_user';

    protected $fillable = [
        'user_id',
        'judge_id',
    ];

    public $timestamps = true;
}