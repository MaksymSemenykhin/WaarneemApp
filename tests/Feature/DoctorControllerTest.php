<?php

namespace Tests\Feature;

use Tests\DatabaseTestCase;

class DoctorControllerTest extends DatabaseTestCase
{
    public function test_returns_json_response(): void
    {
        $response = $this->getJson('/doctor/network-aggregates/56?specialization=Surgery');

        $response->assertOk();
        $response->assertJsonStructure([
            'specializations_aggregrates',
        ]);
    }

    public function test_returns_expected_specialization_data(): void
    {
        $response = $this->getJson('/doctor/network-aggregates/56?specialization=Surgery');

        $response->assertOk();
        $response->assertJson([
            'specializations_aggregrates' => [
                'Surgery' => 41,
                'Cardiology' => 14,
                'Allergy and immunology' => 15,
                'Anesthesiology' => 9,
            ],
        ]);
    }

    public function test_returns_404_for_nonexistent_doctor(): void
    {
        $response = $this->getJson('/doctor/network-aggregates/999?specialization=Surgery');

        $response->assertNotFound();
    }

    public function test_returns_422_without_specialization(): void
    {
        $response = $this->getJson('/doctor/network-aggregates/56');

        $response->assertStatus(422);
    }

    public function test_returns_yoe_aggregates_with_filter(): void
    {
        $response = $this->getJson('/doctor/network-aggregates/56?specialization=Surgery&min_yoe=3&max_yoe=10');

        $response->assertOk();
        $response->assertJsonStructure([
            'specializations_aggregrates',
            'years_of_experience_aggregates',
        ]);
    }

    public function test_yoe_filter_reduces_specialization_counts(): void
    {
        $all = $this->getJson('/doctor/network-aggregates/56?specialization=Surgery');
        $filtered = $this->getJson('/doctor/network-aggregates/56?specialization=Surgery&min_yoe=3&max_yoe=10');

        $allTotal = array_sum($all->json('specializations_aggregrates'));
        $filteredTotal = array_sum($filtered->json('specializations_aggregrates'));

        $this->assertLessThan($allTotal, $filteredTotal);
    }

    public function test_different_specializations_return_different_results(): void
    {
        $surgery = $this->getJson('/doctor/network-aggregates/56?specialization=Surgery');
        $cardiology = $this->getJson('/doctor/network-aggregates/56?specialization=Cardiology');

        $surgery->assertOk();
        $cardiology->assertOk();

        $surgeryTotal = array_sum($surgery->json('specializations_aggregrates'));
        $cardiologyTotal = array_sum($cardiology->json('specializations_aggregrates'));

        $this->assertNotEquals($surgeryTotal, $cardiologyTotal);
    }

    public function test_works_for_any_doctor(): void
    {
        $response = $this->getJson('/doctor/network-aggregates/1?specialization=Surgery');

        $response->assertOk();
        $response->assertJsonStructure([
            'specializations_aggregrates',
        ]);
    }
}
