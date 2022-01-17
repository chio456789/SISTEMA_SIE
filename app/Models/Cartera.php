<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cartera extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'descripcion','caja_id'];
}
