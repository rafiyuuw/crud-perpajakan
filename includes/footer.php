        </main>

        <!-- Footer -->
        <footer class="app-footer">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
                <div>
                    <strong>Sistem Informasi Perpajakan</strong> &copy; <?= date('Y') ?> &mdash; Pembelajaran Basis Data SMK RPL
                </div>
                <div class="text-muted small">
                    <span class="badge bg-light text-secondary border">Simulasi Pendidikan</span>
                    Aplikasi ini ditujukan untuk kebutuhan edukasi basis data relasional.
                </div>
            </div>
        </footer>
    </div>
</div>

<!-- Modal Konfirmasi Hapus Global -->
<div class="modal fade" id="modalKonfirmasiHapus" tabindex="-1" aria-labelledby="modalHapusLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="modalHapusLabel"><i class="bi bi-exclamation-triangle-fill me-2"></i>Konfirmasi Hapus Data</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Batal"></button>
            </div>
            <div class="modal-body py-4">
                <p class="mb-1 text-dark fs-6" id="teksKonfirmasiHapus">Apakah Anda yakin ingin menghapus data ini secara permanen?</p>
                <small class="text-danger"><i class="bi bi-info-circle me-1"></i>Data yang berelasi dengan tabel lain tidak dapat dihapus sembarangan untuk menjaga integritas basis data.</small>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="bi bi-x-circle me-1"></i>Batal</button>
                <a href="#" id="btnEksekusiHapus" class="btn btn-danger"><i class="bi bi-trash-fill me-1"></i>Hapus Permanen</a>
            </div>
        </div>
    </div>
</div>

<!-- JS Libraries -->
<script src="<?= base_url('assets/js/bootstrap.bundle.min.js') ?>"></script>
<script src="<?= base_url('assets/js/chart.umd.min.js') ?>"></script>

<script>
    // Toggle Mobile Sidebar
    const sidebarToggle = document.getElementById('sidebarToggle');
    const appSidebar = document.getElementById('appSidebar');
    if (sidebarToggle && appSidebar) {
        sidebarToggle.addEventListener('click', function() {
            appSidebar.classList.toggle('show');
            let backdrop = document.querySelector('.sidebar-backdrop');
            if (appSidebar.classList.contains('show')) {
                if (!backdrop) {
                    backdrop = document.createElement('div');
                    backdrop.className = 'sidebar-backdrop';
                    document.body.appendChild(backdrop);
                    backdrop.addEventListener('click', function() {
                        appSidebar.classList.remove('show');
                        backdrop.remove();
                    });
                }
            } else if (backdrop) {
                backdrop.remove();
            }
        });
    }

    // Handler Konfirmasi Hapus Modal
    function konfirmasiHapus(url, pesan) {
        const modal = new bootstrap.Modal(document.getElementById('modalKonfirmasiHapus'));
        const teks = document.getElementById('teksKonfirmasiHapus');
        const btn = document.getElementById('btnEksekusiHapus');
        if (pesan) {
            teks.innerHTML = pesan;
        } else {
            teks.innerHTML = 'Apakah Anda yakin ingin menghapus data ini secara permanen?';
        }
        btn.setAttribute('href', url);
        modal.show();
        return false;
    }
</script>
</body>
</html>
