<?php

namespace App\Models;

use App\Models\Department;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Employee extends Model
{
    protected $fillable=['emp_name','emp_email','salary','department_id'];
    use HasFactory;
    public function department(){
        return $this->belongsTo(Department::class);
    }
}
