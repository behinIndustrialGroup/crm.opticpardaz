<?php 
namespace Behin\SimpleWorkflow\Models\Entities; 
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
 class Warehouse extends Model 
{ 
    public $table = 'wf_entity_warehouse'; 
    protected $fillable = ['name', 'code', 'address', 'phone', 'manager_name', 'capacity', 'description', 'status', ]; 
}