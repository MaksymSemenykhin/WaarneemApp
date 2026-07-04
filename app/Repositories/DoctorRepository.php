<?php

namespace App\Repositories;

use App\Models\Doctor;
use App\Models\Specialization;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DoctorRepository
{
    public function findOrFail(int $id): Doctor
    {
        return Doctor::with('specializations')->findOrFail($id);
    }

    public function getNeighborIds(int $doctorId): Collection
    {
        return DB::table('doctors_network')
            ->where('doctor_1_id', $doctorId)
            ->pluck('doctor_2_id');
    }

    public function findByIds(array $ids): Collection
    {
        if (empty($ids)) {
            return collect();
        }

        return Doctor::with('specializations')
            ->whereIn('id', $ids)
            ->get();
    }

    public function getSpecializationIdByName(string $name): ?int
    {
        return Specialization::where('specialization', $name)
            ->value('id');
    }
}
