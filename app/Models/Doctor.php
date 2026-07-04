<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['id', 'name', 'years_of_experience'])]
class Doctor extends Model
{
    protected $table = 'doctors';

    public $timestamps = false;

    public function specializations(): BelongsToMany
    {
        return $this->belongsToMany(
            Specialization::class,
            'doctors_specializations',
            'doctor_id',
            'specialization_id'
        );
    }

    public function connections(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'doctors_network',
            'doctor_1_id',
            'doctor_2_id'
        );
    }
}
