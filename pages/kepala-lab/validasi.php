<?php
// pages/kepala-lab/validasi.php
require_once __DIR__ . '/../../config/database.php';

// Cek login dan role
if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit();
}

if (getUserRole() !== 'kepala_lab') {
    header("Location: ../dashboard.php");
    exit();
}

$message = '';
$messageType = '';
$selected = null;

// Get specific pendaftaran if id provided
$id = $_GET['id'] ?? 0;
if ($id) {
    $stmt = $pdo->prepare("
        SELECT p.*, u.full_name as user_full_name, u.email, u.nim as user_nim
        FROM pendaftaran p
        JOIN users u ON p.user_id = u.id
        WHERE p.id = ?
    ");
    $stmt->execute([$id]);
    $selected = $stmt->fetch();
}

// Get all pendaftaran for list
try {
    $stmt = $pdo->prepare("
        SELECT p.*, u.full_name as user_full_name,
               v.id as validasi_id, v.status as validasi_status, v.catatan as validasi_catatan
        FROM pendaftaran p
        JOIN users u ON p.user_id = u.id
        LEFT JOIN validasi_kepala_lab v ON p.id = v.pendaftaran_id
        WHERE p.status_seleksi IN ('lulus_administrasi', 'tes_program', 'wawancara', 'lulus')
        ORDER BY p.created_at DESC
    ");
    $stmt->execute();
    $pendaftaran = $stmt->fetchAll();
} catch (Exception $e) {
    $pendaftaran = [];
}

// Handle validasi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pendaftaran_id = (int)$_POST['pendaftaran_id'];
    $status = $_POST['status'];
    $catatan = $_POST['catatan'] ?? '';
    $kepala_lab_id = $_SESSION['user_id'];
    
    try {
        // Check if already exists
        $stmt = $pdo->prepare("SELECT * FROM validasi_kepala_lab WHERE pendaftaran_id = ?");
        $stmt->execute([$pendaftaran_id]);
        
        if ($stmt->rowCount() > 0) {
            // Update
            $stmt = $pdo->prepare("UPDATE validasi_kepala_lab SET status = ?, catatan = ?, validated_by = ? 
                                   WHERE pendaftaran_id = ?");
            $stmt->execute([$status, $catatan, $kepala_lab_id, $pendaftaran_id]);
        } else {
            // Insert
            $stmt = $pdo->prepare("INSERT INTO validasi_kepala_lab (pendaftaran_id, status, catatan, validated_by) 
                                   VALUES (?, ?, ?, ?)");
            $stmt->execute([$pendaftaran_id, $status, $catatan, $kepala_lab_id]);
        }
        
        // Update status seleksi if finalized
        if ($status === 'disetujui') {
            $pdo->prepare("UPDATE pendaftaran SET status_seleksi = 'lulus' WHERE id = ?")
               ->execute([$pendaftaran_id]);
            $message = "✅ Pendaftar dinyatakan LULUS sebagai Asisten Laboratorium!";
        } elseif ($status === 'ditolak') {
            $pdo->prepare("UPDATE pendaftaran SET status_seleksi = 'tidak_lulus' WHERE id = ?")
               ->execute([$pendaftaran_id]);
            $message = "❌ Pendaftar dinyatakan TIDAK LULUS.";
        } else {
            $message = "Status validasi diperbarui ke 'Pending'.";
        }
        $messageType = 'success';
        
        // Redirect to dashboard
        header("Location: dashboard.php?msg=" . urlencode($message));
        exit();
        
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = 'danger';
    }
}

$base_path = '../../';
include __DIR__ . '/../../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-primary-dark"><i class="bi bi-check2-square me-2"></i>Validasi Hasil Seleksi</h2>
            <p class="text-muted">Validasi akhir kelulusan asisten laboratorium</p>
        </div>
        <a href="dashboard.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Kembali
        </a>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?> alert-dismissible fade show">
            <?= $message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($selected): ?>
        <!-- Detail Validasi -->
        <div class="row">
            <div class="col-md-8">
                <div class="card card-instansi">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-person me-2"></i>
                            Detail Pendaftar - <?= htmlspecialchars($selected['no_pendaftaran']) ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="text-muted small">Nama Lengkap</label>
                                <p class="fw-bold"><?= htmlspecialchars($selected['nama_lengkap']) ?></p>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small">NIM</label>
                                <p class="fw-bold"><?= htmlspecialchars($selected['nim']) ?></p>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small">Program Studi</label>
                                <p class="fw-bold"><?= htmlspecialchars($selected['prodi']) ?></p>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small">IPK</label>
                                <p class="fw-bold"><?= $selected['ipk'] ?></p>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small">Semester</label>
                                <p class="fw-bold"><?= $selected['semester'] ?></p>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small">No HP</label>
                                <p class="fw-bold"><?= htmlspecialchars($selected['no_hp']) ?></p>
                            </div>
                            <div class="col-12">
                                <label class="text-muted small">Status Seleksi</label>
                                <p>
                                    <span class="badge badge-status <?= $selected['status_seleksi'] ?>">
                                        <?= ucfirst(str_replace('_', ' ', $selected['status_seleksi'])) ?>
                                    </span>
                                </p>
                            </div>
                        </div>

                        <hr>
                        <form method="POST" class="mt-3">
                            <input type="hidden" name="pendaftaran_id" value="<?= $selected['id'] ?>">
                            
                            <div class="mb-3">
                                <label class="fw-bold">Status Validasi</label>
                                <select name="status" class="form-select" required>
                                    <option value="pending">⏳ Pending</option>
                                    <option value="disetujui">✅ Setujui (Lulus)</option>
                                    <option value="ditolak">❌ Tolak (Tidak Lulus)</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="fw-bold">Catatan</label>
                                <textarea name="catatan" class="form-control" rows="3" 
                                          placeholder="Tambahkan catatan validasi..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-instansi w-100">
                                <i class="bi bi-check2-square me-2"></i>Submit Validasi
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card card-instansi">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Timeline</h5>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <div class="timeline-item completed">
                                <div>📝 Pendaftaran</div>
                                <small class="text-muted"><?= date('d/m/Y', strtotime($selected['created_at'])) ?></small>
                            </div>
                            <div class="timeline-item <?= $selected['status_berkas'] === 'diverifikasi' ? 'completed' : '' ?>">
                                <div>✅ Verifikasi Berkas</div>
                                <small class="text-muted"><?= $selected['status_berkas'] === 'diverifikasi' ? 'Lulus' : 'Menunggu' ?></small>
                            </div>
                            <div class="timeline-item <?= in_array($selected['status_seleksi'], ['tes_program', 'wawancara', 'lulus']) ? 'completed' : '' ?>">
                                <div>💻 Tes Program</div>
                                <small class="text-muted"><?= in_array($selected['status_seleksi'], ['tes_program', 'wawancara', 'lulus']) ? 'Selesai' : 'Menunggu' ?></small>
                            </div>
                            <div class="timeline-item <?= in_array($selected['status_seleksi'], ['wawancara', 'lulus']) ? 'completed' : '' ?>">
                                <div>🎤 Wawancara</div>
                                <small class="text-muted"><?= in_array($selected['status_seleksi'], ['wawancara', 'lulus']) ? 'Selesai' : 'Menunggu' ?></small>
                            </div>
                            <div class="timeline-item <?= $selected['status_seleksi'] === 'lulus' ? 'completed' : '' ?>">
                                <div>🏆 Validasi Akhir</div>
                                <small class="text-muted"><?= $selected['status_seleksi'] === 'lulus' ? 'Selesai' : 'Menunggu validasi' ?></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php else: ?>
        <!-- List Pendaftaran -->
        <div class="card card-instansi">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-list me-2"></i>Daftar Pendaftar</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>No Pendaftaran</th>
                                <th>Nama</th>
                                <th>NIM</th>
                                <th>Status Seleksi</th>
                                <th>Validasi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($pendaftaran)): ?>
                                <?php foreach ($pendaftaran as $p): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($p['no_pendaftaran']) ?></strong></td>
                                        <td><?= htmlspecialchars($p['nama_lengkap']) ?></td>
                                        <td><?= htmlspecialchars($p['nim']) ?></td>
                                        <td>
                                            <span class="badge badge-status <?= $p['status_seleksi'] ?>">
                                                <?= ucfirst(str_replace('_', ' ', $p['status_seleksi'])) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($p['validasi_status'] === 'disetujui'): ?>
                                                <span class="badge bg-success">✅ Disetujui</span>
                                            <?php elseif ($p['validasi_status'] === 'ditolak'): ?>
                                                <span class="badge bg-danger">❌ Ditolak</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">⏳ Pending</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="validasi.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-instansi">
                                                <i class="bi bi-eye"></i> Detail
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
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
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>