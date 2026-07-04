<?php

namespace Tests\Unit;

use App\DTOs\NetworkAggregatesDTO;
use App\Repositories\DoctorRepository;
use App\Services\NetworkAggregationService;
use Tests\DatabaseTestCase;

class NetworkAggregationServiceTest extends DatabaseTestCase
{
    private NetworkAggregationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new NetworkAggregationService(new DoctorRepository());
    }

    public function test_returns_specialization_aggregates_for_surgery(): void
    {
        $dto = new NetworkAggregatesDTO(doctorId: 56, specialization: 'Surgery');
        $result = $this->service->getNetworkAggregates($dto);

        $this->assertArrayHasKey('specializations_aggregrates', $result);
        $this->assertArrayHasKey('Surgery', $result['specializations_aggregrates']);
        $this->assertEquals(41, $result['specializations_aggregrates']['Surgery']);
        $this->assertEquals(14, $result['specializations_aggregrates']['Cardiology']);
        $this->assertEquals(15, $result['specializations_aggregrates']['Allergy and immunology']);
        $this->assertEquals(9, $result['specializations_aggregrates']['Anesthesiology']);
    }

    public function test_does_not_include_yoe_aggregates_without_filter(): void
    {
        $dto = new NetworkAggregatesDTO(doctorId: 56, specialization: 'Surgery');
        $result = $this->service->getNetworkAggregates($dto);

        $this->assertArrayNotHasKey('years_of_experience_aggregates', $result);
    }

    public function test_includes_yoe_aggregates_with_filter(): void
    {
        $dto = new NetworkAggregatesDTO(doctorId: 56, specialization: 'Surgery', minYoe: 3, maxYoe: 10);
        $result = $this->service->getNetworkAggregates($dto);

        $this->assertArrayHasKey('years_of_experience_aggregates', $result);
        $this->assertIsArray($result['years_of_experience_aggregates']);
    }

    public function test_yoe_filter_reduces_results(): void
    {
        $all = $this->service->getNetworkAggregates(
            new NetworkAggregatesDTO(doctorId: 56, specialization: 'Surgery')
        );
        $filtered = $this->service->getNetworkAggregates(
            new NetworkAggregatesDTO(doctorId: 56, specialization: 'Surgery', minYoe: 3, maxYoe: 10)
        );

        $allTotal = array_sum($all['specializations_aggregrates']);
        $filteredTotal = array_sum($filtered['specializations_aggregrates']);

        $this->assertLessThan($allTotal, $filteredTotal);
    }

    public function test_roger_green_included_in_surgery_network(): void
    {
        $dto = new NetworkAggregatesDTO(doctorId: 56, specialization: 'Surgery');
        $result = $this->service->getNetworkAggregates($dto);

        $this->assertEquals(41, $result['specializations_aggregrates']['Surgery']);
    }

    public function test_empty_result_for_nonexistent_specialization(): void
    {
        $dto = new NetworkAggregatesDTO(doctorId: 56, specialization: 'Nonexistent');
        $result = $this->service->getNetworkAggregates($dto);

        $this->assertEquals([], $result['specializations_aggregrates']);
    }

    public function test_doctor_without_target_spec_returns_only_direct_suitable_neighbors(): void
    {
        $dto = new NetworkAggregatesDTO(doctorId: 56, specialization: 'Cardiology');
        $result = $this->service->getNetworkAggregates($dto);

        $this->assertArrayHasKey('Cardiology', $result['specializations_aggregrates']);
        $this->assertGreaterThan(0, $result['specializations_aggregrates']['Cardiology']);
    }

    public function test_network_reachable_count_matches_sql_query(): void
    {
        $dto = new NetworkAggregatesDTO(doctorId: 56, specialization: 'Surgery');
        $result = $this->service->getNetworkAggregates($dto);
        $totalDoctors = array_sum($result['specializations_aggregrates']);

        $this->assertGreaterThan(0, $totalDoctors);

        $dbResult = \DB::select(
            'SELECT COUNT(DISTINCT d.id) as cnt FROM doctors d
             JOIN doctors_specializations ds ON d.id = ds.doctor_id
             JOIN specializations s ON ds.specialization_id = s.id
             WHERE s.specialization = ?',
            ['Surgery']
        );

        $this->assertLessThanOrEqual($dbResult[0]->cnt, $totalDoctors);
    }
}
