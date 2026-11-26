<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TestSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'current_test_data',
        'current_position',
        'time_remaining',
        'is_completed',
        'last_question_displayed_at',
        'auto_skip_interval',
        'total_test_time'
    ];

    protected $casts = [
        'current_test_data' => 'array',
        'last_question_displayed_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function generateTestData($totalQuestions = 50)
    {
        $testData = [];
        
        for ($i = 0; $i < $totalQuestions; $i++) {
            $num1 = rand(1, 9);
            $num2 = rand(1, 9);
            $correctAnswer = $num1 + $num2;
            
            $testData[] = [
                'number1' => $num1,
                'number2' => $num2,
                'correct_answer' => $correctAnswer,
                'user_answer' => null,
                'is_correct' => null,
                'time_taken' => null,
                'score' => 0,
                'displayed_at' => null,
                'answered_at' => null,
                'skipped' => false
            ];
        }
        
        return $testData;
    }

    public function shouldSkipToNextQuestion()
    {
        if (!$this->last_question_displayed_at) {
            return false;
        }

        $secondsSinceDisplay = Carbon::now()->diffInSeconds($this->last_question_displayed_at);
        return $secondsSinceDisplay >= $this->auto_skip_interval;
    }

    public function getRemainingTimeForCurrentQuestion()
    {
        if (!$this->last_question_displayed_at) {
            return $this->auto_skip_interval;
        }

        $secondsSinceDisplay = Carbon::now()->diffInSeconds($this->last_question_displayed_at);
        $remaining = $this->auto_skip_interval - $secondsSinceDisplay;
        
        return max(0, $remaining);
    }

    // Method untuk mendapatkan soal berikutnya
    public function getNextQuestion()
    {
        $testData = $this->current_test_data;
        $nextPosition = $this->current_position;
        
        if ($nextPosition < count($testData)) {
            return $testData[$nextPosition];
        }
        
        return null;
    }
}