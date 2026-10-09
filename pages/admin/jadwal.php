<?php
// pages/admin/jadwal.php
require_once __DIR__ . '/../../config/database.php';

if (!isLoggedIn() || getUserRole() !== 'admin') {
    redirect('../login.php');
}

$message = '';
$messageType = '';

// Handle add/edit/delete jadwal
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add' || $action === 'edit') {
        $id = $_POST['id'] ?? 0;
        $jenis_tes = $_POST['jenis_tes'];
        $tanggal = $_POST['tanggal'];
        $waktu_mulai = $_POST['waktu_mulai'];
        $waktu_selesai = $_POST['waktu_selesai'];
        $lokasi = $_POST['lokasi'];
        $keterangan = $_POST['keterangan'] ?? '';
        
        try {
            if ($action === 'add') {
                $stmt = $pdo->prepare("INSERT INTO jadwal_tes (jenis_tes, tanggal, waktu_mulai, waktu_selesai, lokasi, keterangan) 
                                       VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$jenis_tes, $tanggal, $waktu_mulai, $waktu_selesai, $lokasi, $keterangan]);
                $message = "Jadwal berhasil ditambahkan!";
            } else {
                $stmt = $pdo->prepare("UPDATE jadwal_tes SET jenis_tes = ?, tanggal = ?, waktu_mulai = ?, waktu_selesai = ?, lokasi = ?, keterangan = ? 
                                       WHERE id = ?");
                $stmt->execute([$jenis_tes, $tanggal, $waktu_mulai, $waktu_selesai, $lokasi, $keterangan, $id]);
                $message = "Jadwal berhasil diupdate!";
            }
            $messageType = 'success';
        } catch (Exception $e) {
            $message = "Error: " . $e->getMessage();
            $messageType = 'danger';
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'];
        try {
            $stmt = $pdo->prepare("DELETE FROM jadwal_tes WHERE id = ?");
            $stmt->execute([$id]);
            $message = "Jadwal berhasil dihapus!";
            $messageType = 'success';
        } catch (Exception $e) {
            $message = "Error: " . $e->getMessage();
            $messageType = 'danger';
        }
    }
}

$jadwal = $pdo->query("SELECT * FROM jadwal_tes ORDER BY tanggal ASC, waktu_mulai ASC")->fetchAll();

$base_path = '../../';
include __DIR__ . '/../../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-primary-dark"><i class="bi bi-calendar-event me-2"></i>Kelola Jadwal Tes</h2>
            <p class="text-muted">Atur jadwal tes program dan wawancara</p>
        </div>
        <button class="btn btn-instansi" data-bs-toggle="modal" data-bs-target="#addJadwalModal">
            <i class="bi bi-plus-circle me-2"></i>Tambah Jadwal
        </button>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?> alert-dismissible fade show">
            <?= $message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card card-instansi">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-list me-2"></i>Daftar Jadwal</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Jenis Tes</th>
                            <th>Tanggal</th>
                            <th>Waktu Mulai</th>
                            <th>Waktu Selesai</th>
                            <th>Lokasi</th>
                            <th>Keterangan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($jadwal as $key => $j): ?>
                            <tr>
                                <td><?= $key + 1 ?></td>
                                <td>
                                    <span class="badge <?= $j['jenis_tes'] === 'program' ? 'bg-primary' : 'bg-success' ?>">
                                        <?= ucfirst($j['jenis_tes']) ?>
                                    </span>
                                </td>
                                <td><?= date('d F Y', strtotime($j['tanggal'])) ?></td>
                                <td><?= date('H:i', strtotime($j['waktu_mulai'])) ?></td>
                                <td><?= date('H:i', strtotime($j['waktu_selesai'])) ?></td>
                                <td><?= htmlspecialchars($j['lokasi']) ?></td>
                                <td><?= htmlspecialchars($j['keterangan'] ?? '-') ?></td>
                                <td>
                                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal" 
                                            data-bs-target="#editJadwalModal<?= $j['id'] ?>">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" data-bs-toggle="modal" 
                                            data-bs-target="#deleteJadwalModal<?= $j['id'] ?>">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($jadwal)): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="bi bi-calendar3 display-4 d-block mb-2"></i>
                                    Belum ada jadwal yang dibuat
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Add Jadwal -->
<div class="modal fade" id="addJadwalModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Tambah Jadwal</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="mb-3">
                        <label class="fw-bold">Jenis Tes</label>
                        <select name="jenis_tes" class="form-select" required>
                            <option value="program">Tes Program</option>
                            <option value="wawancara">Wawancara</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold">Tanggal</label>
                        <input type="date" name="tanggal" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Waktu Mulai</label>
                            <input type="time" name="waktu_mulai" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Waktu Selesai</label>
                            <input type="time" name="waktu_selesai" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold">Lokasi</label>
                        <input type="text" name="lokasi" class="form-control" placeholder="Contoh: Lab Komputer 1" required>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold">Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="2" placeholder="Informasi tambahan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-instansi">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit & Delete -->
<?php foreach ($jadwal as $j): ?>
<!-- Edit Modal -->
<div class="modal fade" id="editJadwalModal<?= $j['id'] ?>" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Edit Jadwal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" value="<?= $j['id'] ?>">
                    
                    <div class="mb-3">
                        <label class="fw-bold">Jenis Tes</label>
                        <select name="jenis_tes" class="form-select" required>
                            <option value="program" <?= $j['jenis_tes'] === 'program' ? 'selected' : '' ?>>Tes Program</option>
                            <option value="wawancara" <?= $j['jenis_tes'] === 'wawancara' ? 'selected' : '' ?>>Wawancara</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold">Tanggal</label>
                        <input type="date" name="tanggal" class="form-control" value="<?= $j['tanggal'] ?>" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Waktu Mulai</label>
                            <input type="time" name="waktu_mulai" class="form-control" value="<?= $j['waktu_mulai'] ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Waktu Selesai</label>
                            <input type="time" name="waktu_selesai" class="form-control" value="<?= $j['waktu_selesai'] ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold">Lokasi</label>
                        <input type="text" name="lokasi" class="form-control" value="<?= htmlspecialchars($j['lokasi']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold">Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="2"><?= htmlspecialchars($j['keterangan'] ?? '') ?></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-instansi">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteJadwalModal<?= $j['id'] ?>" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-trash me-2"></i>Hapus Jadwal</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $j['id'] ?>">
                    <p>Apakah Anda yakin ingin menghapus jadwal ini?</p>
                    <p class="text-muted small">
                        <?= ucfirst($j['jenis_tes']) ?> - <?= date('d F Y', strtotime($j['tanggal'])) ?>
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endforeach; ?>

<?php include __DIR__ . '/../../includes/footer.php'; ?>