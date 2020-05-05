<?php

namespace App\Models2\HR;

use Illuminate\Database\Eloquent\Model;

class TrainingType extends Model
{
    protected $table = 'hr_training_type';
    protected $primaryKey = 'training_type_id';

    protected $fillable = [
        'training_type_id', 'training_type_name','status'
    ];
}
