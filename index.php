<?php
require_once __DIR__ . '/config/database.php';

$periode_aktif = false;
$stmt = $pdo->query("SELECT * FROM periode_pendaftaran WHERE is_active = 1 AND tanggal_buka <= CURDATE() AND tanggal_tutup >= CURDATE()");
if ($stmt->rowCount() > 0) {
    $periode_aktif = $stmt->fetch();
}

$base_path = '';
include __DIR__ . '/includes/header.php';
?>

<!-- ===== HERO SECTION DENGAN BACKGROUND GEDUNG ===== -->
<section style="
    background: linear-gradient(135deg, rgba(10, 46, 92, 0.85) 0%, rgba(26, 74, 122, 0.75) 100%), 
                url('assets/img/gedung.jpg') center/cover no-repeat;
    color: #fff; 
    padding: 80px 0; 
    min-height: 70vh;
    position: relative;
">
    <div class="container" style="position: relative; z-index: 2;">
        <div class="row align-items-center">
            
            <!-- ===== KOLOM KIRI: TEKS ===== -->
            <div class="col-lg-7">
                <span class="badge bg-warning text-dark px-3 py-2 mb-3">
                    <i class="bi bi-megaphone me-1"></i> PENERIMAAN ASISTEN LABORATORIUM
                </span>
                <h1 class="display-4 fw-bold">Pendaftaran Asisten Laboratorium</h1>
                <p class="lead mb-4">Universitas Putra Indonesia YPTK Padang</p>
                
                <?php if ($periode_aktif): ?>
                    <div class="alert alert-success bg-success bg-opacity-25 border-0 text-white">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        Pendaftaran DIBUKA hingga <?= date('d F Y', strtotime($periode_aktif['tanggal_tutup'])) ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-danger bg-danger bg-opacity-25 border-0 text-white">
                        <i class="bi bi-clock-fill me-2"></i>
                        Pendaftaran DITUTUP
                    </div>
                <?php endif; ?>

                <div class="d-flex gap-3 flex-wrap">
                    <?php if (isLoggedIn()): ?>
                        <a href="pages/dashboard.php" class="btn btn-gold btn-lg px-4">
                            <i class="bi bi-speedometer2 me-2"></i>Dashboard
                        </a>
                    <?php else: ?>
                        <a href="pages/login.php" class="btn btn-gold btn-lg px-4">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Login
                        </a>
                        <a href="pages/register.php" class="btn btn-outline-light btn-lg px-4">
                            <i class="bi bi-person-plus me-2"></i>Daftar
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- ===== KOLOM KANAN: LOGO UPI WARNA ASLI ===== -->
            <div class="col-lg-5 text-center">
                <div class="bg-white bg-opacity-10 rounded-4 p-4 backdrop-blur">
                    
                    <!-- ===== LOGO UPI WARNA ASLI ===== -->
                    <img src="assets/img/logo-upi.png" 
                         alt="Logo UPI YPTK" 
                         style="max-height: 120px; width: auto;"
                         onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22200%22%3E%3Crect fill=%22%23fff%22 width=%22200%22 height=%22200%22/%3E%3Ctext x=%2250%%22 y=%2250%%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%22%231a4a7a%22 font-family=%22Arial%22 font-size=%2220%22 font-weight=%22bold%22%3EUPI YPTK%3C/text%3E%3C/svg%3E'">
                    
                    <div class="mt-3">
                        <h5 class="text-gold">Universitas Putra Indonesia</h5>
                        <p class="mb-0" style="opacity: 0.9;">Fakultas Ilmu Komputer</p>
                        <small style="opacity: 0.7;">Jl. Raya Lubuk Begalung, Padang</small>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</section>

<!-- ===== ALUR PENDAFTARAN ===== -->
<section class="py-5">
    <div class="container">
        <h2 class="text-center text-primary-dark mb-5">Alur Pendaftaran</h2>
        <div class="row g-4">
            <div class="col-md-3">
                <div class="card h-100 text-center border-0 shadow-sm">
                    <div class="card-body py-4">
                        <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3" style="width: 70px; height: 70px;">
                            <i class="bi bi-person-plus fs-2 text-primary m-auto"></i>
                        </div>
                        <h5>1. Registrasi</h5>
                        <p class="text-muted small">Buat akun dengan data diri</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100 text-center border-0 shadow-sm">
                    <div class="card-body py-4">
                        <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3" style="width: 70px; height: 70px;">
                            <i class="bi bi-file-earmark-text fs-2 text-success m-auto"></i>
                        </div>
                        <h5>2. Pendaftaran</h5>
                        <p class="text-muted small">Isi data dan upload berkas</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100 text-center border-0 shadow-sm">
                    <div class="card-body py-4">
                        <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3" style="width: 70px; height: 70px;">
                            <i class="bi bi-check-circle fs-2 text-warning m-auto"></i>
                        </div>
                        <h5>3. Seleksi</h5>
                        <p class="text-muted small">Tes program dan wawancara</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100 text-center border-0 shadow-sm">
                    <div class="card-body py-4">
                        <div class="bg-info bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3" style="width: 70px; height: 70px;">
                            <i class="bi bi-trophy fs-2 text-info m-auto"></i>
                        </div>
                        <h5>4. Pengumuman</h5>
                        <p class="text-muted small">Hasil di dashboard</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== INFORMASI & PERSYARATAN ===== -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row g-4">
            
            <!-- ===== INFORMASI PENDAFTARAN ===== -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="text-primary-dark"><i class="bi bi-info-circle me-2"></i>Informasi Pendaftaran</h5>
                        <ul class="list-unstyled mt-3">
                            <li class="py-2 border-bottom">
                                <i class="bi bi-check-circle-fill text-success me-2"></i> 
                                Pendaftaran dilakukan secara online
                            </li>
                            <li class="py-2 border-bottom">
                                <i class="bi bi-check-circle-fill text-success me-2"></i> 
                                Berkas diupload dalam format PDF/JPG
                            </li>
                            <li class="py-2 border-bottom">
                                <i class="bi bi-check-circle-fill text-success me-2"></i> 
                                Tes program dan wawancara terjadwal
                            </li>
                            <li class="py-2">
                                <i class="bi bi-check-circle-fill text-success me-2"></i> 
                                Pengumuman kelulusan di sistem
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- ===== PERSYARATAN ===== -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="text-primary-dark"><i class="bi bi-file-pdf me-2"></i>Persyaratan</h5>
                        <ul class="list-unstyled mt-3">
                            <li class="py-2 border-bottom">
                                <i class="bi bi-file-earmark-check text-primary me-2"></i> 
                                CV / Daftar Riwayat Hidup
                            </li>
                            <li class="py-2 border-bottom">
                                <i class="bi bi-file-earmark-check text-primary me-2"></i> 
                                Surat Lamaran
                            </li>
                            <li class="py-2 border-bottom">
                                <i class="bi bi-file-earmark-check text-primary me-2"></i> 
                                Transkrip Nilai
                            </li>
                            <li class="py-2">
                                <i class="bi bi-file-earmark-check text-primary me-2"></i> 
                                Pas Foto 3x4
                            </li>
                        </ul>
                        <!-- ===== TAMBAHAN INFORMASI ===== -->
                        <div class="alert alert-info mt-3">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Catatan:</strong> Surat Lamaran dapat dibuat sendiri dan ditandatangani oleh pelamar.
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>