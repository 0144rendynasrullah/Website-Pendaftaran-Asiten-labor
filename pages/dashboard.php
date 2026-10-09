<?php
// pages/dashboard.php
require_once __DIR__ . '/../config/database.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$role = getUserRole();
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$stmt = $pdo->prepare("SELECT * FROM pendaftaran WHERE user_id = ?");
$stmt->execute([$user_id]);
$pendaftaran = $stmt->fetch();

$base_path = '../';
include __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col">
            <h2 class="text-primary-dark">Dashboard</h2>
            <p class="text-muted">Selamat datang, <strong><?= htmlspecialchars($user['full_name']) ?></strong></p>
        </div>
    </div>
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

    <?php if ($role === 'admin'): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            Anda login sebagai <strong>Administrator</strong>.
            <a href="admin/dashboard.php" class="btn btn-sm btn-primary ms-2">Buka Dashboard Admin</a>
        </div>
    <?php elseif ($role === 'kepala_lab'): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            Anda login sebagai <strong>Kepala Laboratorium</strong>.
            <a href="kepala-lab/dashboard.php" class="btn btn-sm btn-success ms-2">Buka Dashboard</a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card card-instansi">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>Pendaftaran</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($pendaftaran): ?>
                            <p>Anda sudah mendaftar dengan No: <strong><?= htmlspecialchars($pendaftaran['no_pendaftaran']) ?></strong></p>
                            <p>Status: <span class="badge badge-status <?= $pendaftaran['status_seleksi'] ?>">
                                <?= ucfirst(str_replace('_', ' ', $pendaftaran['status_seleksi'])) ?>
                            </span></p>
                            <a href="status.php" class="btn btn-instansi">
                                <i class="bi bi-clipboard-data me-2"></i>Lihat Status
                            </a>
                        <?php else: ?>
                            <p>Anda belum melakukan pendaftaran.</p>
                            <a href="pendaftaran.php" class="btn btn-instansi">
                                <i class="bi bi-file-earmark-text me-2"></i>Daftar Sekarang
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card-instansi">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-person me-2"></i>Profil</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Nama:</strong> <?= htmlspecialchars($user['full_name']) ?></p>
                        <p><strong>NIM:</strong> <?= htmlspecialchars($user['nim']) ?></p>
                        <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                        <a href="profile.php" class="btn btn-outline-primary">
                            <i class="bi bi-pencil me-2"></i>Edit Profil
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>