<?php

namespace App\Services;

use App\DTOs\NetworkAggregatesDTO;
use App\Models\Doctor;
use App\Repositories\DoctorRepository;
use Illuminate\Support\Collection;

readonly class NetworkAggregationService
{
    public function __construct(
        private readonly DoctorRepository $repository
    ) {}

    public function getNetworkAggregates(NetworkAggregatesDTO $dto): array
    {
        $targetSpecId = $this->repository->getSpecializationIdByName($dto->specialization);

        if ($targetSpecId === null) {
            $result = ['specializations_aggregrates' => []];
            if ($dto->minYoe !== null || $dto->maxYoe !== null) {
                $result['years_of_experience_aggregates'] = [];
            }
            return $result;
        }

        $startDoctor = $this->repository->findOrFail($dto->doctorId);

        $reachableDoctorIds = $this->findReachableDoctors(
            startDoctor: $startDoctor,
            targetSpecId: $targetSpecId,
        );

        //all the reachable surgeons from Roger Green (including Roger Green himself).
        if ($startDoctor->specializations->contains('id', $targetSpecId)) {
            $reachableDoctorIds[] = $startDoctor->id;
        }

        $reachableDoctorIds = array_unique($reachableDoctorIds);

        $doctors = $this->repository->findByIds($reachableDoctorIds);

        if ($dto->minYoe !== null || $dto->maxYoe !== null) {
            $doctors = $doctors->filter(function (Doctor $doctor) use ($dto) {
                $yoe = $doctor->years_of_experience;
                if ($dto->minYoe !== null && $yoe < $dto->minYoe) {
                    return false;
                }
                if ($dto->maxYoe !== null && $yoe > $dto->maxYoe) {
                    return false;
                }
                return true;
            });
        }

        $result = [
            'specializations_aggregrates' => $this->aggregateSpecializations($doctors),
        ];

        if ($dto->minYoe !== null || $dto->maxYoe !== null) {
            $result['years_of_experience_aggregates'] = $this->aggregateYearsOfExperience($doctors);
        }

        return $result;
    }

    /**
     * BFS
     * run through nodes that have the target specialization.
     */
    private function findReachableDoctors(Doctor $startDoctor, int $targetSpecId): array
    {
        $visited = [$startDoctor->id => true];
        $queue = [$startDoctor->id];
        $result = [];

        while (!empty($queue)) {
            $currentId = array_shift($queue);
            $neighborIds = $this->repository->getNeighborIds($currentId);

            foreach ($neighborIds as $neighborId) {
                if (isset($visited[$neighborId])) {
                    continue;
                }

                $neighbor = $this->repository->findOrFail($neighborId);
                $visited[$neighborId] = true;

                if ($neighbor->specializations->contains('id', $targetSpecId)) {
                    $result[] = $neighborId;
                    $queue[] = $neighborId;
                }
            }
        }

        return $result;
    }

    private function aggregateSpecializations(Collection $doctors): array
    {
        $aggregates = [];

        foreach ($doctors as $doctor) {
            foreach ($doctor->specializations as $spec) {
                $name = $spec->specialization;
                $aggregates[$name] = ($aggregates[$name] ?? 0) + 1;
            }
        }

        ksort($aggregates);

        return $aggregates;
    }

    private function aggregateYearsOfExperience(Collection $doctors): array
    {
        $aggregates = [];

        foreach ($doctors as $doctor) {
            $yoe = (string) $doctor->years_of_experience;
            $aggregates[$yoe] = ($aggregates[$yoe] ?? 0) + 1;
        }

        ksort($aggregates);

        return $aggregates;
    }
}
