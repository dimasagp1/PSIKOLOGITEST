<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tes Kraepelin</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 100px 0;
        }
        .feature-icon {
            font-size: 3rem;
            color: #667eea;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold">Tes Kraepelin</h1>
                    <p class="lead">Sistem tes psikologi untuk mengukur kecepatan, ketelitian, dan ketahanan kerja karyawan</p>
                    <div class="mt-4">
                        @if (Route::has('login'))
                            <div class="d-flex gap-2">
                                @auth
                                    <a href="{{ url('/home') }}" class="btn btn-light btn-lg">
                                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                                    </a>
                                @else
                                    <a href="{{ route('login') }}" class="btn btn-light btn-lg">
                                        <i class="fas fa-sign-in-alt me-2"></i>Login
                                    </a>
                                    @if (Route::has('register'))
                                        <a href="{{ route('register') }}" class="btn btn-outline-light btn-lg">
                                            <i class="fas fa-user-plus me-2"></i>Daftar
                                        </a>
                                    @endif
                                @endauth
                            </div>
                        @endif
                    </div>
                </div>
                <div class="col-lg-6 text-center">
                    <i class="fas fa-brain display-1"></i>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5">
        <div class="container">
            <div class="row text-center">
                <div class="col-lg-4 mb-4">
                    <div class="feature-icon">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <h3>Kecepatan</h3>
                    <p>Ukur kecepatan processing dan respons terhadap tugas berulang</p>
                </div>
                <div class="col-lg-4 mb-4">
                    <div class="feature-icon">
                        <i class="fas fa-bullseye"></i>
                    </div>
                    <h3>Ketelitian</h3>
                    <p>Evaluasi tingkat akurasi dan ketelitian dalam bekerja</p>
                </div>
                <div class="col-lg-4 mb-4">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3>Konsistensi</h3>
                    <p>Analisis konsistensi performa dari waktu ke waktu</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container text-center">
            <p>&copy; 2024 Tes Kraepelin. All rights reserved.</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>