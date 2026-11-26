<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Tes Kraepelin</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @page {
            margin: 15px;
            size: A4;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #2c3e50;
            margin: 0;
            padding: 0;
            background-color: #f8fafc;
        }
        
        .container {
            max-width: 21cm;
            margin: 0 auto;
            background: white;
            padding: 15px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }
        
        /* Header Styles */
        .header {
            text-align: center;
            padding-bottom: 12px;
            margin-bottom: 20px;
            border-bottom: 2px solid #3498db;
        }
        
        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #2c3e50;
            font-weight: 700;
        }
        
        .header .subtitle {
            margin: 5px 0 0 0;
            font-size: 12px;
            color: #7f8c8d;
            font-weight: 500;
        }
        
        /* Card Layout */
        .card {
            background: white;
            border-radius: 6px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            margin-bottom: 15px;
            overflow: hidden;
            border: 1px solid #e9ecef;
        }
        
        .card-header {
            background-color: #3498db;
            color: white;
            padding: 8px 12px;
            font-size: 11px;
            font-weight: 600;
        }
        
        .card-body {
            padding: 12px;
        }
        
        /* Grid System */
        .grid {
            display: grid;
            gap: 12px;
        }
        
        .grid-2 {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .grid-3 {
            grid-template-columns: repeat(3, 1fr);
        }
        
        .grid-4 {
            grid-template-columns: repeat(4, 1fr);
        }
        
        /* Data Display */
        .data-item {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            border-bottom: 1px solid #f1f3f4;
        }
        
        .data-label {
            font-weight: 600;
            color: #5a6c7d;
        }
        
        .data-value {
            font-weight: 700;
            color: #2c3e50;
        }
        
        /* Metric Cards */
        .metric-card {
            text-align: center;
            padding: 10px;
            border-radius: 6px;
            background: #f8fafc;
            border: 1px solid #e9ecef;
        }
        
        .metric-value {
            font-size: 16px;
            font-weight: 700;
            margin: 5px 0;
        }
        
        .metric-label {
            font-size: 9px;
            color: #7f8c8d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Progress Bar */
        .progress-container {
            margin: 8px 0;
        }
        
        .progress-label {
            display: flex;
            justify-content: space-between;
            font-size: 9px;
            margin-bottom: 3px;
        }
        
        .progress-bar {
            height: 8px;
            background-color: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            border-radius: 4px;
        }
        
        /* Chart Container */
        .chart-container {
            height: 140px;
            margin: 10px 0;
            position: relative;
        }
        
        /* Analysis Section */
        .analysis-section {
            margin: 15px 0;
        }
        
        .section-title {
            font-size: 11px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
            padding-bottom: 5px;
            border-bottom: 1px solid #e9ecef;
        }
        
        /* Badges */
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .badge-success { background-color: #2ecc71; color: white; }
        .badge-warning { background-color: #f39c12; color: white; }
        .badge-danger { background-color: #e74c3c; color: white; }
        .badge-info { background-color: #3498db; color: white; }
        .badge-primary { background-color: #9b59b6; color: white; }
        
        /* Text Colors */
        .text-success { color: #2ecc71; }
        .text-warning { color: #f39c12; }
        .text-danger { color: #e74c3c; }
        .text-info { color: #3498db; }
        .text-primary { color: #9b59b6; }
        
        /* Signature & Footer */
        .signature-area {
            margin-top: 30px;
            text-align: right;
        }
        
        .signature-line {
            border-top: 1px solid #2c3e50;
            width: 200px;
            margin: 25px 0 5px auto;
            padding-top: 5px;
            text-align: center;
            font-size: 9px;
        }
        
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 8px;
            color: #7f8c8d;
            border-top: 1px solid #e9ecef;
            padding-top: 8px;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        /* Responsive Adjustments */
        @media print {
            body {
                background-color: white;
            }
            
            .container {
                box-shadow: none;
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>LAPORAN HASIL TES KRAEPELIN</h1>
            <div class="subtitle">PSIKOTEST KECEPATAN KERJA DAN KETELITIAN</div>
        </div>
        
        <!-- Informasi Peserta -->
        <div class="card">
            <div class="card-header">
                INFORMASI PESERTA
            </div>
            <div class="card-body">
                <div class="grid grid-2">
                    <div class="data-item">
                        <span class="data-label">Nama</span>
                        <span class="data-value">Ahmad Fauzi</span>
                    </div>
                    <div class="data-item">
                        <span class="data-label">Usia</span>
                        <span class="data-value">24 Tahun</span>
                    </div>
                    <div class="data-item">
                        <span class="data-label">Jenis Kelamin</span>
                        <span class="data-value">Laki-laki</span>
                    </div>
                    <div class="data-item">
                        <span class="data-label">Pendidikan</span>
                        <span class="data-value">S1 Teknik Informatika</span>
                    </div>
                    <div class="data-item">
                        <span class="data-label">Tanggal Tes</span>
                        <span class="data-value">15 Oktober 2023</span>
                    </div>
                    <div class="data-item">
                        <span class="data-label">Posisi</span>
                        <span class="data-value">Software Developer</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Ringkasan Hasil -->
        <div class="card">
            <div class="card-header">
                RINGKASAN HASIL
            </div>
            <div class="card-body">
                <div class="grid grid-4">
                    <div class="metric-card">
                        <div class="metric-label">Total Jawaban</div>
                        <div class="metric-value">485</div>
                        <div class="badge badge-info">Tinggi</div>
                    </div>
                    <div class="metric-card">
                        <div class="metric-label">Rata-rata Per Kolom</div>
                        <div class="metric-value">48.5</div>
                        <div class="badge badge-success">Optimal</div>
                    </div>
                    <div class="metric-card">
                        <div class="metric-label">Kesalahan</div>
                        <div class="metric-value">12</div>
                        <div class="badge badge-warning">Sedang</div>
                    </div>
                    <div class="metric-card">
                        <div class="metric-label">Konsistensi</div>
                        <div class="metric-value">85%</div>
                        <div class="badge badge-success">Baik</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Grafik Performa -->
        <div class="card">
            <div class="card-header">
                GRAFIK PERFORMA KERJA
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="performanceChart"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Analisis Faktor -->
        <div class="grid grid-2">
            <div class="card">
                <div class="card-header">
                    ANALISIS KECEPATAN
                </div>
                <div class="card-body">
                    <div class="progress-container">
                        <div class="progress-label">
                            <span>Kecepatan Awal</span>
                            <span>75%</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: 75%; background-color: #3498db;"></div>
                        </div>
                    </div>
                    
                    <div class="progress-container">
                        <div class="progress-label">
                            <span>Kecepatan Tengah</span>
                            <span>82%</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: 82%; background-color: #2ecc71;"></div>
                        </div>
                    </div>
                    
                    <div class="progress-container">
                        <div class="progress-label">
                            <span>Kecepatan Akhir</span>
                            <span>68%</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: 68%; background-color: #f39c12;"></div>
                        </div>
                    </div>
                    
                    <div class="data-item">
                        <span class="data-label">Kecenderungan</span>
                        <span class="data-value text-warning">Meningkat kemudian menurun</span>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    ANALISIS KETELITIAN
                </div>
                <div class="card-body">
                    <div class="progress-container">
                        <div class="progress-label">
                            <span>Akurasi Awal</span>
                            <span>88%</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: 88%; background-color: #3498db;"></div>
                        </div>
                    </div>
                    
                    <div class="progress-container">
                        <div class="progress-label">
                            <span>Akurasi Tengah</span>
                            <span>92%</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: 92%; background-color: #2ecc71;"></div>
                        </div>
                    </div>
                    
                    <div class="progress-container">
                        <div class="progress-label">
                            <span>Akurasi Akhir</span>
                            <span>85%</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: 85%; background-color: #f39c12;"></div>
                        </div>
                    </div>
                    
                    <div class="data-item">
                        <span class="data-label">Kecenderungan</span>
                        <span class="data-value text-success">Stabil dengan penurunan ringan</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Interpretasi Hasil -->
        <div class="card">
            <div class="card-header">
                INTERPRETASI HASIL
            </div>
            <div class="card-body">
                <div class="analysis-section">
                    <div class="section-title">Profil Kecepatan Kerja</div>
                    <p style="margin: 0; font-size: 10px; line-height: 1.5;">
                        Peserta menunjukkan kecepatan kerja yang baik dengan rata-rata 48.5 jawaban per kolom. 
                        Terlihat peningkatan kecepatan di bagian tengah tes, namun mengalami penurunan di bagian akhir, 
                        mengindikasikan kemungkinan kelelahan atau penurunan fokus setelah bekerja dalam waktu yang lama.
                    </p>
                </div>
                
                <div class="analysis-section">
                    <div class="section-title">Profil Ketelitian</div>
                    <p style="margin: 0; font-size: 10px; line-height: 1.5;">
                        Tingkat ketelitian peserta berada dalam kategori baik dengan akurasi rata-rata 88%. 
                        Pola ketelitian yang stabil menunjukkan kemampuan menjaga konsentrasi yang baik, 
                        meskipun terdapat sedikit penurunan di akhir sesi tes.
                    </p>
                </div>
                
                <div class="analysis-section">
                    <div class="section-title">Konsistensi Kerja</div>
                    <p style="margin: 0; font-size: 10px; line-height: 1.5;">
                        Dengan tingkat konsistensi 85%, peserta mampu mempertahankan performa kerja yang stabil 
                        sepanjang durasi tes. Hal ini mengindikasikan ketahanan dan stabilitas emosi yang baik 
                        dalam menghadapi tugas berulang dan monoton.
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Rekomendasi -->
        <div class="card">
            <div class="card-header">
                REKOMENDASI
            </div>
            <div class="card-body">
                <div style="font-size: 10px; line-height: 1.5;">
                    <p style="margin: 0 0 8px 0;">
                        <strong>Kekuatan:</strong> Kecepatan pemrosesan informasi yang baik, ketelitian tinggi, 
                        dan kemampuan mempertahankan konsentrasi dalam tugas berulang.
                    </p>
                    <p style="margin: 0 0 8px 0;">
                        <strong>Area Pengembangan:</strong> Ketahanan dalam menjaga kecepatan kerja hingga akhir sesi, 
                        serta strategi mengelola energi untuk mencegah penurunan performa.
                    </p>
                    <p style="margin: 0;">
                        <strong>Rekomendasi Posisi:</strong> Cocok untuk posisi yang membutuhkan kecepatan dan ketelitian 
                        seperti analisis data, quality control, atau administrasi dengan volume pekerjaan tinggi.
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Tanda Tangan -->
        <div class="signature-area">
            <div>Jakarta, 15 Oktober 2023</div>
            <div class="signature-line">
                Psikolog Penilai
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            Laporan ini dibuat secara otomatis oleh Sistem Tes Kraepelin<br>
            Dokumen ini bersifat rahasia dan hanya untuk keperluan assessment
        </div>
    </div>

    <script>
        // Sample chart data
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('performanceChart').getContext('2d');
            const performanceChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10'],
                    datasets: [{
                        label: 'Jawaban Per Kolom',
                        data: [45, 48, 52, 50, 49, 51, 48, 47, 46, 45],
                        borderColor: '#3498db',
                        backgroundColor: 'rgba(52, 152, 219, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: false,
                            min: 40,
                            max: 55,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>