<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'full_name',
        'national_id',
        'password',
        'role',
        'password_changed_at',
        'two_factor_secret',
       'two_factor_enabled',
       'two_factor_recovery_codes',
  
    ];

    protected $hidden = [
        'password',
    ];

    public function tribunal()
{
    return $this->belongsTo(Tribunal::class);
}

public function department()
{
    return $this->belongsTo(\App\Models\Department::class);
}


public function cases()
{
    return $this->hasMany(CourtCase::class, 'judge_id');
}

// المستخدم (كاتب أو طابعة) يتبع عدة قضاة
public function assignedJudges()
{
    return $this->belongsToMany(User::class, 'judge_user', 'user_id', 'judge_id');
}

// القاضي يمكن أن يتبعه عدة مستخدمين
public function followers()
{
    return $this->belongsToMany(User::class, 'judge_user', 'judge_id', 'user_id');
}





}