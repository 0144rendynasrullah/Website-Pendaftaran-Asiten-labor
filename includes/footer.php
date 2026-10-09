<?php
// includes/footer.php
?>
    <!-- FOOTER -->
    <footer class="footer-instansi mt-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <h5 class="text-gold mb-3">
                        <i class="bi bi-laptop me-2"></i>Sistem Aslab
                    </h5>
                    <p class="small">
                        Sistem Informasi Pendaftaran Asisten Laboratorium<br>
                        <strong>Universitas Putra Indonesia YPTK Padang</strong>
                    </p>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-white-50"><i class="bi bi-facebook fs-5"></i></a>
                        <a href="#" class="text-white-50"><i class="bi bi-instagram fs-5"></i></a>
                        <a href="#" class="text-white-50"><i class="bi bi-youtube fs-5"></i></a>
                    </div>
                </div>
                <div class="col-md-4">
                    <h5 class="text-gold mb-3">Kontak</h5>
                    <ul class="list-unstyled small">
                        <li class="mb-2"><i class="bi bi-geo-alt me-2"></i>Jl. Raya Lubuk Begalung, Padang</li>
                        <li class="mb-2"><i class="bi bi-telephone me-2"></i>+62 751 123456</li>
                        <li class="mb-2"><i class="bi bi-envelope me-2"></i>info@upiyptk.ac.id</li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5 class="text-gold mb-3">Link Terkait</h5>
                    <ul class="list-unstyled small">
                        <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none">Beranda</a></li>
                        <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none">Login</a></li>
                        <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none">Registrasi</a></li>
                        <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none">Cek Status</a></li>
                    </ul>
                </div>
            </div>
            <hr class="border-secondary opacity-25 my-4">
            <div class="text-center small">
                &copy; <?= date('Y') ?> UPI YPTK Padang. All Rights Reserved.
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="<?= isset($base_path) ? $base_path : '../' ?>assets/js/script.js"></script>
</body>
</html>