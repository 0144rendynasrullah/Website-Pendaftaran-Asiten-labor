<?php
// pages/register.php
require_once __DIR__ . '/../config/database.php';

if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $full_name = trim($_POST['full_name'] ?? '');
    $nim = trim($_POST['nim'] ?? '');
    
    $errors = [];
    if (empty($username)) $errors[] = 'Username wajib diisi';
    if (empty($email)) $errors[] = 'Email wajib diisi';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Format email tidak valid';
    if (empty($password)) $errors[] = 'Password wajib diisi';
    if (strlen($password) < 6) $errors[] = 'Password minimal 6 karakter';
    if ($password !== $confirm_password) $errors[] = 'Konfirmasi password tidak cocok';
    if (empty($full_name)) $errors[] = 'Nama lengkap wajib diisi';
    if (empty($nim)) $errors[] = 'NIM wajib diisi';
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ? OR nim = ?");
            $stmt->execute([$username, $email, $nim]);
            
            if ($stmt->rowCount() > 0) {
                $error = 'Username, Email, atau NIM sudah terdaftar!';
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, nim, role) 
                                       VALUES (?, ?, ?, ?, ?, 'pendaftar')");
                $stmt->execute([$username, $email, $hashed_password, $full_name, $nim]);
                
                $success = 'Registrasi berhasil! Silakan <a href="login.php">login</a>.';
                $_POST = [];
            }
        } catch (Exception $e) {
            $error = 'Terjadi kesalahan: ' . $e->getMessage();
        }
    } else {
        $error = implode('<br>', $errors);
    }
}

$base_path = '../';
include __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card card-instansi">
                <div class="card-header text-center">
                    <h4 class="mb-0"><i class="bi bi-person-plus me-2"></i>Registrasi</h4>
                    <p class="mb-0 small opacity-75">Buat akun untuk memulai pendaftaran</p>
                </div>
                <div class="card-body p-4">
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <?= $error ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            <?= $success ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold">Nama Lengkap *</label>
                                <input type="text" name="full_name" class="form-control" 
                                       value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold">NIM *</label>
                                <input type="text" name="nim" class="form-control" 
                                       value="<?= htmlspecialchars($_POST['nim'] ?? '') ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold">Username *</label>
                            <input type="text" name="username" class="form-control" 
                                   value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold">Email *</label>
                            <input type="email" name="email" class="form-control" 
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold">Password *</label>
                                <input type="password" name="password" class="form-control" 
                                       placeholder="Minimal 6 karakter" required minlength="6">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold">Konfirmasi Password *</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-instansi w-100">
                            <i class="bi bi-person-plus me-2"></i>Daftar Sekarang
                        </button>
                    </form>
                    <hr>
                    <div class="text-center">
                        <p class="mb-0">Sudah punya akun? <a href="login.php" class="text-primary fw-bold">Login</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>