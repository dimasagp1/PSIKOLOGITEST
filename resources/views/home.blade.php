@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div class="alert alert-info">
                        <h5><i class="fas fa-user"></i> Selamat Datang, {{ Auth::user()->name }}!</h5>
                        <p class="mb-0">Anda login sebagai peserta tes.</p>
                    </div>

                    @if(Auth::user()->has_taken_test)
                        <div class="alert alert-success">
                            <h5><i class="icon fas fa-check"></i> Tes Telah Selesai</h5>
                            <p>Terima kasih telah menyelesaikan tes Kraepelin. Hasil tes sedang dianalisis oleh tim HR.</p>
                            <a href="{{ route('test.status') }}" class="btn btn-success">Lihat Status Tes</a>
                            <a href="{{ route('test.index') }}" class="btn btn-primary">Ulangi Tes</a>
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <h5><i class="icon fas fa-exclamation-triangle"></i> Tes Belum Dilakukan</h5>
                            <p>Silakan lakukan tes Kraepelin untuk melanjutkan proses seleksi.</p>
                            <a href="{{ route('test.index') }}" class="btn btn-primary">Mulai Tes</a>
                        </div>
                    @endif

                    @if(Auth::user()->admin_notes)
                        <div class="alert alert-info mt-3">
                            <h5><i class="icon fas fa-info-circle"></i> Informasi dari HR</h5>
                            <p>{{ Auth::user()->admin_notes }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection