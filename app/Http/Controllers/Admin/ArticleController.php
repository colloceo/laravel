<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $articles = Article::with(['category', 'user'])->latest()->paginate(10);
        return view('admin.articles.index', compact('articles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::all();
        return view('admin.articles.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255|unique:articles',
            'category_id' => 'required|integer|exists:categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'image_url' => 'nullable|url|max:2000|required_without:image',
            'tags' => 'nullable|string',
            'excerpt' => 'required|string|max:500',
            'content' => 'required|string',
            'status' => 'required|string|in:draft,published,featured,breaking',
        ]);

        $imagePath = null;
        if (!empty($validated['image_url'])) { $imagePath = $validated['image_url']; }
        if ($request->hasFile('image')) {
            $imageFile = $request->file('image');
            $newFilename = Str::slug(pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME)) . '_' . time() . '.webp';
            $webpImage = Image::make($imageFile)->encode('webp', 80);
            Storage::disk('uploads')->put($newFilename, (string) $webpImage);
            $imagePath = $newFilename;
        }

        // --- EXPLICIT MODEL CREATION ---
        $article = new Article();
        $article->user_id = Auth::id();
        $article->category_id = $validated['category_id'];
        $article->title = $validated['title'];
        $article->slug = Str::slug($validated['title']);
        $article->excerpt = $validated['excerpt'];
        $article->content = $validated['content'];
        $article->status = $validated['status'];
        $article->image = $imagePath;
        $article->published_at = (in_array($validated['status'], ['published', 'featured', 'breaking'])) ? now() : null;
        $article->save();

        // Tag handling...
        if (!empty($validated['tags'])) {
            $tagNames = explode(',', $validated['tags']);
            $tagIds = [];
            foreach ($tagNames as $tagName) {
                $tag = Tag::firstOrCreate(['name' => trim($tagName)], ['slug' => Str::slug(trim($tagName))]);
                $tagIds[] = $tag->id;
            }
            $article->tags()->sync($tagIds);
        }

        return redirect()->route('admin.articles.index')->with('success', 'Article created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Article $article)
    {
        $categories = Category::all();
        return view('admin.articles.edit', compact('article', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Article $article)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255|unique:articles,title,' . $article->id,
            'category_id' => 'required|integer|exists:categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'image_url' => 'nullable|url|max:2000|required_without:image',
            'tags' => 'nullable|string',
            'excerpt' => 'required|string|max:500',
            'content' => 'required|string',
            'status' => 'required|string|in:draft,published,featured,breaking',
        ]);

        $imagePath = $request->input('current_image');
        if (!empty($validated['image_url'])) { $imagePath = $validated['image_url']; }
        if ($request->hasFile('image')) {
            if ($article->image && !Str::startsWith($article->image, 'http')) {
                Storage::disk('uploads')->delete($article->image);
            }
            $imageFile = $request->file('image');
            $newFilename = Str::slug(pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME)) . '_' . time() . '.webp';
            $webpImage = Image::make($imageFile)->encode('webp', 80);
            Storage::disk('uploads')->put($newFilename, (string) $webpImage);
            $imagePath = $newFilename;
        }

        // --- THIS IS THE CORRECTED LOGIC ---
        // We set each property individually instead of using update()
        $article->category_id = $validated['category_id'];
        $article->title = $validated['title'];
        $article->slug = Str::slug($validated['title']);
        $article->excerpt = $validated['excerpt'];
        $article->content = $validated['content'];
        $article->status = $validated['status']; // This will now be handled correctly
        $article->image = $imagePath;
        if (in_array($validated['status'], ['published', 'featured', 'breaking']) && is_null($article->published_at)) {
            $article->published_at = now();
        } elseif ($validated['status'] === 'draft') {
            $article->published_at = null;
        }
        $article->save(); // We use save() to commit the changes

        // Tag handling...
        $tagIds = [];
        if (!empty($validated['tags'])) {
            $tagNames = explode(',', $validated['tags']);
            foreach ($tagNames as $tagName) {
                $tag = Tag::firstOrCreate(['name' => trim($tagName)], ['slug' => Str::slug(trim($tagName))]);
                $tagIds[] = $tag->id;
            }
        }
        $article->tags()->sync($tagIds);

        return redirect()->route('admin.articles.index')->with('success', 'Article updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Article $article)
    {
        if ($article->image && !Str::startsWith($article->image, 'http')) {
            Storage::disk('uploads')->delete($article->image);
        }
        $article->delete();
        return redirect()->route('admin.articles.index')->with('success', 'Article deleted successfully.');
    }
}