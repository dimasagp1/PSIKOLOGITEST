@extends('layouts.pdf')

@section('title', 'Laporan Tes Kraepelin - ' . $test->user->name)

@section('content')
    <!-- Informasi Peserta - 2 Kotak Per Baris -->
    <div class="section">
        <div class="section-title">INFORMASI PESERTA</div>
        <div class="row">
            <div class="col-6">
                <div class="card">
                    <div class="card-header">
                        <h4>Data Pribadi</h4>
                    </div>
                    <div class="card-body">
                        <div class="metric-row">
                            <span class="metric-label">Nama Lengkap:</span>
                            <span class="metric-value">{{ $test->user->name }}</span>
                        </div>
                        <div class="metric-row">
                            <span class="metric-label">Email:</span>
                            <span class="metric-value">{{ $test->user->email }}</span>
                        </div>
                        <div class="metric-row">
                            <span class="metric-label">Tanggal Tes:</span>
                            <span class="metric-value">{{ $test->created_at->format('d F Y H:i') }}</span>
                        </div>
                        @if($test->user->phone)
                        <div class="metric-row">
                            <span class="metric-label">Telepon:</span>
                            <span class="metric-value">{{ $test->user->phone }}</span>
                        </div>
                        @endif
                        @if($test->user->age)
                        <div class="metric-row">
                            <span class="metric-label">Usia:</span>
                            <span class="metric-value">{{ $test->user->age }} tahun</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="col-6">
                <div class="card">
                    <div class="card-header">
                        <h4>Statistik Tes</h4>
                    </div>
                    <div class="card-body">
                        <div class="metric-row">
                            <span class="metric-label">Total Soal:</span>
                            <span class="metric-value">50 soal</span>
                        </div>
                        <div class="metric-row">
                            <span class="metric-label">Waktu Pengerjaan:</span>
                            <span class="metric-value">{{ floor($test->completion_time / 60) }}m {{ $test->completion_time % 60 }}s</span>
                        </div>
                        <div class="metric-row">
                            <span class="metric-label">Tingkat Performa:</span>
                            <span class="metric-value {{ $performanceLevel == 'Sangat Baik' ? 'text-success' : ($performanceLevel == 'Baik' ? 'text-info' : ($performanceLevel == 'Cukup' ? 'text-warning' : 'text-danger')) }}">
                                {{ $performanceLevel }}
                            </span>
                        </div>
                        <div class="metric-row">
                            <span class="metric-label">Skor Total:</span>
                            <span class="metric-value">{{ $test->total_score }}/50</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistik Utama - 4 Kotak Per Baris -->
    <div class="section">
        <div class="section-title">STATISTIK UTAMA</div>
        <div class="row">
            <div class="col-3">
                <div class="stat-card">
                    <div class="stat-label">JAWABAN BENAR</div>
                    <div class="stat-value text-success">{{ $correctAnswers }}</div>
                    <div class="stat-label">{{ number_format(($correctAnswers / 50) * 100, 1) }}%</div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: {{ ($correctAnswers / 50) * 100 }}%; background-color: #27ae60;"></div>
                    </div>
                </div>
            </div>
            
            <div class="col-3">
                <div class="stat-card">
                    <div class="stat-label">JAWABAN SALAH</div>
                    <div class="stat-value text-danger">{{ $wrongAnswers }}</div>
                    <div class="stat-label">{{ number_format(($wrongAnswers / 50) * 100, 1) }}%</div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: {{ ($wrongAnswers / 50) * 100 }}%; background-color: #e74c3c;"></div>
                    </div>
                </div>
            </div>
            
            <div class="col-3">
                <div class="stat-card">
                    <div class="stat-label">SOAL DILEWATI</div>
                    <div class="stat-value text-warning">{{ $skippedAnswers }}</div>
                    <div class="stat-label">{{ number_format(($skippedAnswers / 50) * 100, 1) }}%</div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: {{ ($skippedAnswers / 50) * 100 }}%; background-color: #f39c12;"></div>
                    </div>
                </div>
            </div>
            
            <div class="col-3">
                <div class="stat-card">
                    <div class="stat-label">AKURASI</div>
                    <div class="stat-value text-info">{{ $accuracyRate }}%</div>
                    <div class="stat-label">Tingkat Ketepatan</div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: {{ $accuracyRate }}%; background-color: #3498db;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Diagram & Faktor - 2 Kotak Per Baris -->
    <div class="section">
        <div class="section-title">DIAGRAM & ANALISIS</div>
        <div class="row">
            <div class="col-6">
                <div class="card">
                    <div class="card-header">
                        <h4>Distribusi Jawaban</h4>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="answerDistributionChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-6">
                <div class="card">
                    <div class="card-header">
                        <h4>Performa Per Bagian</h4>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="sectionPerformanceChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Faktor Kraepelin - 3 Kotak Per Baris -->
    <div class="section">
        <div class="section-title">FAKTOR KRAEPELIN</div>
        <div class="row">
            <div class="col-4">
                <div class="factor-card">
                    <div class="factor-label">SPEED FACTOR</div>
                    <div class="factor-value {{ $speedFactor >= 25 ? 'text-success' : ($speedFactor >= 20 ? 'text-info' : 'text-danger') }}">
                        {{ $speedFactor }}
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: {{ min(100, $speedFactor / 40 * 100) }}%; background-color: {{ $speedFactor >= 25 ? '#27ae60' : ($speedFactor >= 20 ? '#3498db' : '#e74c3c') }}"></div>
                    </div>
                    <small>Soal per Menit</small>
                </div>
            </div>
            
            <div class="col-4">
                <div class="factor-card">
                    <div class="factor-label">RHYTHM FACTOR</div>
                    <div class="factor-value {{ $rhythmFactor >= 80 ? 'text-success' : ($rhythmFactor >= 60 ? 'text-info' : 'text-danger') }}">
                        {{ $rhythmFactor }}%
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: {{ $rhythmFactor }}%; background-color: {{ $rhythmFactor >= 80 ? '#27ae60' : ($rhythmFactor >= 60 ? '#3498db' : '#e74c3c') }}"></div>
                    </div>
                    <small>Konsistensi Tempo</small>
                </div>
            </div>
            
            <div class="col-4">
                <div class="factor-card">
                    <div class="factor-label">AUSDEUR FACTOR</div>
                    <div class="factor-value {{ $ausdeurFactor >= 80 ? 'text-success' : ($ausdeurFactor >= 60 ? 'text-info' : 'text-danger') }}">
                        {{ $ausdeurFactor }}%
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: {{ min(100, $ausdeurFactor) }}%; background-color: {{ $ausdeurFactor >= 80 ? '#27ae60' : ($ausdeurFactor >= 60 ? '#3498db' : '#e74c3c') }}"></div>
                    </div>
                    <small>Daya Tahan Kerja</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Analisis Per Bagian - 3 Kotak Per Baris -->
    <div class="section">
        <div class="section-title">ANALISIS PER BAGIAN TES</div>
        <div class="row">
            <div class="col-4">
                <div class="section-card">
                    <div class="factor-label">PANGKER</div>
                    <div class="section-score text-primary">{{ $pangker }}/10</div>
                    <div class="section-percentage">{{ $sectionAnalysis['pangker_percentage'] ?? 0 }}%</div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: {{ $sectionAnalysis['pangker_percentage'] ?? 0 }}%; background-color: #007bff;"></div>
                    </div>
                    <small>Soal 1-10<br>Awal Tes</small>
                </div>
            </div>
            
            <div class="col-4">
                <div class="section-card">
                    <div class="factor-label">JANKER</div>
                    <div class="section-score text-info">{{ $janker }}/30</div>
                    <div class="section-percentage">{{ $sectionAnalysis['janker_percentage'] ?? 0 }}%</div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: {{ $sectionAnalysis['janker_percentage'] ?? 0 }}%; background-color: #6c757d;"></div>
                    </div>
                    <small>Soal 11-40<br>Inti Tes</small>
                </div>
            </div>
            
            <div class="col-4">
                <div class="section-card">
                    <div class="factor-label">TIANGKER</div>
                    <div class="section-score text-warning">{{ $tiangker }}/10</div>
                    <div class="section-percentage">{{ $sectionAnalysis['tiangker_percentage'] ?? 0 }}%</div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: {{ $sectionAnalysis['tiangker_percentage'] ?? 0 }}%; background-color: #f39c12;"></div>
                    </div>
                    <small>Soal 41-50<br>Akhir Tes</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Metrik Kinerja - 2 Kotak Per Baris -->
    <div class="section">
        <div class="section-title">METRIK KINERJA DETAIL</div>
        <div class="row">
            <div class="col-6">
                <div class="card">
                    <div class="card-header">
                        <h4>Kualitas Kerja</h4>
                    </div>
                    <div class="card-body">
                        <div class="metric-row">
                            <span class="metric-label">Akurasi:</span>
                            <span class="metric-value {{ $accuracyRate >= 80 ? 'text-success' : ($accuracyRate >= 60 ? 'text-info' : 'text-danger') }}">
                                {{ $accuracyRate }}%
                            </span>
                        </div>
                        <div class="metric-row">
                            <span class="metric-label">Konsistensi:</span>
                            <span class="metric-value {{ $consistencyRate >= 80 ? 'text-success' : ($consistencyRate >= 60 ? 'text-info' : 'text-danger') }}">
                                {{ $consistencyRate }}%
                            </span>
                        </div>
                        <div class="metric-row">
                            <span class="metric-label">Stabilitas:</span>
                            <span class="metric-value {{ $stabilityRate >= 80 ? 'text-success' : ($stabilityRate >= 60 ? 'text-info' : 'text-danger') }}">
                                {{ $stabilityRate }}%
                            </span>
                        </div>
                        <div class="metric-row">
                            <span class="metric-label">Kecepatan:</span>
                            <span class="metric-value {{ $analysisResults['speed'] >= 25 ? 'text-success' : ($analysisResults['speed'] >= 20 ? 'text-info' : 'text-danger') }}">
                                {{ $analysisResults['speed'] ?? 0 }} soal/mnt
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-6">
                <div class="card">
                    <div class="card-header">
                        <h4>Daya Tahan & Kelelahan</h4>
                    </div>
                    <div class="card-body">
                        <div class="metric-row">
                            <span class="metric-label">Ketahanan:</span>
                            <span class="metric-value {{ $enduranceRate >= 80 ? 'text-success' : ($enduranceRate >= 60 ? 'text-info' : 'text-danger') }}">
                                {{ $enduranceRate }}%
                            </span>
                        </div>
                        <div class="metric-row">
                            <span class="metric-label">Indeks Kelelahan:</span>
                            <span class="metric-value {{ $fatigueIndex <= 10 ? 'text-success' : ($fatigueIndex <= 20 ? 'text-info' : 'text-danger') }}">
                                {{ $fatigueIndex }}%
                            </span>
                        </div>
                        <div class="metric-row">
                            <span class="metric-label">Soal Terlewati:</span>
                            <span class="metric-value {{ $skippedAnswers <= 5 ? 'text-success' : ($skippedAnswers <= 10 ? 'text-warning' : 'text-danger') }}">
                                {{ $skippedAnswers }}
                            </span>
                        </div>
                        <div class="metric-row">
                            <span class="metric-label">Trend Performa:</span>
                            <span class="metric-value {{ $y50_minus_y0 >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $y50_minus_y0 >= 0 ? '+' : '' }}{{ $y50_minus_y0 }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ringkasan Evaluasi - Full Width -->
    <div class="section">
        <div class="section-title">RINGKASAN EVALUASI</div>
        <div class="row">
            <div class="col-12">
                <div class="performance-summary">
                    <h4 style="margin-top: 0; color: #2c3e50;">Tingkat Performa: 
                        <span class="{{ $performanceLevel == 'Sangat Baik' ? 'text-success' : ($performanceLevel == 'Baik' ? 'text-info' : ($performanceLevel == 'Cukup' ? 'text-warning' : 'text-danger')) }}">
                            {{ $performanceLevel }}
                        </span>
                    </h4>
                    
                    <div class="row">
                        <div class="col-6">
                            <strong>Kelebihan:</strong>
                            <ul style="margin: 3px 0; padding-left: 15px; font-size: 9px;">
                                @if($speedFactor >= 25)
                                <li>Kecepatan kerja yang sangat baik</li>
                                @endif
                                @if($accuracyRate >= 80)
                                <li>Tingkat akurasi yang tinggi</li>
                                @endif
                                @if($consistencyRate >= 80)
                                <li>Konsistensi performa yang stabil</li>
                                @endif
                                @if($ausdeurFactor >= 80)
                                <li>Daya tahan kerja yang baik</li>
                                @endif
                                @if($y50_minus_y0 > 0)
                                <li>Performa meningkat di akhir tes</li>
                                @endif
                                @if($stabilityRate >= 80)
                                <li>Stabilitas emosi yang baik selama tes</li>
                                @endif
                            </ul>
                        </div>
                        <div class="col-6">
                            <strong>Area Perbaikan:</strong>
                            <ul style="margin: 3px 0; padding-left: 15px; font-size: 9px;">
                                @if($speedFactor < 20)
                                <li>Perlu peningkatan kecepatan kerja</li>
                                @endif
                                @if($accuracyRate < 70)
                                <li>Perlu meningkatkan ketelitian</li>
                                @endif
                                @if($consistencyRate < 70)
                                <li>Perlu meningkatkan konsistensi</li>
                                @endif
                                @if($ausdeurFactor < 60)
                                <li>Perlu meningkatkan daya tahan</li>
                                @endif
                                @if($skippedAnswers > 5)
                                <li>Terlalu banyak soal yang dilewati</li>
                                @endif
                                @if($fatigueIndex > 20)
                                <li>Tingkat kelelahan yang tinggi</li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-12">
                <div class="recommendation">
                    <h4 style="margin-top: 0; color: #2980b9;">REKOMENDASI</h4>
                    <p style="margin: 0; font-size: 9px;">
                        @if($performanceLevel == 'Sangat Baik')
                        Kandidat menunjukkan performa yang sangat baik di semua aspek. Sangat direkomendasikan untuk posisi yang membutuhkan ketelitian, kecepatan tinggi, dan ketahanan kerja. Cocok untuk posisi dengan tekanan tinggi dan deadline ketat.
                        @elseif($performanceLevel == 'Baik')
                        Kandidat memiliki performa yang baik dengan beberapa area yang masih dapat ditingkatkan. Direkomendasikan untuk berbagai posisi dengan supervisi yang memadai. Dapat ditempatkan di posisi yang membutuhkan konsistensi kerja.
                        @elseif($performanceLevel == 'Cukup')
                        Kandidat menunjukkan kemampuan yang cukup namun perlu pengembangan lebih lanjut. Disarankan untuk pelatihan tambahan sebelum penempatan. Cocok untuk posisi dengan tekanan kerja sedang.
                        @else
                        Kandidat memerlukan pembinaan dan pelatihan intensif sebelum dapat ditempatkan di posisi yang membutuhkan ketelitian tinggi. Disarankan untuk evaluasi ulang setelah program pengembangan.
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    /* Layout Styles */
    .section {
        margin-bottom: 15px;
        page-break-inside: avoid;
    }
    
    .section-title {
        font-size: 12px;
        font-weight: bold;
        color: #2c3e50;
        margin-bottom: 8px;
        padding-bottom: 4px;
        border-bottom: 2px solid #3498db;
        text-transform: uppercase;
    }
    
    .row {
        display: flex;
        flex-wrap: wrap;
        margin: 0 -5px;
    }
    
    .col-12 { width: 100%; padding: 0 5px; }
    .col-6 { width: 50%; padding: 0 5px; }
    .col-4 { width: 33.333%; padding: 0 5px; }
    .col-3 { width: 25%; padding: 0 5px; }
    
    /* Card Styles */
    .card {
        border: 1px solid #bdc3c7;
        border-radius: 6px;
        margin-bottom: 8px;
        background: white;
    }
    
    .card-header {
        background: #ecf0f1;
        padding: 6px 8px;
        border-bottom: 1px solid #bdc3c7;
    }
    
    .card-header h4 {
        margin: 0;
        font-size: 10px;
        font-weight: bold;
        color: #2c3e50;
    }
    
    .card-body {
        padding: 8px;
    }
    
    /* Metric Rows */
    .metric-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 4px;
        font-size: 9px;
    }
    
    .metric-label {
        font-weight: 600;
        color: #7f8c8d;
    }
    
    .metric-value {
        font-weight: bold;
        color: #2c3e50;
    }
    
    /* Stat Cards */
    .stat-card, .factor-card, .section-card {
        background: white;
        border: 1px solid #bdc3c7;
        border-radius: 6px;
        padding: 10px;
        text-align: center;
        margin-bottom: 8px;
    }
    
    .stat-label, .factor-label {
        font-size: 8px;
        font-weight: bold;
        color: #7f8c8d;
        text-transform: uppercase;
        margin-bottom: 5px;
    }
    
    .stat-value, .factor-value, .section-score {
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 3px;
    }
    
    .section-percentage {
        font-size: 10px;
        color: #7f8c8d;
        margin-bottom: 5px;
    }
    
    /* Progress Bars */
    .progress-bar {
        width: 100%;
        height: 6px;
        background: #ecf0f1;
        border-radius: 3px;
        overflow: hidden;
        margin-top: 5px;
    }
    
    .progress-fill {
        height: 100%;
        border-radius: 3px;
        transition: width 0.3s ease;
    }
    
    /* Text Colors */
    .text-success { color: #27ae60; }
    .text-info { color: #3498db; }
    .text-warning { color: #f39c12; }
    .text-danger { color: #e74c3c; }
    .text-primary { color: #007bff; }
    
    /* Chart Container */
    .chart-container {
        height: 150px;
        position: relative;
    }
    
    /* Performance Summary */
    .performance-summary {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        padding: 10px;
        margin-bottom: 8px;
    }
    
    .recommendation {
        background: #e8f4fd;
        border: 1px solid #b3d9f7;
        border-radius: 6px;
        padding: 10px;
    }
    
    /* Utility Classes */
    small {
        font-size: 7px;
        color: #95a5a6;
        line-height: 1.2;
    }
    
    ul {
        margin: 3px 0;
        padding-left: 15px;
    }
    
    li {
        margin-bottom: 2px;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Data untuk chart
    const answerData = {
        correct: {{ $correctAnswers }},
        wrong: {{ $wrongAnswers }},
        skipped: {{ $skippedAnswers }}
    };

    const sectionData = {
        pangker: {{ $pangker }},
        janker: {{ $janker }},
        tiangker: {{ $tiangker }}
    };

    // Chart Distribusi Jawaban
    const ctx1 = document.getElementById('answerDistributionChart').getContext('2d');
    new Chart(ctx1, {
        type: 'doughnut',
        data: {
            labels: ['Benar ({{ $correctAnswers }})', 'Salah ({{ $wrongAnswers }})', 'Dilewati ({{ $skippedAnswers }})'],
            datasets: [{
                data: [answerData.correct, answerData.wrong, answerData.skipped],
                backgroundColor: ['#28a745', '#dc3545', '#ffc107'],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 8,
                        usePointStyle: true,
                        font: { size: 7 }
                    }
                }
            },
            cutout: '50%'
        }
    });

    // Chart Performa Bagian
    const ctx2 = document.getElementById('sectionPerformanceChart').getContext('2d');
    new Chart(ctx2, {
        type: 'bar',
        data: {
            labels: ['Pangker (1-10)', 'Janker (11-40)', 'Tiangker (41-50)'],
            datasets: [{
                label: 'Jawaban Benar',
                data: [sectionData.pangker, sectionData.janker, sectionData.tiangker],
                backgroundColor: ['#007bff', '#6c757d', '#28a745'],
                borderWidth: 1,
                borderColor: '#fff',
                borderRadius: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 30,
                    ticks: { font: { size: 7 } }
                },
                x: {
                    ticks: { font: { size: 7 } }
                }
            },
            plugins: {
                legend: { display: false }
            }
        }
    });

    // Force render charts
    setTimeout(() => {
        window.dispatchEvent(new Event('resize'));
    }, 100);
</script>
@endpush