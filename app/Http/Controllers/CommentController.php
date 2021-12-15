<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Comment;
use App\CommentReact;
use App\CommentSpam;
use App\User;
use Auth;

class CommentController extends Controller
{
    
    /**
     * Get Comments for postId
     *
     * @return Comments
     */
    public function index($postId)
    {
        //
        $comments = Comment::where('post_id',$postId)->get();

        $commentsData = [];
        
        
        foreach ($comments as $key) {
            $user = User::find($key->users_id);
            $name = $user->name;
            $replies = $this->replies($key->id);
            $photo = $user->first()->photo_url;
            // dd($photo->photo_url);
            $reply = 0;
            $react = 0;
            $reactStatus = 0;
            $spam = 0;
            if(Auth::user()){
                $reactByUser = CommentReact::where('comment_id',$key->id)->where('user_id',Auth::user()->id)->first();
                $spamComment = CommentSpam::where('comment_id',$key->id)->where('user_id',Auth::user()->id)->first();
                
                if($reactByUser){
                    $react = 1;
                    $reactStatus = $reactByUser->react;
                }

                if($spamComment){
                    $spam = 1;
                }
            }
            
            if(sizeof($replies) > 0){
                $reply = 1;
            }

            if(!$spam){
                array_push($commentsData,[
                    "name" => $name,
                    "photo_url" => (string)$photo,
                    "commentid" => $key->id,
                    "comment" => $key->comment,
                    "reacts" => $key->reacts,
                    "reply" => $reply,
                    "reactdByUser" =>$react,
                    "react" =>$reactStatus,
                    "spam" => $spam,
                    "replies" => $replies,
                    "date" => $key->created_at->toDateTimeString()
                ]);
            }    
            
        }
        $collection = collect($commentsData);
        return $collection->sortBy('reacts');
    }

    protected function replies($commentId)
    {
        $comments = Comment::where('reply_id',$commentId)->get();
        $replies = [];
        

        foreach ($comments as $key) {
            $user = User::find($key->users_id);
            $name = $user->name;
            $photo = $user->first()->photo_url;

            $react = 0;
            $reactStatus = 0;
            $spam = 0;    
            
            if(Auth::user()){
                $reactByUser = CommentReact::where('comment_id',$key->id)->where('user_id',Auth::user()->id)->first();
                $spamComment = CommentSpam::where('comment_id',$key->id)->where('user_id',Auth::user()->id)->first();

                if($reactByUser){
                    $react = 1;
                    $reactStatus = $reactByUser->react;
                }

                if($spamComment){
                    $spam = 1;
                }
            }
            if(!$spam){
                
                array_push($replies,[
                    "name" => $name,
                    "photo_url" => $photo,
                    "commentid" => $key->id,
                    "comment" => $key->comment,
                    "reacts" => $key->reacts,
                    "reactdByUser" => $react,
                    "react" => $reactStatus,
                    "spam" => $spam,
                    "date" => $key->created_at->toDateTimeString()
                ]);
            }
            
        
        }
        
        $collection = collect($replies);
        return $collection->sortBy('reacts');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
        'comment' => 'required',
        'reply_id' => 'filled',
        'post_id' => 'filled',
        'users_id' => 'required',
        ]);
        $comment = Comment::create($request->all());
        // dd($comment); 
        if($comment)
            return [ "status" => "true","commentId" => $comment->id ];
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $commentId
     * @param  $type
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $commentId,$type)
    {
        if($type == "react"){
            
            $this->validate($request, [
            'react' => 'required',
            'users_id' => 'required',
            ]);

            $comments = Comment::find($commentId);
            $data = [
                "comment_id" => $commentId,
                'react' => $request->react,
                'user_id' => $request->users_id,
            ];

            if($request->react == "up"){
                $comment = $comments->first();
                $react = $comment->reacts;
                $react++;
                $comments->reacts = $react;
                $comments->save();
            }

            if($request->react == "down"){
                $comment = $comments->first();
                $react = $comment->reacts;
                $react--;
                $comments->reacts = $react;
                $comments->save();
            }

            if(CommentReact::create($data))
                return "true";
        }

        if($type == "spam"){
            
            $this->validate($request, [
                'users_id' => 'required',
            ]);

            $comments = Comment::find($commentId);
            
            $comment = $comments->first();
            $spam = $comment->spam;
            $spam++;
            $comments->spam = $spam;
            $comments->save();

            $data = [
                "comment_id" => $commentId,
                'user_id' => $request->users_id,
            ];

            if(CommentSpam::create($data))
                return "true";
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * 
     * @return \Illuminate\Http\Response
     */
    public function delete($commentId)
    {
        $comments = Comment::findOrFail($commentId);
        $comments->delete();
    }
}