<?php

namespace Tests\Feature;

use App\Models\ReferenceRange;
use App\Models\Test;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TestManagementTest extends TestCase
{
    use RefreshDatabase;

    private function testData(array $overrides = []): array
    {
        return array_merge([
            'test_code'   => 'CBC001',
            'test_name'   => 'Complete Blood Count',
            'category'    => 'Hematology',
            'sample_type' => 'blood',
            'price'       => 250.00,
        ], $overrides);
    }

    // ── CREATE ─────────────────────────────────────────────────────────────

    public function test_can_create_test(): void
    {
        $response = $this->postJson('/api/v1/tests', $this->testData());

        $response->assertCreated()
            ->assertJsonPath('test_code', 'CBC001')
            ->assertJsonPath('is_active', true)
            ->assertJsonStructure(['id', 'test_code', 'test_name', 'price', 'reference_ranges']);

        $this->assertDatabaseHas('tests', ['test_code' => 'CBC001']);
    }

    public function test_create_test_fails_with_duplicate_code(): void
    {
        Test::factory()->create(['test_code' => 'CBC001']);

        $this->postJson('/api/v1/tests', $this->testData())
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['test_code']);
    }

    public function test_create_test_fails_without_required_fields(): void
    {
        $this->postJson('/api/v1/tests', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['test_code', 'test_name', 'category', 'sample_type', 'price']);
    }

    public function test_create_test_fails_with_invalid_sample_type(): void
    {
        $this->postJson('/api/v1/tests', $this->testData(['sample_type' => 'plasma']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['sample_type']);
    }

    // ── READ ───────────────────────────────────────────────────────────────

    public function test_can_list_tests_with_reference_ranges(): void
    {
        $test = Test::factory()->create();
        ReferenceRange::factory()->create(['test_id' => $test->id]);

        $response = $this->getJson('/api/v1/tests');

        $response->assertOk()
            ->assertJsonStructure(['data' => [['id', 'test_name', 'reference_ranges']]]);
    }

    public function test_can_filter_tests_by_category(): void
    {
        Test::factory()->create(['category' => 'Hematology']);
        Test::factory()->create(['category' => 'Biochemistry']);

        $response = $this->getJson('/api/v1/tests?category=Hematology');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('Hematology', $response->json('data.0.category'));
    }

    public function test_active_only_filter_excludes_inactive_tests(): void
    {
        Test::factory()->create(['is_active' => true]);
        Test::factory()->create(['is_active' => false]);

        $response = $this->getJson('/api/v1/tests?active_only=1');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }

    public function test_can_show_single_test_with_ranges(): void
    {
        $test = Test::factory()->create();
        ReferenceRange::factory()->count(3)->create(['test_id' => $test->id]);

        $response = $this->getJson("/api/v1/tests/{$test->id}");

        $response->assertOk()
            ->assertJsonPath('id', $test->id)
            ->assertJsonCount(3, 'reference_ranges');
    }

    // ── UPDATE ─────────────────────────────────────────────────────────────

    public function test_can_update_test(): void
    {
        $test = Test::factory()->create(['price' => 100]);

        $response = $this->putJson("/api/v1/tests/{$test->id}", $this->testData(['price' => 350, 'test_code' => $test->test_code]));

        $response->assertOk()->assertJsonPath('price', '350.00');
        $this->assertDatabaseHas('tests', ['id' => $test->id, 'price' => 350]);
    }

    // ── DELETE ─────────────────────────────────────────────────────────────

    public function test_can_soft_delete_test(): void
    {
        $test = Test::factory()->create();

        $this->deleteJson("/api/v1/tests/{$test->id}")->assertNoContent();

        $this->assertSoftDeleted('tests', ['id' => $test->id]);
    }

    // ── REFERENCE RANGES ───────────────────────────────────────────────────

    public function test_can_add_reference_range_to_test(): void
    {
        $test = Test::factory()->create();

        $response = $this->postJson("/api/v1/tests/{$test->id}/ranges", [
            'parameter_name' => 'Hemoglobin',
            'unit'           => 'g/dL',
            'min_value'      => 13.0,
            'max_value'      => 17.0,
            'gender_filter'  => 'male',
        ]);

        $response->assertCreated()
            ->assertJsonPath('parameter_name', 'Hemoglobin')
            ->assertJsonPath('test_id', $test->id);

        $this->assertDatabaseHas('reference_ranges', ['test_id' => $test->id, 'parameter_name' => 'Hemoglobin']);
    }

    public function test_can_update_reference_range(): void
    {
        $test  = Test::factory()->create();
        $range = ReferenceRange::factory()->create(['test_id' => $test->id, 'min_value' => 10]);

        $response = $this->putJson("/api/v1/tests/{$test->id}/ranges/{$range->id}", [
            'parameter_name' => $range->parameter_name,
            'min_value'      => 12.0,
            'max_value'      => 18.0,
        ]);

        $response->assertOk()->assertJsonPath('min_value', '12.000');
    }

    public function test_can_delete_reference_range(): void
    {
        $test  = Test::factory()->create();
        $range = ReferenceRange::factory()->create(['test_id' => $test->id]);

        $this->deleteJson("/api/v1/tests/{$test->id}/ranges/{$range->id}")->assertNoContent();

        $this->assertDatabaseMissing('reference_ranges', ['id' => $range->id]);
    }
}
