@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $totalUsers }}</h3>
                    <p>Total User</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
                <a href="{{ route('admin.users') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $totalTests }}</h3>
                    <p>Total Tes</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <a href="{{ route('admin.test-results') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $todayTests }}</h3>
                    <p>Tes Hari Ini</p>
                </div>
                <div class="icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <a href="{{ route('admin.test-results') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ number_format($recentTests->avg('total_score') ?? 0, 1) }}</h3>
                    <p>Rata-rata Skor</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <a href="{{ route('admin.analytics') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>

    <!-- Debug Info -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Info Sistem</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <strong>Total User Tes:</strong> {{ App\Models\User::where('has_taken_test', true)->count() }}
                        </div>
                        <div class="col-md-4">
                            <strong>Total Data Tes:</strong> {{ App\Models\KraeplinTest::count() }}
                        </div>
                        <div class="col-md-4">
                            <strong>User Belum Tes:</strong> {{ App\Models\User::where('has_taken_test', false)->where('role', 'user')->count() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Tes Terbaru</h3>
                    <div class="card-tools">
                        <span class="badge badge-primary">{{ $recentTests->count() }} tes</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($recentTests->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Tanggal</th>
                                    <th>Skor</th>
                                    <th>Benar</th>
                                    <th>Salah</th>
                                    <th>Dilewati</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentTests as $test)
                                @php
                                    $performanceLevel = $test->analysis_results['performance_level'] ?? 'Tidak Ada';
                                    $badgeColor = 'secondary';
                                    if ($performanceLevel == 'Sangat Baik') $badgeColor = 'success';
                                    elseif ($performanceLevel == 'Baik') $badgeColor = 'primary';
                                    elseif ($performanceLevel == 'Cukup') $badgeColor = 'warning';
                                    elseif ($performanceLevel == 'Perlu Perbaikan') $badgeColor = 'danger';
                                @endphp
                                <tr>
                                    <td>{{ $test->user->name }}</td>
                                    <td>{{ $test->created_at->format('d/m H:i') }}</td>
                                    <td><strong>{{ $test->total_score }}</strong></td>
                                    <td><span class="badge badge-success">{{ $test->correct_answers }}</span></td>
                                    <td><span class="badge badge-danger">{{ $test->wrong_answers }}</span></td>
                                    <td><span class="badge badge-warning">{{ $test->skipped_answers ?? 0 }}</span></td>
                                    <td>
                                        <span class="badge badge-{{ $badgeColor }}">
                                            {{ $performanceLevel }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.results.show', $test) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i> Detail
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-warning m-3">
                        <i class="fas fa-exclamation-triangle"></i> Belum ada data tes yang tersimpan.
                    </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Quick Actions</h3>
                </div>
                <div class="card-body">
                    <a href="{{ route('admin.export-results') }}" class="btn btn-success btn-block mb-3">
                        <i class="fas fa-download"></i> Export Hasil Tes
                    </a>
                    <a href="{{ route('admin.analytics') }}" class="btn btn-info btn-block mb-3">
                        <i class="fas fa-chart-line"></i> Lihat Analitik
                    </a>
                    <a href="{{ route('admin.users') }}" class="btn btn-warning btn-block mb-3">
                        <i class="fas fa-user-plus"></i> Kelola User
                    </a>
                    <a href="{{ route('admin.test-results') }}" class="btn btn-primary btn-block mb-3">
                        <i class="fas fa-list"></i> Semua Hasil Tes
                    </a>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Statistik Cepat</h3>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>User Aktif:</span>
                        <strong>{{ App\Models\User::where('has_taken_test', true)->count() }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Belum Tes:</span>
                        <strong>{{ App\Models\User::where('has_taken_test', false)->count() }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Data Tes:</span>
                        <strong>{{ App\Models\KraeplinTest::count() }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Rata Akurasi:</span>
                        <strong>{{ number_format($recentTests->avg('analysis_results.accuracy') ?? 0, 1) }}%</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection