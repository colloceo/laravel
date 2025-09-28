<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * Store a newly created comment.
     */
    public function store(Request $request, Article $article)
    {
        $validated = $request->validate([
            'author_name' => 'required|string|max:255',
            'content' => 'required|string|max:2000',
        ]);

        $comment = new Comment();
        $comment->author_name = $validated['author_name'];
        $comment->content = $validated['content'];
        
        $article->comments()->save($comment);

        return back()->with('success', 'Thank you! Your comment has been submitted.');
    }
}