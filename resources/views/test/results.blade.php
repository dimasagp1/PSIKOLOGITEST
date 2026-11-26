@extends('layouts.app')

@section('title', 'Hasil Tes Kraepelin')

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
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-success">
                    <h3 class="card-title text-white">
                        <i class="fas fa-chart-bar"></i> Hasil Tes Kraepelin - Analisis Lengkap
                    </h3>
                    <div class="card-tools">
                        <button onclick="downloadPDF()" class="btn btn-light btn-sm">
                            <i class="fas fa-download"></i> Download PDF
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Statistik Utama -->
                    <div class="row mb-4">
                        <div class="col-md-3 col-sm-6">
                            <div class="info-box bg-gradient-success">
                                <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Jawaban Benar</span>
                                    <span class="info-box-number">{{ $latestTest->correct_answers }}</span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: {{ ($latestTest->correct_answers / 50) * 100 }}%"></div>
                                    </div>
                                    <span class="progress-description">
                                        {{ number_format(($latestTest->correct_answers / 50) * 100, 1) }}% dari total soal
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="info-box bg-gradient-danger">
                                <span class="info-box-icon"><i class="fas fa-times-circle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Jawaban Salah</span>
                                    <span class="info-box-number">{{ $latestTest->wrong_answers }}</span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: {{ ($latestTest->wrong_answers / 50) * 100 }}%"></div>
                                    </div>
                                    <span class="progress-description">
                                        {{ number_format(($latestTest->wrong_answers / 50) * 100, 1) }}% dari total soal
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="info-box bg-gradient-warning">
                                <span class="info-box-icon"><i class="fas fa-forward"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Soal Dilewati</span>
                                    <span class="info-box-number">{{ $latestTest->skipped_answers ?? 0 }}</span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: {{ (($latestTest->skipped_answers ?? 0) / 50) * 100 }}%"></div>
                                    </div>
                                    <span class="progress-description">
                                        {{ number_format((($latestTest->skipped_answers ?? 0) / 50) * 100, 1) }}% dari total soal
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="info-box bg-gradient-info">
                                <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Waktu</span>
                                    <span class="info-box-number">{{ $latestTest->completion_time }}s</span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: 100%"></div>
                                    </div>
                                    <span class="progress-description">
                                        {{ floor($latestTest->completion_time / 60) }}m {{ $latestTest->completion_time % 60 }}s
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
                                    <h4 class="card-title">Distribusi Jawaban</h4>
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
                                    <h4 class="card-title">Performa per Bagian</h4>
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
                                            <div class="factor-box {{ $latestTest->speed_factor >= 25 ? 'success' : ($latestTest->speed_factor >= 20 ? 'warning' : 'danger') }}">
                                                <h5>Speed Factor</h5>
                                                <p class="mb-1">Kecepatan Kerja: <strong>{{ $latestTest->speed_factor ?? 0 }}</strong></p>
                                                <small>Soal per menit</small>
                                                <div class="progress progress-thin mt-2">
                                                    <div class="progress-bar bg-success" style="width: {{ min(100, ($latestTest->speed_factor ?? 0) / 40 * 100) }}%"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="factor-box {{ $latestTest->rhythm_factor >= 80 ? 'success' : ($latestTest->rhythm_factor >= 60 ? 'warning' : 'danger') }}">
                                                <h5>Rhythm Factor</h5>
                                                <p class="mb-1">Irama Kerja: <strong>{{ $latestTest->rhythm_factor ?? 0 }}</strong></p>
                                                <small>Konsistensi tempo</small>
                                                <div class="progress progress-thin mt-2">
                                                    <div class="progress-bar bg-info" style="width: {{ $latestTest->rhythm_factor ?? 0 }}%"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="factor-box {{ $latestTest->ausdeur_factor >= 80 ? 'success' : ($latestTest->ausdeur_factor >= 60 ? 'warning' : 'danger') }}">
                                                <h5>Ausdeur Factor</h5>
                                                <p class="mb-1">Ketahanan: <strong>{{ $latestTest->ausdeur_factor ?? 0 }}</strong></p>
                                                <small>Daya tahan kerja</small>
                                                <div class="progress progress-thin mt-2">
                                                    <div class="progress-bar bg-warning" style="width: {{ $latestTest->ausdeur_factor ?? 0 }}%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mt-4">
                                        <div class="col-md-3">
                                            <div class="text-center">
                                                <h6>Pangker (1-10)</h6>
                                                <div class="h3 text-primary">{{ $latestTest->detailed_analysis['pangker'] ?? 0 }}/10</div>
                                                <small>{{ $latestTest->detailed_analysis['section_analysis']['pangker_percentage'] ?? 0 }}%</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="text-center">
                                                <h6>Janker (11-40)</h6>
                                                <div class="h3 text-info">{{ $latestTest->detailed_analysis['janker'] ?? 0 }}/30</div>
                                                <small>{{ $latestTest->detailed_analysis['section_analysis']['janker_percentage'] ?? 0 }}%</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="text-center">
                                                <h6>Tiangker (41-50)</h6>
                                                <div class="h3 text-warning">{{ $latestTest->detailed_analysis['tiangker'] ?? 0 }}/10</div>
                                                <small>{{ $latestTest->detailed_analysis['section_analysis']['tiangker_percentage'] ?? 0 }}%</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="text-center">
                                                <h6>Y(50)-Y(0)</h6>
                                                <div class="h3 {{ ($latestTest->detailed_analysis['y50_minus_y0'] ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                                                    {{ $latestTest->detailed_analysis['y50_minus_y0'] ?? 0 }}
                                                </div>
                                                <small>Performa akhir - awal</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Metrik Detail -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Metrik Kinerja</h4>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label>Akurasi:</label>
                                        <div class="progress mb-2">
                                            <div class="progress-bar bg-success" style="width: {{ $latestTest->accuracy_rate ?? 0 }}%">
                                                {{ $latestTest->accuracy_rate ?? 0 }}%
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label>Konsistensi:</label>
                                        <div class="progress mb-2">
                                            <div class="progress-bar bg-info" style="width: {{ $latestTest->consistency_rate ?? 0 }}%">
                                                {{ $latestTest->consistency_rate ?? 0 }}%
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label>Stabilitas:</label>
                                        <div class="progress mb-2">
                                            <div class="progress-bar bg-primary" style="width: {{ $latestTest->stability_rate ?? 0 }}%">
                                                {{ $latestTest->stability_rate ?? 0 }}%
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label>Ketahanan:</label>
                                        <div class="progress mb-2">
                                            <div class="progress-bar bg-warning" style="width: {{ $latestTest->endurance_rate ?? 0 }}%">
                                                {{ $latestTest->endurance_rate ?? 0 }}%
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Trend Performa</h4>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="performanceTrendChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Informasi Admin -->
                    @if(Auth::user()->admin_notes)
                        <div class="alert alert-info mt-4">
                            <h5><i class="icon fas fa-info-circle"></i> Informasi dari HR/Admin</h5>
                            <p class="mb-0">{{ Auth::user()->admin_notes }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
// Data untuk chart
const answerData = {
    correct: {{ $latestTest->correct_answers }},
    wrong: {{ $latestTest->wrong_answers }},
    skipped: {{ $latestTest->skipped_answers ?? 0 }}
};

const sectionData = {
    pangker: {{ $latestTest->detailed_analysis['pangker'] ?? 0 }},
    janker: {{ $latestTest->detailed_analysis['janker'] ?? 0 }},
    tiangker: {{ $latestTest->detailed_analysis['tiangker'] ?? 0 }}
};

// Chart Distribusi Jawaban
new Chart(document.getElementById('answerDistributionChart'), {
    type: 'doughnut',
    data: {
        labels: ['Benar', 'Salah', 'Dilewati'],
        datasets: [{
            data: [answerData.correct, answerData.wrong, answerData.skipped],
            backgroundColor: ['#28a745', '#dc3545', '#ffc107'],
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Chart Performa Bagian
new Chart(document.getElementById('sectionPerformanceChart'), {
    type: 'bar',
    data: {
        labels: ['Pangker', 'Janker', 'Tiangker'],
        datasets: [{
            label: 'Jawaban Benar',
            data: [sectionData.pangker, sectionData.janker, sectionData.tiangker],
            backgroundColor: ['#007bff', '#6c757d', '#28a745']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                max: 30
            }
        }
    }
});

// Chart Trend Performa
new Chart(document.getElementById('performanceTrendChart'), {
    type: 'line',
    data: {
        labels: ['Q1-10', 'Q11-20', 'Q21-30', 'Q31-40', 'Q41-50'],
        datasets: [{
            label: 'Jawaban Benar',
            data: [{{ $latestTest->detailed_analysis['pangker'] ?? 0 }}, 
                   {{ floor(($latestTest->detailed_analysis['janker'] ?? 0) / 3) }},
                   {{ floor(($latestTest->detailed_analysis['janker'] ?? 0) / 3) }},
                   {{ floor(($latestTest->detailed_analysis['janker'] ?? 0) / 3) }},
                   {{ $latestTest->detailed_analysis['tiangker'] ?? 0 }}],
            borderColor: '#007bff',
            backgroundColor: 'rgba(0, 123, 255, 0.1)',
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                max: 10
            }
        }
    }
});

// Download PDF
function downloadPDF() {
    const element = document.querySelector('.card');
    const options = {
        margin: 1,
        filename: 'hasil_tes_kraepelin_{{ $latestTest->user->name }}_{{ date("Y-m-d") }}.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2 },
        jsPDF: { unit: 'cm', format: 'a4', orientation: 'portrait' }
    };
    
    html2pdf().set(options).from(element).save();
}
</script>
@endpush