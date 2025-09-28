<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Show the application's homepage.
     */
    public function index()
    {
        $publishedArticles = Article::where('status', 'published')->latest('published_at');

        // Fetch the most recent published article as the top story
        $topArticle = $publishedArticles->first();

        // Fetch the next 5 for the "Latest News" list
        $latestArticles = $publishedArticles->skip(1)->take(5)->get();
        
        // Fetch up to 5 articles from the "Breaking News" category
        $breakingNews = Article::whereHas('category', function ($query) {
            $query->where('slug', 'breaking-news');
        })->where('status', 'published')->latest('published_at')->take(5)->get();

        return view('home', compact('topArticle', 'latestArticles', 'breakingNews'));
    }

    /**
     * Show a single article.
     */
    public function show(Article $article)
    {
        // We will implement this in a later step
        return view('articles.show', compact('article'));
    }
}