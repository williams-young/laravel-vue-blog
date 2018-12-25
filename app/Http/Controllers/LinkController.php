<?php

namespace App\Http\Controllers;

use App\Models\Link;

class LinkController extends Controller
{
    /**
     * Display the link resource.
     *
     * @return mix
     */
    public function index()
    {
        $links = Link::checkAuth()->orderBy('created_at', 'desc')->paginate(10);

        return view('web.link.index', compact('links'));
    }
}
