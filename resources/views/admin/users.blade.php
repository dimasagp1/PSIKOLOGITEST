@extends('layouts.app')

@section('title', 'Manajemen User')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">Daftar User</h3>
            <div class="card-tools">
                <a href="{{ url('/admin/users/create') }}" class="btn btn-success btn-sm">
                    <i class="fas fa-user-plus mr-1"></i> Tambah User Baru
                </a>
            </div>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <!-- Filter dan Pencarian -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <input type="text" class="form-control" placeholder="Cari user..." id="searchInput">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-control form-control-sm" id="roleFilter">
                        <option value="">Semua Role</option>
                        <option value="admin">Admin</option>
                        <option value="hr">HR</option>
                        <option value="user">User</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-control form-control-sm" id="testStatusFilter">
                        <option value="">Semua Status Tes</option>
                        <option value="completed">Selesai Tes</option>
                        <option value="not_completed">Belum Tes</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-secondary btn-sm btn-block" id="resetFilters">
                        <i class="fas fa-redo"></i> Reset
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover" id="usersTable">
                    <thead class="thead-light">
                        <tr>
                            <th width="60">ID</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th width="100">Role</th>
                            <th width="120">Status Tes</th>
                            <th width="150">Tanggal Daftar</th>
                            <th width="120">Catatan Admin</th>
                            <th width="200" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr data-role="{{ $user->role }}" data-test-status="{{ $user->has_taken_test ? 'completed' : 'not_completed' }}">
                            <td class="text-center">{{ $user->id }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="user-avatar bg-primary rounded-circle d-flex align-items-center justify-content-center mr-2" 
                                         style="width: 32px; height: 32px; font-size: 12px; color: white;">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </div>
                                    <span class="font-weight-medium">{{ $user->name }}</span>
                                </div>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td class="text-center">
                                <span class="badge badge-pill badge-{{ $user->role == 'admin' ? 'danger' : ($user->role == 'hr' ? 'warning' : 'primary') }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($user->has_taken_test)
                                    <span class="badge badge-pill badge-success">
                                        <i class="fas fa-check mr-1"></i>Selesai
                                    </span>
                                @else
                                    <span class="badge badge-pill badge-warning">
                                        <i class="fas fa-clock mr-1"></i>Belum
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                <small class="text-muted">{{ $user->created_at->format('d/m/Y') }}</small>
                                <br>
                                <small class="text-muted">{{ $user->created_at->format('H:i') }}</small>
                            </td>
                            <td class="text-center">
                                @if($user->admin_notes)
                                    <button class="btn btn-outline-info btn-sm" data-toggle="tooltip" data-placement="top" 
                                            title="{{ Str::limit($user->admin_notes, 100) }}" data-toggle="modal" 
                                            data-target="#notesModal{{ $user->id }}">
                                        <i class="fas fa-sticky-note mr-1"></i>Lihat
                                    </button>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1 justify-content-center">
                                    <!-- Tombol Catatan -->
                                    <button class="btn btn-info btn-sm action-btn" data-toggle="modal" 
                                            data-target="#notesModal{{ $user->id }}" 
                                            data-toggle="tooltip" title="Edit Catatan">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <!-- Tombol Edit User -->
                                    <a href="{{ url('/admin/users/' . $user->id . '/edit') }}" class="btn btn-warning btn-sm action-btn" 
                                       data-toggle="tooltip" title="Edit User">
                                        <i class="fas fa-user-edit"></i>
                                    </a>

                                    <!-- Tombol Ganti Password -->
                                    <button class="btn btn-dark btn-sm action-btn" data-toggle="modal" 
                                            data-target="#passwordModal{{ $user->id }}" 
                                            data-toggle="tooltip" title="Ganti Password">
                                        <i class="fas fa-key"></i>
                                    </button>

                                    <!-- Tombol Reset Tes -->
                                    @if($user->has_taken_test)
                                    <form action="{{ url('/admin/users/' . $user->id . '/reset-test') }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-secondary btn-sm action-btn" 
                                                data-toggle="tooltip" title="Reset Tes" 
                                                onclick="return confirm('Reset data tes user ini? Tindakan ini tidak dapat dibatalkan.')">
                                            <i class="fas fa-redo"></i>
                                        </button>
                                    </form>
                                    @else
                                    <button class="btn btn-outline-secondary btn-sm action-btn" disabled 
                                            data-toggle="tooltip" title="User belum mengerjakan tes">
                                        <i class="fas fa-redo"></i>
                                    </button>
                                    @endif

                                    <!-- Tombol Hapus -->
                                    <form action="{{ url('/admin/users/' . $user->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm action-btn" 
                                                data-toggle="tooltip" title="Hapus User" 
                                                onclick="return confirm('Hapus user {{ $user->name }}? Semua data tes dan riwayat akan dihapus permanen.')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        <!-- Notes Modal -->
                        <div class="modal fade" id="notesModal{{ $user->id }}">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header bg-info text-white">
                                        <h5 class="modal-title">
                                            <i class="fas fa-sticky-note mr-2"></i>Catatan untuk {{ $user->name }}
                                        </h5>
                                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                                    </div>
                                    <form action="{{ url('/admin/users/' . $user->id . '/update-notes') }}" method="POST">
                                        @csrf
                                        <div class="modal-body">
                                            <div class="form-group">
                                                <label class="font-weight-bold">Catatan Admin/HR:</label>
                                                <textarea name="admin_notes" class="form-control" rows="6" 
                                                          placeholder="Berikan catatan atau informasi untuk user...">{{ $user->admin_notes }}</textarea>
                                                <small class="form-text text-muted">
                                                    <i class="fas fa-info-circle mr-1"></i>Catatan ini akan visible oleh user di dashboard mereka.
                                                </small>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                                <i class="fas fa-times mr-1"></i>Tutup
                                            </button>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save mr-1"></i>Simpan Catatan
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Password Modal -->
                        <div class="modal fade" id="passwordModal{{ $user->id }}">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header bg-dark text-white">
                                        <h5 class="modal-title">
                                            <i class="fas fa-key mr-2"></i>Ganti Password - {{ $user->name }}
                                        </h5>
                                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                                    </div>
                                    <form action="{{ url('/admin/users/' . $user->id . '/change-password') }}" method="POST">
                                        @csrf
                                        <div class="modal-body">
                                            <div class="form-group">
                                                <label for="new_password{{ $user->id }}" class="font-weight-bold">Password Baru *</label>
                                                <div class="input-group">
                                                    <input type="password" class="form-control @error('new_password') is-invalid @enderror" 
                                                           id="new_password{{ $user->id }}" name="new_password" required>
                                                    <div class="input-group-append">
                                                        <button type="button" class="btn btn-outline-secondary toggle-password" 
                                                                data-target="#new_password{{ $user->id }}">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                @error('new_password')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            <div class="form-group">
                                                <label for="new_password_confirmation{{ $user->id }}" class="font-weight-bold">Konfirmasi Password Baru *</label>
                                                <div class="input-group">
                                                    <input type="password" class="form-control" 
                                                           id="new_password_confirmation{{ $user->id }}" name="new_password_confirmation" required>
                                                    <div class="input-group-append">
                                                        <button type="button" class="btn btn-outline-secondary toggle-password" 
                                                                data-target="#new_password_confirmation{{ $user->id }}">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="alert alert-info">
                                                <small>
                                                    <i class="fas fa-info-circle mr-1"></i> 
                                                    Password harus minimal 8 karakter dan mengandung kombinasi huruf dan angka.
                                                </small>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                                <i class="fas fa-times mr-1"></i>Batal
                                            </button>
                                            <button type="submit" class="btn btn-warning">
                                                <i class="fas fa-key mr-1"></i>Ganti Password
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination Minimalis -->
            @if($users->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-4">
                <!-- Informasi Data -->
                <div class="text-muted small">
                    <i class="fas fa-info-circle mr-1"></i>
                    Menampilkan <strong>{{ $users->firstItem() ?? 0 }}-{{ $users->lastItem() ?? 0 }}</strong> 
                    dari <strong>{{ $users->total() }}</strong> user
                </div>
                
                <!-- Pagination Links -->
                <nav aria-label="Page navigation">
                    <ul class="pagination pagination-sm mb-0">
                        {{-- Previous Page Link --}}
                        @if ($users->onFirstPage())
                            <li class="page-item disabled">
                                <span class="page-link border-0">
                                    <i class="fas fa-chevron-left fa-xs"></i>
                                </span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link border-0 text-dark" href="{{ $users->previousPageUrl() }}" rel="prev">
                                    <i class="fas fa-chevron-left fa-xs"></i>
                                </a>
                            </li>
                        @endif

                        {{-- Pagination Elements --}}
                        @php
                            $current = $users->currentPage();
                            $last = $users->lastPage();
                            
                            // Tampilkan maksimal 5 halaman
                            $start = max(1, $current - 1);
                            $end = min($last, $current + 1);
                            
                            // Jika di awal, tampilkan lebih banyak di kanan
                            if ($current <= 2) {
                                $end = min($last, 3);
                            }
                            
                            // Jika di akhir, tampilkan lebih banyak di kiri
                            if ($current >= $last - 1) {
                                $start = max(1, $last - 2);
                            }
                        @endphp

                        {{-- First Page Link --}}
                        @if ($start > 1)
                            <li class="page-item">
                                <a class="page-link border-0 text-dark" href="{{ $users->url(1) }}">1</a>
                            </li>
                            @if ($start > 2)
                                <li class="page-item disabled">
                                    <span class="page-link border-0">...</span>
                                </li>
                            @endif
                        @endif

                        {{-- Array Of Links --}}
                        @for ($i = $start; $i <= $end; $i++)
                            <li class="page-item {{ $i == $current ? 'active' : '' }}">
                                <a class="page-link border-0 {{ $i == $current ? 'bg-primary text-white' : 'text-dark' }}" 
                                   href="{{ $users->url($i) }}">
                                    {{ $i }}
                                </a>
                            </li>
                        @endfor

                        {{-- Last Page Link --}}
                        @if ($end < $last)
                            @if ($end < $last - 1)
                                <li class="page-item disabled">
                                    <span class="page-link border-0">...</span>
                                </li>
                            @endif
                            <li class="page-item">
                                <a class="page-link border-0 text-dark" href="{{ $users->url($last) }}">{{ $last }}</a>
                            </li>
                        @endif

                        {{-- Next Page Link --}}
                        @if ($users->hasMorePages())
                            <li class="page-item">
                                <a class="page-link border-0 text-dark" href="{{ $users->nextPageUrl() }}" rel="next">
                                    <i class="fas fa-chevron-right fa-xs"></i>
                                </a>
                            </li>
                        @else
                            <li class="page-item disabled">
                                <span class="page-link border-0">
                                    <i class="fas fa-chevron-right fa-xs"></i>
                                </span>
                            </li>
                        @endif
                    </ul>
                </nav>
            </div>
            @else
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div class="text-muted small">
                    <i class="fas fa-info-circle mr-1"></i>
                    Menampilkan semua <strong>{{ $users->count() }}</strong> user
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<style>
.action-btn {
    width: 36px;
    height: 36px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    transition: all 0.3s ease;
}

.action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}

.user-avatar {
    font-weight: 600;
}

.table th {
    border-top: none;
    font-weight: 600;
    font-size: 0.875rem;
}

.gap-1 > * {
    margin: 2px;
}

/* Pagination Minimalis */
.pagination {
    margin: 0;
}

.page-link {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
    border-radius: 4px;
    margin: 0 2px;
    min-width: 32px;
    text-align: center;
    background: transparent;
}

.page-link:hover {
    background-color: #f8f9fa;
    color: #0056b3;
}

.page-item.active .page-link {
    background-color: #007bff;
    border-color: #007bff;
    color: white;
}

.page-item.disabled .page-link {
    color: #6c757d;
    background: transparent;
}

/* Responsive */
@media (max-width: 768px) {
    .action-btn {
        width: 32px;
        height: 32px;
        font-size: 0.75rem;
    }
    
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .d-flex.justify-content-between.align-items-center {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
    
    .pagination {
        justify-content: center;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Toggle password visibility
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const target = document.querySelector(this.getAttribute('data-target'));
            const type = target.getAttribute('type') === 'password' ? 'text' : 'password';
            target.setAttribute('type', type);
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });
    });
    
    // Simple search functionality
    const searchInput = document.getElementById('searchInput');
    const roleFilter = document.getElementById('roleFilter');
    const testStatusFilter = document.getElementById('testStatusFilter');
    const usersTable = document.getElementById('usersTable');
    const resetFilters = document.getElementById('resetFilters');
    
    function filterUsers() {
        const searchTerm = searchInput.value.toLowerCase();
        const roleValue = roleFilter.value;
        const testStatusValue = testStatusFilter.value;
        
        document.querySelectorAll('#usersTable tbody tr').forEach(row => {
            const name = row.cells[1].textContent.toLowerCase();
            const email = row.cells[2].textContent.toLowerCase();
            const role = row.getAttribute('data-role');
            const testStatus = row.getAttribute('data-test-status');
            
            const matchesSearch = name.includes(searchTerm) || email.includes(searchTerm);
            const matchesRole = !roleValue || role === roleValue;
            const matchesTestStatus = !testStatusValue || testStatus === testStatusValue;
            
            row.style.display = (matchesSearch && matchesRole && matchesTestStatus) ? '' : 'none';
        });
    }
    
    searchInput.addEventListener('input', filterUsers);
    roleFilter.addEventListener('change', filterUsers);
    testStatusFilter.addEventListener('change', filterUsers);
    
    resetFilters.addEventListener('click', function() {
        searchInput.value = '';
        roleFilter.value = '';
        testStatusFilter.value = '';
        filterUsers();
    });
});
</script>
@endsection