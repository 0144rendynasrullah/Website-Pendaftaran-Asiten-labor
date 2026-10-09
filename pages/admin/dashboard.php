<?php
// pages/admin/dashboard.php
require_once __DIR__ . '/../../config/database.php';

if (!isLoggedIn() || getUserRole() !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// ===== GET STATISTICS =====
try {
    $total = $pdo->query("SELECT COUNT(*) FROM pendaftaran")->fetchColumn();
    $pending = $pdo->query("SELECT COUNT(*) FROM pendaftaran WHERE status_berkas = 'pending'")->fetchColumn();
    $verified = $pdo->query("SELECT COUNT(*) FROM pendaftaran WHERE status_berkas = 'diverifikasi'")->fetchColumn();
    $rejected = $pdo->query("SELECT COUNT(*) FROM pendaftaran WHERE status_berkas = 'ditolak'")->fetchColumn();
    $lulus = $pdo->query("SELECT COUNT(*) FROM pendaftaran WHERE status_seleksi = 'lulus'")->fetchColumn();
    $tidak_lulus = $pdo->query("SELECT COUNT(*) FROM pendaftaran WHERE status_seleksi = 'tidak_lulus'")->fetchColumn();
    
    // ===== GET RECENT PENDAFTARAN =====
    $recent = $pdo->query("
        SELECT p.*, u.full_name as user_name 
        FROM pendaftaran p
        JOIN users u ON p.user_id = u.id
        ORDER BY p.created_at DESC 
        LIMIT 10
    ")->fetchAll();
    
} catch (Exception $e) {
    $total = $pending = $verified = $rejected = $lulus = $tidak_lulus = 0;
    $recent = [];
}

$base_path = '../../';
include __DIR__ . '/../../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-primary-dark"><i class="bi bi-speedometer2 me-2"></i>Dashboard Admin</h2>
            <p class="text-muted">Sistem Informasi Pendaftaran Asisten Laboratorium</p>
        </div>
        <div>
            <!-- ===== JAM + TANGGAL ===== -->
            <span class="badge bg-primary p-2">
                <i class="bi bi-clock me-1"></i> 
                <span id="adminClock">--:--:-- WIB</span>
                <span class="mx-1">|</span>
                <i class="bi bi-calendar3 me-1"></i>
                <span id="adminDate">--</span>
            </span>
            <span class="badge bg-success p-2 ms-2">
                <i class="bi bi-person-circle me-1"></i> <?= htmlspecialchars(getUserName()) ?>
            </span>
        </div>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($_GET['msg']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- ===== STATISTICS CARDS ===== -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Total Pendaftar</p>
                        <h3 class="stat-number"><?= $total ?></h3>
                    </div>
                    <div class="stat-icon bg-primary">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="border-left-color: #ffc107;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Pending Verifikasi</p>
                        <h3 class="stat-number text-warning"><?= $pending ?></h3>
                    </div>
                    <div class="stat-icon bg-warning">
                        <i class="bi bi-clock-history"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="border-left-color: #28a745;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Lulus Seleksi</p>
                        <h3 class="stat-number text-success"><?= $lulus ?></h3>
                    </div>
                    <div class="stat-icon bg-success">
                        <i class="bi bi-trophy"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="border-left-color: #dc3545;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Tidak Lulus</p>
                        <h3 class="stat-number text-danger"><?= $tidak_lulus ?></h3>
                    </div>
                    <div class="stat-icon bg-danger">
                        <i class="bi bi-x-lg"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== QUICK ACTIONS ===== -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card card-instansi h-100">
                <div class="card-body text-center">
                    <i class="bi bi-folder-check display-4 text-primary"></i>
                    <h6 class="mt-2">Verifikasi Berkas</h6>
                    <p class="text-muted small">Periksa berkas pendaftar</p>
                    <a href="verifikasi.php" class="btn btn-sm btn-instansi">
                        <i class="bi bi-arrow-right"></i> Kelola
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-instansi h-100">
                <div class="card-body text-center">
                    <i class="bi bi-calendar-event display-4 text-success"></i>
                    <h6 class="mt-2">Jadwal Tes</h6>
                    <p class="text-muted small">Atur jadwal tes</p>
                    <a href="jadwal.php" class="btn btn-sm btn-instansi">
                        <i class="bi bi-arrow-right"></i> Kelola
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-instansi h-100">
                <div class="card-body text-center">
                    <i class="bi bi-pencil-square display-4 text-warning"></i>
                    <h6 class="mt-2">Input Nilai</h6>
                    <p class="text-muted small">Masukkan nilai tes</p>
                    <a href="input_nilai.php" class="btn btn-sm btn-instansi">
                        <i class="bi bi-arrow-right"></i> Kelola
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-instansi h-100">
                <div class="card-body text-center">
                    <i class="bi bi-file-text display-4 text-danger"></i>
                    <h6 class="mt-2">Laporan</h6>
                    <p class="text-muted small">Lihat rekap data</p>
                    <a href="laporan.php" class="btn btn-sm btn-instansi">
                        <i class="bi bi-arrow-right"></i> Lihat
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== PENDAFTARAN TERBARU ===== -->
    <div class="card card-instansi">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-clock-history me-2"></i>
                Pendaftaran Terbaru
                <span class="badge bg-primary ms-2"><?= count($recent) ?></span>
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>No Pendaftaran</th>
                            <th>Nama</th>
                            <th>NIM</th>
                            <th>Status Berkas</th>
                            <th>Status Seleksi</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recent)): ?>
                            <?php foreach ($recent as $p): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($p['no_pendaftaran']) ?></strong></td>
                                    <td><?= htmlspecialchars($p['nama_lengkap']) ?></td>
                                    <td><?= htmlspecialchars($p['nim']) ?></td>
                                    <td>
                                        <span class="badge badge-status <?= $p['status_berkas'] ?>">
                                            <?= ucfirst($p['status_berkas']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-status <?= $p['status_seleksi'] ?>">
                                            <?= ucfirst(str_replace('_', ' ', $p['status_seleksi'])) ?>
                                        </span>
                                    </td>
                                    <td><?= date('d/m/Y H:i', strtotime($p['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                    Belum ada pendaftaran
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.text-primary-dark { color: #0a2e5c; }

.stat-card {
    background: #fff;
    border-radius: 15px;
    padding: 1.5rem;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    transition: all 0.3s;
    border-left: 5px solid #1a4a7a;
}
.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}
.stat-card .stat-icon {
    width: 55px;
    height: 55px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: #fff;
}
.stat-card .stat-number {
    font-size: 2rem;
    font-weight: 700;
    color: #0a2e5c;
}
.stat-card .stat-icon.bg-primary { background: #1a4a7a; }
.stat-card .stat-icon.bg-warning { background: #ffc107; }
.stat-card .stat-icon.bg-success { background: #28a745; }
.stat-card .stat-icon.bg-danger { background: #dc3545; }

.card-instansi {
    border: none;
    border-radius: 15px;
    box-shadow: 0 10px 40px rgba(10, 46, 92, 0.1);
    overflow: hidden;
}
.card-instansi .card-header {
    background: linear-gradient(135deg, #0a2e5c, #1a4a7a);
    color: #fff;
    border-bottom: 3px solid #c9a84c;
    padding: 1.2rem 1.5rem;
}
.btn-instansi {
    background: linear-gradient(135deg, #1a4a7a, #2a6aaa);
    border: none;
    color: #fff;
    padding: 0.4rem 1.2rem;
    border-radius: 8px;
    transition: all 0.3s;
}
.btn-instansi:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(26, 74, 122, 0.3);
    color: #fff;
}
.badge-status {
    padding: 0.4rem 0.8rem;
    border-radius: 50px;
    font-weight: 600;
    font-size: 0.75rem;
}
.badge-status.pending { background: #ffc107; color: #000; }
.badge-status.diverifikasi { background: #17a2b8; color: #fff; }
.badge-status.lulus_administrasi { background: #17a2b8; color: #fff; }
.badge-status.tes_program { background: #6f42c1; color: #fff; }
.badge-status.wawancara { background: #fd7e14; color: #fff; }
.badge-status.lulus { background: #28a745; color: #fff; }
.badge-status.tidak_lulus { background: #dc3545; color: #fff; }
.badge-status.ditolak { background: #dc3545; color: #fff; }
</style>

<!-- ===== SCRIPT JAM + TANGGAL REAL-TIME WIB ===== -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    function updateAdminClock() {
        var now = new Date();
        // Set ke WIB (UTC+7)
        var wib = new Date(now.getTime() + (7 * 60 * 60 * 1000));
        
        // Format Jam
        var hours = String(wib.getHours()).padStart(2, '0');
        var minutes = String(wib.getMinutes()).padStart(2, '0');
        var seconds = String(wib.getSeconds()).padStart(2, '0');
        
        // Format Tanggal
        var days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        var months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        var dayName = days[wib.getDay()];
        var day = String(wib.getDate()).padStart(2, '0');
        var month = months[wib.getMonth()];
        var year = wib.getFullYear();
        var dateStr = dayName + ', ' + day + ' ' + month + ' ' + year;
        
        // Update elemen
        var clockDisplay = document.getElementById('adminClock');
        var dateDisplay = document.getElementById('adminDate');
        
        if (clockDisplay) {
            clockDisplay.textContent = hours + ':' + minutes + ':' + seconds + ' WIB';
        }
        if (dateDisplay) {
            dateDisplay.textContent = dateStr;
        }
    }
    
    // Jalankan pertama kali
    updateAdminClock();
    // Update setiap detik
    setInterval(updateAdminClock, 1000);
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>