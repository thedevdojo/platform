<?php

namespace App\Models;

use Database\Factories\ArticleCategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ArticleCategory extends Model
{
    /** @use HasFactory<ArticleCategoryFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'position',
    ];

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class)->orderBy('position');
    }

    public function publishedArticles(): HasMany
    {
        return $this->articles()->whereNotNull('published_at');
    }
}
