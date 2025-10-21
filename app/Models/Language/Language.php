<?php

namespace App\Models\Language;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Language extends Model
{
    use HasFactory, SoftDeletes;
    protected $primaryKey = 'id';
    protected $table = 'language.languages';

    protected $fillable = ['name', 'code', 'status'];


    public function translate()
    {
        return $this->hasMany(Translate::class, 'name', 'code');
    }
}
