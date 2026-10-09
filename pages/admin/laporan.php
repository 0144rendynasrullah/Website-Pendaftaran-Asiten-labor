<?php
// pages/admin/laporan.php
require_once __DIR__ . '/../../config/database.php';

if (!isLoggedIn() || getUserRole() !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// ===== GET LAPORAN DATA =====
try {
    $laporan = $pdo->query("
        SELECT 
            p.*,
            u.full_name as user_full_name,
            u.email,
            (SELECT nilai FROM nilai_tes WHERE pendaftaran_id = p.id AND jenis_tes = 'program' LIMIT 1) as nilai_program,
            (SELECT nilai FROM nilai_tes WHERE pendaftaran_id = p.id AND jenis_tes = 'wawancara' LIMIT 1) as nilai_wawancara,
            v.status as validasi_status,
            v.catatan as validasi_catatan
        FROM pendaftaran p
        JOIN users u ON p.user_id = u.id
        LEFT JOIN validasi_kepala_lab v ON p.id = v.pendaftaran_id
        ORDER BY p.created_at DESC
    ")->fetchAll();
} catch (Exception $e) {
    $laporan = [];
}

// ===== STATISTIK =====
$stats = [
    'total' => count($laporan),
    'lulus_administrasi' => count(array_filter($laporan, fn($p) => $p['status_seleksi'] === 'lulus_administrasi')),
    'tes_program' => count(array_filter($laporan, fn($p) => $p['status_seleksi'] === 'tes_program')),
    'wawancara' => count(array_filter($laporan, fn($p) => $p['status_seleksi'] === 'wawancara')),
    'lulus' => count(array_filter($laporan, fn($p) => $p['status_seleksi'] === 'lulus')),
    'tidak_lulus' => count(array_filter($laporan, fn($p) => $p['status_seleksi'] === 'tidak_lulus')),
];

$base_path = '../../';
include __DIR__ . '/../../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-primary-dark"><i class="bi bi-file-text me-2"></i>Laporan Pendaftaran</h2>
            <p class="text-muted">Rekapitulasi data pendaftar dan hasil seleksi</p>
        </div>
        <div>
            <button class="btn btn-instansi" onclick="window.print()">
                <i class="bi bi-printer me-2"></i>Cetak Laporan
            </button>
        </div>
    </div>

    <!-- ===== STATISTIK CARDS ===== -->
    <div class="row g-4 mb-4">
        <div class="col-md-2">
            <div class="stat-card">
                <div class="text-center">
                    <p class="text-muted mb-1">Total</p>
                    <h3 class="stat-number"><?= $stats['total'] ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card" style="border-left-color: #17a2b8;">
                <div class="text-center">
                    <p class="text-muted mb-1">Lulus Admin</p>
                    <h3 class="stat-number text-info"><?= $stats['lulus_administrasi'] ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card" style="border-left-color: #6f42c1;">
                <div class="text-center">
                    <p class="text-muted mb-1">Tes Program</p>
                    <h3 class="stat-number text-purple"><?= $stats['tes_program'] ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card" style="border-left-color: #fd7e14;">
                <div class="text-center">
                    <p class="text-muted mb-1">Wawancara</p>
                    <h3 class="stat-number text-warning"><?= $stats['wawancara'] ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card" style="border-left-color: #28a745;">
                <div class="text-center">
                    <p class="text-muted mb-1">Lulus</p>
                    <h3 class="stat-number text-success"><?= $stats['lulus'] ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card" style="border-left-color: #dc3545;">
                <div class="text-center">
                    <p class="text-muted mb-1">Tidak Lulus</p>
                    <h3 class="stat-number text-danger"><?= $stats['tidak_lulus'] ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== TABEL LAPORAN ===== -->
    <div class="card card-instansi">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-table me-2"></i>Data Lengkap Pendaftaran</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="laporanTable">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>No Pendaftaran</th>
                            <th>Nama</th>
                            <th>NIM</th>
                            <th>Prodi</th>
                            <th>IPK</th>
                            <th>Status Berkas</th>
                            <th>Status Seleksi</th>
                            <th>Nilai Program</th>
                            <th>Nilai Wawancara</th>
                            <th>Validasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($laporan)): ?>
                            <?php $no = 1; ?>
                            <?php foreach ($laporan as $p): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><strong><?= htmlspecialchars($p['no_pendaftaran']) ?></strong></td>
                                    <td><?= htmlspecialchars($p['nama_lengkap']) ?></td>
                                    <td><?= htmlspecialchars($p['nim']) ?></td>
                                    <td><?= htmlspecialchars($p['prodi']) ?></td>
                                    <td><?= number_format($p['ipk'], 2) ?></td>
                                    <td>
                                        <?php if ($p['status_berkas'] === 'pending'): ?>
                                            <span class="badge badge-status pending">⏳ Pending</span>
                                        <?php elseif ($p['status_berkas'] === 'diverifikasi'): ?>
                                            <span class="badge badge-status diverifikasi">Diverifikasi</span>
                                        <?php elseif ($p['status_berkas'] === 'ditolak'): ?>
                                            <span class="badge badge-status ditolak">Ditolak</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($p['status_seleksi'] === 'pending'): ?>
                                            <span class="badge badge-status pending">Pending</span>
                                        <?php elseif ($p['status_seleksi'] === 'lulus_administrasi'): ?>
                                            <span class="badge badge-status lulus_administrasi">Lulus Admin</span>
                                        <?php elseif ($p['status_seleksi'] === 'tes_program'): ?>
                                            <span class="badge badge-status tes_program">Tes Program</span>
                                        <?php elseif ($p['status_seleksi'] === 'wawancara'): ?>
                                            <span class="badge badge-status wawancara">Wawancara</span>
                                        <?php elseif ($p['status_seleksi'] === 'lulus'): ?>
                                            <span class="badge badge-status lulus">Lulus</span>
                                        <?php elseif ($p['status_seleksi'] === 'tidak_lulus'): ?>
                                            <span class="badge badge-status tidak_lulus">Tidak Lulus</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($p['nilai_program'] !== null): ?>
                                            <span class="badge bg-primary"><?= $p['nilai_program'] ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($p['nilai_wawancara'] !== null): ?>
                                            <span class="badge bg-success"><?= $p['nilai_wawancara'] ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($p['validasi_status'] === 'disetujui'): ?>
                                            <span class="badge bg-success">Disetujui</span>
                                        <?php elseif ($p['validasi_status'] === 'ditolak'): ?>
                                            <span class="badge bg-danger">Ditolak</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">⏳ Pending</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="11" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                    Belum ada data pendaftaran
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ===== EKSPORT DATA ===== -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card card-instansi">
                <div class="card-body">
                    <h6><i class="bi bi-download me-2"></i>Ekspor Data</h6>
                    <div class="d-flex gap-2">
                        <a href="?export=excel" class="btn btn-success">
                            <i class="bi bi-file-excel me-2"></i>Export Excel
                        </a>
                        <a href="?export=pdf" class="btn btn-danger">
                            <i class="bi bi-file-pdf me-2"></i>Export PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.text-primary-dark { color: #0a2e5c; }
.text-purple { color: #6f42c1; }

.stat-card {
    background: #fff;
    border-radius: 15px;
    padding: 1.2rem;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    transition: all 0.3s;
    border-left: 5px solid #1a4a7a;
}
.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}
.stat-card .stat-number {
    font-size: 1.8rem;
    font-weight: 700;
    color: #0a2e5c;
}

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
    padding: 1rem 1.5rem;
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
    padding: 0.3rem 0.7rem;
    border-radius: 50px;
    font-weight: 600;
    font-size: 0.7rem;
}
.badge-status.pending { background: #ffc107; color: #000; }
.badge-status.diverifikasi { background: #17a2b8; color: #fff; }
.badge-status.lulus_administrasi { background: #17a2b8; color: #fff; }
.badge-status.tes_program { background: #6f42c1; color: #fff; }
.badge-status.wawancara { background: #fd7e14; color: #fff; }
.badge-status.lulus { background: #28a745; color: #fff; }
.badge-status.tidak_lulus { background: #dc3545; color: #fff; }
.badge-status.ditolak { background: #dc3545; color: #fff; }

@media print {
    .btn-instansi, .btn-success, .btn-danger, .d-flex.gap-2 { display: none !important; }
    .card-instansi { box-shadow: none !important; border: 1px solid #ddd !important; }
    .stat-card { box-shadow: none !important; border: 1px solid #ddd !important; }
}
</style>

<?php include __DIR__ . '/../../includes/footer.php'; ?>