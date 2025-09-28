<?php

// 1. ENSURE THE NAMESPACE IS CORRECT
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * Display a listing of all comments.
     */
    public function index()
    {
        $comments = Comment::with('article')->latest()->paginate(15);
        return view('admin.comments.index', compact('comments'));
    }

    /**
     * Remove the specified comment from storage.
     */
    public function destroy(Comment $comment)
    {
        $comment->delete();
        return back()->with('success', 'Comment deleted successfully.');
    }
}