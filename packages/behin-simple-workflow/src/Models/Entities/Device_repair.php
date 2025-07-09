<?php 
namespace Behin\SimpleWorkflow\Models\Entities; 
use Behin\SimpleWorkflow\Controllers\Core\VariableController; 
use Illuminate\Database\Eloquent\Factories\HasFactory; 
use Illuminate\Database\Eloquent\Model; 
use Illuminate\Support\Str; 
use Illuminate\Database\Eloquent\SoftDeletes;
use BehinUserRoles\Models\User;
 class Device_repair extends Model 
{ 
    use SoftDeletes; 
    public $incrementing = false; 
    protected $keyType = 'string'; 
    public $table = 'wf_entity_device_repair'; 
    protected $fillable = ['case_id', 'case_number', 'device_id', 'repairman', 'repair_type', 'repair_subtype', 'repair_start_timestamp', 'repair_pic', 'repairman_assitant', 'repair_report', 'repair_is_approved', 'repair_is_approved_by', 'repair_is_approved_description', 'repair_is_approved_2', 'repair_is_approved_by_2', 'repair_is_approved_description_2', 'repair_is_approved_3', 'repair_is_approved_by_3', 'repair_is_approved_description_3',  'created_by', 'updated_by', 'contributers', ]; 
protected static function boot()
        {
            parent::boot();

            static::creating(function ($model) {
                $model->id = $model->id ?? substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'), 0, 10);
            });
        }
protected $casts = [
    'repair_type' => 'array',
    'repair_subtype' => 'array',
    'repairman_assitant' => 'array',
];


public function repairman(){
    return User::find($this->repairman);
}

public function getRepairTypeTextAttribute()
{
    if (is_array($this->repair_type)) {
        return implode(',', $this->repair_type);
    }

    return $this->repair_type;
}

public function getRepairSubTypeTextAttribute()
{
    if (is_array($this->repair_subtype)) {
        return implode(',', $this->repair_subtype);
    }

    return $this->repair_subtype;
}

public function getRepairmanAssitantTextAttribute()
{
    if (is_array($this->repairman_assitant)) {
        $users = User::whereIn('id', $this->repairman_assitant)->get();
        return $users->pluck('name')->implode(', ');
    }

    return $this->repairman_assitant;
}}