<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Model;

class UserSkill extends Model
{
    protected $table = 'user_skills';

    protected $fillable = [
        'tourist',
        'conversationally_fluent',
        'fluent',
    ];
}
