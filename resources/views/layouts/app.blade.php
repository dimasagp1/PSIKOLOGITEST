<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - Tes Kraepelin</title>
    
    <!-- AdminLTE -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        /* Fixed Layout Styles */
        .wrapper {
            height: 100vh;
            overflow: hidden;
        }
        
        /* Navbar Fixed */
        .main-header {
            position: fixed;
            top: 0;
            right: 0;
            left: 0;
            z-index: 1030;
        }
        
        /* Sidebar Fixed */
        .main-sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 1020;
            height: 100vh;
            overflow-y: auto;
        }
        
        /* Content Wrapper Adjustment */
        .content-wrapper {
            margin-top: 57px; /* Height of navbar */
            margin-left: 250px; /* Width of sidebar */
            height: calc(100vh - 57px);
            overflow-y: auto;
            transition: margin-left 0.3s ease;
        }
        
        /* Footer Adjustment */
        .main-footer {
            margin-left: 250px;
        }
        
        /* When sidebar is collapsed */
        .sidebar-collapse .content-wrapper,
        .sidebar-collapse .main-footer {
            margin-left: 0;
        }
        
        /* Responsive for mobile */
        @media (max-width: 767.98px) {
            .content-wrapper {
                margin-left: 0;
            }
            
            .main-sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar-open .main-sidebar {
                transform: translateX(0);
            }
        }
        
        /* Ensure content has proper padding */
        .content {
            padding: 20px;
        }
    </style>
    
    @stack('styles')
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">

    @auth
    <!-- Navbar - Fixed -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
        </ul>

        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#">
                    <i class="far fa-user"></i>
                    {{ Auth::user()->name }}
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="dropdown-item">
                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </div>
            </li>
        </ul>
    </nav>

    <!-- Sidebar - Fixed -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <!-- Brand Logo -->
        <a href="{{ route('home') }}" class="brand-link">
            <span class="brand-text font-weight-light">Tes Kraepelin</span>
        </a>

        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Sidebar Menu -->
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                    @if(Auth::user()->isAdmin() || Auth::user()->isHR())
                        <li class="nav-item">
                            <a href="{{ route('admin.dashboard') }}" class="nav-link">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.users') }}" class="nav-link">
                                <i class="nav-icon fas fa-users"></i>
                                <p>Manajemen User</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.test-results') }}" class="nav-link">
                                <i class="nav-icon fas fa-chart-bar"></i>
                                <p>Hasil Tes</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.analytics') }}" class="nav-link">
                                <i class="nav-icon fas fa-chart-line"></i>
                                <p>Analitik</p>
                            </a>
                        </li>
                    @else
                        <li class="nav-item">
                            <a href="{{ route('home') }}" class="nav-link">
                                <i class="nav-icon fas fa-home"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('test.index') }}" class="nav-link">
                                <i class="nav-icon fas fa-edit"></i>
                                <p>Tes Kraepelin</p>
                            </a>
                        </li>
                        @if(Auth::user()->has_taken_test)
                        <li class="nav-item">
                            <a href="{{ route('test.status') }}" class="nav-link">
                                <i class="nav-icon fas fa-info-circle"></i>
                                <p>Status Tes</p>
                            </a>
                        </li>
                        @endif
                    @endif
                </ul>
            </nav>
        </div>
    </aside>
    @endauth

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                @yield('content')
            </div>
        </section>
    </div>

</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

@stack('scripts')
</body>
</html>