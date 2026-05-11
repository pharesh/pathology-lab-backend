<?php

namespace Database\Seeders;

use App\Models\ReferenceRange;
use App\Models\Test;
use Illuminate\Database\Seeder;

class TestSeeder extends Seeder
{
    public function run(): void
    {
        $tests = [
            [
                'test_code' => 'CBC001',
                'test_name' => 'Complete Blood Count (CBC)',
                'category'  => 'Hematology',
                'sample_type' => 'blood',
                'price'     => 250.00,
                'turnaround_hours' => 4,
                'ranges' => [
                    ['parameter_name' => 'Hemoglobin', 'unit' => 'g/dL', 'min_value' => 13.0, 'max_value' => 17.0, 'gender_filter' => 'male'],
                    ['parameter_name' => 'Hemoglobin', 'unit' => 'g/dL', 'min_value' => 11.5, 'max_value' => 15.5, 'gender_filter' => 'female'],
                    ['parameter_name' => 'WBC Count', 'unit' => '10³/µL', 'min_value' => 4.0, 'max_value' => 11.0, 'gender_filter' => 'all'],
                    ['parameter_name' => 'Platelet Count', 'unit' => '10³/µL', 'min_value' => 150.0, 'max_value' => 400.0, 'gender_filter' => 'all'],
                    ['parameter_name' => 'RBC Count', 'unit' => '10⁶/µL', 'min_value' => 4.5, 'max_value' => 5.5, 'gender_filter' => 'male'],
                    ['parameter_name' => 'RBC Count', 'unit' => '10⁶/µL', 'min_value' => 3.8, 'max_value' => 5.0, 'gender_filter' => 'female'],
                    ['parameter_name' => 'Hematocrit (PCV)', 'unit' => '%', 'min_value' => 40.0, 'max_value' => 52.0, 'gender_filter' => 'male'],
                    ['parameter_name' => 'Hematocrit (PCV)', 'unit' => '%', 'min_value' => 36.0, 'max_value' => 46.0, 'gender_filter' => 'female'],
                ],
            ],
            [
                'test_code' => 'LFT001',
                'test_name' => 'Liver Function Test (LFT)',
                'category'  => 'Biochemistry',
                'sample_type' => 'blood',
                'price'     => 500.00,
                'turnaround_hours' => 6,
                'ranges' => [
                    ['parameter_name' => 'Total Bilirubin', 'unit' => 'mg/dL', 'min_value' => 0.2, 'max_value' => 1.2, 'gender_filter' => 'all'],
                    ['parameter_name' => 'Direct Bilirubin', 'unit' => 'mg/dL', 'min_value' => 0.0, 'max_value' => 0.3, 'gender_filter' => 'all'],
                    ['parameter_name' => 'SGOT (AST)', 'unit' => 'U/L', 'min_value' => 10.0, 'max_value' => 40.0, 'gender_filter' => 'all'],
                    ['parameter_name' => 'SGPT (ALT)', 'unit' => 'U/L', 'min_value' => 7.0, 'max_value' => 56.0, 'gender_filter' => 'all'],
                    ['parameter_name' => 'Alkaline Phosphatase', 'unit' => 'U/L', 'min_value' => 44.0, 'max_value' => 147.0, 'gender_filter' => 'all'],
                    ['parameter_name' => 'Total Protein', 'unit' => 'g/dL', 'min_value' => 6.0, 'max_value' => 8.3, 'gender_filter' => 'all'],
                    ['parameter_name' => 'Albumin', 'unit' => 'g/dL', 'min_value' => 3.5, 'max_value' => 5.0, 'gender_filter' => 'all'],
                ],
            ],
            [
                'test_code' => 'KFT001',
                'test_name' => 'Kidney Function Test (KFT)',
                'category'  => 'Biochemistry',
                'sample_type' => 'blood',
                'price'     => 450.00,
                'turnaround_hours' => 6,
                'ranges' => [
                    ['parameter_name' => 'Blood Urea', 'unit' => 'mg/dL', 'min_value' => 15.0, 'max_value' => 45.0, 'gender_filter' => 'all'],
                    ['parameter_name' => 'Serum Creatinine', 'unit' => 'mg/dL', 'min_value' => 0.7, 'max_value' => 1.2, 'gender_filter' => 'male'],
                    ['parameter_name' => 'Serum Creatinine', 'unit' => 'mg/dL', 'min_value' => 0.5, 'max_value' => 1.0, 'gender_filter' => 'female'],
                    ['parameter_name' => 'Uric Acid', 'unit' => 'mg/dL', 'min_value' => 3.5, 'max_value' => 7.2, 'gender_filter' => 'male'],
                    ['parameter_name' => 'Uric Acid', 'unit' => 'mg/dL', 'min_value' => 2.6, 'max_value' => 6.0, 'gender_filter' => 'female'],
                    ['parameter_name' => 'Sodium', 'unit' => 'mEq/L', 'min_value' => 135.0, 'max_value' => 145.0, 'gender_filter' => 'all'],
                    ['parameter_name' => 'Potassium', 'unit' => 'mEq/L', 'min_value' => 3.5, 'max_value' => 5.0, 'gender_filter' => 'all'],
                ],
            ],
            [
                'test_code' => 'RBS001',
                'test_name' => 'Random Blood Sugar',
                'category'  => 'Biochemistry',
                'sample_type' => 'blood',
                'price'     => 80.00,
                'turnaround_hours' => 1,
                'ranges' => [
                    ['parameter_name' => 'Blood Glucose (Random)', 'unit' => 'mg/dL', 'min_value' => 70.0, 'max_value' => 140.0, 'gender_filter' => 'all'],
                ],
            ],
            [
                'test_code' => 'FBS001',
                'test_name' => 'Fasting Blood Sugar',
                'category'  => 'Biochemistry',
                'sample_type' => 'blood',
                'price'     => 80.00,
                'turnaround_hours' => 1,
                'ranges' => [
                    ['parameter_name' => 'Blood Glucose (Fasting)', 'unit' => 'mg/dL', 'min_value' => 70.0, 'max_value' => 100.0, 'gender_filter' => 'all'],
                ],
            ],
            [
                'test_code' => 'URINE001',
                'test_name' => 'Urine Routine & Microscopy',
                'category'  => 'Urology',
                'sample_type' => 'urine',
                'price'     => 120.00,
                'turnaround_hours' => 2,
                'ranges' => [
                    ['parameter_name' => 'Color', 'unit' => null, 'text_range' => 'Pale Yellow', 'gender_filter' => 'all'],
                    ['parameter_name' => 'Transparency', 'unit' => null, 'text_range' => 'Clear', 'gender_filter' => 'all'],
                    ['parameter_name' => 'pH', 'unit' => null, 'min_value' => 4.5, 'max_value' => 8.0, 'gender_filter' => 'all'],
                    ['parameter_name' => 'Specific Gravity', 'unit' => null, 'min_value' => 1.005, 'max_value' => 1.030, 'gender_filter' => 'all'],
                    ['parameter_name' => 'Protein', 'unit' => null, 'text_range' => 'Negative', 'gender_filter' => 'all'],
                    ['parameter_name' => 'Glucose', 'unit' => null, 'text_range' => 'Negative', 'gender_filter' => 'all'],
                ],
            ],
        ];

        foreach ($tests as $testData) {
            $ranges = $testData['ranges'];
            unset($testData['ranges']);

            $test = Test::create($testData);

            foreach ($ranges as $range) {
                ReferenceRange::create(array_merge($range, ['test_id' => $test->id]));
            }
        }
    }
}
