<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Comment;
use Illuminate\Database\Eloquent\SoftDeletes; 

class Post extends Model
{
    use SoftDeletes;

    protected $fillable = ['title', 'body', 'user_id']; 

    // A Post belongs to a User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // A Post has many Comments
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
