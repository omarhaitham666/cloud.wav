<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Artist extends Model
{
    //
    use HasFactory;
    protected $table = 'artists';
    protected $fillable = [
        'user_id', 'profile_image', 'name', 'email', 'number', 'whatsapp_number', 'details', 'social_links','division','famous_id_card_image','type',
    ];
    public function songs(){
        return $this->hasMany(Song::class);
    }

}
