<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Laporan Tes Kraepelin')</title>
    <style>
        /* Reset Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #333;
            background: #fff;
        }
        
        /* Header Styles */
        .header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #3498db;
        }
        
        .header h1 {
            font-size: 16px;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .header .subtitle {
            font-size: 12px;
            color: #7f8c8d;
        }
        
        /* Main Content */
        .container {
            padding: 15px;
        }
        
        /* Footer Styles */
        .footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #bdc3c7;
            font-size: 8px;
            color: #7f8c8d;
        }
        
        /* Page Break */
        .page-break {
            page-break-before: always;
        }
        
        /* Utility Classes */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        
        .mt-10 { margin-top: 10px; }
        .mb-10 { margin-bottom: 10px; }
        .p-10 { padding: 10px; }
    </style>
    @stack('styles')
</head>
<body>
    <div class="header">
        <h1>LAPORAN TES KRAEPELIN</h1>
        <div class="subtitle">Psikotes Kemampuan Kerja & Ketahanan Mental</div>
    </div>
    
    <div class="container">
        @yield('content')
    </div>
    
    <div class="footer">
        <p>Dokumen ini dibuat secara otomatis pada {{ date('d F Y H:i') }} • Halaman <span class="page-number"></span></p>
    </div>

    @stack('scripts')
</body>
</html>