<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\KraeplinTest;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DummyDataSeeder extends Seeder
{
    public function run()
    {
        // Create dummy users
        $users = [
            [
                'name' => 'Ahmad Santoso',
                'email' => 'ahmad.santoso@example.com',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'has_taken_test' => true,
                'created_at' => Carbon::now()->subDays(5)
            ],
            [
                'name' => 'Siti Rahayu',
                'email' => 'siti.rahayu@example.com',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'has_taken_test' => true,
                'created_at' => Carbon::now()->subDays(4)
            ],
            [
                'name' => 'Budi Prasetyo',
                'email' => 'budi.prasetyo@example.com',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'has_taken_test' => true,
                'created_at' => Carbon::now()->subDays(3)
            ],
            [
                'name' => 'Maya Sari',
                'email' => 'maya.sari@example.com',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'has_taken_test' => true,
                'created_at' => Carbon::now()->subDays(2)
            ],
            [
                'name' => 'Rizki Abdullah',
                'email' => 'rizki.abdullah@example.com',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'has_taken_test' => true,
                'created_at' => Carbon::now()->subDays(1)
            ]
        ];

        foreach ($users as $userData) {
            $user = User::create($userData);
            
            // Create test data for each user
            $this->createTestData($user);
        }

        $this->command->info('Dummy data created successfully!');
    }

    private function createTestData($user)
    {
        // Generate random test data
        $correctAnswers = rand(25, 45);
        $wrongAnswers = rand(3, 15);
        $skippedAnswers = 50 - $correctAnswers - $wrongAnswers;
        $completionTime = rand(300, 700); // 5-12 minutes in seconds

        // Generate detailed test data
        $testData = [];
        for ($i = 0; $i < 50; $i++) {
            $num1 = rand(1, 9);
            $num2 = rand(1, 9);
            $isCorrect = $i < $correctAnswers;
            $skipped = $i >= ($correctAnswers + $wrongAnswers);
            
            $testData[] = [
                'number1' => $num1,
                'number2' => $num2,
                'correct_answer' => $num1 + $num2,
                'user_answer' => $isCorrect ? $num1 + $num2 : ($skipped ? null : rand(2, 18)),
                'is_correct' => $isCorrect,
                'time_taken' => rand(5, 15),
                'score' => $isCorrect ? 1 : 0,
                'displayed_at' => now()->subSeconds($i * 10),
                'skipped' => $skipped
            ];
        }

        // Calculate analysis factors
        $speedFactor = round(50 / ($completionTime / 60), 2);
        $accuracyRate = round(($correctAnswers / ($correctAnswers + $wrongAnswers)) * 100, 2);
        $rhythmFactor = rand(65, 95);
        $ausdeurFactor = rand(70, 110);
        $consistencyRate = rand(75, 90);
        $stabilityRate = rand(70, 95);
        $enduranceRate = rand(65, 120);

        // Pangker, Janker, Tiangker scores
        $pangker = rand(6, 10);
        $janker = rand(18, 28);
        $tiangker = rand(5, 9);

        $detailedAnalysis = [
            'pangker' => $pangker,
            'janker' => $janker,
            'tiangker' => $tiangker,
            'y0' => 1,
            'y50' => rand(0, 1),
            'y50_minus_y0' => rand(-1, 1),
            'speed_factor' => $speedFactor,
            'rhythm_factor' => $rhythmFactor,
            'ausdeur_factor' => $ausdeurFactor,
            'accuracy_rate' => $accuracyRate,
            'consistency_rate' => $consistencyRate,
            'stability_rate' => $stabilityRate,
            'endurance_rate' => $enduranceRate,
            'section_analysis' => [
                'pangker_percentage' => round(($pangker / 10) * 100, 2),
                'janker_percentage' => round(($janker / 30) * 100, 2),
                'tiangker_percentage' => round(($tiangker / 10) * 100, 2)
            ]
        ];

        // Determine performance level
        $performanceLevel = $this->getPerformanceLevel($accuracyRate, $speedFactor);

        $analysisResults = [
            'accuracy' => $accuracyRate,
            'speed' => $speedFactor,
            'consistency' => $consistencyRate,
            'fatigue_index' => rand(5, 25),
            'performance_level' => $performanceLevel,
            'skipped_answers' => $skippedAnswers,
            'completion_rate' => round((($correctAnswers + $wrongAnswers) / 50) * 100, 2)
        ];

        // Create the test record
        KraeplinTest::create([
            'user_id' => $user->id,
            'test_data' => $testData,
            'total_score' => $correctAnswers,
            'correct_answers' => $correctAnswers,
            'wrong_answers' => $wrongAnswers,
            'skipped_answers' => $skippedAnswers,
            'completion_time' => $completionTime,
            'analysis_results' => $analysisResults,
            'detailed_analysis' => $detailedAnalysis,
            'speed_factor' => $speedFactor,
            'rhythm_factor' => $rhythmFactor,
            'ausdeur_factor' => $ausdeurFactor,
            'accuracy_rate' => $accuracyRate,
            'consistency_rate' => $consistencyRate,
            'stability_rate' => $stabilityRate,
            'endurance_rate' => $enduranceRate,
            'created_at' => $user->created_at->addHours(2)
        ]);
    }

    private function getPerformanceLevel($accuracy, $speed)
    {
        if ($accuracy >= 90 && $speed >= 30) return 'Sangat Baik';
        if ($accuracy >= 80 && $speed >= 25) return 'Baik';
        if ($accuracy >= 70 && $speed >= 20) return 'Cukup';
        if ($accuracy >= 60 && $speed >= 15) return 'Sedang';
        return 'Perlu Perbaikan';
    }
}