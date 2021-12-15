<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    //
    /**
     * Fillable fields for a course
     *
     * @return array
     */
    protected $fillable = ['comment','reacts','spam','reply_id','post_id','users_id'];

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    // protected $dateFormat = "U";

    public function replies()
    {
        return $this->hasMany('App\Comment','id','reply_id');
    }
}