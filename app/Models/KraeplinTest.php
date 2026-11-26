<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KraeplinTest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'test_data',
        'total_score',
        'correct_answers',
        'wrong_answers',
        'skipped_answers',
        'completion_time',
        'analysis_results',
        'detailed_analysis',
        'speed_factor',
        'rhythm_factor',
        'ausdeur_factor',
        'accuracy_rate',
        'consistency_rate',
        'stability_rate',
        'endurance_rate'
    ];

    protected $casts = [
        'test_data' => 'array',
        'analysis_results' => 'array',
        'detailed_analysis' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function calculateAnalysis()
    {
        // Validasi data sebelum menghitung analisis
        $validation = $this->validateTestData();
        if (!$validation['match']) {
            \Log::warning("Data tidak konsisten sebelum analisis, memperbaiki...");
            $this->fixInconsistentData();
        }

        $totalQuestions = 50;
        $attemptedQuestions = $this->correct_answers + $this->wrong_answers;
        $accuracy = $attemptedQuestions > 0 ? ($this->correct_answers / $attemptedQuestions) * 100 : 0;
        $speed = $this->completion_time > 0 ? ($totalQuestions / ($this->completion_time / 60)) : 0;
        
        // Hitung analisis detail
        $detailedAnalysis = $this->calculateDetailedAnalysis();

        $analysis = [
            'accuracy' => round($accuracy, 2),
            'speed' => round($speed, 2),
            'consistency' => $this->calculateConsistency(),
            'fatigue_index' => $this->calculateFatigueIndex(),
            'performance_level' => $this->getPerformanceLevel($accuracy, $speed),
            'skipped_answers' => $this->skipped_answers ?? 0,
            'completion_rate' => $totalQuestions > 0 ? round(($attemptedQuestions / $totalQuestions) * 100, 2) : 0,
        ];

        // Update factors ke database
        $this->update([
            'speed_factor' => $detailedAnalysis['speed_factor'],
            'rhythm_factor' => $detailedAnalysis['rhythm_factor'],
            'ausdeur_factor' => $detailedAnalysis['ausdeur_factor'],
            'accuracy_rate' => $detailedAnalysis['accuracy_rate'],
            'consistency_rate' => $detailedAnalysis['consistency_rate'],
            'stability_rate' => $detailedAnalysis['stability_rate'],
            'endurance_rate' => $detailedAnalysis['endurance_rate'],
            'detailed_analysis' => $detailedAnalysis,
            'analysis_results' => $analysis
        ]);

        return $analysis;
    }


    private function calculateDetailedAnalysis()
    {
        $data = $this->test_data;
        $totalQuestions = count($data);
        
        if (!is_array($data) || count($data) === 0) {
            return $this->getDefaultDetailedAnalysis();
        }

        // Pangker (First 10 questions) - Soal 1-10
        $pangker = array_slice($data, 0, 10);
        $pangkerCorrect = $this->countCorrectAnswers($pangker);
        $pangkerScore = $pangkerCorrect;
        
        // Janker (Middle 30 questions) - Soal 11-40
        $janker = array_slice($data, 10, 30);
        $jankerCorrect = $this->countCorrectAnswers($janker);
        $jankerScore = $jankerCorrect;
        
        // Tiangker (Last 10 questions) - Soal 41-50
        $tiangker = array_slice($data, 40, 10);
        $tiangkerCorrect = $this->countCorrectAnswers($tiangker);
        $tiangkerScore = $tiangkerCorrect;
        
        // Y(0) - First question performance
        $y0 = isset($data[0]) && ($data[0]['is_correct'] ?? false) ? 1 : 0;
        
        // Y(50) - Last question performance  
        $lastIndex = count($data) - 1;
        $y50 = isset($data[$lastIndex]) && ($data[$lastIndex]['is_correct'] ?? false) ? 1 : 0;
        
        // Y(50) - Y(0) - Performance difference
        $y50_minus_y0 = $y50 - $y0;
        
        // Calculate factors
        $speedFactor = $this->calculateSpeedFactor();
        $rhythmFactor = $this->calculateRhythmFactor();
        $ausdeurFactor = $this->calculateAusdeurFactor();
        
        // Additional metrics
        $accuracyRate = $this->calculateAccuracyRate();
        $consistencyRate = $this->calculateConsistencyRate();
        $stabilityRate = $this->calculateStabilityRate();
        $enduranceRate = $this->calculateEnduranceRate();

        return [
            'pangker' => $pangkerScore,
            'janker' => $jankerScore,
            'tiangker' => $tiangkerScore,
            'y0' => $y0,
            'y50' => $y50,
            'y50_minus_y0' => $y50_minus_y0,
            'speed_factor' => $speedFactor,
            'rhythm_factor' => $rhythmFactor,
            'ausdeur_factor' => $ausdeurFactor,
            'accuracy_rate' => $accuracyRate,
            'consistency_rate' => $consistencyRate,
            'stability_rate' => $stabilityRate,
            'endurance_rate' => $enduranceRate,
            'section_analysis' => [
                'pangker_percentage' => $pangkerScore > 0 ? round(($pangkerScore / 10) * 100, 2) : 0,
                'janker_percentage' => $jankerScore > 0 ? round(($jankerScore / 30) * 100, 2) : 0,
                'tiangker_percentage' => $tiangkerScore > 0 ? round(($tiangkerScore / 10) * 100, 2) : 0
            ]
        ];
    }

    private function countCorrectAnswers($questions)
    {
        if (!is_array($questions)) return 0;
        
        $correct = 0;
        foreach ($questions as $question) {
            if (($question['is_correct'] ?? false) && !($question['skipped'] ?? false)) {
                $correct++;
            }
        }
        return $correct;
    }

    private function calculateSpeedFactor()
    {
        $totalWork = 50;
        $timeInMinutes = $this->completion_time / 60;
        
        if ($timeInMinutes > 0) {
            $speed = $totalWork / $timeInMinutes;
            return round($speed, 2);
        }
        
        return 0;
    }

    private function calculateRhythmFactor()
    {
        $data = $this->test_data;
        if (!is_array($data) || count($data) < 2) {
            return 100;
        }

        $responseTimes = [];
        for ($i = 0; $i < count($data); $i++) {
            if (isset($data[$i]['time_taken']) && $data[$i]['time_taken'] !== null) {
                $responseTimes[] = $data[$i]['time_taken'];
            }
        }

        if (count($responseTimes) < 2) {
            return 100;
        }

        // Hitung variasi waktu respons
        $mean = array_sum($responseTimes) / count($responseTimes);
        $variance = 0;
        foreach ($responseTimes as $time) {
            $variance += pow($time - $mean, 2);
        }
        $variance /= count($responseTimes);
        $stdDev = sqrt($variance);

        // Rhythm factor: semakin kecil stdDev, semakin baik (mendekati 100)
        $rhythmFactor = max(0, 100 - ($stdDev * 5));
        return round($rhythmFactor, 2);
    }

    private function calculateAusdeurFactor()
    {
        $data = $this->test_data;
        if (!is_array($data) || count($data) < 4) {
            return 100;
        }

        $firstHalf = array_slice($data, 0, 25);
        $secondHalf = array_slice($data, 25);

        $firstHalfCorrect = $this->countCorrectAnswers($firstHalf);
        $secondHalfCorrect = $this->countCorrectAnswers($secondHalf);

        if ($firstHalfCorrect > 0) {
            $enduranceRatio = ($secondHalfCorrect / $firstHalfCorrect) * 100;
            return round(min(150, max(0, $enduranceRatio)), 2);
        }

        return $secondHalfCorrect > 0 ? 100 : 0;
    }

    private function calculateAccuracyRate()
    {
        $attempted = $this->correct_answers + $this->wrong_answers;
        if ($attempted > 0) {
            $accuracy = ($this->correct_answers / $attempted) * 100;
            return round($accuracy, 2);
        }
        return 0;
    }

    private function calculateConsistencyRate()
    {
        $data = $this->test_data;
        if (!is_array($data) || count($data) < 5) {
            return 100;
        }

        // Group by 5 questions
        $groupScores = [];
        $groupSize = 5;
        
        for ($i = 0; $i < count($data); $i += $groupSize) {
            $group = array_slice($data, $i, $groupSize);
            $groupScore = $this->countCorrectAnswers($group);
            $groupScores[] = $groupScore;
        }

        if (count($groupScores) > 1) {
            $mean = array_sum($groupScores) / count($groupScores);
            if ($mean == 0) return 100;
            
            $variance = 0;
            foreach ($groupScores as $score) {
                $variance += pow($score - $mean, 2);
            }
            $variance /= count($groupScores);
            $stdDev = sqrt($variance);
            
            $consistency = max(0, 100 - ($stdDev / $mean * 100));
            return round($consistency, 2);
        }

        return 100;
    }

    private function calculateStabilityRate()
    {
        $data = $this->test_data;
        if (!is_array($data) || count($data) < 10) {
            return 100;
        }

        $quarters = array_chunk($data, ceil(count($data) / 4));
        $quarterScores = [];
        
        foreach ($quarters as $quarter) {
            $quarterScores[] = $this->countCorrectAnswers($quarter);
        }

        if (count($quarterScores) > 1) {
            $maxScore = max($quarterScores);
            $minScore = min($quarterScores);
            
            if ($maxScore > 0) {
                $stability = (1 - (($maxScore - $minScore) / $maxScore)) * 100;
                return round(max(0, $stability), 2);
            }
        }

        return 100;
    }

    private function calculateEnduranceRate()
    {
        $data = $this->test_data;
        if (!is_array($data) || count($data) < 4) {
            return 100;
        }

        $firstQuarter = array_slice($data, 0, floor(count($data) / 4));
        $lastQuarter = array_slice($data, -floor(count($data) / 4));

        $firstScore = $this->countCorrectAnswers($firstQuarter);
        $lastScore = $this->countCorrectAnswers($lastQuarter);

        if ($firstScore > 0) {
            $endurance = ($lastScore / $firstScore) * 100;
            return round(min(200, max(0, $endurance)), 2);
        }

        return $lastScore > 0 ? 100 : 0;
    }

    private function calculateConsistency()
    {
        return $this->calculateConsistencyRate();
    }

    private function calculateFatigueIndex()
    {
        $data = $this->test_data;
        if (!is_array($data) || count($data) < 4) {
            return 0;
        }
        
        $firstHalf = array_slice($data, 0, floor(count($data) / 2));
        $secondHalf = array_slice($data, floor(count($data) / 2));
        
        $firstScores = array_column($firstHalf, 'score');
        $secondScores = array_column($secondHalf, 'score');
        
        $firstAvg = count($firstScores) > 0 ? array_sum($firstScores) / count($firstScores) : 0;
        $secondAvg = count($secondScores) > 0 ? array_sum($secondScores) / count($secondScores) : 0;
        
        if ($firstAvg == 0) return 0;
        
        $fatigueIndex = max(0, (($firstAvg - $secondAvg) / $firstAvg) * 100);
        return round($fatigueIndex, 2);
    }

    private function getPerformanceLevel($accuracy, $speed)
    {
        if ($accuracy >= 90 && $speed >= 30) return 'Sangat Baik';
        if ($accuracy >= 80 && $speed >= 25) return 'Baik';
        if ($accuracy >= 70 && $speed >= 20) return 'Cukup';
        if ($accuracy >= 60 && $speed >= 15) return 'Sedang';
        return 'Perlu Perbaikan';
    }

    private function getDefaultDetailedAnalysis()
    {
        return [
            'pangker' => 0,
            'janker' => 0,
            'tiangker' => 0,
            'y0' => 0,
            'y50' => 0,
            'y50_minus_y0' => 0,
            'speed_factor' => 0,
            'rhythm_factor' => 0,
            'ausdeur_factor' => 0,
            'accuracy_rate' => 0,
            'consistency_rate' => 0,
            'stability_rate' => 0,
            'endurance_rate' => 0,
            'section_analysis' => [
                'pangker_percentage' => 0,
                'janker_percentage' => 0,
                'tiangker_percentage' => 0
            ]
        ];
    }

    // Method untuk memastikan analisis sudah dihitung
    public function ensureAnalysisCalculated()
    {
        if (empty($this->analysis_results) || empty($this->detailed_analysis)) {
            $this->calculateAnalysis();
            $this->refresh();
        }
        return $this;
    }

    // Validasi data tes dengan perhitungan ulang
    // Validasi data tes dengan perhitungan ulang yang lebih akurat
    public function validateTestData()
    {
        $testData = $this->test_data;
        $correctCount = 0;
        $wrongCount = 0;
        $skippedCount = 0;
        $totalScore = 0;
        
        \Log::info("=== VALIDATING TEST DATA ===");
        
        foreach ($testData as $index => $question) {
            // Pastikan semua field ada dengan nilai default
            $userAnswer = $question['user_answer'] ?? null;
            $isCorrect = $question['is_correct'] ?? false;
            $skipped = $question['skipped'] ?? false;
            $correctAnswer = $question['correct_answer'] ?? 0;
            
            // Debug setiap soal
            \Log::info("Q" . ($index + 1) . ": {$question['number1']} + {$question['number2']} = {$correctAnswer} | " .
                      "User: {$userAnswer} | Correct: " . ($isCorrect ? 'YES' : 'NO') . " | Skipped: " . ($skipped ? 'YES' : 'NO'));
            
            if ($skipped) {
                $skippedCount++;
            } elseif ($isCorrect) {
                $correctCount++;
                $totalScore++;
            } else {
                $wrongCount++;
            }
        }
        
        $match = ($correctCount == $this->correct_answers && 
                 $wrongCount == $this->wrong_answers && 
                 $skippedCount == $this->skipped_answers &&
                 $totalScore == $this->total_score);
        
        if (!$match) {
            \Log::warning("DATA MISMATCH DETECTED!");
            \Log::warning("Calculated - Correct: {$correctCount}, Wrong: {$wrongCount}, Skipped: {$skippedCount}, Score: {$totalScore}");
            \Log::warning("Stored - Correct: {$this->correct_answers}, Wrong: {$this->wrong_answers}, Skipped: {$this->skipped_answers}, Score: {$this->total_score}");
        }
        
        return [
            'calculated_correct' => $correctCount,
            'calculated_wrong' => $wrongCount,
            'calculated_skipped' => $skippedCount,
            'calculated_score' => $totalScore,
            'stored_correct' => $this->correct_answers,
            'stored_wrong' => $this->wrong_answers,
            'stored_skipped' => $this->skipped_answers,
            'stored_score' => $this->total_score,
            'match' => $match
        ];
    }

    // Method untuk memperbaiki data yang tidak konsisten
    public function fixInconsistentData()
    {
        $validation = $this->validateTestData();
        
        if (!$validation['match']) {
            \Log::warning("Fixing inconsistent data for test ID: " . $this->id);
            
            $this->update([
                'correct_answers' => $validation['calculated_correct'],
                'wrong_answers' => $validation['calculated_wrong'],
                'skipped_answers' => $validation['calculated_skipped'],
                'total_score' => $validation['calculated_score']
            ]);
            
            // Recalculate analysis
            $this->calculateAnalysis();
            
            \Log::info("Data fixed for test ID: " . $this->id);
            \Log::info("New values - Correct: {$this->correct_answers}, Wrong: {$this->wrong_answers}, Skipped: {$this->skipped_answers}, Score: {$this->total_score}");
        }
        
        return $this;
    }

}