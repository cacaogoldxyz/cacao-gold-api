<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'task',
        'status',
        'user_id',
    ];

    protected $dates = ['deleted_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // public function getStatusAttribute($value)
    // {
    //     return (bool) $value; 
    // }


    public function getStatusAttribute($value)
    {
        return $value ? 'Completed' : 'Incomplete';
    }
}
