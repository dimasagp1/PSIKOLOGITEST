@extends('layouts.app')

@section('title', 'Hasil Tes')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Hasil Tes Kraepelin</h3>
            <div class="card-tools">
                <a href="{{ route('admin.export-results') }}" class="btn btn-success">
                    <i class="fas fa-download"></i> Export CSV
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Tanggal Tes</th>
                            <th>Skor Total</th>
                            <th>Jawaban Benar</th>
                            <th>Jawaban Salah</th>
                            <th>Soal Dilewati</th>
                            <th>Waktu (detik)</th>
                            <th>Akurasi</th>
                            <th>Kecepatan</th>
                            <th>Performa</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($results as $result)
                        <tr>
                            <td>{{ $result->user->name }}</td>
                            <td>{{ $result->user->email }}</td>
                            <td>{{ $result->created_at->format('d/m/Y H:i') }}</td>
                            <td><span class="badge badge-primary">{{ $result->total_score }}</span></td>
                            <td><span class="badge badge-success">{{ $result->correct_answers }}</span></td>
                            <td><span class="badge badge-danger">{{ $result->wrong_answers }}</span></td>
                            <td><span class="badge badge-warning">{{ $result->skipped_answers ?? 0 }}</span></td>
                            <td>{{ $result->completion_time }}</td>
                            <td>
                                <span class="badge badge-{{ ($result->analysis_results['accuracy'] ?? 0) >= 80 ? 'success' : (($result->analysis_results['accuracy'] ?? 0) >= 60 ? 'warning' : 'danger') }}">
                                    {{ $result->analysis_results['accuracy'] ?? 0 }}%
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-info">
                                    {{ $result->analysis_results['speed'] ?? 0 }}/mnt
                                </span>
                            </td>
                            <td>
                                @php
                                    $performanceLevel = $result->analysis_results['performance_level'] ?? 'Tidak Ada';
                                    $badgeColor = 'secondary';
                                    if ($performanceLevel == 'Sangat Baik') $badgeColor = 'success';
                                    elseif ($performanceLevel == 'Baik') $badgeColor = 'primary';
                                    elseif ($performanceLevel == 'Cukup') $badgeColor = 'warning';
                                    elseif ($performanceLevel == 'Perlu Perbaikan') $badgeColor = 'danger';
                                @endphp
                                <span class="badge badge-{{ $badgeColor }}">
                                    {{ $performanceLevel }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.results.show', $result) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> Detail
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="d-flex justify-content-center mt-3">
                {{ $results->links() }}
            </div>
        </div>
    </div>
</div>
@endsection