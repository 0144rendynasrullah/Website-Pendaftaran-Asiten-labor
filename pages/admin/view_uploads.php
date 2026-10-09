<?php
// pages/admin/view_uploads.php
require_once __DIR__ . '/../../config/database.php';

if (!isLoggedIn() || getUserRole() !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$id = $_GET['id'] ?? 0;
if (!$id) {
    header("Location: verifikasi.php");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM pendaftaran WHERE id = ?");
$stmt->execute([$id]);
$data = $stmt->fetch();

if (!$data) {
    header("Location: verifikasi.php");
    exit();
}

$base_path = '../../';
include __DIR__ . '/../../includes/header.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-primary-dark"><i class="bi bi-files me-2"></i>File Upload</h2>
            <p class="text-muted"><?= htmlspecialchars($data['nama_lengkap']) ?> - <?= htmlspecialchars($data['no_pendaftaran']) ?></p>
        </div>
        <a href="verifikasi.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-2"></i>Kembali
        </a>
    </div>

    <div class="row g-4">
        <!-- CV -->
        <div class="col-md-6">
            <div class="card card-instansi h-100">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-file-pdf me-2"></i>CV / Daftar Riwayat Hidup</h5>
                </div>
                <div class="card-body text-center py-4">
                    <?php if ($data['cv_path'] && file_exists(__DIR__ . '/../../' . $data['cv_path'])): ?>
                        <i class="bi bi-file-earmark-pdf display-1 text-danger"></i>
                        <p class="mt-2 text-muted"><?= basename($data['cv_path']) ?></p>
                        <div class="d-flex gap-2 justify-content-center">
                            <a href="<?= $base_path . $data['cv_path'] ?>" target="_blank" class="btn btn-primary">
                                <i class="bi bi-eye me-2"></i>Lihat
                            </a>
                            <a href="<?= $base_path . $data['cv_path'] ?>" download class="btn btn-success">
                                <i class="bi bi-download me-2"></i>Download
                            </a>
                        </div>
                    <?php else: ?>
                        <i class="bi bi-file-earmark-x display-1 text-muted"></i>
                        <p class="mt-2 text-muted">Belum upload CV</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Surat Lamaran -->
        <div class="col-md-6">
            <div class="card card-instansi h-100">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>Surat Lamaran</h5>
                </div>
                <div class="card-body text-center py-4">
                    <?php if ($data['ktp_path'] && file_exists(__DIR__ . '/../../' . $data['ktp_path'])): ?>
                        <i class="bi bi-file-earmark-text display-1 text-success"></i>
                        <p class="mt-2 text-muted"><?= basename($data['ktp_path']) ?></p>
                        <div class="d-flex gap-2 justify-content-center">
                            <a href="<?= $base_path . $data['ktp_path'] ?>" target="_blank" class="btn btn-primary">
                                <i class="bi bi-eye me-2"></i>Lihat
                            </a>
                            <a href="<?= $base_path . $data['ktp_path'] ?>" download class="btn btn-success">
                                <i class="bi bi-download me-2"></i>Download
                            </a>
                        </div>
                    <?php else: ?>
                        <i class="bi bi-file-earmark-x display-1 text-muted"></i>
                        <p class="mt-2 text-muted">Belum upload Surat Lamaran</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Transkrip -->
        <div class="col-md-6">
            <div class="card card-instansi h-100">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-file-pdf me-2"></i>Transkrip Nilai</h5>
                </div>
                <div class="card-body text-center py-4">
                    <?php if ($data['transkrip_path'] && file_exists(__DIR__ . '/../../' . $data['transkrip_path'])): ?>
                        <i class="bi bi-file-earmark-pdf display-1 text-info"></i>
                        <p class="mt-2 text-muted"><?= basename($data['transkrip_path']) ?></p>
                        <div class="d-flex gap-2 justify-content-center">
                            <a href="<?= $base_path . $data['transkrip_path'] ?>" target="_blank" class="btn btn-primary">
                                <i class="bi bi-eye me-2"></i>Lihat
                            </a>
                            <a href="<?= $base_path . $data['transkrip_path'] ?>" download class="btn btn-success">
                                <i class="bi bi-download me-2"></i>Download
                            </a>
                        </div>
                    <?php else: ?>
                        <i class="bi bi-file-earmark-x display-1 text-muted"></i>
                        <p class="mt-2 text-muted">Belum upload Transkrip</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Foto -->
        <div class="col-md-6">
            <div class="card card-instansi h-100">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-image me-2"></i>Pas Foto</h5>
                </div>
                <div class="card-body text-center py-4">
                    <?php if ($data['foto_path'] && file_exists(__DIR__ . '/../../' . $data['foto_path'])): ?>
                        <i class="bi bi-image display-1 text-secondary"></i>
                        <p class="mt-2 text-muted"><?= basename($data['foto_path']) ?></p>
                        <div class="d-flex gap-2 justify-content-center">
                            <a href="<?= $base_path . $data['foto_path'] ?>" target="_blank" class="btn btn-primary">
                                <i class="bi bi-eye me-2"></i>Lihat
                            </a>
                            <a href="<?= $base_path . $data['foto_path'] ?>" download class="btn btn-success">
                                <i class="bi bi-download me-2"></i>Download
                            </a>
                        </div>
                    <?php else: ?>
                        <i class="bi bi-file-earmark-x display-1 text-muted"></i>
                        <p class="mt-2 text-muted">Belum upload Foto</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
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
.text-primary-dark { color: #0a2e5c; }
</style>

<?php include __DIR__ . '/../../includes/footer.php'; ?>