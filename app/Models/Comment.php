<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Article;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = ['article_id', 'author_name', 'content'];

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }
}
