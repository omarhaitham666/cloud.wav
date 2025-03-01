<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FamousArtistRequest extends Model
{
    //
    use HasFactory;
    protected $fillable=[
        'user_id',
        'famous_profile_image',
        'famous_name',
        'famous_email',
        'famous_number',
        'famous_whatsapp_number',
        'famous_details',
        'famous_social_links',
        'famous_division',
        'famous_id_card_image',
        'status',
    ];
    public function user(){
        return $this->belongsTo(User::class);
    }

}
