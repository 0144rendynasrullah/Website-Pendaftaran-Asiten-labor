<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$base_path = $base_path ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sistem Pendaftaran Asisten Laboratorium</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f0f4f8; }
        
        .navbar-instansi {
            background: linear-gradient(135deg, #0a2e5c, #1a4a7a);
            border-bottom: 3px solid #c9a84c;
            padding: 0.6rem 0;
        }
        .navbar-instansi .navbar-brand {
            color: #fff !important;
            font-weight: 700;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .navbar-instansi .navbar-brand img {
            border-radius: 50%;
            border: 2px solid #c9a84c;
            object-fit: cover;
            background: #fff;
            padding: 3px;
        }
        .navbar-instansi .navbar-brand .brand-text {
            font-size: 1rem;
            letter-spacing: 0.5px;
        }
        .navbar-instansi .navbar-brand .brand-text small {
            font-weight: 400;
            opacity: 0.7;
            font-size: 0.7rem;
            display: block;
            letter-spacing: 1px;
        }
        .navbar-instansi .nav-link {
            color: rgba(255,255,255,0.85) !important;
            font-size: 0.9rem;
        }
        .navbar-instansi .nav-link:hover {
            color: #c9a84c !important;
        }
        .text-gold { color: #c9a84c; }
        .btn-gold { background: #c9a84c; color: #fff; border: none; }
        .btn-gold:hover { background: #b8943a; color: #fff; }
        .btn-instansi { background: #1a4a7a; color: #fff; border: none; }
        .btn-instansi:hover { background: #0a2e5c; color: #fff; }
        .badge-role { font-size: 10px; padding: 2px 8px; border-radius: 50px; margin-left: 5px; }
        .badge-role.admin { background: #dc3545; color: #fff; }
        .badge-role.kepala_lab { background: #28a745; color: #fff; }
        .badge-role.pendaftar { background: #17a2b8; color: #fff; }
        .footer-instansi { background: #0a2e5c; color: rgba(255,255,255,0.7); padding: 2rem 0; border-top: 3px solid #c9a84c; margin-top: 3rem; }
        .footer-instansi a { color: #c9a84c; text-decoration: none; }
        .backdrop-blur { backdrop-filter: blur(10px); }

        .logo-aslab-nav {
            width: 55px;
            height: 55px;
            border-radius: 50%;
            border: 2.5px solid #c9a84c;
            object-fit: cover;
            background: #fff;
            padding: 3px;
            transition: transform 0.3s;
        }
        .logo-aslab-nav:hover {
            transform: scale(1.08);
        }
    </style>
</head>
<body>

<!-- ===== NAVBAR ===== -->
<nav class="navbar navbar-expand-lg navbar-instansi sticky-top">
    <div class="container">
        <a class="navbar-brand" href="<?= $base_path ?>index.php">
            <img src="<?= $base_path ?>assets/img/logo-aslab.png" 
                 alt="Logo Aslab" 
                 class="logo-aslab-nav"
                 onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2255%22 height=%2255%22%3E%3Crect fill=%22%231a4a7a%22 width=%2255%22 height=%2255%22/%3E%3Ctext x=%2250%%22 y=%2250%%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%22%23c9a84c%22 font-family=%22Arial%22 font-size=%2216%22 font-weight=%22bold%22%3EASLAB%3C/text%3E%3C/svg%3E'">
            <div class="brand-text">
                ASISTEN LABORATORIUM
                <small>UNIVERSITAS PUTRA INDONESIA YPTK PADANG</small>
            </div>
        </a>
        
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?= $base_path ?>index.php"><i class="bi bi-house"></i> Beranda</a>
                </li>
                
                <?php if (isLoggedIn()): ?>
                    <?php $role = getUserRole(); ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $base_path ?>pages/dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
                    </li>
                    <?php if ($role === 'pendaftar'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $base_path ?>pages/pendaftaran.php"><i class="bi bi-file-earmark-text"></i> Pendaftaran</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $base_path ?>pages/status.php"><i class="bi bi-clipboard-data"></i> Status</a>
                        </li>
                    <?php endif; ?>
                    <?php if ($role === 'admin'): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown"><i class="bi bi-gear"></i> Admin</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="<?= $base_path ?>pages/admin/verifikasi.php">Verifikasi</a></li>
                                <li><a class="dropdown-item" href="<?= $base_path ?>pages/admin/jadwal.php">Jadwal</a></li>
                                <li><a class="dropdown-item" href="<?= $base_path ?>pages/admin/input_nilai.php">Input Nilai</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?= $base_path ?>pages/admin/laporan.php">Laporan</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                    <?php if ($role === 'kepala_lab'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $base_path ?>pages/kepala-lab/dashboard.php"><i class="bi bi-check2-square"></i> Validasi</a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?= htmlspecialchars(getUserName()) ?>
                            <span class="badge-role <?= $role ?>"><?= ucfirst($role) ?></span>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?= $base_path ?>pages/profile.php"><i class="bi bi-person"></i> Profil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?= $base_path ?>pages/logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?= $base_path ?>pages/login.php"><i class="bi bi-box-arrow-in-right"></i> Login</a></li>
                    <li class="nav-item"><a class="nav-link btn btn-gold text-white px-3 rounded-pill" href="<?= $base_path ?>pages/register.php"><i class="bi bi-person-plus"></i> Daftar</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>