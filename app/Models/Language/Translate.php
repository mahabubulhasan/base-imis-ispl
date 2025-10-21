<?php

namespace App\Models\Language;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Translate extends Model
{
    use HasFactory;
    protected $table = 'language.translates';

    protected $fillable = ['key', 'name', 'text', 'pages', 'group', 'platform', 'load'];
}
