<?php 
namespace Behin\SimpleWorkflow\Models\Entities; 
use Behin\SimpleWorkflow\Controllers\Core\VariableController; 
use Illuminate\Database\Eloquent\Factories\HasFactory; 
use Illuminate\Database\Eloquent\Model; 
use Illuminate\Support\Str; 
use Illuminate\Database\Eloquent\SoftDeletes;
 class Device_repair extends Model 
{ 
    use SoftDeletes; 
    public $incrementing = false; 
    protected $keyType = 'string'; 
    public $table = 'wf_entity_device_repair'; 
    protected $fillable = ['case_id', 'case_number', 'device_id', 'repairman', 'repair_type', 'repair_subtype', 'repair_start_timestamp', 'repair_pic', 'repairman_assitant', 'repair_report', 'repair_is_approved', 'repair_is_approved_by', 'repair_is_approved_description', 'repair_is_approved_2', 'repair_is_approved_by_2', 'repair_is_approved_description_2', 'repair_is_approved_3', 'repair_is_approved_by_3', 'repair_is_approved_description_3', ]; 
protected static function boot()
        {
            parent::boot();

            static::creating(function ($model) {
                $model->id = $model->id ?? substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'), 0, 10);
            });
        }
}