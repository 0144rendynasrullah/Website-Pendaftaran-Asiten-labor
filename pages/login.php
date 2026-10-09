<?php
// pages/login.php
require_once __DIR__ . '/../config/database.php';

// Jika sudah login, redirect ke dashboard
if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Username/email dan password wajib diisi!';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['nim'] = $user['nim'];
                
                // Redirect berdasarkan role
                if ($user['role'] === 'admin') {
                    header("Location: admin/dashboard.php");
                } elseif ($user['role'] === 'kepala_lab') {
                    header("Location: kepala-lab/dashboard.php");
                } else {
                    header("Location: dashboard.php");
                }
                exit();
            } else {
                $error = 'Username/email atau password salah!';
            }
        } catch (Exception $e) {
            $error = 'Terjadi kesalahan: ' . $e->getMessage();
        }
    }
}

$base_path = '../';
include __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card card-instansi">
                <div class="card-header text-center">
                    <h4 class="mb-0"><i class="bi bi-box-arrow-in-right me-2"></i>Login</h4>
                    <p class="mb-0 small opacity-75">Sistem Pendaftaran Asisten Laboratorium</p>
                </div>
                <div class="card-body p-4">
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <?= $error ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="fw-bold">Username / Email</label>
                            <input type="text" name="username" class="form-control" 
                                   placeholder="Masukkan username atau email" 
                                   value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required autofocus>
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold">Password</label>
                            <input type="password" name="password" class="form-control" 
                                   placeholder="Masukkan password" required>
                        </div>
                        <button type="submit" class="btn btn-instansi w-100">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Login
                        </button>
                    </form>
                    <hr>
                    <div class="text-center">
                        <p class="mb-2">Belum punya akun? <a href="register.php" class="text-primary fw-bold">Daftar Sekarang</a></p>
                        <small class="text-muted">
                            <i class="bi bi-info-circle"></i> 
                            Demo: admin / password | kepala_lab / password
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>