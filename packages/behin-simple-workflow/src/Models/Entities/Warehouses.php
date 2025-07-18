<?php 
namespace Behin\SimpleWorkflow\Models\Entities; 
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
 class Warehouses extends Model 
{ 
    use SoftDeletes; 
    public $incrementing = false; 
    protected $keyType = 'string'; 
    public $table = 'wf_entity_warehouses'; 
    protected $fillable = ['name', 'manager', 'address', 'phone', 'capacity', ]; 
protected static function boot()
        {
            parent::boot();

            static::creating(function ($model) {
                $model->id = $model->id ?? substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'), 0, 10);
            });
        }
}