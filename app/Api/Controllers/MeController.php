<?php

namespace App\Api\Controllers;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Http\Request;

class MeController extends BaseController
{
    /**
     * post up vote the comment by user.
     *
     * @param Request $request
     * @param string  $type
     *
     * @return mixed
     */
    public function postVoteComment(Request $request, $type)
    {
        $this->validate($request, [
            'id' => 'required|exists:comments,id',
        ]);

        $user = auth()->user();

        $comment = Comment::findOrFail($request->id);

        ($type == 'up') ? User::upOrDownVote($user, $comment) : User::upOrDownVote($user, $comment, 'down');

        return $this->responseSuccess();
    }
}
