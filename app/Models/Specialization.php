<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['id', 'specialization'])]
class Specialization extends Model
{
    protected $table = 'specializations';

    public $timestamps = false;

    public $incrementing = false;

    public function doctors(): BelongsToMany
    {
        return $this->belongsToMany(
            Doctor::class,
            'doctors_specializations',
            'specialization_id',
            'doctor_id'
        );
    }
}
