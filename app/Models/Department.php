<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    // Define the table name if it's non-standard
    protected $table = 'departments';

    // Specify the fields that are mass assignable
    protected $fillable = [
        'name', // If your table has additional fields like "name"
    ];

    /**
     * Define a relationship with the Employee model.
     */
    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}
