<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prompt extends Model
{
    // Set the table name if it's different from the model name
    protected $table = 'prompts';

    // Specify the fillable fields
    protected $fillable = [
        'name',
        'content'
    ];

    // Automatically handle timestamps
    public $timestamps = true;
}
