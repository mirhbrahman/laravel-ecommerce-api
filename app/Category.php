<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Transformers\CategoryTransformer;

class Category extends Model
{
    public $transformer = CategoryTransformer::class;

    protected $fillable = [
        'name',
        'description',
    ];

    protected $hidden = [
        'pivot',
    ];

    public function products()
    {
        return $this->belongsToMany('App\Product');
    }
}
