<?php
// pages/admin/verifikasi.php
require_once __DIR__ . '/../../config/database.php';

if (!isLoggedIn() || getUserRole() !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$message = '';
$messageType = '';

// Handle verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $pendaftaran_id = (int)$_POST['pendaftaran_id'];
    $status = $_POST['status'];
    $catatan = trim($_POST['catatan'] ?? '');
    
    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("UPDATE pendaftaran SET status_berkas = ?, catatan_admin = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$status, $catatan, $pendaftaran_id]);
        
        if ($status === 'diverifikasi') {
            $stmt = $pdo->prepare("UPDATE pendaftaran SET status_seleksi = 'lulus_administrasi' WHERE id = ?");
            $stmt->execute([$pendaftaran_id]);
            $message = "Berkas berhasil diverifikasi! Status seleksi diperbarui ke 'Lulus Administrasi'.";
        } elseif ($status === 'ditolak') {
            $stmt = $pdo->prepare("UPDATE pendaftaran SET status_seleksi = 'tidak_lulus' WHERE id = ?");
            $stmt->execute([$pendaftaran_id]);
            $message = "Berkas ditolak. Status seleksi diperbarui ke 'Tidak Lulus'.";
        } else {
            $message = "Status berkas diperbarui ke 'Pending'.";
        }
        
        $pdo->commit();
        $messageType = 'success';
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = "Error: " . $e->getMessage();
        $messageType = 'danger';
    }
}

// Get all pendaftaran with user info
$stmt = $pdo->prepare("
    SELECT p.*, u.username, u.email, u.full_name as user_full_name
    FROM pendaftaran p 
    JOIN users u ON p.user_id = u.id 
    ORDER BY 
        FIELD(p.status_berkas, 'pending', 'diverifikasi', 'ditolak'),
        p.created_at DESC
");
$stmt->execute();
$pendaftaran = $stmt->fetchAll();

$base_path = '../../';
include __DIR__ . '/../../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-primary-dark"><i class="bi bi-folder-check me-2"></i>Verifikasi Berkas</h2>
            <p class="text-muted">Periksa kelengkapan dan keabsahan berkas pendaftar</p>
        </div>
        <div>
            <span class="badge bg-info p-2">
                <i class="bi bi-file-earmark me-1"></i> Total: <?= count($pendaftaran) ?>
            </span>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?> alert-dismissible fade show">
            <?= $message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Filter Tabs -->
    <ul class="nav nav-tabs mb-4" id="verifikasiTabs">
        <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="tab" href="#all">Semua</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#pending">
                Pending <span class="badge bg-warning"><?= count(array_filter($pendaftaran, fn($p) => $p['status_berkas'] === 'pending')) ?></span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#verified">
                Diverifikasi <span class="badge bg-success"><?= count(array_filter($pendaftaran, fn($p) => $p['status_berkas'] === 'diverifikasi')) ?></span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#rejected">
                Ditolak <span class="badge bg-danger"><?= count(array_filter($pendaftaran, fn($p) => $p['status_berkas'] === 'ditolak')) ?></span>
            </a>
        </li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="all">
            <?php renderTable($pendaftaran, $base_path); ?>
        </div>
        <div class="tab-pane fade" id="pending">
            <?php renderTable(array_filter($pendaftaran, fn($p) => $p['status_berkas'] === 'pending'), $base_path); ?>
        </div>
        <div class="tab-pane fade" id="verified">
            <?php renderTable(array_filter($pendaftaran, fn($p) => $p['status_berkas'] === 'diverifikasi'), $base_path); ?>
        </div>
        <div class="tab-pane fade" id="rejected">
            <?php renderTable(array_filter($pendaftaran, fn($p) => $p['status_berkas'] === 'ditolak'), $base_path); ?>
        </div>
    </div>
</div>

<?php
function renderTable($data, $base_path) {
    if (empty($data)): ?>
        <div class="text-center py-5">
            <i class="bi bi-inbox display-1 text-muted"></i>
            <p class="text-muted mt-3">Tidak ada data</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>No Pendaftaran</th>
                        <th>Nama</th>
                        <th>NIM</th>
                        <th>Prodi</th>
                        <th>IPK</th>
                        <th>Status</th>
                        <th>Berkas</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $p): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($p['no_pendaftaran']) ?></strong></td>
                            <td><?= htmlspecialchars($p['nama_lengkap']) ?></td>
                            <td><?= htmlspecialchars($p['nim']) ?></td>
                            <td><?= htmlspecialchars($p['prodi']) ?></td>
                            <td><?= $p['ipk'] ?></td>
                            <td>
                                <span class="badge badge-status <?= $p['status_berkas'] ?>">
                                    <?= ucfirst($p['status_berkas']) ?>
                                </span>
                                <?php if ($p['status_berkas'] === 'diverifikasi'): ?>
                                    <span class="badge bg-success ms-1">Lulus Admin</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($p['cv_path']): ?>
                                    <a href="<?= $base_path . $p['cv_path'] ?>" target="_blank" class="btn btn-sm btn-outline-primary" title="CV">
                                        <i class="bi bi-file-pdf"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if ($p['ktp_path']): ?>
                                    <a href="<?= $base_path . $p['ktp_path'] ?>" target="_blank" class="btn btn-sm btn-outline-success" title="Surat Lamaran">
                                        <i class="bi bi-file-earmark-text"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if ($p['transkrip_path']): ?>
                                    <a href="<?= $base_path . $p['transkrip_path'] ?>" target="_blank" class="btn btn-sm btn-outline-info" title="Transkrip">
                                        <i class="bi bi-file-pdf"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if ($p['foto_path']): ?>
                                    <a href="<?= $base_path . $p['foto_path'] ?>" target="_blank" class="btn btn-sm btn-outline-secondary" title="Foto">
                                        <i class="bi bi-image"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="view_uploads.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-info" title="Lihat Semua Berkas">
                                    <i class="bi bi-files me-1"></i>Berkas
                                </a>
                                <button class="btn btn-sm btn-instansi" data-bs-toggle="modal" 
                                        data-bs-target="#verifikasiModal<?= $p['id'] ?>">
                                    <i class="bi bi-eye"></i> Verifikasi
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif;
}

// Modals
foreach ($pendaftaran as $p):
?>
<div class="modal fade" id="verifikasiModal<?= $p['id'] ?>" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: var(--primary); color: #fff;">
                <h5 class="modal-title">
                    <i class="bi bi-folder-check me-2"></i>
                    Verifikasi Berkas - <?= htmlspecialchars($p['no_pendaftaran']) ?>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="pendaftaran_id" value="<?= $p['id'] ?>">
                    <input type="hidden" name="action" value="verify">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="fw-bold text-primary-dark">Nama Lengkap</label>
                            <p class="border-bottom pb-2"><?= htmlspecialchars($p['nama_lengkap']) ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="fw-bold text-primary-dark">NIM</label>
                            <p class="border-bottom pb-2"><?= htmlspecialchars($p['nim']) ?></p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="fw-bold text-primary-dark">Program Studi</label>
                            <p class="border-bottom pb-2"><?= htmlspecialchars($p['prodi']) ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="fw-bold text-primary-dark">IPK</label>
                            <p class="border-bottom pb-2"><?= $p['ipk'] ?></p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="fw-bold text-primary-dark">Semester</label>
                            <p class="border-bottom pb-2"><?= $p['semester'] ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="fw-bold text-primary-dark">No HP</label>
                            <p class="border-bottom pb-2"><?= htmlspecialchars($p['no_hp']) ?></p>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold text-primary-dark">Alamat</label>
                        <p class="border-bottom pb-2"><?= htmlspecialchars($p['alamat'] ?? '-') ?></p>
                    </div>
                    
                    <!-- File Preview -->
                    <hr>
                    <h6 class="fw-bold text-primary-dark"><i class="bi bi-files me-2"></i>File yang Diupload</h6>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="text-muted small">CV</label>
                            <p><?= $p['cv_path'] ? '<a href="'.$base_path.$p['cv_path'].'" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i> Lihat CV</a>' : '<span class="text-muted">Belum upload</span>' ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Surat Lamaran</label>
                            <p><?= $p['ktp_path'] ? '<a href="'.$base_path.$p['ktp_path'].'" target="_blank" class="btn btn-sm btn-outline-success"><i class="bi bi-eye"></i> Lihat Surat</a>' : '<span class="text-muted">Belum upload</span>' ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Transkrip Nilai</label>
                            <p><?= $p['transkrip_path'] ? '<a href="'.$base_path.$p['transkrip_path'].'" target="_blank" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i> Lihat Transkrip</a>' : '<span class="text-muted">Belum upload</span>' ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Pas Foto</label>
                            <p><?= $p['foto_path'] ? '<a href="'.$base_path.$p['foto_path'].'" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i> Lihat Foto</a>' : '<span class="text-muted">Belum upload</span>' ?></p>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="mb-3">
                        <label class="fw-bold">Status Verifikasi</label>
                        <select name="status" class="form-select" required>
                            <option value="pending" <?= $p['status_berkas'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="diverifikasi" <?= $p['status_berkas'] === 'diverifikasi' ? 'selected' : '' ?>>Setujui (Diverifikasi)</option>
                            <option value="ditolak" <?= $p['status_berkas'] === 'ditolak' ? 'selected' : '' ?>>Tolak</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="fw-bold">Catatan</label>
                        <textarea name="catatan" class="form-control" rows="3" 
                                  placeholder="Tambahkan catatan jika diperlukan..."><?= htmlspecialchars($p['catatan_admin'] ?? '') ?></textarea>
                    </div>
                    
                    <?php if ($p['status_berkas'] === 'diverifikasi'): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            Pendaftar ini sudah dinyatakan <strong>Lulus Administrasi</strong>.
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-instansi">
                        <i class="bi bi-check-circle me-1"></i> Simpan Verifikasi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endforeach; ?>

<style>
.badge-status {
    padding: 0.5rem 1rem;
    border-radius: 50px;
    font-weight: 600;
}
.badge-status.pending { background: #ffc107; color: #000; }
.badge-status.diverifikasi { background: #17a2b8; color: #fff; }
.badge-status.ditolak { background: #dc3545; color: #fff; }
.btn-instansi {
    background: linear-gradient(135deg, #1a4a7a, #2a6aaa);
    border: none;
    color: #fff;
    padding: 0.4rem 1rem;
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

<?php include __DIR__ . '/../../includes/footer.php'; ?>