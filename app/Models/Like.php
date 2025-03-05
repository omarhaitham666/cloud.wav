<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Like extends Model
{
    //
    use HasFactory;
    protected $fillable=[
    'user_id',
    'song_id',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function song(){
        return $this->belongsTo(Song::class);
    }
}
