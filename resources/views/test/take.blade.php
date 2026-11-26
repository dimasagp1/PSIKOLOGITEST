@extends('layouts.app')

@section('title', 'Tes Kraepelin - Pengerjaan')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header bg-warning">
                    <h3 class="card-title text-white">
                        <i class="fas fa-edit"></i> Tes Kraepelin - Sedang Berlangsung
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-danger" id="total-timer">12:00</span>
                        <span class="badge badge-info ml-2" id="question-timer">15</span>
                    </div>
                </div>
                <div class="card-body text-center">
                    <!-- Progress Bar -->
                    <div class="progress mb-4" style="height: 20px;">
                        <div id="progress-bar" class="progress-bar bg-success progress-bar-striped progress-bar-animated" 
                             role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                            <span id="progress-text" class="font-weight-bold">0%</span>
                        </div>
                    </div>
                    
                    <!-- Soal Display -->
                    <div class="card bg-light mb-4">
                        <div class="card-body py-5">
                            <div class="display-4 font-weight-bold text-primary mb-3" id="question-display">
                                {{ $session->current_test_data[$session->current_position]['number1'] }} + 
                                {{ $session->current_test_data[$session->current_position]['number2'] }}
                            </div>
                            <small class="text-muted">Soal <span id="current-number">{{ $session->current_position + 1 }}</span> dari {{ count($session->current_test_data) }}</small>
                        </div>
                    </div>
                    
                    <!-- Form Jawaban -->
                    <form id="answer-form" class="mt-4">
                        @csrf
                        <div class="form-group">
                            <label for="answer-input" class="h5">Jawaban Anda:</label>
                            <input type="number" 
                                   class="form-control form-control-lg text-center mx-auto" 
                                   id="answer-input" 
                                   min="2" 
                                   max="18" 
                                   required 
                                   autofocus
                                   style="font-size: 2rem; height: 80px; width: 200px;"
                                   placeholder="?">
                            <small class="form-text text-muted mt-2">
                                Tekan <kbd>Enter</kbd> untuk submit jawaban
                            </small>
                        </div>
                        
                        <div class="btn-group mt-3" role="group">
                            <button type="submit" class="btn btn-primary btn-lg px-4" id="submit-btn">
                                <i class="fas fa-arrow-right"></i> Jawab & Lanjut
                            </button>
                            <button type="button" class="btn btn-secondary btn-lg px-4" id="skip-btn">
                                <i class="fas fa-forward"></i> Lewati
                            </button>
                            <button type="button" class="btn btn-success btn-lg px-4" id="complete-btn">
                                <i class="fas fa-check-circle"></i> Selesai
                            </button>
                        </div>
                    </form>

                    <!-- Info Penting -->
                    <div class="alert alert-info mt-4">
                        <h6><i class="fas fa-lightbulb"></i> Informasi Tes:</h6>
                        <ul class="mb-0">
                            <li>Setiap soal memiliki waktu <strong>15 detik</strong></li>
                            <li>Soal akan otomatis dilewati setelah 15 detik</li>
                            <li>Total waktu tes: <strong>12 menit</strong></li>
                            <li>Gunakan tombol "Lewati" untuk langsung ke soal berikutnya</li>
                            <li>Gunakan tombol "Selesai" untuk mengakhiri tes kapan saja</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .progress-bar {
        transition: width 0.3s ease;
    }
    #question-display {
        text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
    }
    #answer-input:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
        transform: scale(1.05);
        transition: all 0.2s ease;
    }
    #question-timer {
        font-size: 1.1em;
        padding: 8px 12px;
    }
    .btn-group .btn {
        margin: 0 2px;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
let totalTimeRemaining = {{ $session->time_remaining }};
let questionTimeRemaining = {{ $session->getRemainingTimeForCurrentQuestion() }};
let totalTimerInterval;
let questionTimerInterval;
let totalQuestions = {{ count($session->current_test_data) }};
let currentQuestion = {{ $session->current_position }};
const QUESTION_TIME_LIMIT = 15;
const TOTAL_TIME_LIMIT = 720;
let isProcessing = false;
let testCompleted = false;

function updateTotalTimer() {
    const minutes = Math.floor(totalTimeRemaining / 60);
    const seconds = totalTimeRemaining % 60;
    document.getElementById('total-timer').textContent = 
        `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    
    if (totalTimeRemaining <= 0 && !testCompleted) {
        clearAllIntervals();
        submitTimeUpdate(0);
        showTimeUpAlert();
    } else {
        totalTimeRemaining--;
    }
}

function updateQuestionTimer() {
    document.getElementById('question-timer').textContent = questionTimeRemaining;
    
    // Update color based on remaining time
    const questionTimer = document.getElementById('question-timer');
    if (questionTimeRemaining <= 5) {
        questionTimer.className = 'badge badge-danger ml-2';
    } else if (questionTimeRemaining <= 10) {
        questionTimer.className = 'badge badge-warning ml-2';
    } else {
        questionTimer.className = 'badge badge-info ml-2';
    }
    
    if (questionTimeRemaining <= 0 && !isProcessing && !testCompleted) {
        autoSkipQuestion();
    } else {
        questionTimeRemaining--;
    }
}

function clearAllIntervals() {
    if (totalTimerInterval) clearInterval(totalTimerInterval);
    if (questionTimerInterval) clearInterval(questionTimerInterval);
}

function autoSkipQuestion() {
    if (isProcessing || testCompleted) return;
    
    isProcessing = true;
    disableAllButtons();
    
    console.log('Auto-skip question:', currentQuestion + 1);
    
    fetch('{{ route("test.skip") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        console.log('Auto-skip response:', data);
        
        if (data.completed) {
            handleTestCompletion(data);
        } else if (data.success) {
            handleNextQuestion(data);
        } else {
            throw new Error(data.error || 'Terjadi kesalahan tidak diketahui');
        }
    })
    .catch(error => {
        console.error('Error auto-skip:', error);
        resetButtons();
    })
    .finally(() => {
        isProcessing = false;
    });
}

function showTimeUpAlert() {
    Swal.fire({
        icon: 'info',
        title: 'Waktu Habis',
        text: 'Waktu pengerjaan tes telah habis. Hasil tes akan disimpan.',
        confirmButtonText: 'Lihat Hasil',
        allowOutsideClick: false
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '{{ route("test.status") }}';
        }
    });
}

function updateProgress() {
    const progress = ((currentQuestion + 1) / totalQuestions) * 100;
    const progressBar = document.getElementById('progress-bar');
    const progressText = document.getElementById('progress-text');
    
    progressBar.style.width = progress + '%';
    progressBar.setAttribute('aria-valuenow', progress);
    progressText.textContent = Math.round(progress) + '%';
    
    document.getElementById('current-number').textContent = currentQuestion + 1;
}

function submitTimeUpdate(time) {
    fetch('{{ route("test.update-time") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            time_remaining: time
        })
    });
}

function submitAnswer(answer) {
    if (isProcessing || testCompleted) return;
    
    // Validasi answer
    if (answer < 2 || answer > 18) {
        Swal.fire('Peringatan', 'Jawaban harus antara 2 dan 18.', 'warning');
        document.getElementById('answer-input').focus();
        return;
    }
    
    isProcessing = true;
    disableAllButtons();
    
    console.log('Mengirim jawaban:', {
        question: currentQuestion + 1,
        answer: answer
    });
    
    fetch('{{ route("test.submit") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            answer: parseInt(answer)
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        console.log('Response dari server:', data);
        
        if (data.completed) {
            handleTestCompletion(data);
        } else if (data.success) {
            handleNextQuestion(data);
            // TIDAK tampilkan notifikasi untuk setiap jawaban
        } else {
            throw new Error(data.error || 'Terjadi kesalahan tidak diketahui');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        resetButtons();
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Terjadi kesalahan saat mengirim jawaban: ' + error.message,
            confirmButtonText: 'OK'
        });
    })
    .finally(() => {
        isProcessing = false;
    });
}

function skipQuestion() {
    if (isProcessing || testCompleted) return;
    
    isProcessing = true;
    disableAllButtons();
    
    console.log('Melewatkan soal:', currentQuestion + 1);
    
    fetch('{{ route("test.skip") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        console.log('Response skip dari server:', data);
        
        if (data.completed) {
            handleTestCompletion(data);
        } else if (data.success) {
            handleNextQuestion(data);
            // Tampilkan notifikasi hanya untuk skip manual
            Swal.fire({
                icon: 'info',
                title: 'Soal Dilewati',
                text: 'Soal telah dilewati dan dilanjutkan ke soal berikutnya.',
                timer: 1500,
                showConfirmButton: false
            });
        } else {
            throw new Error(data.error || 'Terjadi kesalahan tidak diketahui');
        }
    })
    .catch(error => {
        console.error('Error skip:', error);
        resetButtons();
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Terjadi kesalahan saat melewati soal: ' + error.message,
            confirmButtonText: 'OK'
        });
    })
    .finally(() => {
        isProcessing = false;
    });
}

function completeTest() {
    if (isProcessing || testCompleted) return;
    
    Swal.fire({
        title: 'Selesaikan Tes?',
        html: `
            <p>Apakah Anda yakin ingin menyelesaikan tes sekarang?</p>
            <p><strong>Progress: ${Math.round(((currentQuestion + 1) / totalQuestions) * 100)}% (${currentQuestion + 1} dari ${totalQuestions} soal)</strong></p>
            <p class="text-warning"><i class="fas fa-exclamation-triangle"></i> Pastikan Anda sudah memeriksa semua jawaban.</p>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Selesaikan & Simpan',
        cancelButtonText: 'Lanjutkan Tes',
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d'
    }).then((result) => {
        if (result.isConfirmed) {
            processCompleteTest();
        }
    });
}

function processCompleteTest() {
    isProcessing = true;
    testCompleted = true;
    disableAllButtons();
    
    console.log('Menyelesaikan tes secara manual...');
    
    fetch('{{ route("test.complete") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        console.log('Complete test response:', data);
        
        if (data.success) {
            handleTestCompletion(data);
        } else {
            throw new Error(data.error || 'Terjadi kesalahan tidak diketahui');
        }
    })
    .catch(error => {
        console.error('Error complete test:', error);
        testCompleted = false;
        resetButtons();
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Terjadi kesalahan saat menyelesaikan tes: ' + error.message,
            confirmButtonText: 'OK'
        });
    })
    .finally(() => {
        isProcessing = false;
    });
}

function handleTestCompletion(data) {
    testCompleted = true;
    clearAllIntervals();
    
    console.log('Tes selesai, menampilkan notifikasi...');
    console.log('Result data:', data);
    
    Swal.fire({
        icon: 'success',
        title: 'Tes Berhasil Disimpan!',
        html: `
            <p>Selamat! Anda telah menyelesaikan tes Kraepelin.</p>
            <p><strong>${data.message || 'Hasil tes telah berhasil disimpan di database.'}</strong></p>
            <div class="alert alert-success mt-3">
                <i class="fas fa-check-circle"></i> Semua jawaban telah tersimpan dengan aman.
            </div>
            <div class="text-left mt-3">
                <p><strong>Detail Hasil:</strong></p>
                <ul class="list-unstyled">
                    <li>✅ Jawaban Benar: <strong>${data.correct_answers || 0}/50</strong></li>
                    <li>❌ Jawaban Salah: <strong>${data.wrong_answers || 0}</strong></li>
                    <li>⏩ Soal Dilewati: <strong>${data.skipped_answers || 0}</strong></li>
                    <li>📊 Total Skor: <strong>${data.total_score || 0}</strong></li>
                </ul>
            </div>
        `,
        confirmButtonText: 'Lihat Status Tes',
        allowOutsideClick: false,
        allowEscapeKey: false,
        allowEnterKey: false
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '{{ route("test.status") }}';
        }
    });
}


function handleNextQuestion(data) {
    currentQuestion = data.next_position;
    questionTimeRemaining = QUESTION_TIME_LIMIT;
    updateProgress();
    
    // Reset form
    const answerInput = document.getElementById('answer-input');
    answerInput.value = '';
    answerInput.disabled = false;
    answerInput.focus();
    
    resetButtons();
    
    // Update question display dengan data REAL dari server
    if (data.next_question_data) {
        updateQuestionDisplayWithRealData(data.next_question_data);
    } else {
        // Fallback ke random jika data tidak ada
        updateQuestionDisplayWithRandom();
    }
}

function updateQuestionDisplayWithRealData(questionData) {
    const num1 = questionData.number1;
    const num2 = questionData.number2;
    document.getElementById('question-display').textContent = `${num1} + ${num2}`;
    
    // Debug log
    console.log('Updated question display with real data:', { num1, num2 });
}

function updateQuestionDisplayWithRandom() {
    // Hanya untuk fallback - seharusnya tidak terpakai
    const num1 = Math.floor(Math.random() * 9) + 1;
    const num2 = Math.floor(Math.random() * 9) + 1;
    document.getElementById('question-display').textContent = `${num1} + ${num2}`;
    
    console.warn('Using random question data - this should not happen!');
}


function disableAllButtons() {
    const submitBtn = document.getElementById('submit-btn');
    const skipBtn = document.getElementById('skip-btn');
    const completeBtn = document.getElementById('complete-btn');
    const answerInput = document.getElementById('answer-input');
    
    submitBtn.disabled = true;
    skipBtn.disabled = true;
    completeBtn.disabled = true;
    answerInput.disabled = true;
    
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
}

function resetButtons() {
    const submitBtn = document.getElementById('submit-btn');
    const skipBtn = document.getElementById('skip-btn');
    const completeBtn = document.getElementById('complete-btn');
    
    if (!testCompleted) {
        submitBtn.disabled = false;
        skipBtn.disabled = false;
        completeBtn.disabled = false;
        
        submitBtn.innerHTML = '<i class="fas fa-arrow-right"></i> Jawab & Lanjut';
        skipBtn.innerHTML = '<i class="fas fa-forward"></i> Lewati';
        completeBtn.innerHTML = '<i class="fas fa-check-circle"></i> Selesai';
    }
}

function updateQuestionDisplay() {
    // Untuk sementara, generate angka acak
    // Di production, sebaiknya ambil dari data session yang di-update
    const num1 = Math.floor(Math.random() * 9) + 1;
    const num2 = Math.floor(Math.random() * 9) + 1;
    document.getElementById('question-display').textContent = `${num1} + ${num2}`;
}

function checkAutoSkipFromServer() {
    if (isProcessing || testCompleted) return;
    
    fetch('{{ route("test.check-auto-skip") }}')
    .then(response => response.json())
    .then(data => {
        if (data.should_skip && !isProcessing && !testCompleted) {
            autoSkipQuestion();
        }
    })
    .catch(error => console.error('Error checking auto-skip:', error));
}

// Event Listeners
document.getElementById('answer-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const answer = parseInt(document.getElementById('answer-input').value);
    
    if (answer >= 2 && answer <= 18) {
        submitAnswer(answer);
    } else {
        Swal.fire('Peringatan', 'Jawaban harus antara 2 dan 18.', 'warning');
        document.getElementById('answer-input').focus();
    }
});

document.getElementById('skip-btn').addEventListener('click', function() {
    if (isProcessing || testCompleted) return;
    
    Swal.fire({
        title: 'Lewati Soal?',
        text: 'Apakah Anda yakin ingin melewati soal ini?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Lewati',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            skipQuestion();
        }
    });
});

document.getElementById('complete-btn').addEventListener('click', function() {
    completeTest();
});

document.getElementById('answer-input').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        document.getElementById('answer-form').dispatchEvent(new Event('submit'));
    }
});

// Prevent leaving page during test dengan peringatan data tidak tersimpan
window.addEventListener('beforeunload', function (e) {
    if (totalTimeRemaining > 0 && !testCompleted) {
        e.preventDefault();
        e.returnValue = 'PERINGATAN: Tes masih berlangsung. Jika Anda meninggalkan halaman sekarang, progress tes mungkin akan hilang dan jawaban tidak tersimpan!';
        
        // Tampilkan alert konfirmasi tambahan
        Swal.fire({
            icon: 'warning',
            title: 'Tes Masih Berlangsung!',
            html: `
                <p>Anda sedang dalam proses mengerjakan tes.</p>
                <p><strong class="text-danger">Jika Anda meninggalkan halaman sekarang, jawaban Anda mungkin tidak tersimpan!</strong></p>
                <p>Apakah Anda yakin ingin meninggalkan halaman?</p>
            `,
            showCancelButton: true,
            confirmButtonText: 'Ya, Tinggalkan',
            cancelButtonText: 'Lanjutkan Tes',
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#28a745'
        }).then((result) => {
            if (result.isConfirmed) {
                // Izinkan user meninggalkan halaman
                window.removeEventListener('beforeunload', arguments.callee);
                window.location.reload();
            }
        });
        
        return e.returnValue;
    }
});

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    // Start timers
    totalTimerInterval = setInterval(updateTotalTimer, 1000);
    questionTimerInterval = setInterval(updateQuestionTimer, 1000);
    
    // Update timers display
    updateTotalTimer();
    updateQuestionTimer();
    updateProgress();
    
    // Update server every 30 seconds for total time
    setInterval(() => {
        if (totalTimeRemaining > 0 && !isProcessing && !testCompleted) {
            submitTimeUpdate(totalTimeRemaining);
        }
    }, 30000);
    
    // Check for auto-skip from server every second
    setInterval(checkAutoSkipFromServer, 1000);
    
    // Focus on input
    document.getElementById('answer-input').focus();
});
</script>
@endpush