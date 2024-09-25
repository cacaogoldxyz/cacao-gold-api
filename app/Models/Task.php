<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    // TODO: Create a crud functionality for Post that can have many comments
    use HasFactory;

    protected $fillable = [
        'name',
        'user_id', // Add user_id as fillable for assignment
        'is_completed', // You may also have this field if using task completion
    ];

    // Defining the relationship that a Task belongs to a User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

