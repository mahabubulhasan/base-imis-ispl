<?php
// Last Modified Date: 18-04-2024
// Developed By: Innovative Solution Pvt. Ltd. (ISPL)
namespace App\Models\Fsm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Fsm\Application;

class Feedback extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The table name along with the schema.
     *
     * @var String
     */
    protected $table= 'fsm.feedbacks';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'application_id',
        'customer_name',
        'customer_gender',
        'customer_number',
        'customer_address',
        'fsm_service_quality',
        'wear_ppe',
        'comments',
        'service_provider_id',
        'user_id',
        'service_quality_price',
        'service_delivery_efficiency',
        'fsm_quality_level',
        'price_reasonable',
        'advertising_media',
        'safety_measures',
        'payment_mechanism_comments',
        'apply_in_future',
        'apply_in_future_comments',
        'recommend_service',
        'recommend_service_comments',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'fsm_service_quality' => 'boolean',
        'wear_ppe' => 'boolean',
        'price_reasonable' => 'boolean',
        'service_quality_price' => 'integer',
        'service_delivery_efficiency' => 'integer',
        'fsm_quality_level' => 'integer',
        'apply_in_future' => 'integer',
        'recommend_service' => 'integer',
    ];

     /**
     * Get the application associated with the application.
     *
     *
     * @return BelongsTo
     */
    public function application(){
        return $this->belongsTo(Application::class,'application_id','id');
    }
}
