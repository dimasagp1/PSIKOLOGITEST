@extends('layouts.app')

@section('title', 'Status Tes Kraepelin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header bg-success">
                    <h3 class="card-title text-white">
                        <i class="fas fa-check-circle"></i> Status Tes Kraepelin
                    </h3>
                </div>
                <div class="card-body text-center">
                    @if($hasTakenTest && $latestTest)
                        <div class="alert alert-success">
                            <h4><i class="fas fa-check"></i> Tes Telah Selesai</h4>
                            <p class="mb-0">Selamat! Anda telah menyelesaikan tes Kraepelin. <strong>Semua jawaban telah tersimpan dengan aman.</strong></p>
                        </div>
                        
                        <!-- Debug Information (Hanya tampil di development) -->
                        @if(env('APP_DEBUG'))
                        <div class="alert alert-info text-left">
                            <h6><i class="fas fa-bug"></i> Debug Information:</h6>
                            @php
                                $validation = $latestTest->validateTestData();
                            @endphp
                            <pre class="mb-0">Validation: {{ $validation['match'] ? 'PASS' : 'FAIL' }}
Correct: {{ $validation['calculated_correct'] }} (stored: {{ $validation['stored_correct'] }})
Wrong: {{ $validation['calculated_wrong'] }} (stored: {{ $validation['stored_wrong'] }})
Skipped: {{ $validation['calculated_skipped'] }} (stored: {{ $validation['stored_skipped'] }})
Score: {{ $validation['calculated_score'] }} (stored: {{ $validation['stored_score'] }})</pre>
                        </div>
                        @endif
                        
                        <div class="row mt-4">
                            <div class="col-md-3">
                                <div class="info-box bg-gradient-success">
                                    <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Jawaban Benar</span>
                                        <span class="info-box-number">{{ $latestTest->correct_answers }}/50</span>
                                        <small>{{ number_format(($latestTest->correct_answers / 50) * 100, 1) }}%</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box bg-gradient-danger">
                                    <span class="info-box-icon"><i class="fas fa-times-circle"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Jawaban Salah</span>
                                        <span class="info-box-number">{{ $latestTest->wrong_answers }}</span>
                                        <small>{{ number_format(($latestTest->wrong_answers / 50) * 100, 1) }}%</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box bg-gradient-warning">
                                    <span class="info-box-icon"><i class="fas fa-forward"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Soal Dilewati</span>
                                        <span class="info-box-number">{{ $latestTest->skipped_answers }}</span>
                                        <small>{{ number_format(($latestTest->skipped_answers / 50) * 100, 1) }}%</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box bg-gradient-info">
                                    <span class="info-box-icon"><i class="fas fa-chart-line"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Skor Total</span>
                                        <span class="info-box-number">{{ $latestTest->total_score }}</span>
                                        <small>Waktu: {{ $latestTest->completion_time }}s</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <p class="text-muted">
                                <i class="fas fa-info-circle"></i> 
                                Hasil detail dan analisis lengkap dapat dilihat oleh administrator sistem.
                            </p>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <h4><i class="fas fa-info-circle"></i> Belum Mengikuti Tes</h4>
                            <p class="mb-0">Anda belum mengikuti tes Kraepelin. Silakan klik tombol di bawah untuk memulai tes.</p>
                        </div>
                        
                        <a href="{{ route('test.index') }}" class="btn btn-primary btn-lg mt-3">
                            <i class="fas fa-play-circle"></i> Mulai Tes Kraepelin
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection