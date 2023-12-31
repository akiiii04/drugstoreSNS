<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;
    
    protected $fillable=[
            'name'
        ];
        
    public $timestamps = false;
    
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
    public function getTags($post_id)
    {
        return where("post_id", $post_id)->get();
    }
    
}
