<?php

namespace App\Models;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Department extends Model
{
    protected $fillable = ['department_name'];
    use HasFactory;
    public function employee(){
        return $this->hasMany(Employee::class);

    }
}
