@extends('layouts.app')

@section('title', 'Hasil Tes - ' . $test->user->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
               <div class="card-header bg-primary">
    <h3 class="card-title text-white">
        <i class="fas fa-chart-bar"></i> Hasil Tes Kraepelin - {{ $test->user->name }}
    </h3>
    <div class="card-tools">
        <button type="button" onclick="window.location.href='{{ route('admin.results.download', $test) }}'" class="btn btn-light btn-sm">
            <i class="fas fa-download"></i> Download PDF
        </button>
    </div>
</div>
                <div class="card-body">
                    <!-- Info User -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="info-box bg-light">
                                <span class="info-box-icon bg-info"><i class="fas fa-user"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Nama Peserta</span>
                                    <span class="info-box-number">{{ $test->user->name }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box bg-light">
                                <span class="info-box-icon bg-success"><i class="fas fa-envelope"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Email</span>
                                    <span class="info-box-number">{{ $test->user->email }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- INCLUDE ANALISIS DI SINI -->
                    @include('admin.partials.test-analysis', ['test' => $test])

                    <!-- Catatan Admin -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Catatan untuk Peserta</h4>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('admin.update-notes', $test->user) }}" method="POST">
                                        @csrf
                                        <div class="form-group">
                                            <textarea name="admin_notes" class="form-control" rows="4" 
                                                      placeholder="Berikan catatan atau feedback untuk peserta...">{{ $test->user->admin_notes }}</textarea>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Simpan Catatan</button>
                                    </form>
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

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
function downloadPDF() {
    const element = document.querySelector('.card');
    const options = {
        margin: 1,
        filename: 'hasil_tes_{{ $test->user->name }}_{{ date("Y-m-d") }}.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2 },
        jsPDF: { unit: 'cm', format: 'a4', orientation: 'portrait' }
    };
    
    html2pdf().set(options).from(element).save();
}
</script>
@endpush