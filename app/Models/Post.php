<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Comment;
use Illuminate\Database\Eloquent\SoftDeletes; 


class Post extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['title', 'body'];

    // A Post can have many comments
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
