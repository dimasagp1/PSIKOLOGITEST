@extends('layouts.app')

@section('title', 'Analitik')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-3 col-sm-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ number_format($performanceData->avg_accuracy ?? 0, 1) }}<sup style="font-size: 20px">%</sup></h3>
                    <p>Rata-rata Akurasi</p>
                </div>
                <div class="icon">
                    <i class="fas fa-bullseye"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ number_format($performanceData->avg_speed ?? 0, 1) }}</h3>
                    <p>Rata-rata Kecepatan</p>
                </div>
                <div class="icon">
                    <i class="fas fa-tachometer-alt"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ number_format($performanceData->avg_consistency ?? 0, 1) }}<sup style="font-size: 20px">%</sup></h3>
                    <p>Rata-rata Konsistensi</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ number_format($performanceData->avg_fatigue ?? 0, 1) }}<sup style="font-size: 20px">%</sup></h3>
                    <p>Rata-rata Kelelahan</p>
                </div>
                <div class="icon">
                    <i class="fas fa-battery-half"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Distribusi Tingkat Performa</h3>
                </div>
                <div class="card-body">
                    @if($performanceLevels->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Tingkat Performa</th>
                                    <th>Jumlah</th>
                                    <th>Persentase</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($performanceLevels as $level)
                                @php
                                    $badgeColor = 'secondary';
                                    if ($level->level == 'Sangat Baik') $badgeColor = 'success';
                                    elseif ($level->level == 'Baik') $badgeColor = 'primary';
                                    elseif ($level->level == 'Cukup') $badgeColor = 'warning';
                                    elseif ($level->level == 'Perlu Perbaikan') $badgeColor = 'danger';
                                @endphp
                                <tr>
                                    <td>
                                        <span class="badge badge-{{ $badgeColor }}">
                                            {{ $level->level ?? 'Tidak Terklasifikasi' }}
                                        </span>
                                    </td>
                                    <td>{{ $level->count }}</td>
                                    <td>{{ number_format(($level->count / $performanceData->total_tests) * 100, 1) }}%</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Belum ada data performa yang tersedia.
                    </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Statistik Tes</h3>
                </div>
                <div class="card-body">
                    <div class="info-box bg-light">
                        <span class="info-box-icon bg-info"><i class="fas fa-file-alt"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Tes Dilakukan</span>
                            <span class="info-box-number">{{ $performanceData->total_tests ?? 0 }}</span>
                        </div>
                    </div>
                    
                    <div class="info-box bg-light mt-3">
                        <span class="info-box-icon bg-success"><i class="fas fa-users"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Peserta Tes</span>
                            <span class="info-box-number">{{ App\Models\User::where('has_taken_test', true)->count() }}</span>
                        </div>
                    </div>

                    <div class="info-box bg-light mt-3">
                        <span class="info-box-icon bg-warning"><i class="fas fa-user-clock"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Belum Tes</span>
                            <span class="info-box-number">{{ App\Models\User::where('has_taken_test', false)->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Rekap Harian Tes</h3>
                </div>
                <div class="card-body">
                    @php
                        $dailyTests = \App\Models\KraeplinTest::selectRaw('DATE(created_at) as date, COUNT(*) as count')
                            ->groupBy('date')
                            ->orderBy('date', 'desc')
                            ->take(7)
                            ->get();
                    @endphp
                    
                    @if($dailyTests->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Jumlah Tes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dailyTests as $daily)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($daily->date)->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="badge badge-primary">{{ $daily->count }}</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Belum ada data tes harian.
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection