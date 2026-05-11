<?php

namespace Tests\Feature;

use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientTest extends TestCase
{
    use RefreshDatabase;

    private function patientData(array $overrides = []): array
    {
        return array_merge([
            'name'   => 'Rahul Sharma',
            'age'    => 30,
            'gender' => 'male',
            'phone'  => '9876543210',
        ], $overrides);
    }

    // ── CREATE ─────────────────────────────────────────────────────────────

    public function test_can_create_patient_with_required_fields(): void
    {
        $response = $this->postJson('/api/v1/patients', $this->patientData());

        $response->assertCreated()
            ->assertJsonPath('name', 'Rahul Sharma')
            ->assertJsonPath('gender', 'male')
            ->assertJsonStructure(['id', 'patient_uid', 'name', 'age', 'gender', 'phone']);

        $this->assertDatabaseHas('patients', ['name' => 'Rahul Sharma', 'phone' => '9876543210']);
    }

    public function test_patient_uid_is_auto_generated(): void
    {
        $response = $this->postJson('/api/v1/patients', $this->patientData());

        $uid = $response->json('patient_uid');
        $this->assertMatchesRegularExpression('/^PAT-\d{4}\d{4}$/', $uid);
    }

    public function test_create_patient_fails_without_required_fields(): void
    {
        $this->postJson('/api/v1/patients', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'age', 'gender', 'phone']);
    }

    public function test_create_patient_fails_with_invalid_gender(): void
    {
        $this->postJson('/api/v1/patients', $this->patientData(['gender' => 'unknown']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['gender']);
    }

    public function test_create_patient_with_all_optional_fields(): void
    {
        $data = $this->patientData([
            'email'       => 'rahul@test.com',
            'address'     => 'Mumbai',
            'referred_by' => 'Dr. Patel',
            'age_unit'    => 'months',
        ]);

        $response = $this->postJson('/api/v1/patients', $data);

        $response->assertCreated()
            ->assertJsonPath('email', 'rahul@test.com')
            ->assertJsonPath('referred_by', 'Dr. Patel')
            ->assertJsonPath('age_unit', 'months');
    }

    // ── READ ───────────────────────────────────────────────────────────────

    public function test_can_list_patients_with_pagination(): void
    {
        Patient::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/patients');

        $response->assertOk()
            ->assertJsonStructure(['current_page', 'data', 'total', 'per_page'])
            ->assertJsonPath('total', 5);
    }

    public function test_can_search_patients_by_name(): void
    {
        Patient::factory()->create(['name' => 'Priya Patel']);
        Patient::factory()->create(['name' => 'Rahul Shah']);

        $response = $this->getJson('/api/v1/patients?search=Priya');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('Priya Patel', $response->json('data.0.name'));
    }

    public function test_can_search_patients_by_phone(): void
    {
        Patient::factory()->create(['phone' => '9111111111']);
        Patient::factory()->create(['phone' => '9222222222']);

        $response = $this->getJson('/api/v1/patients?search=9111');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }

    public function test_can_show_patient_with_order_history(): void
    {
        $patient = Patient::factory()->create();

        $response = $this->getJson("/api/v1/patients/{$patient->id}");

        $response->assertOk()
            ->assertJsonPath('id', $patient->id)
            ->assertJsonStructure(['id', 'patient_uid', 'orders']);
    }

    // ── UPDATE ─────────────────────────────────────────────────────────────

    public function test_can_update_patient(): void
    {
        $patient = Patient::factory()->create(['name' => 'Old Name']);

        $response = $this->putJson("/api/v1/patients/{$patient->id}", $this->patientData(['name' => 'New Name']));

        $response->assertOk()->assertJsonPath('name', 'New Name');
        $this->assertDatabaseHas('patients', ['id' => $patient->id, 'name' => 'New Name']);
    }

    // ── DELETE ─────────────────────────────────────────────────────────────

    public function test_can_soft_delete_patient(): void
    {
        $patient = Patient::factory()->create();

        $this->deleteJson("/api/v1/patients/{$patient->id}")->assertNoContent();

        $this->assertSoftDeleted('patients', ['id' => $patient->id]);
    }

    public function test_soft_deleted_patient_not_in_list(): void
    {
        $patient = Patient::factory()->create();
        $patient->delete();

        $response = $this->getJson('/api/v1/patients');
        $ids = array_column($response->json('data'), 'id');

        $this->assertNotContains($patient->id, $ids);
    }
}
