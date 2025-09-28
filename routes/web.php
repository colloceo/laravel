<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\Admin\ArticleController;
use App\Http\Controllers\Admin\CategoryController;
use App\Models\Article;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Tag;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// --- PUBLIC ROUTES ---

Route::get('/', function () {
    $published = Article::where('status', 'published')->latest('published_at');
    $heroArticles = $published->clone()->take(4)->get();
    $dontMissArticles = $published->clone()->skip(4)->take(5)->get();
    $mainFeedArticles = $published->clone()->skip(9)->take(6)->get();
    $sidebarPopular = $published->clone()->orderBy('views', 'desc')->take(4)->get();
    $sidebarBreaking = Article::whereHas('category', function ($q) { $q->where('slug', 'breaking-news'); })
                              ->where('status', 'published')->latest('published_at')->take(5)->get();
    
    return view('home', compact('heroArticles', 'dontMissArticles', 'mainFeedArticles', 'sidebarPopular', 'sidebarBreaking'));
})->name('home');

Route::get('/articles/{article:slug}', function (Article $article) {
    $article->increment('views');
    $article->load(['comments' => fn($query) => $query->latest()]);
    $sidebarPopular = Article::where('status', 'published')->where('id', '!=', $article->id)->orderBy('views', 'desc')->take(4)->get();
    $sidebarBreaking = Article::whereHas('category', function ($q) { $q->where('slug', 'breaking-news'); })->where('status', 'published')->latest('published_at')->take(5)->get();
    
    return view('articles.show', compact('article', 'sidebarPopular', 'sidebarBreaking'));
})->name('articles.show');

Route::post('/articles/{article:slug}/comments', function (Request $request, Article $article) {
    $validated = $request->validate([
        'author_name' => 'required|string|max:255',
        'content' => 'required|string|max:2000',
    ]);
    $comment = new Comment($validated);
    $article->comments()->save($comment);
    return back()->with('success', 'Thank you! Your comment has been submitted.');
})->name('comments.store');

Route::get('/categories/{category:slug}', function (Category $category) {
    $articles = $category->articles()->where('status', 'published')->latest('published_at')->paginate(10);
    $sidebarBreaking = Article::whereHas('category', function ($q) { $q->where('slug', 'breaking-news'); })->where('status', 'published')->latest('published_at')->take(5)->get();
    
    return view('articles.index', compact('articles', 'category', 'sidebarBreaking'));
})->name('categories.articles');

Route::get('/tags/{tag:slug}', function (Tag $tag) {
    $articles = $tag->articles()->where('status', 'published')->latest('published_at')->paginate(10);
    $sidebarBreaking = Article::whereHas('category', function ($q) { $q->where('slug', 'breaking-news'); })->where('status', 'published')->latest('published_at')->take(5)->get();

    return view('articles.index', compact('articles', 'tag', 'sidebarBreaking'));
})->name('tags.articles');

Route::get('/search', function (Request $request) {
    $query = $request->input('query');
    if (!$query) { return back(); }
    $sortBy = $request->input('sort_by', 'relevance');
    $articlesQuery = Article::where('status', 'published')->where(fn($q) => $q->where('title', 'like', "%{$query}%")->orWhere('content', 'like', "%{$query}%"));
    switch ($sortBy) {
        case 'date_asc': $articlesQuery->oldest('published_at'); break;
        case 'most_viewed': $articlesQuery->orderBy('views', 'desc'); break;
        default: $articlesQuery->latest('published_at'); break;
    }
    $articles = $articlesQuery->paginate(10);
    $sidebarBreaking = Article::whereHas('category', function ($q) { $q->where('slug', 'breaking-news'); })->where('status', 'published')->latest('published_at')->take(5)->get();
    
    return view('articles.index', compact('articles', 'query', 'sortBy', 'sidebarBreaking'));
})->name('search');

// --- Static Pages ---
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/privacy-policy', [PageController::class, 'privacy'])->name('privacy');
Route::get('/contact', [PageController::class, 'contactShow'])->name('contact');
Route::post('/contact', [PageController::class, 'contactSend'])->name('contact.send');

// --- Sitemap ---
Route::get('/sitemap.xml', function () {
    $sitemap = Sitemap::create();
    $sitemap->add(Url::create(route('home'))->setPriority(1.0)->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY));
    Article::where('status', 'published')->get()->each(function (Article $article) use ($sitemap) {
        $sitemap->add(Url::create(route('articles.show', $article))->setLastModificationDate($article->updated_at)->setPriority(0.9)->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY));
    });
    Category::all()->each(function (Category $category) use ($sitemap) {
        $sitemap->add(Url::create(route('categories.articles', $category))->setPriority(0.8)->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY));
    });
    $sitemap->add(Url::create(route('about'))->setPriority(0.7)->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY));
    $sitemap->add(Url::create(route('contact'))->setPriority(0.7)->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY));
    $sitemap->add(Url::create(route('privacy'))->setPriority(0.5)->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY));

    return $sitemap;
})->name('sitemap');


// --- AUTHENTICATION & ADMIN ROUTES ---

require __DIR__.'/auth.php';

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        if (auth()->user()->is_admin) {
            return redirect()->route('admin.dashboard');
        }
        return view('dashboard');
    })->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', fn() => view('admin.dashboard'))->name('dashboard');
    Route::resource('categories', CategoryController::class);
    Route::resource('articles', ArticleController::class);
    Route::get('comments', function () {
        $comments = Comment::with('article')->latest()->paginate(15);
        return view('admin.comments.index', compact('comments'));
    })->name('comments.index');
    Route::delete('comments/{comment}', function (Comment $comment) {
        $comment->delete();
        return back()->with('success', 'Comment deleted successfully.');
    })->name('comments.destroy');
});