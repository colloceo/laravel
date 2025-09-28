<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Category;
use App\Models\User;
use App\Models\Comment;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id', 'user_id', 'title', 'slug', 'excerpt',
        'content', 'image', 'status', 'published_at'
    ];

    protected $casts = [
        'published_at' => 'datetime',
        // ADD THIS LINE TO FIX THE ENUM ISSUE
        'status' => 'string',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    /**
     * Accessor to calculate the estimated reading time for the article.
     *
     * @return int
     */
    public function getReadingTimeAttribute(): int
    {
        // Average reading speed in words per minute
        $wordsPerMinute = 200;

        // Count the words in the article's content after stripping HTML tags
        $wordCount = str_word_count(strip_tags($this->content));

        // Calculate the reading time and round up to the nearest whole number
        // The max(1, ...) ensures it never shows "0 min read" for very short articles
        $readingTime = ceil($wordCount / $wordsPerMinute);

        return max(1, $readingTime);
    }
}
