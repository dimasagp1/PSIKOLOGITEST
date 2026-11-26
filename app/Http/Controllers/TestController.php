<?php

namespace App\Http\Controllers;

use App\Models\KraeplinTest;
use App\Models\TestSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TestController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        $activeSession = TestSession::where('user_id', $user->id)
            ->where('is_completed', false)
            ->first();
            
        return view('test.index', compact('activeSession'));
    }

    public function startTest()
    {
        $user = Auth::user();
        
        // Hapus session yang belum selesai
        TestSession::where('user_id', $user->id)
            ->where('is_completed', false)
            ->delete();
            
        // Buat session baru dengan waktu 12 menit
        $session = TestSession::create([
            'user_id' => $user->id,
            'current_test_data' => (new TestSession())->generateTestData(50),
            'current_position' => 0,
            'time_remaining' => 720, // 12 menit
            'is_completed' => false,
            'last_question_displayed_at' => Carbon::now(),
            'auto_skip_interval' => 15, // 15 detik per soal
            'total_test_time' => 720 // 12 menit
        ]);
        
        return redirect()->route('test.take');
    }

    public function takeTest()
    {
        $user = Auth::user();
        $session = TestSession::where('user_id', $user->id)
            ->where('is_completed', false)
            ->firstOrFail();

        // Update waktu terakhir soal ditampilkan
        if (!$session->last_question_displayed_at) {
            $session->update([
                'last_question_displayed_at' => Carbon::now()
            ]);
        }
            
        return view('test.take', compact('session'));
    }

    public function submitAnswer(Request $request)
    {
        $request->validate([
            'answer' => 'required|integer|min:2|max:18'
        ]);
        
        $user = Auth::user();
        $session = TestSession::where('user_id', $user->id)
            ->where('is_completed', false)
            ->firstOrFail();
            
        return $this->processAnswer($session, $request->answer);
    }

    public function skipQuestion()
    {
        $user = Auth::user();
        $session = TestSession::where('user_id', $user->id)
            ->where('is_completed', false)
            ->firstOrFail();
            
        return $this->processAnswer($session, null, true);
    }

    private function processAnswer($session, $answer = null, $skipped = false)
    {
        $testData = $session->current_test_data;
        $currentPosition = $session->current_position;
        
        \Log::info("=== PROCESS ANSWER START ===");
        \Log::info("Position: {$currentPosition}, Answer: {$answer}, Skipped: " . ($skipped ? 'Yes' : 'No'));
        
        // Validasi posisi saat ini
        if ($currentPosition >= count($testData)) {
            \Log::info("Position melebihi batas, complete test");
            return $this->completeTest($session);
        }
        
        // Pastikan data soal ada
        if (!isset($testData[$currentPosition])) {
            \Log::error("Data soal tidak ditemukan di posisi: {$currentPosition}");
            return response()->json(['success' => false, 'error' => 'Data soal tidak valid']);
        }
        
        $currentQuestion = $testData[$currentPosition];
        $num1 = $currentQuestion['number1'] ?? '?';
        $num2 = $currentQuestion['number2'] ?? '?';
        $correctAnswer = $currentQuestion['correct_answer'] ?? null;
        
        \Log::info("Soal: {$num1} + {$num2} = {$correctAnswer}");
        
        if ($correctAnswer === null) {
            \Log::error("Correct answer tidak ditemukan untuk soal: {$currentPosition}");
            return response()->json(['success' => false, 'error' => 'Jawaban benar tidak ditemukan']);
        }
        
        // Hitung waktu yang digunakan untuk soal ini
        $timeTaken = $session->getRemainingTimeForCurrentQuestion();
        
        // Update jawaban user dengan logging detail
        if ($skipped) {
            // Soal dilewati
            $testData[$currentPosition]['user_answer'] = null;
            $testData[$currentPosition]['is_correct'] = false;
            $testData[$currentPosition]['score'] = 0;
            $testData[$currentPosition]['skipped'] = true;
            $testData[$currentPosition]['time_taken'] = $timeTaken;
            $testData[$currentPosition]['answered_at'] = now();
            
            \Log::info("RESULT: SKIPPED - Soal {$currentPosition} dilewati, Time: {$timeTaken}s");
        } else {
            // Soal dijawab - PASTIKAN perbandingan benar
            $userAnswer = (int)$answer;
            $correctAnswer = (int)$correctAnswer;
            $isCorrect = ($userAnswer === $correctAnswer);
            
            $testData[$currentPosition]['user_answer'] = $userAnswer;
            $testData[$currentPosition]['is_correct'] = $isCorrect;
            $testData[$currentPosition]['score'] = $isCorrect ? 1 : 0;
            $testData[$currentPosition]['skipped'] = false;
            $testData[$currentPosition]['time_taken'] = $timeTaken;
            $testData[$currentPosition]['answered_at'] = now();
            
            \Log::info("RESULT: ANSWERED - User: {$userAnswer}, Correct: {$correctAnswer}, Is Correct: " . ($isCorrect ? 'YES' : 'NO') . ", Time: {$timeTaken}s");
        }
        
        $testData[$currentPosition]['displayed_at'] = $session->last_question_displayed_at;
            
        // Update session data
        $session->current_test_data = $testData;
        $session->current_position = $currentPosition + 1;
        $session->last_question_displayed_at = Carbon::now();
        
        \Log::info("Session updated - New position: {$session->current_position}");
        
        // Cek apakah tes sudah selesai
        if ($session->current_position >= count($testData)) {
            \Log::info("=== TEST COMPLETED - All questions answered ===");
            return $this->completeTest($session);
        }
        
        // Simpan session
        $session->save();
        
        \Log::info("=== PROCESS ANSWER END ===");
        
        return response()->json([
            'success' => true,
            'next_position' => $session->current_position,
            'progress' => ($session->current_position / count($testData)) * 100,
            'skipped' => $skipped,
            'time_remaining' => $session->time_remaining,
            'show_notification' => false, // Tidak tampilkan notifikasi untuk setiap jawaban
            'next_question_data' => $testData[$session->current_position] // Kirim data soal berikutnya
        ]);
    }

    public function checkAutoSkip()
    {
        $user = Auth::user();
        $session = TestSession::where('user_id', $user->id)
            ->where('is_completed', false)
            ->firstOrFail();

        if ($session->shouldSkipToNextQuestion()) {
            \Log::info("Auto-skip triggered for position: " . $session->current_position);
            return $this->processAnswer($session, null, true);
        }

        return response()->json([
            'success' => true,
            'should_skip' => false,
            'remaining_time_for_question' => $session->getRemainingTimeForCurrentQuestion(),
            'total_time_remaining' => $session->time_remaining
        ]);
    }

    // Method baru untuk selesaikan test manual
    public function completeTestManual(Request $request)
    {
        $user = Auth::user();
        $session = TestSession::where('user_id', $user->id)
            ->where('is_completed', false)
            ->firstOrFail();

        \Log::info("Manual test completion triggered by user");

        return $this->completeTest($session);
    }

    private function completeTest($session)
    {
        $testData = $session->current_test_data;
        
        $correctAnswers = 0;
        $wrongAnswers = 0;
        $skippedAnswers = 0;
        $totalScore = 0;
        
        \Log::info("=== COMPLETE TEST START ===");
        \Log::info("Total questions: " . count($testData));
        
        // Hitung ulang semua jawaban dengan validasi ketat
        foreach ($testData as $index => $item) {
            $num1 = $item['number1'] ?? '?';
            $num2 = $item['number2'] ?? '?';
            $correct = $item['correct_answer'] ?? '?';
            $userAnswer = $item['user_answer'] ?? 'NULL';
            $isCorrect = $item['is_correct'] ?? false;
            $skipped = $item['skipped'] ?? false;
            
            // Validasi data setiap soal
            if (!isset($item['skipped'])) {
                $testData[$index]['skipped'] = false;
                $skipped = false;
            }
            
            if ($skipped) {
                $skippedAnswers++;
                \Log::info("Soal {$index}: {$num1} + {$num2} = {$correct} | USER: SKIPPED");
            } elseif ($isCorrect) {
                $correctAnswers++;
                $totalScore++;
                \Log::info("Soal {$index}: {$num1} + {$num2} = {$correct} | USER: {$userAnswer} ✅ CORRECT");
            } else {
                $wrongAnswers++;
                \Log::info("Soal {$index}: {$num1} + {$num2} = {$correct} | USER: {$userAnswer} ❌ WRONG");
            }
        }
        
        \Log::info("FINAL COUNT - Correct: {$correctAnswers}, Wrong: {$wrongAnswers}, Skipped: {$skippedAnswers}, Total Score: {$totalScore}");
        
        // Validasi total harus 50
        $totalCalculated = $correctAnswers + $wrongAnswers + $skippedAnswers;
        if ($totalCalculated !== 50) {
            \Log::warning("Total tidak 50! Calculated: {$totalCalculated}. Adjusting...");
            // Adjust skipped answers to make total 50
            $skippedAnswers = 50 - $correctAnswers - $wrongAnswers;
            \Log::info("Adjusted - Correct: {$correctAnswers}, Wrong: {$wrongAnswers}, Skipped: {$skippedAnswers}");
        }
        
        try {
            // Simpan hasil tes dengan transaction untuk memastikan konsistensi
            \DB::transaction(function () use ($session, $testData, $correctAnswers, $wrongAnswers, $skippedAnswers, $totalScore) {
                // Simpan ke KraeplinTest
                $kraeplinTest = KraeplinTest::create([
                    'user_id' => $session->user_id,
                    'test_data' => $testData,
                    'total_score' => $totalScore,
                    'correct_answers' => $correctAnswers,
                    'wrong_answers' => $wrongAnswers,
                    'skipped_answers' => $skippedAnswers,
                    'completion_time' => $session->total_test_time - $session->time_remaining,
                    'analysis_results' => [],
                    'detailed_analysis' => []
                ]);
                
                \Log::info("KraeplinTest created - ID: {$kraeplinTest->id}");
                
                // Hitung analisis
                $analysis = $kraeplinTest->calculateAnalysis();
                \Log::info("Analysis calculated");
                
                // Update session sebagai completed
                $session->is_completed = true;
                $session->save();
                
                // Update user status
                $user = $session->user;
                $user->has_taken_test = true;
                $user->save();
                
                \Log::info("=== COMPLETE TEST END ===");
                \Log::info("User {$user->name} completed test successfully");
                
                // Validasi data yang disimpan
                $validation = $kraeplinTest->validateTestData();
                \Log::info("Data validation result:", $validation);
                
                // Log detail untuk debugging
                $this->logTestDetails($kraeplinTest);
            });
            
            return response()->json([
                'success' => true,
                'completed' => true,
                'message' => 'Tes berhasil disimpan! Hasil tes telah tersimpan di database.',
                'correct_answers' => $correctAnswers,
                'wrong_answers' => $wrongAnswers,
                'skipped_answers' => $skippedAnswers,
                'total_score' => $totalScore,
                'status_url' => route('test.status')
            ]);
            
        } catch (\Exception $e) {
            \Log::error("Error saving test: " . $e->getMessage());
            \Log::error("Stack trace: " . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'error' => 'Gagal menyimpan hasil tes: ' . $e->getMessage()
            ], 500);
        }
    }

    // Method untuk logging detail test
    private function logTestDetails($kraeplinTest)
    {
        \Log::info("=== TEST DETAILS FOR VALIDATION ===");
        \Log::info("Test ID: " . $kraeplinTest->id);
        \Log::info("User ID: " . $kraeplinTest->user_id);
        \Log::info("Correct Answers: " . $kraeplinTest->correct_answers);
        \Log::info("Wrong Answers: " . $kraeplinTest->wrong_answers);
        \Log::info("Skipped Answers: " . $kraeplinTest->skipped_answers);
        \Log::info("Total Score: " . $kraeplinTest->total_score);
        
        $testData = $kraeplinTest->test_data;
        $calculatedCorrect = 0;
        $calculatedWrong = 0;
        $calculatedSkipped = 0;
        
        foreach ($testData as $index => $question) {
            $status = 'UNKNOWN';
            if ($question['skipped'] ?? false) {
                $calculatedSkipped++;
                $status = 'SKIPPED';
            } elseif ($question['is_correct'] ?? false) {
                $calculatedCorrect++;
                $status = 'CORRECT';
            } else {
                $calculatedWrong++;
                $status = 'WRONG';
            }
            
            \Log::info("Q" . ($index + 1) . ": {$question['number1']} + {$question['number2']} = {$question['correct_answer']} | User: {$question['user_answer']} | {$status}");
        }
        
        \Log::info("CALCULATED - Correct: {$calculatedCorrect}, Wrong: {$calculatedWrong}, Skipped: {$calculatedSkipped}");
        \Log::info("STORED - Correct: {$kraeplinTest->correct_answers}, Wrong: {$kraeplinTest->wrong_answers}, Skipped: {$kraeplinTest->skipped_answers}");
        \Log::info("MATCH: " . ($calculatedCorrect == $kraeplinTest->correct_answers && 
                              $calculatedWrong == $kraeplinTest->wrong_answers && 
                              $calculatedSkipped == $kraeplinTest->skipped_answers ? 'YES' : 'NO'));
    }

    public function updateTime(Request $request)
    {
        $user = Auth::user();
        $session = TestSession::where('user_id', $user->id)
            ->where('is_completed', false)
            ->firstOrFail();
            
        $session->time_remaining = $request->time_remaining;
        $session->save();
        
        if ($request->time_remaining <= 0) {
            \Log::info("Time expired, completing test");
            return $this->completeTest($session);
        }
        
        return response()->json(['success' => true]);
    }

    // Method status untuk user (bukan results)
    public function status()
    {
        $user = Auth::user();
        $hasTakenTest = $user->has_taken_test;
        
        // Ambil hasil tes terbaru jika sudah selesai
        $latestTest = null;
        if ($hasTakenTest) {
            $latestTest = KraeplinTest::where('user_id', $user->id)
                ->latest()
                ->first();
                
            if ($latestTest) {
                // Validasi data sebelum ditampilkan
                $validation = $latestTest->validateTestData();
                if (!$validation['match']) {
                    \Log::warning("Data test tidak konsisten untuk user {$user->id}, memperbaiki...");
                    $latestTest->fixInconsistentData();
                    $latestTest->refresh();
                }
            }
        }
        
        return view('test.status', compact('hasTakenTest', 'latestTest'));
    }
}