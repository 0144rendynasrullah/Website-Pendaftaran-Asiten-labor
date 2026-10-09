<?php
// pages/pendaftaran.php
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
$message = '';
$messageType = '';

$stmt = $pdo->prepare("SELECT * FROM pendaftaran WHERE user_id = ?");
$stmt->execute([$user_id]);
$existing = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$existing) {
    $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
    $nim = trim($_POST['nim'] ?? '');
    $prodi = trim($_POST['prodi'] ?? '');
    $semester = (int)($_POST['semester'] ?? 0);
    $ipk = (float)($_POST['ipk'] ?? 0);
    $no_hp = trim($_POST['no_hp'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    
    $errors = [];
    if (empty($nama_lengkap)) $errors[] = 'Nama lengkap wajib diisi';
    if (empty($nim)) $errors[] = 'NIM wajib diisi';
    if (empty($prodi)) $errors[] = 'Program studi wajib dipilih';
    if ($semester < 1 || $semester > 8) $errors[] = 'Semester tidak valid';
    if ($ipk < 0 || $ipk > 4) $errors[] = 'IPK tidak valid';
    if (empty($no_hp)) $errors[] = 'No HP wajib diisi';
    
    // ===== FOLDER UPLOADS =====
    $upload_dir = __DIR__ . '/../uploads/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // ===== FUNGSI UPLOAD FILE =====
    function uploadFile($file, $prefix, $nim, $upload_dir) {
        if (isset($file) && $file['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
            if (!in_array($ext, $allowed)) {
                return ['error' => 'Format file tidak diizinkan (PDF, JPG, PNG, DOC)'];
            }
            if ($file['size'] > 2 * 1024 * 1024) {
                return ['error' => 'Ukuran file maksimal 2MB'];
            }
            $filename = $prefix . '_' . $nim . '_' . time() . '.' . $ext;
            $target = $upload_dir . $filename;
            if (move_uploaded_file($file['tmp_name'], $target)) {
                return ['path' => 'uploads/' . $filename];
            }
            return ['error' => 'Gagal upload file'];
        }
        return ['path' => ''];
    }
    
    // Upload semua file
    $cv = uploadFile($_FILES['cv'] ?? null, 'cv', $nim, $upload_dir);
    $surat_lamaran = uploadFile($_FILES['surat_lamaran'] ?? null, 'surat', $nim, $upload_dir);
    $transkrip = uploadFile($_FILES['transkrip'] ?? null, 'transkrip', $nim, $upload_dir);
    $foto = uploadFile($_FILES['foto'] ?? null, 'foto', $nim, $upload_dir);
    
    // Cek error upload
    if (isset($cv['error'])) $errors[] = 'CV: ' . $cv['error'];
    if (isset($surat_lamaran['error'])) $errors[] = 'Surat Lamaran: ' . $surat_lamaran['error'];
    if (isset($transkrip['error'])) $errors[] = 'Transkrip: ' . $transkrip['error'];
    if (isset($foto['error'])) $errors[] = 'Foto: ' . $foto['error'];
    
    if (empty($errors)) {
        try {
            $no_pendaftaran = 'REG-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            
            $stmt = $pdo->prepare("INSERT INTO pendaftaran 
                (user_id, no_pendaftaran, nama_lengkap, nim, prodi, semester, ipk, no_hp, alamat, 
                 cv_path, ktp_path, transkrip_path, foto_path, status_berkas, status_seleksi) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'pending')");
            
            $stmt->execute([
                $user_id,
                $no_pendaftaran,
                $nama_lengkap,
                $nim,
                $prodi,
                $semester,
                $ipk,
                $no_hp,
                $alamat,
                $cv['path'] ?? '',
                $surat_lamaran['path'] ?? '',
                $transkrip['path'] ?? '',
                $foto['path'] ?? ''
            ]);
            
            $message = "Pendaftaran berhasil! No Pendaftaran: <strong>$no_pendaftaran</strong>";
            $messageType = 'success';
            
            $stmt = $pdo->prepare("SELECT * FROM pendaftaran WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $existing = $stmt->fetch();
            
        } catch (Exception $e) {
            $message = "Error: " . $e->getMessage();
            $messageType = 'danger';
        }
    } else {
        $message = implode('<br>', $errors);
        $messageType = 'danger';
    }
}

$base_path = '../';
include __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card card-instansi">
                <div class="card-header">
                    <h4 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>Form Pendaftaran Asisten Laboratorium</h4>
                </div>
                <div class="card-body">
                    <?php if ($message): ?>
                        <div class="alert alert-<?= $messageType ?> alert-dismissible fade show">
                            <?= $message ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($existing): ?>
                        <div class="text-center py-4">
                            <div class="mb-3">
                                <i class="bi bi-check-circle-fill text-success display-1"></i>
                            </div>
                            <h5>Anda sudah mendaftar!</h5>
                            <p class="text-muted">No Pendaftaran: <strong><?= htmlspecialchars($existing['no_pendaftaran']) ?></strong></p>
                            <p>Status: 
                                <span class="badge badge-status <?= $existing['status_seleksi'] ?>">
                                    <?= ucfirst(str_replace('_', ' ', $existing['status_seleksi'])) ?>
                                </span>
                            </p>
                            <a href="status.php" class="btn btn-instansi">
                                <i class="bi bi-clipboard-data me-2"></i>Lihat Status
                            </a>
                        </div>
                    <?php else: ?>
                        <form method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="fw-bold">Nama Lengkap *</label>
                                    <input type="text" name="nama_lengkap" class="form-control" 
                                           value="<?= htmlspecialchars($_SESSION['full_name'] ?? '') ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="fw-bold">NIM *</label>
                                    <input type="text" name="nim" class="form-control" 
                                           value="<?= htmlspecialchars($_SESSION['nim'] ?? '') ?>" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="fw-bold">Program Studi *</label>
                                    <select name="prodi" class="form-select" required>
                                        <option value="">Pilih Prodi</option>
                                        <option value="Sistem Informasi">Sistem Informasi</option>
                                        <option value="Informatika">Informatika</option>
                                        <option value="Teknik Komputer">Teknik Komputer</option>
                                        <option value="Manajemen Informatika">Manajemen Informatika</option>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="fw-bold">Semester *</label>
                                    <input type="number" name="semester" class="form-control" min="1" max="8" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="fw-bold">IPK *</label>
                                    <input type="number" name="ipk" class="form-control" step="0.01" min="0" max="4" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="fw-bold">No HP *</label>
                                    <input type="text" name="no_hp" class="form-control" placeholder="08xxxxxxxxxx" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="fw-bold">Alamat</label>
                                    <textarea name="alamat" class="form-control" rows="2" placeholder="Alamat lengkap"></textarea>
                                </div>
                            </div>
                            
                            <hr>
                            <h6 class="fw-bold mb-3"><i class="bi bi-upload me-2"></i>Upload Berkas (Max 2MB)</h6>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="fw-bold">CV (PDF/DOC)</label>
                                    <input type="file" name="cv" class="form-control" accept=".pdf,.doc,.docx">
                                    <small class="text-muted">Upload CV / Daftar Riwayat Hidup</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="fw-bold">Surat Lamaran (PDF/JPG) *</label>
                                    <input type="file" name="surat_lamaran" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                                    <small class="text-muted">Surat lamaran yang ditandatangani</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="fw-bold">Transkrip Nilai (PDF)</label>
                                    <input type="file" name="transkrip" class="form-control" accept=".pdf">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="fw-bold">Pas Foto (JPG/PNG)</label>
                                    <input type="file" name="foto" class="form-control" accept=".jpg,.jpeg,.png">
                                </div>
                            </div>
                            
                            <div class="alert alert-info mt-3">
                                <i class="bi bi-info-circle me-2"></i>
                                <strong>Catatan:</strong> 
                                <br>• Surat Lamaran wajib diupload
                                <br>• Format yang diterima: PDF, JPG, PNG, DOC
                                <br>• Maksimal ukuran file: <strong>2MB</strong>
                            </div>
                            
                            <button type="submit" class="btn btn-instansi w-100">
                                <i class="bi bi-send me-2"></i>Submit Pendaftaran
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>