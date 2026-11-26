<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\KraeplinTest;
use App\Models\TestSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Barryvdh\DomPDF\Facade\Pdf;


class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (!Auth::check()) {
                return redirect()->route('login');
            }

            $user = Auth::user();
            if (!$user->isAdmin() && !$user->isHR()) {
                abort(403, 'Unauthorized access.');
            }
            
            return $next($request);
        });
    }

    public function dashboard()
    {
        $totalUsers = User::count();
        $totalTests = KraeplinTest::count();
        $todayTests = KraeplinTest::whereDate('created_at', today())->count();
        
        $recentTests = KraeplinTest::with('user')
            ->latest()
            ->take(10)
            ->get();
            
        return view('admin.dashboard', compact(
            'totalUsers', 
            'totalTests', 
            'todayTests',
            'recentTests'
        ));
    }

public function users()
{
    $users = User::with('kraeplinTests')
        ->orderBy('created_at', 'desc')
        ->paginate(10);
        
    return view('admin.users', compact('users'));
}


    public function createUser()
    {
        return view('admin.create-user');
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:user,hr,admin',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'education' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'phone' => $request->phone,
            'date_of_birth' => $request->date_of_birth,
            'education' => $request->education,
            'department' => $request->department,
            'address' => $request->address,
        ]);

        return redirect()->route('admin.users')->with('success', 'User berhasil ditambahkan.');
    }

    public function editUser(User $user)
    {
        return view('admin.edit-user', compact('user'));
    }

    public function updateUser(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:user,hr,admin',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'date_of_birth' => 'nullable|date',
            'education' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255'
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'phone' => $request->phone,
            'address' => $request->address,
            'date_of_birth' => $request->date_of_birth,
            'education' => $request->education,
            'department' => $request->department
        ]);

        return redirect()->route('admin.users')->with('success', 'Data user berhasil diperbarui.');
    }

    public function deleteUser(User $user)
    {
        // Hapus data tes terkait
        KraeplinTest::where('user_id', $user->id)->delete();
        TestSession::where('user_id', $user->id)->delete();
        
        // Hapus user
        $user->delete();

        return redirect()->route('admin.users')->with('success', 'User berhasil dihapus.');
    }

    public function resetUserTest(User $user)
    {
        // Hapus data tes user
        KraeplinTest::where('user_id', $user->id)->delete();
        TestSession::where('user_id', $user->id)->delete();
        
        // Reset status tes
        $user->update(['has_taken_test' => false]);

        return redirect()->back()->with('success', 'Data tes user berhasil direset.');
    }

public function changePassword(Request $request, User $user)
    {
        $request->validate([
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return redirect()->back()->with('success', 'Password berhasil diubah.');
    }

 public function testResults()
{
    // Ambil semua hasil tes dengan user
    $results = KraeplinTest::with('user')
        ->orderBy('created_at', 'desc')
        ->paginate(15);
    
    // Debug: Log jumlah data
    \Log::info("Admin test results", [
        'total_results' => $results->total(),
        'current_count' => $results->count()
    ]);
    
    return view('admin.test-results', compact('results'));
}


    public function updateUserNotes(Request $request, User $user)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:1000'
        ]);
        
        $user->update([
            'admin_notes' => $request->admin_notes
        ]);
        
        return redirect()->back()->with('success', 'Catatan berhasil diperbarui.');
    }

    public function exportResults(Request $request)
    {
        $results = KraeplinTest::with('user')
            ->when($request->date_from, function($query) use ($request) {
                $query->whereDate('created_at', '>=', $request->date_from);
            })
            ->when($request->date_to, function($query) use ($request) {
                $query->whereDate('created_at', '<=', $request->date_to);
            })
            ->get();
            
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="test_results_detailed_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function() use ($results) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fwrite($file, "\xEF\xBB\xBF");
            
            fputcsv($file, [
                'Nama', 'Email', 'Tanggal Tes', 
                'Skor Total', 'Jawaban Benar', 'Jawaban Salah', 'Soal Dilewati',
                'Waktu (detik)', 'Akurasi (%)', 'Kecepatan (soal/mnt)',
                'Pangker', 'Janker', 'Tiangker', 'Y(0)', 'Y(50)', 'Y(50)-Y(0)',
                'Speed Factor', 'Rhythm Factor', 'Ausdeur Factor',
                'Konsistensi', 'Stabilitas', 'Ketahanan',
                'Tingkat Performa'
            ]);

            foreach ($results as $result) {
                fputcsv($file, [
                    $result->user->name,
                    $result->user->email,
                    $result->created_at->format('Y-m-d H:i:s'),
                    $result->total_score,
                    $result->correct_answers,
                    $result->wrong_answers,
                    $result->skipped_answers ?? 0,
                    $result->completion_time,
                    $result->analysis_results['accuracy'] ?? 0,
                    $result->analysis_results['speed'] ?? 0,
                    $result->detailed_analysis['pangker'] ?? 0,
                    $result->detailed_analysis['janker'] ?? 0,
                    $result->detailed_analysis['tiangker'] ?? 0,
                    $result->detailed_analysis['y0'] ?? 0,
                    $result->detailed_analysis['y50'] ?? 0,
                    $result->detailed_analysis['y50_minus_y0'] ?? 0,
                    $result->speed_factor ?? 0,
                    $result->rhythm_factor ?? 0,
                    $result->ausdeur_factor ?? 0,
                    $result->consistency_rate ?? 0,
                    $result->stability_rate ?? 0,
                    $result->endurance_rate ?? 0,
                    $result->analysis_results['performance_level'] ?? 'Tidak Ada'
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function analytics()
    {
        $performanceData = DB::table('kraeplin_tests')
            ->selectRaw('
                AVG(CAST(JSON_UNQUOTE(JSON_EXTRACT(analysis_results, "$.accuracy")) AS DECIMAL(10,2))) as avg_accuracy,
                AVG(CAST(JSON_UNQUOTE(JSON_EXTRACT(analysis_results, "$.speed")) AS DECIMAL(10,2))) as avg_speed,
                AVG(CAST(JSON_UNQUOTE(JSON_EXTRACT(analysis_results, "$.consistency")) AS DECIMAL(10,2))) as avg_consistency,
                AVG(CAST(JSON_UNQUOTE(JSON_EXTRACT(analysis_results, "$.fatigue_index")) AS DECIMAL(10,2))) as avg_fatigue,
                COUNT(*) as total_tests
            ')
            ->first();
            
        $performanceLevels = DB::table('kraeplin_tests')
            ->selectRaw('
                COALESCE(JSON_UNQUOTE(JSON_EXTRACT(analysis_results, "$.performance_level")), "Tidak Terklasifikasi") as level,
                COUNT(*) as count
            ')
            ->whereNotNull('analysis_results')
            ->groupBy('level')
            ->get();
            
        return view('admin.analytics', compact('performanceData', 'performanceLevels'));
    }

    // Method untuk melihat detail hasil user
public function showUserResult(KraeplinTest $test)
{
    // Pastikan hanya admin yang bisa akses
    if (!Auth::user()->isAdmin() && !Auth::user()->isHR()) {
        abort(403, 'Unauthorized access.');
    }

    // Pastikan analisis sudah dihitung
    $test->ensureAnalysisCalculated();
    
    // Debug: Log data yang akan ditampilkan
    \Log::info("Showing user result", [
        'test_id' => $test->id,
        'user_name' => $test->user->name,
        'correct_answers' => $test->correct_answers,
        'wrong_answers' => $test->wrong_answers,
        'skipped_answers' => $test->skipped_answers
    ]);

    return view('admin.user-result', compact('test'));
}


public function downloadPDF($testId)
{
    $test = KraeplinTest::with('user')->findOrFail($testId);
    $test->ensureAnalysisCalculated();

    // Data untuk PDF
    $correctAnswers = $test->correct_answers ?? 0;
    $wrongAnswers = $test->wrong_answers ?? 0;
    $skippedAnswers = $test->skipped_answers ?? 0;
    $detailedAnalysis = $test->detailed_analysis ?? [];
    $analysisResults = $test->analysis_results ?? [];
    
    $pangker = $detailedAnalysis['pangker'] ?? 0;
    $janker = $detailedAnalysis['janker'] ?? 0;
    $tiangker = $detailedAnalysis['tiangker'] ?? 0;
    $speedFactor = $detailedAnalysis['speed_factor'] ?? 0;
    $rhythmFactor = $detailedAnalysis['rhythm_factor'] ?? 0;
    $ausdeurFactor = $detailedAnalysis['ausdeur_factor'] ?? 0;
    $accuracyRate = $detailedAnalysis['accuracy_rate'] ?? 0;
    $consistencyRate = $detailedAnalysis['consistency_rate'] ?? 0;
    $stabilityRate = $detailedAnalysis['stability_rate'] ?? 0;
    $enduranceRate = $detailedAnalysis['endurance_rate'] ?? 0;
    $y50_minus_y0 = $detailedAnalysis['y50_minus_y0'] ?? 0;
    $sectionAnalysis = $detailedAnalysis['section_analysis'] ?? [
        'pangker_percentage' => 0, 
        'janker_percentage' => 0, 
        'tiangker_percentage' => 0
    ];
    $performanceLevel = $analysisResults['performance_level'] ?? 'Tidak Terdeteksi';
    $fatigueIndex = $analysisResults['fatigue_index'] ?? 0;

    // Gunakan view yang benar
    $pdf = PDF::loadView('admin.partials.pdf-content', compact(
        'test', 'correctAnswers', 'wrongAnswers', 'skippedAnswers',
        'pangker', 'janker', 'tiangker', 'speedFactor', 'rhythmFactor',
        'ausdeurFactor', 'accuracyRate', 'consistencyRate', 'stabilityRate',
        'enduranceRate', 'y50_minus_y0', 'sectionAnalysis', 'performanceLevel',
        'fatigueIndex', 'analysisResults'
    ));

    $filename = 'Laporan_Tes_Kraepelin_' . str_replace(' ', '_', $test->user->name) . '_' . $test->created_at->format('Y-m-d') . '.pdf';

    return $pdf->download($filename);
}

}