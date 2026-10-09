<?php
// pages/admin/input_nilai.php
require_once __DIR__ . '/../../config/database.php';

if (!isLoggedIn() || getUserRole() !== 'admin') {
    redirect('../login.php');
}

$message = '';
$messageType = '';

// Get pendaftaran yang lolos administrasi
$stmt = $pdo->prepare("
    SELECT p.*, u.full_name as user_full_name
    FROM pendaftaran p
    JOIN users u ON p.user_id = u.id
    WHERE p.status_seleksi IN ('lulus_administrasi', 'tes_program', 'wawancara')
    ORDER BY p.created_at ASC
");
$stmt->execute();
$pendaftaran = $stmt->fetchAll();

// Handle input nilai
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $pendaftaran_id = (int)$_POST['pendaftaran_id'];
    $jenis_tes = $_POST['jenis_tes'];
    $nilai = (int)$_POST['nilai'];
    $catatan = $_POST['catatan'] ?? '';
    $admin_id = $_SESSION['user_id'];
    
    try {
        // Check if nilai already exists
        $stmt = $pdo->prepare("SELECT * FROM nilai_tes WHERE pendaftaran_id = ? AND jenis_tes = ?");
        $stmt->execute([$pendaftaran_id, $jenis_tes]);
        
        if ($stmt->rowCount() > 0) {
            // Update
            $stmt = $pdo->prepare("UPDATE nilai_tes SET nilai = ?, catatan = ?, input_by = ? 
                                   WHERE pendaftaran_id = ? AND jenis_tes = ?");
            $stmt->execute([$nilai, $catatan, $admin_id, $pendaftaran_id, $jenis_tes]);
        } else {
            // Insert
            $stmt = $pdo->prepare("INSERT INTO nilai_tes (pendaftaran_id, jenis_tes, nilai, catatan, input_by) 
                                   VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$pendaftaran_id, $jenis_tes, $nilai, $catatan, $admin_id]);
        }
        
        // Update status seleksi
        $pdo->prepare("UPDATE pendaftaran SET status_seleksi = ? WHERE id = ?")
           ->execute([$jenis_tes === 'program' ? 'tes_program' : 'wawancara', $pendaftaran_id]);
        
        $message = "Nilai berhasil disimpan!";
        $messageType = 'success';
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = 'danger';
    }
}

// Get existing nilai
$nilaiMap = [];
foreach ($pendaftaran as $p) {
    $stmt = $pdo->prepare("SELECT jenis_tes, nilai FROM nilai_tes WHERE pendaftaran_id = ?");
    $stmt->execute([$p['id']]);
    $nilaiMap[$p['id']] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
}

$base_path = '../../';
include __DIR__ . '/../../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-primary-dark"><i class="bi bi-pencil-square me-2"></i>Input Nilai Tes</h2>
            <p class="text-muted">Masukkan nilai tes program dan wawancara peserta</p>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?> alert-dismissible fade show">
            <?= $message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card card-instansi">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-people me-2"></i>Daftar Peserta</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>No Pendaftaran</th>
                            <th>Nama</th>
                            <th>NIM</th>
                            <th>Nilai Program</th>
                            <th>Nilai Wawancara</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendaftaran as $p): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($p['no_pendaftaran']) ?></strong></td>
                                <td><?= htmlspecialchars($p['nama_lengkap']) ?></td>
                                <td><?= htmlspecialchars($p['nim']) ?></td>
                                <td>
                                    <?php if (isset($nilaiMap[$p['id']]['program'])): ?>
                                        <span class="badge bg-success"><?= $nilaiMap[$p['id']]['program'] ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (isset($nilaiMap[$p['id']]['wawancara'])): ?>
                                        <span class="badge bg-success"><?= $nilaiMap[$p['id']]['wawancara'] ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-instansi" data-bs-toggle="modal" 
                                            data-bs-target="#nilaiModal<?= $p['id'] ?>">
                                        <i class="bi bi-pencil"></i> Input
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($pendaftaran)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="bi bi-info-circle display-4 d-block mb-2"></i>
                                    Belum ada peserta yang lolos administrasi
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Input Nilai -->
<?php foreach ($pendaftaran as $p): ?>
<div class="modal fade" id="nilaiModal<?= $p['id'] ?>" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: var(--primary); color: #fff;">
                <h5 class="modal-title">
                    <i class="bi bi-pencil-square me-2"></i>
                    Input Nilai - <?= htmlspecialchars($p['nama_lengkap']) ?>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="input">
                    <input type="hidden" name="pendaftaran_id" value="<?= $p['id'] ?>">
                    
                    <div class="mb-3">
                        <label class="fw-bold">Jenis Tes</label>
                        <select name="jenis_tes" class="form-select" required>
                            <option value="program">Tes Program</option>
                            <option value="wawancara">Wawancara</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold">Nilai (0-100)</label>
                        <input type="number" name="nilai" class="form-control" min="0" max="100" required>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold">Catatan</label>
                        <textarea name="catatan" class="form-control" rows="2" placeholder="Catatan tambahan..."></textarea>
                    </div>
                    
                    <?php if (isset($nilaiMap[$p['id']]['program'])): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            Nilai Program: <strong><?= $nilaiMap[$p['id']]['program'] ?></strong>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($nilaiMap[$p['id']]['wawancara'])): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            Nilai Wawancara: <strong><?= $nilaiMap[$p['id']]['wawancara'] ?></strong>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-instansi">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endforeach; ?>

<?php include __DIR__ . '/../../includes/footer.php'; ?>