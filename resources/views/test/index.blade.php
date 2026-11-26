@extends('layouts.app')

@section('title', 'Tes Kraepelin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header bg-primary">
                    <h3 class="card-title text-white">
                        <i class="fas fa-brain"></i> Tes Kraepelin
                    </h3>
                </div>
                <div class="card-body">
                    @if($activeSession)
                        <div class="alert alert-warning">
                            <h5><i class="icon fas fa-exclamation-triangle"></i> Tes Dalam Proses</h5>
                            <p>Anda memiliki tes yang belum diselesaikan. Waktu tersisa akan terus berjalan.</p>
                            <div class="mt-3">
                                <a href="{{ route('test.take') }}" class="btn btn-warning">
                                    <i class="fas fa-play-circle"></i> Lanjutkan Tes
                                </a>
                                <form action="{{ route('test.start') }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin memulai tes baru? Tes yang sedang berjalan akan dihapus.')">
                                        <i class="fas fa-redo"></i> Mulai Tes Baru
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif

                    <!-- Petunjuk Tes -->
                    <div class="callout callout-info">
                        <h5><i class="fas fa-info-circle"></i> Petunjuk Tes Kraepelin</h5>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <h6><i class="fas fa-list-ol text-primary"></i> Format Tes:</h6>
                                <ul>
                                    <li>Tes terdiri dari <strong>50 soal penjumlahan</strong></li>
                                    <li>Setiap soal berupa penjumlahan 2 angka (1-9)</li>
                                    <li>Hasil penjumlahan antara 2 sampai 18</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="fas fa-clock text-primary"></i> Waktu:</h6>
                                <ul>
                                    <li>Waktu pengerjaan: <strong>5 menit</strong></li>
                                    <li>Timer akan berjalan otomatis</li>
                                    <li>Tes berhenti otomatis ketika waktu habis</li>
                                </ul>
                            </div>
                        </div>
                        
                        <h6><i class="fas fa-mouse-pointer text-primary"></i> Cara Mengerjakan:</h6>
                        <ol>
                            <li>Baca soal yang muncul di layar</li>
                            <li>Ketik jawaban Anda di kolom input</li>
                            <li>Tekan <kbd>Enter</kbd> atau klik tombol "Jawab"</li>
                            <li>Soal berikutnya akan muncul otomatis</li>
                            <li>Kerjakan dengan <strong>cepat dan tepat</strong></li>
                        </ol>

                        <div class="alert alert-warning mt-3">
                            <h6><i class="fas fa-exclamation-triangle"></i> Penting!</h6>
                            <ul class="mb-0">
                                <li>Pastikan koneksi internet stabil</li>
                                <li>Jangan merefresh halaman selama tes</li>
                                <li>Fokus dan hindari gangguan</li>
                                <li>Jawab dengan jujur sesuai kemampuan</li>
                            </ul>
                        </div>
                    </div>

                    @if(!Auth::user()->has_taken_test || $activeSession)
                        <div class="text-center mt-4">
                            <form action="{{ route('test.start') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success btn-lg px-5 py-3">
                                    <i class="fas fa-play-circle fa-2x mb-2"></i><br>
                                    <span style="font-size: 1.5em;">MULAI TES</span>
                                </button>
                            </form>
                            
                            <div class="mt-3">
                                <small class="text-muted">
                                    <i class="fas fa-shield-alt"></i> Data tes Anda akan disimpan dan dianalisis secara profesional
                                </small>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-success text-center">
                            <h5><i class="icon fas fa-check-circle"></i> Tes Telah Selesai</h5>
                            <p>Anda telah menyelesaikan tes Kraepelin. Hasil tes dapat dilihat di halaman status.</p>
                            <div class="mt-3">
                                <a href="{{ route('test.status') }}" class="btn btn-success">
                                    <i class="fas fa-info-circle"></i> Lihat Status Tes
                                </a>
                                <form action="{{ route('test.start') }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-redo"></i> Ulangi Tes
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif

                    <!-- Contoh Soal -->
                    <div class="card mt-4">
                        <div class="card-header bg-light">
                            <h6 class="card-title mb-0"><i class="fas fa-eye"></i> Contoh Soal</h6>
                        </div>
                        <div class="card-body text-center">
                            <div class="row justify-content-center">
                                <div class="col-auto">
                                    <div class="border rounded p-4 bg-white">
                                        <div class="display-4 font-weight-bold text-primary">
                                            7 + 5
                                        </div>
                                        <div class="mt-3">
                                            <input type="number" class="form-control form-control-lg text-center" value="12" readonly style="width: 120px; margin: 0 auto;">
                                        </div>
                                        <small class="text-muted mt-2 d-block">Ketik jawaban lalu tekan Enter</small>
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
@endsection