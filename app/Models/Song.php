<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    
}


