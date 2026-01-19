<?php

namespace App\Models\Fsm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Yadahan\AuthenticationLog\AuthenticationLogable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class Nsd extends Model
{
    use HasFactory;
    use HasApiTokens;
    use SoftDeletes;
    use AuthenticationLogable;

    protected $table = 'cwis.nsd_setting';
    protected $primaryKey = 'id';
    protected $fillable = ['nsd_username', 'nsd_password', 'api_post_url', 'api_login_url', 'city'];
}
