<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CommentReact extends Model
{
    //
    protected $fillable = ['comment_id','user_id','react'];
    protected $table = "comment_user_react";
    public $timestamps = false;
}