<?php

namespace App\Models\LayerInfo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\BuildingInfo\Building;
class Lic extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $primaryKey = 'id';
    protected $table = 'layer_info.low_income_communities';

    public function buildings(){
        return $this->hasMany(Building::class,'lic_id','id');
    }
}
