<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    // Define the table name if it doesn't follow Laravel's default naming conventions
    protected $table = 'employees';

    // Specify the fields that are mass assignable
    protected $fillable = [
        'name',
        'email',
        'department_id'
    ];

    // Define the relationship with the Department model
    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
