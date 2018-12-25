<?php

namespace App\Api\Controllers;

use App\Models\Article;
use App\Models\Comment;
use App\Models\User;
use App\Models\Visitor;

class HomeController extends BaseController
{
    public function statistics()
    {
        $users = User::query()->count();
        $visitors = Visitor::query()->sum('clicks');
        $articles = Article::query()->count();
        $comments = Comment::query()->count();

        $data = compact('users', 'visitors', 'articles', 'comments');

        return $this->responseSuccess($data);
    }
}
