<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Post extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = ['category_id', 'title', 'description', 'image'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function parts()
    {
        return $this->hasMany(Part::class);
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

}
