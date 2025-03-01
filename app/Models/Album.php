<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Album extends Model
{
    //
    use HasFactory;
    protected $fillable=[
        'title',
        'artist_id',
        'album_cover',
    ];

    public function songs()
    {
        return $this->belongsToMany(Song::class);
    }

    public function artist(){
        return $this->belongsTo(Artist::class,'artist_id');
    }
}
