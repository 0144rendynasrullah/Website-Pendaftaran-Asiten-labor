<?php
// pages/status.php
require_once __DIR__ . '/../config/database.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

if (getUserRole() !== 'pendaftar') {
    header("Location: dashboard.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM pendaftaran WHERE user_id = ?");
$stmt->execute([$user_id]);
$pendaftaran = $stmt->fetch();

$nilai = [];
if ($pendaftaran) {
    $stmt = $pdo->prepare("SELECT jenis_tes, nilai FROM nilai_tes WHERE pendaftaran_id = ?");
    $stmt->execute([$pendaftaran['id']]);
    $nilai = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
}

$base_path = '../';
include __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card card-instansi">
                <div class="card-header">
                    <h4 class="mb-0"><i class="bi bi-clipboard-data me-2"></i>Status Pendaftaran</h4>
                </div>
                <div class="card-body">
                    <?php if ($pendaftaran): ?>
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5>No Pendaftaran: <span class="badge bg-primary"><?= htmlspecialchars($pendaftaran['no_pendaftaran']) ?></span></h5>
                                <span class="text-muted small"><?= date('d F Y', strtotime($pendaftaran['created_at'])) ?></span>
                            </div>
                        </div>

                        <!-- Timeline -->
                        <?php
                        $status = $pendaftaran['status_seleksi'];
                        
                        $steps = [
                            'pending' => [
                                'label' => 'Pendaftaran Diterima',
                                'icon' => 'bi-inbox',
                                'desc' => 'Pendaftaran Anda telah kami terima',
                                'done' => in_array($status, ['pending', 'lulus_administrasi', 'tes_program', 'wawancara', 'lulus'])
                            ],
                            'lulus_administrasi' => [
                                'label' => 'Lulus Administrasi',
                                'icon' => 'bi-file-check',
                                'desc' => 'Berkas Anda telah diverifikasi dan dinyatakan lengkap',
                                'done' => in_array($status, ['lulus_administrasi', 'tes_program', 'wawancara', 'lulus'])
                            ],
                            'tes_program' => [
                                'label' => 'Tes Program',
                                'icon' => 'bi-code-square',
                                'desc' => 'Anda telah mengikuti tes program',
                                'done' => in_array($status, ['tes_program', 'wawancara', 'lulus'])
                            ],
                            'wawancara' => [
                                'label' => 'Wawancara',
                                'icon' => 'bi-person-video2',
                                'desc' => 'Anda telah mengikuti sesi wawancara',
                                'done' => in_array($status, ['wawancara', 'lulus'])
                            ],
                            'lulus' => [
                                'label' => 'Lulus Seleksi',
                                'icon' => 'bi-trophy',
                                'desc' => 'Selamat! Anda diterima sebagai Asisten Laboratorium',
                                'done' => ($status === 'lulus')
                            ],
                        ];

                        if ($status === 'tidak_lulus') {
                            $steps['tidak_lulus'] = [
                                'label' => 'Tidak Lulus',
                                'icon' => 'bi-x-circle',
                                'desc' => 'Maaf, Anda belum berhasil dalam seleksi ini',
                                'done' => true
                            ];
                        }
                        ?>

                        <div class="timeline-container">
                            <?php foreach ($steps as $key => $step): ?>
                                <?php 
                                $isDone = $step['done'] ?? false;
                                $isCurrent = ($key === $status && $status !== 'lulus' && $status !== 'tidak_lulus');
                                ?>
                                <div class="timeline-item <?= $isDone ? 'done' : '' ?> <?= $isCurrent ? 'current' : '' ?>">
                                    <div class="timeline-icon">
                                        <i class="bi <?= $step['icon'] ?>"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0 <?= $isDone ? 'text-success' : ($isCurrent ? 'text-primary' : 'text-muted') ?>">
                                                <?= $step['label'] ?>
                                            </h6>
                                            <?php if ($isDone): ?>
                                                <span class="badge bg-success"><i class="bi bi-check-circle"></i> Selesai</span>
                                            <?php elseif ($isCurrent): ?>
                                                <span class="badge bg-warning text-dark"><i class="bi bi-clock"></i> Proses</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Menunggu</span>
                                            <?php endif; ?>
                                        </div>
                                        <p class="text-muted small mb-0"><?= $step['desc'] ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Pesan Selamat -->
                        <?php if ($status === 'lulus'): ?>
                            <div class="alert alert-success mt-4">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                <strong>Selamat!</strong> Anda resmi menjadi Asisten Laboratorium.
                                <br><small>Silakan hubungi admin untuk informasi lebih lanjut.</small>
                            </div>
                        <?php endif; ?>

                        <?php if ($status === 'tidak_lulus'): ?>
                            <div class="alert alert-danger mt-4">
                                <i class="bi bi-x-circle-fill me-2"></i>
                                <strong>Maaf,</strong> Anda belum berhasil dalam seleksi ini.
                                <br><small>Terima kasih atas partisipasi Anda.</small>
                            </div>
                        <?php endif; ?>

                        <!-- Nilai Tes -->
                        <?php if (!empty($nilai)): ?>
                        <div class="mt-4">
                            <h6 class="text-primary-dark"><i class="bi bi-bar-chart me-2"></i>Nilai Tes</h6>
                            <div class="row g-3">
                                <?php if (isset($nilai['program'])): ?>
                                <div class="col-md-6">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h6 class="text-muted">Tes Program</h6>
                                            <h3 class="text-primary"><?= $nilai['program'] ?></h3>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                <?php if (isset($nilai['wawancara'])): ?>
                                <div class="col-md-6">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h6 class="text-muted">Wawancara</h6>
                                            <h3 class="text-success"><?= $nilai['wawancara'] ?></h3>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Data Pendaftaran + File Upload -->
                        <div class="mt-4">
                            <h6 class="text-primary-dark"><i class="bi bi-file-earmark-text me-2"></i>Data Pendaftaran</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="text-muted small">Nama Lengkap</label>
                                    <p class="fw-bold"><?= htmlspecialchars($pendaftaran['nama_lengkap']) ?></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small">NIM</label>
                                    <p class="fw-bold"><?= htmlspecialchars($pendaftaran['nim']) ?></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small">Program Studi</label>
                                    <p class="fw-bold"><?= htmlspecialchars($pendaftaran['prodi']) ?></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small">Semester / IPK</label>
                                    <p class="fw-bold"><?= $pendaftaran['semester'] ?> / <?= $pendaftaran['ipk'] ?></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small">No HP</label>
                                    <p class="fw-bold"><?= htmlspecialchars($pendaftaran['no_hp'] ?? '-') ?></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small">Status Berkas</label>
                                    <p>
                                        <span class="badge badge-status <?= $pendaftaran['status_berkas'] ?>">
                                            <?= ucfirst($pendaftaran['status_berkas']) ?>
                                        </span>
                                    </p>
                                </div>
                                
                                <!-- ===== FILE UPLOAD - PENDAFTAR LIHAT ===== -->
                                <div class="col-12 mt-2">
                                    <label class="text-muted small">Berkas yang Diupload</label>
                                    <div class="d-flex gap-2 flex-wrap mt-1">
                                        <?php if ($pendaftaran['cv_path'] && file_exists(__DIR__ . '/../' . $pendaftaran['cv_path'])): ?>
                                            <a href="<?= $base_path . $pendaftaran['cv_path'] ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-file-pdf"></i> CV
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($pendaftaran['ktp_path'] && file_exists(__DIR__ . '/../' . $pendaftaran['ktp_path'])): ?>
                                            <a href="<?= $base_path . $pendaftaran['ktp_path'] ?>" target="_blank" class="btn btn-sm btn-outline-success">
                                                <i class="bi bi-file-earmark-text"></i> Surat Lamaran
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($pendaftaran['transkrip_path'] && file_exists(__DIR__ . '/../' . $pendaftaran['transkrip_path'])): ?>
                                            <a href="<?= $base_path . $pendaftaran['transkrip_path'] ?>" target="_blank" class="btn btn-sm btn-outline-info">
                                                <i class="bi bi-file-pdf"></i> Transkrip
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($pendaftaran['foto_path'] && file_exists(__DIR__ . '/../' . $pendaftaran['foto_path'])): ?>
                                            <a href="<?= $base_path . $pendaftaran['foto_path'] ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                                                <i class="bi bi-image"></i> Foto
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!$pendaftaran['cv_path'] && !$pendaftaran['ktp_path'] && !$pendaftaran['transkrip_path'] && !$pendaftaran['foto_path']): ?>
                                        <p class="text-muted small">Belum ada file yang diupload</p>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if ($pendaftaran['catatan_admin']): ?>
                                <div class="col-12">
                                    <label class="text-muted small">Catatan Admin</label>
                                    <div class="border p-2 rounded bg-light">
                                        <?= nl2br(htmlspecialchars($pendaftaran['catatan_admin'])) ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="bi bi-inbox display-1 text-muted"></i>
                            <h5 class="mt-3">Belum Melakukan Pendaftaran</h5>
                            <p class="text-muted">Silakan lakukan pendaftaran terlebih dahulu.</p>
                            <a href="pendaftaran.php" class="btn btn-instansi">
                                <i class="bi bi-file-earmark-text me-2"></i>Daftar Sekarang
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Timeline */
.timeline-container {
    position: relative;
    padding-left: 30px;
}
.timeline-container::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    height: 100%;
    width: 3px;
    background: #dee2e6;
}
.timeline-item {
    position: relative;
    padding-bottom: 25px;
    padding-left: 20px;
}
.timeline-item:last-child {
    padding-bottom: 0;
}
.timeline-item .timeline-icon {
    position: absolute;
    left: -26px;
    top: 0;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #fff;
    border: 3px solid #dee2e6;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    color: #6c757d;
    z-index: 1;
    transition: all 0.3s;
}
.timeline-item.done .timeline-icon {
    border-color: #28a745;
    background: #28a745;
    color: #fff;
    box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.3);
}
.timeline-item.current .timeline-icon {
    border-color: #ffc107;
    background: #ffc107;
    color: #000;
    box-shadow: 0 0 0 3px rgba(255, 193, 7, 0.3);
}
.timeline-item .timeline-content {
    background: #f8f9fa;
    padding: 12px 18px;
    border-radius: 10px;
    border-left: 4px solid #dee2e6;
    transition: all 0.3s;
}
.timeline-item.done .timeline-content {
    border-left-color: #28a745;
    background: #f0fff4;
}
.timeline-item.current .timeline-content {
    border-left-color: #ffc107;
    background: #fff8e1;
}
.timeline-item .badge {
    font-size: 11px;
    padding: 4px 10px;
}
.badge-status {
    padding: 0.5rem 1rem;
    border-radius: 50px;
    font-weight: 600;
}
.badge-status.pending { background: #ffc107; color: #000; }
.badge-status.diverifikasi { background: #17a2b8; color: #fff; }
.badge-status.lulus { background: #28a745; color: #fff; }
.badge-status.tidak_lulus { background: #dc3545; color: #fff; }
.badge-status.ditolak { background: #dc3545; color: #fff; }
.btn-instansi {
    background: linear-gradient(135deg, #1a4a7a, #2a6aaa);
    border: none;
    color: #fff;
    padding: 0.6rem 1.5rem;
    border-radius: 8px;
    transition: all 0.3s;
}
.btn-instansi:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(26, 74, 122, 0.3);
    color: #fff;
}
.text-primary-dark { color: #0a2e5c; }
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>