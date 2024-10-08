<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Post;
use Illuminate\Database\Eloquent\SoftDeletes; 

class Comment extends Model
{
    use SoftDeletes;

    protected $fillable = ['post_id', 'user_id', 'body'];

    // A Comment belongs to a User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    // A Comment belongs to a Post
    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
