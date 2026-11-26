@push('styles')
<style>
    .chart-container {
        position: relative;
        height: 300px;
        width: 100%;
    }
    .factor-box {
        border-left: 4px solid #007bff;
        padding-left: 15px;
        margin-bottom: 20px;
    }
    .factor-box.success { border-color: #28a745; }
    .factor-box.warning { border-color: #ffc107; }
    .factor-box.danger { border-color: #dc3545; }
    .progress-thin {
        height: 8px;
    }
    .metric-card {
        border: 1px solid #e3e6f0;
        border-radius: 0.35rem;
        padding: 1rem;
        margin-bottom: 1rem;
    }
    .metric-value {
        font-size: 1.5rem;
        font-weight: bold;
    }
    .performance-badge {
        font-size: 0.8rem;
        padding: 0.3rem 0.6rem;
    }
    .no-data {
        text-align: center;
        padding: 2rem;
        color: #6c757d;
    }
    .section-score {
        font-size: 2rem;
        font-weight: bold;
        margin-bottom: 0.5rem;
    }
    .section-label {
        font-size: 0.9rem;
        color: #6c757d;
        margin-bottom: 0.5rem;
    }
</style>
@endpush

@php
    // Data untuk chart dengan default values
    $correctAnswers = $test->correct_answers ?? 0;
    $wrongAnswers = $test->wrong_answers ?? 0;
    $skippedAnswers = $test->skipped_answers ?? 0;
    
    $detailedAnalysis = $test->detailed_analysis ?? [];
    $sectionAnalysis = $detailedAnalysis['section_analysis'] ?? [
        'pangker_percentage' => 0, 
        'janker_percentage' => 0, 
        'tiangker_percentage' => 0
    ];
    
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
    
    $y0 = $detailedAnalysis['y0'] ?? 0;
    $y50 = $detailedAnalysis['y50'] ?? 0;
    $y50_minus_y0 = $detailedAnalysis['y50_minus_y0'] ?? 0;

    // Analysis results
    $analysisResults = $test->analysis_results ?? [];
    $performanceLevel = $analysisResults['performance_level'] ?? 'Tidak Terdeteksi';
    $fatigueIndex = $analysisResults['fatigue_index'] ?? 0;
@endphp

<!-- Statistik Utama -->
<div class="row mb-4">
    <div class="col-md-3 col-sm-6">
        <div class="info-box bg-gradient-success">
            <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Jawaban Benar</span>
                <span class="info-box-number">{{ $correctAnswers }}</span>
                <div class="progress">
                    <div class="progress-bar" style="width: {{ ($correctAnswers / 50) * 100 }}%"></div>
                </div>
                <span class="progress-description">
                    {{ number_format(($correctAnswers / 50) * 100, 1) }}% dari total soal
                </span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="info-box bg-gradient-danger">
            <span class="info-box-icon"><i class="fas fa-times-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Jawaban Salah</span>
                <span class="info-box-number">{{ $wrongAnswers }}</span>
                <div class="progress">
                    <div class="progress-bar" style="width: {{ ($wrongAnswers / 50) * 100 }}%"></div>
                </div>
                <span class="progress-description">
                    {{ number_format(($wrongAnswers / 50) * 100, 1) }}% dari total soal
                </span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="info-box bg-gradient-warning">
            <span class="info-box-icon"><i class="fas fa-forward"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Soal Dilewati</span>
                <span class="info-box-number">{{ $skippedAnswers }}</span>
                <div class="progress">
                    <div class="progress-bar" style="width: {{ ($skippedAnswers / 50) * 100 }}%"></div>
                </div>
                <span class="progress-description">
                    {{ number_format(($skippedAnswers / 50) * 100, 1) }}% dari total soal
                </span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="info-box bg-gradient-info">
            <span class="info-box-icon"><i class="fas fa-clock"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Waktu Pengerjaan</span>
                <span class="info-box-number">{{ $test->completion_time }}s</span>
                <div class="progress">
                    <div class="progress-bar" style="width: 100%"></div>
                </div>
                <span class="progress-description">
                    {{ floor($test->completion_time / 60) }}m {{ $test->completion_time % 60 }}s
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Grafik Performa -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title"><i class="fas fa-chart-pie"></i> Distribusi Jawaban</h4>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="answerDistributionChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title"><i class="fas fa-chart-bar"></i> Performa per Bagian</h4>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="sectionPerformanceChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Faktor Kraepelin -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="card-title mb-0">
                    <i class="fas fa-calculator"></i> Analisis Faktor Kraepelin
                </h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="factor-box {{ $speedFactor >= 25 ? 'success' : ($speedFactor >= 20 ? 'warning' : 'danger') }}">
                            <h5><i class="fas fa-tachometer-alt"></i> Speed Factor</h5>
                            <p class="mb-1">Kecepatan Kerja: <strong>{{ $speedFactor }}</strong></p>
                            <small>Soal per menit - Mengukur kecepatan processing</small>
                            <div class="progress progress-thin mt-2">
                                <div class="progress-bar bg-success" style="width: {{ min(100, $speedFactor / 40 * 100) }}%"></div>
                            </div>
                            <small class="text-muted">
                                @if($speedFactor >= 25) Sangat Cepat
                                @elseif($speedFactor >= 20) Cukup Cepat  
                                @elseif($speedFactor >= 15) Sedang
                                @else Perlu Peningkatan
                                @endif
                            </small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="factor-box {{ $rhythmFactor >= 80 ? 'success' : ($rhythmFactor >= 60 ? 'warning' : 'danger') }}">
                            <h5><i class="fas fa-music"></i> Rhythm Factor</h5>
                            <p class="mb-1">Irama Kerja: <strong>{{ $rhythmFactor }}</strong></p>
                            <small>Konsistensi tempo - Stabilitas kecepatan</small>
                            <div class="progress progress-thin mt-2">
                                <div class="progress-bar bg-info" style="width: {{ $rhythmFactor }}%"></div>
                            </div>
                            <small class="text-muted">
                                @if($rhythmFactor >= 80) Sangat Konsisten
                                @elseif($rhythmFactor >= 60) Cukup Konsisten
                                @elseif($rhythmFactor >= 40) Fluktuatif
                                @else Tidak Konsisten
                                @endif
                            </small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="factor-box {{ $ausdeurFactor >= 80 ? 'success' : ($ausdeurFactor >= 60 ? 'warning' : 'danger') }}">
                            <h5><i class="fas fa-battery-three-quarters"></i> Ausdeur Factor</h5>
                            <p class="mb-1">Ketahanan: <strong>{{ $ausdeurFactor }}</strong></p>
                            <small>Daya tahan kerja - Konsistensi performa</small>
                            <div class="progress progress-thin mt-2">
                                <div class="progress-bar bg-warning" style="width: {{ min(100, $ausdeurFactor) }}%"></div>
                            </div>
                            <small class="text-muted">
                                @if($ausdeurFactor >= 80) Daya Tahan Baik
                                @elseif($ausdeurFactor >= 60) Cukup Tahan
                                @elseif($ausdeurFactor >= 40) Mulai Lelah
                                @else Kelelahan Cepat
                                @endif
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Analisis Bagian Tes -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <h5><i class="fas fa-layer-group"></i> Analisis per Bagian Tes</h5>
                        <div class="row text-center">
                            <div class="col-md-3">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <div class="section-label">PANGKER</div>
                                        <div class="section-score text-primary">{{ $pangker }}/10</div>
                                        <div class="text-muted">Soal 1-10</div>
                                        <div class="progress progress-thin mt-2">
                                            <div class="progress-bar bg-primary" style="width: {{ $sectionAnalysis['pangker_percentage'] }}%"></div>
                                        </div>
                                        <small>{{ $sectionAnalysis['pangker_percentage'] }}%</small>
                                        <div class="mt-2">
                                            <small class="text-muted">Awal Tes - Adaptasi</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <div class="section-label">JANKER</div>
                                        <div class="section-score text-info">{{ $janker }}/30</div>
                                        <div class="text-muted">Soal 11-40</div>
                                        <div class="progress progress-thin mt-2">
                                            <div class="progress-bar bg-info" style="width: {{ $sectionAnalysis['janker_percentage'] }}%"></div>
                                        </div>
                                        <small>{{ $sectionAnalysis['janker_percentage'] }}%</small>
                                        <div class="mt-2">
                                            <small class="text-muted">Inti Tes - Stabilisasi</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <div class="section-label">TIANGKER</div>
                                        <div class="section-score text-warning">{{ $tiangker }}/10</div>
                                        <div class="text-muted">Soal 41-50</div>
                                        <div class="progress progress-thin mt-2">
                                            <div class="progress-bar bg-warning" style="width: {{ $sectionAnalysis['tiangker_percentage'] }}%"></div>
                                        </div>
                                        <small>{{ $sectionAnalysis['tiangker_percentage'] }}%</small>
                                        <div class="mt-2">
                                            <small class="text-muted">Akhir Tes - Ketahanan</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <div class="section-label">TREND</div>
                                        <div class="section-score {{ $y50_minus_y0 >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ $y50_minus_y0 >= 0 ? '+' : '' }}{{ $y50_minus_y0 }}
                                        </div>
                                        <div class="text-muted">Y(50) - Y(0)</div>
                                        <div class="mt-3">
                                            @if($y50_minus_y0 > 0)
                                                <span class="badge badge-success performance-badge"><i class="fas fa-arrow-up"></i> Meningkat</span>
                                            @elseif($y50_minus_y0 < 0)
                                                <span class="badge badge-danger performance-badge"><i class="fas fa-arrow-down"></i> Menurun</span>
                                            @else
                                                <span class="badge badge-secondary performance-badge"><i class="fas fa-minus"></i> Stabil</span>
                                            @endif
                                        </div>
                                        <div class="mt-2">
                                            <small class="text-muted">Performa Akhir vs Awal</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Metrik Detail -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title"><i class="fas fa-tachometer-alt"></i> Metrik Kinerja Detail</h4>
            </div>
            <div class="card-body">
                <div class="metric-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>
                            <i class="fas fa-bullseye text-success mr-2"></i>
                            Akurasi:
                        </span>
                        <span class="metric-value text-success">{{ $accuracyRate }}%</span>
                    </div>
                    <div class="progress mb-3">
                        <div class="progress-bar bg-success" style="width: {{ $accuracyRate }}%"></div>
                    </div>
                    <small class="text-muted">
                        Rasio jawaban benar dari total yang dikerjakan
                        @if($accuracyRate >= 80) ✓ Tinggi
                        @elseif($accuracyRate >= 60) ○ Sedang  
                        @else ✗ Perlu ditingkatkan
                        @endif
                    </small>
                </div>

                <div class="metric-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>
                            <i class="fas fa-wave-square text-info mr-2"></i>
                            Konsistensi:
                        </span>
                        <span class="metric-value text-info">{{ $consistencyRate }}%</span>
                    </div>
                    <div class="progress mb-3">
                        <div class="progress-bar bg-info" style="width: {{ $consistencyRate }}%"></div>
                    </div>
                    <small class="text-muted">
                        Stabilitas performa sepanjang tes
                        @if($consistencyRate >= 80) ✓ Stabil
                        @elseif($consistencyRate >= 60) ○ Cukup
                        @else ✗ Fluktuatif
                        @endif
                    </small>
                </div>

                <div class="metric-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>
                            <i class="fas fa-chart-line text-primary mr-2"></i>
                            Stabilitas:
                        </span>
                        <span class="metric-value text-primary">{{ $stabilityRate }}%</span>
                    </div>
                    <div class="progress mb-3">
                        <div class="progress-bar bg-primary" style="width: {{ $stabilityRate }}%"></div>
                    </div>
                    <small class="text-muted">
                        Kemampuan mempertahankan performa optimal
                        @if($stabilityRate >= 80) ✓ Sangat Stabil
                        @elseif($stabilityRate >= 60) ○ Stabil
                        @else ✗ Kurang Stabil
                        @endif
                    </small>
                </div>

                <div class="metric-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>
                            <i class="fas fa-running text-warning mr-2"></i>
                            Ketahanan:
                        </span>
                        <span class="metric-value text-warning">{{ $enduranceRate }}%</span>
                    </div>
                    <div class="progress mb-3">
                        <div class="progress-bar bg-warning" style="width: {{ min(100, $enduranceRate) }}%"></div>
                    </div>
                    <small class="text-muted">
                        Daya tahan menghadapi tekanan waktu
                        @if($enduranceRate >= 80) ✓ Tahan Lama
                        @elseif($enduranceRate >= 60) ○ Cukup Tahan
                        @else ✗ Cepat Lelah
                        @endif
                    </small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title"><i class="fas fa-chart-line"></i> Trend Performa</h4>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="performanceTrendChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Analisis Per Kuartal -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title"><i class="fas fa-chart-area"></i> Analisis Per Kuartal</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    @php
                        $quarters = [
                            ['range' => '1-12', 'score' => $pangker, 'total' => 12, 'color' => 'primary', 'label' => 'Adaptasi'],
                            ['range' => '13-25', 'score' => floor($janker / 3), 'total' => 13, 'color' => 'info', 'label' => 'Stabilisasi'],
                            ['range' => '26-38', 'score' => floor($janker / 3), 'total' => 13, 'color' => 'success', 'label' => 'Optimal'],
                            ['range' => '39-50', 'score' => $tiangker, 'total' => 12, 'color' => 'warning', 'label' => 'Ketahanan']
                        ];
                    @endphp
                    @foreach($quarters as $quarter)
                    <div class="col-md-3">
                        <div class="text-center">
                            <h6>Kuartal {{ $quarter['range'] }}</h6>
                            <div class="small text-muted mb-2">{{ $quarter['label'] }}</div>
                            <div class="h3 text-{{ $quarter['color'] }}">{{ $quarter['score'] }}/{{ $quarter['total'] }}</div>
                            @php
                                $percentage = $quarter['total'] > 0 ? round(($quarter['score'] / $quarter['total']) * 100, 1) : 0;
                            @endphp
                            <small>{{ $percentage }}%</small>
                            <div class="progress progress-thin mt-1">
                                <div class="progress-bar bg-{{ $quarter['color'] }}" style="width: {{ $percentage }}%"></div>
                            </div>
                            <div class="mt-2">
                                @if($percentage >= 80)
                                    <span class="badge badge-success">Excellent</span>
                                @elseif($percentage >= 60)
                                    <span class="badge badge-primary">Good</span>
                                @elseif($percentage >= 40)
                                    <span class="badge badge-warning">Fair</span>
                                @else
                                    <span class="badge badge-danger">Poor</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Ringkasan Evaluasi -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-{{ $performanceLevel == 'Sangat Baik' ? 'success' : ($performanceLevel == 'Baik' ? 'primary' : ($performanceLevel == 'Cukup' ? 'warning' : 'danger')) }}">
                <h4 class="card-title text-white mb-0">
                    <i class="fas fa-chart-line"></i> Ringkasan Evaluasi - {{ $performanceLevel }}
                </h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5><i class="fas fa-check-circle text-success"></i> Kelebihan:</h5>
                        <ul class="list-unstyled">
                            @if($speedFactor >= 25)
                            <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> Kecepatan kerja yang sangat baik</li>
                            @endif
                            @if($accuracyRate >= 80)
                            <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> Tingkat akurasi yang tinggi</li>
                            @endif
                            @if($consistencyRate >= 80)
                            <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> Konsistensi performa yang stabil</li>
                            @endif
                            @if($ausdeurFactor >= 80)
                            <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> Daya tahan kerja yang baik</li>
                            @endif
                            @if($y50_minus_y0 > 0)
                            <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> Performa meningkat di akhir tes</li>
                            @endif
                            @if($fatigueIndex <= 10)
                            <li class="mb-2"><i class="fas fa-check text-success mr-2"></i> Tingkat kelelahan rendah</li>
                            @endif
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h5><i class="fas fa-exclamation-triangle text-warning"></i> Area Perbaikan:</h5>
                        <ul class="list-unstyled">
                            @if($speedFactor < 20)
                            <li class="mb-2"><i class="fas fa-clock text-warning mr-2"></i> Perlu peningkatan kecepatan kerja</li>
                            @endif
                            @if($accuracyRate < 70)
                            <li class="mb-2"><i class="fas fa-bullseye text-warning mr-2"></i> Perlu meningkatkan ketelitian</li>
                            @endif
                            @if($consistencyRate < 70)
                            <li class="mb-2"><i class="fas fa-wave-square text-warning mr-2"></i> Perlu meningkatkan konsistensi</li>
                            @endif
                            @if($ausdeurFactor < 60)
                            <li class="mb-2"><i class="fas fa-battery-quarter text-warning mr-2"></i> Perlu meningkatkan daya tahan</li>
                            @endif
                            @if($skippedAnswers > 5)
                            <li class="mb-2"><i class="fas fa-forward text-warning mr-2"></i> Terlalu banyak soal yang dilewati</li>
                            @endif
                            @if($y50_minus_y0 < 0)
                            <li class="mb-2"><i class="fas fa-arrow-down text-warning mr-2"></i> Performa menurun di akhir tes</li>
                            @endif
                        </ul>
                    </div>
                </div>
                
                <div class="mt-3 p-3 bg-light rounded">
                    <h6><i class="fas fa-lightbulb text-info"></i> Rekomendasi:</h6>
                    <p class="mb-0">
                        @if($performanceLevel == 'Sangat Baik')
                        <strong>Kandidat menunjukkan performa yang sangat baik di semua aspek.</strong> Sangat direkomendasikan untuk posisi yang membutuhkan ketelitian, kecepatan tinggi, dan ketahanan kerja. Potensi pengembangan: dapat ditempatkan di posisi strategis dengan tanggung jawab tinggi.
                        @elseif($performanceLevel == 'Baik')
                        <strong>Kandidat memiliki performa yang baik dengan beberapa area yang masih dapat ditingkatkan.</strong> Direkomendasikan untuk berbagai posisi dengan supervisi yang memadai. Fokus pengembangan: meningkatkan kecepatan dan ketahanan kerja.
                        @elseif($performanceLevel == 'Cukup')
                        <strong>Kandidat menunjukkan kemampuan yang cukup namun perlu pengembangan lebih lanjut.</strong> Disarankan untuk pelatihan tambahan dan pembinaan intensif sebelum penempatan. Fokus: meningkatkan akurasi dan konsistensi kerja.
                        @else
                        <strong>Kandidat memerlukan pembinaan dan pelatihan intensif</strong> sebelum dapat ditempatkan di posisi yang membutuhkan ketelitian tinggi. Rekomendasi: program pengembangan dasar dengan monitoring ketat.
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

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

// Tunggu sampai DOM siap
document.addEventListener('DOMContentLoaded', function() {
    // Chart Distribusi Jawaban
    const ctx1 = document.getElementById('answerDistributionChart').getContext('2d');
    new Chart(ctx1, {
        type: 'doughnut',
        data: {
            labels: ['Benar ({{ $correctAnswers }})', 'Salah ({{ $wrongAnswers }})', 'Dilewati ({{ $skippedAnswers }})'],
            datasets: [{
                data: [answerData.correct, answerData.wrong, answerData.skipped],
                backgroundColor: ['#28a745', '#dc3545', '#ffc107'],
                borderWidth: 3,
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
                        padding: 20,
                        usePointStyle: true,
                        font: {
                            size: 11
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const total = answerData.correct + answerData.wrong + answerData.skipped;
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${label}: ${value} soal (${percentage}%)`;
                        }
                    }
                }
            },
            cutout: '60%'
        }
    });

    // Chart Performa Bagian
    const ctx2 = document.getElementById('sectionPerformanceChart').getContext('2d');
    new Chart(ctx2, {
        type: 'bar',
        data: {
            labels: ['Pangker\n(1-10)', 'Janker\n(11-40)', 'Tiangker\n(41-50)'],
            datasets: [{
                label: 'Jawaban Benar',
                data: [sectionData.pangker, sectionData.janker, sectionData.tiangker],
                backgroundColor: ['#007bff', '#6c757d', '#28a745'],
                borderWidth: 1,
                borderColor: '#fff',
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 30,
                    title: {
                        display: true,
                        text: 'Jumlah Benar'
                    },
                    grid: {
                        color: 'rgba(0,0,0,0.1)'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Bagian Tes'
                    },
                    grid: {
                        display: false
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const maxScore = context.datasetIndex === 0 ? 
                                (context.dataIndex === 0 ? 10 : 
                                 context.dataIndex === 1 ? 30 : 10) : 0;
                            const percentage = ((context.raw / maxScore) * 100).toFixed(1);
                            return `Jawaban Benar: ${context.raw}/${maxScore} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });

    // Chart Trend Performa
    const ctx3 = document.getElementById('performanceTrendChart').getContext('2d');
    new Chart(ctx3, {
        type: 'line',
        data: {
            labels: ['1-10', '11-20', '21-30', '31-40', '41-50'],
            datasets: [{
                label: 'Jawaban Benar per 10 Soal',
                data: [
                    {{ $pangker }},
                    {{ floor($janker / 3) }},
                    {{ floor($janker / 3) }},
                    {{ floor($janker / 3) }},
                    {{ $tiangker }}
                ],
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#007bff',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 6,
                pointHoverRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 10,
                    title: {
                        display: true,
                        text: 'Jawaban Benar'
                    },
                    grid: {
                        color: 'rgba(0,0,0,0.1)'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Rentang Soal'
                    },
                    grid: {
                        color: 'rgba(0,0,0,0.05)'
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `Soal ${context.label}: ${context.raw} benar`;
                        }
                    }
                }
            }
        }
    });
});
</script>
@endpush