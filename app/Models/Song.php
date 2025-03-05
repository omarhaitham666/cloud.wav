<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Song extends Model
{
    //
    use HasFactory;

    protected $fillable=[
        'artist_id',
        'title',
        'artist_name',
        'song_path',
        'cover_path',
    ];

    public function artist(){
        return $this->belongsTo(Artist::class,'artist_id');
    }
    public function albums()
    {
        return $this->belongsToMany(Album::class);
    }

    public function likes(){
        return $this->hasMany(Like::class);
    }

    public function isLikedByUser(){


        if (!Auth::check()) {
            return false;
        }
       
        return $this->likes()->where('user_id', Auth::id())->exists();
    }
       
    
}


