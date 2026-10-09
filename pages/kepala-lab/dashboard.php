<?php
// pages/kepala-lab/dashboard.php
require_once __DIR__ . '/../../config/database.php';

if (!isLoggedIn() || getUserRole() !== 'kepala_lab') {
    header("Location: ../login.php");
    exit();
}

// ===== GET STATISTICS =====
try {
    $total = $pdo->query("SELECT COUNT(*) FROM pendaftaran WHERE status_seleksi != 'pending'")->fetchColumn();
    $pending_validasi = $pdo->query("
        SELECT COUNT(*) FROM validasi_kepala_lab WHERE status = 'pending'
    ")->fetchColumn();
    $disetujui = $pdo->query("
        SELECT COUNT(*) FROM validasi_kepala_lab WHERE status = 'disetujui'
    ")->fetchColumn();
    $ditolak = $pdo->query("
        SELECT COUNT(*) FROM validasi_kepala_lab WHERE status = 'ditolak'
    ")->fetchColumn();

    // ===== GET PENDING VALIDASI =====
    $pending_data = $pdo->query("
        SELECT p.*, u.full_name as user_full_name, v.id as validasi_id
        FROM pendaftaran p
        JOIN users u ON p.user_id = u.id
        LEFT JOIN validasi_kepala_lab v ON p.id = v.pendaftaran_id
        WHERE p.status_seleksi IN ('lulus_administrasi', 'tes_program', 'wawancara', 'lulus')
        AND (v.id IS NULL OR v.status = 'pending')
        ORDER BY p.created_at ASC
    ")->fetchAll();
} catch (Exception $e) {
    $total = $pending_validasi = $disetujui = $ditolak = 0;
    $pending_data = [];
}

$base_path = '../../';
include __DIR__ . '/../../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-primary-dark"><i class="bi bi-check2-square me-2"></i>Dashboard Kepala Laboratorium</h2>
            <p class="text-muted">Validasi hasil seleksi asisten laboratorium</p>
        </div>
        <div>
            <span class="badge bg-success p-2">
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
                        <p class="text-muted mb-1">Total Peserta</p>
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
                        <p class="text-muted mb-1">Pending Validasi</p>
                        <h3 class="stat-number text-warning"><?= $pending_validasi ?></h3>
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
                        <p class="text-muted mb-1">Disetujui</p>
                        <h3 class="stat-number text-success"><?= $disetujui ?></h3>
                    </div>
                    <div class="stat-icon bg-success">
                        <i class="bi bi-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="border-left-color: #dc3545;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Ditolak</p>
                        <h3 class="stat-number text-danger"><?= $ditolak ?></h3>
                    </div>
                    <div class="stat-icon bg-danger">
                        <i class="bi bi-x-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== MENUNGGU VALIDASI ===== -->
    <div class="card card-instansi">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-clock-history me-2"></i>
                Menunggu Validasi
                <span class="badge bg-warning ms-2"><?= count($pending_data) ?></span>
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
                            <th>Prodi</th>
                            <th>Status Seleksi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($pending_data)): ?>
                            <?php foreach ($pending_data as $p): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($p['no_pendaftaran']) ?></strong></td>
                                    <td><?= htmlspecialchars($p['nama_lengkap']) ?></td>
                                    <td><?= htmlspecialchars($p['nim']) ?></td>
                                    <td><?= htmlspecialchars($p['prodi']) ?></td>
                                    <td>
                                        <?php if ($p['status_seleksi'] === 'lulus_administrasi'): ?>
                                            <span class="badge badge-status lulus_administrasi">📋 Lulus Admin</span>
                                        <?php elseif ($p['status_seleksi'] === 'tes_program'): ?>
                                            <span class="badge badge-status tes_program">💻 Tes Program</span>
                                        <?php elseif ($p['status_seleksi'] === 'wawancara'): ?>
                                            <span class="badge badge-status wawancara">🎤 Wawancara</span>
                                        <?php elseif ($p['status_seleksi'] === 'lulus'): ?>
                                            <span class="badge badge-status lulus">🏆 Lulus</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="validasi.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-instansi">
                                            <i class="bi bi-check2-square"></i> Validasi
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="bi bi-check-all display-4 d-block mb-2"></i>
                                    Semua data sudah divalidasi
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
.badge-status.lulus_administrasi { background: #17a2b8; color: #fff; }
.badge-status.tes_program { background: #6f42c1; color: #fff; }
.badge-status.wawancara { background: #fd7e14; color: #fff; }
.badge-status.lulus { background: #28a745; color: #fff; }
</style>

<?php include __DIR__ . '/../../includes/footer.php'; ?>