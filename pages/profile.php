<?php
// pages/profile.php
require_once __DIR__ . '/../config/database.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$message = '';
$messageType = '';

// Get user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Get pendaftaran data if exists
$stmt = $pdo->prepare("SELECT * FROM pendaftaran WHERE user_id = ?");
$stmt->execute([$user_id]);
$pendaftaran = $stmt->fetch();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action === 'update_profile') {
        $full_name = trim($_POST['full_name']);
        $email = trim($_POST['email']);
        $no_hp = trim($_POST['no_hp'] ?? '');
        $alamat = trim($_POST['alamat'] ?? '');
        
        try {
            // Check if email already used by another user
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $user_id]);
            if ($stmt->rowCount() > 0) {
                $message = "Email sudah digunakan oleh pengguna lain!";
                $messageType = 'danger';
            } else {
                $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ? WHERE id = ?");
                $stmt->execute([$full_name, $email, $user_id]);
                
                // Update session
                $_SESSION['full_name'] = $full_name;
                
                // Update pendaftaran if exists
                if ($pendaftaran) {
                    $stmt = $pdo->prepare("UPDATE pendaftaran SET nama_lengkap = ?, no_hp = ?, alamat = ? WHERE user_id = ?");
                    $stmt->execute([$full_name, $no_hp, $alamat, $user_id]);
                }
                
                $message = "Profil berhasil diperbarui!";
                $messageType = 'success';
                
                // Refresh data
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $user = $stmt->fetch();
            }
        } catch (Exception $e) {
            $message = "Error: " . $e->getMessage();
            $messageType = 'danger';
        }
    }
    
    if ($action === 'change_password') {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Verify current password
        if (!password_verify($current_password, $user['password'])) {
            $message = "Password saat ini salah!";
            $messageType = 'danger';
        } elseif (strlen($new_password) < 6) {
            $message = "Password baru minimal 6 karakter!";
            $messageType = 'danger';
        } elseif ($new_password !== $confirm_password) {
            $message = "Konfirmasi password tidak cocok!";
            $messageType = 'danger';
        } else {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed, $user_id]);
            $message = "Password berhasil diubah!";
            $messageType = 'success';
        }
    }
}

$base_path = '../';
include __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <div class="row">
        <div class="col-md-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Profil</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3">
            <div class="card card-instansi mb-4">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <div class="avatar-circle bg-primary text-white mx-auto" style="width: 100px; height: 100px; font-size: 40px; line-height: 100px; border-radius: 50%;">
                            <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
                        </div>
                    </div>
                    <h5 class="mb-1"><?= htmlspecialchars($user['full_name']) ?></h5>
                    <p class="text-muted small"><?= htmlspecialchars($user['email']) ?></p>
                    <span class="badge bg-primary"><?= ucfirst(str_replace('_', ' ', $user['role'])) ?></span>
                    <hr>
                    <div class="text-start small">
                        <p><i class="bi bi-person me-2"></i>Username: <strong><?= htmlspecialchars($user['username']) ?></strong></p>
                        <p><i class="bi bi-card-text me-2"></i>NIM: <strong><?= htmlspecialchars($user['nim']) ?></strong></p>
                        <p><i class="bi bi-calendar3 me-2"></i>Bergabung: <strong><?= date('d F Y', strtotime($user['created_at'])) ?></strong></p>
                    </div>
                </div>
            </div>
            
            <?php if ($pendaftaran): ?>
            <div class="card card-instansi">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>Status Pendaftaran</h6>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <span class="badge badge-status <?= $pendaftaran['status_seleksi'] ?> d-block p-2 mb-2" style="font-size: 1rem;">
                            <?= ucfirst(str_replace('_', ' ', $pendaftaran['status_seleksi'])) ?>
                        </span>
                        <small class="text-muted">No. Pendaftaran: <?= htmlspecialchars($pendaftaran['no_pendaftaran']) ?></small>
                    </div>
                    <hr>
                    <div class="d-grid">
                        <a href="status.php" class="btn btn-sm btn-instansi">
                            <i class="bi bi-eye me-2"></i>Lihat Detail
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Main Content -->
        <div class="col-md-9">
            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?> alert-dismissible fade show">
                    <?= $message ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Tab Navigation -->
            <ul class="nav nav-tabs mb-4" id="profileTabs">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#editProfile">
                        <i class="bi bi-person me-2"></i>Edit Profil
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#changePassword">
                        <i class="bi bi-key me-2"></i>Ubah Password
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#activityLog">
                        <i class="bi bi-clock-history me-2"></i>Aktivitas
                    </a>
                </li>
            </ul>

            <div class="tab-content">
                <!-- Tab 1: Edit Profile -->
                <div class="tab-pane fade show active" id="editProfile">
                    <div class="card card-instansi">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-pencil me-2"></i>Edit Data Diri</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="action" value="update_profile">
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="fw-bold">Nama Lengkap *</label>
                                        <input type="text" name="full_name" class="form-control" 
                                               value="<?= htmlspecialchars($user['full_name']) ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="fw-bold">Email *</label>
                                        <input type="email" name="email" class="form-control" 
                                               value="<?= htmlspecialchars($user['email']) ?>" required>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="fw-bold">Username</label>
                                        <input type="text" class="form-control" 
                                               value="<?= htmlspecialchars($user['username']) ?>" disabled>
                                        <small class="text-muted">Username tidak dapat diubah</small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="fw-bold">NIM</label>
                                        <input type="text" class="form-control" 
                                               value="<?= htmlspecialchars($user['nim']) ?>" disabled>
                                        <small class="text-muted">NIM tidak dapat diubah</small>
                                    </div>
                                </div>
                                
                                <?php if ($pendaftaran): ?>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="fw-bold">No HP</label>
                                        <input type="text" name="no_hp" class="form-control" 
                                               value="<?= htmlspecialchars($pendaftaran['no_hp'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="fw-bold">Alamat</label>
                                        <textarea name="alamat" class="form-control" rows="2"><?= htmlspecialchars($pendaftaran['alamat'] ?? '') ?></textarea>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <hr>
                                <button type="submit" class="btn btn-instansi">
                                    <i class="bi bi-save me-2"></i>Simpan Perubahan
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Tab 2: Change Password -->
                <div class="tab-pane fade" id="changePassword">
                    <div class="card card-instansi">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-key me-2"></i>Ubah Password</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="action" value="change_password">
                                
                                <div class="mb-3">
                                    <label class="fw-bold">Password Saat Ini *</label>
                                    <input type="password" name="current_password" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="fw-bold">Password Baru *</label>
                                    <input type="password" name="new_password" class="form-control" required minlength="6">
                                    <small class="text-muted">Minimal 6 karakter</small>
                                </div>
                                <div class="mb-3">
                                    <label class="fw-bold">Konfirmasi Password Baru *</label>
                                    <input type="password" name="confirm_password" class="form-control" required>
                                </div>
                                
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>
                                    Password akan dienkripsi secara aman sebelum disimpan.
                                </div>
                                
                                <button type="submit" class="btn btn-warning">
                                    <i class="bi bi-arrow-clockwise me-2"></i>Ubah Password
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Tab 3: Activity Log -->
                <div class="tab-pane fade" id="activityLog">
                    <div class="card card-instansi">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Log Aktivitas</h5>
                        </div>
                        <div class="card-body">
                            <?php
                            // Get activity logs
                            $stmt = $pdo->prepare("SELECT * FROM logs_aktivitas WHERE user_id = ? ORDER BY created_at DESC LIMIT 20");
                            $stmt->execute([$user_id]);
                            $logs = $stmt->fetchAll();
                            ?>
                            
                            <?php if ($logs): ?>
                                <div class="list-group">
                                    <?php foreach ($logs as $log): ?>
                                        <div class="list-group-item list-group-item-action">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <i class="bi bi-<?= $log['aktivitas'] === 'login' ? 'box-arrow-in-right text-success' : 'box-arrow-right text-warning' ?> me-2"></i>
                                                    <strong><?= ucfirst($log['aktivitas']) ?></strong>
                                                    <?php if ($log['detail']): ?>
                                                        <span class="text-muted ms-2">- <?= htmlspecialchars($log['detail']) ?></span>
                                                    <?php endif; ?>
                                                </div>
                                                <small class="text-muted"><?= date('d/m/Y H:i', strtotime($log['created_at'])) ?></small>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="bi bi-inbox display-4 text-muted"></i>
                                    <p class="text-muted mt-2">Belum ada aktivitas tercatat</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-circle {
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    text-transform: uppercase;
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>