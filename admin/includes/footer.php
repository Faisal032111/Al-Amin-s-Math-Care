<?php

/**
 * admin/includes/footer.php — Admin Panel Footer Template
 * Al Amin's Math Care | alaminmathcare.com
 */

declare(strict_types=1);
?>
            <footer class="mt-auto pt-4 pb-2 text-center text-muted small border-top">
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
                    <div>
                        &copy; <?= date('Y') ?> <strong>Al Amin's Math Care</strong>. All Rights Reserved.
                    </div>
                    <div>
                        <span class="badge bg-light text-secondary border">Farmgate Campus • v2.0 (Phase 6)</span>
                    </div>
                </div>
            </footer>
        </main>
    </div>
</div>

<!-- Global Generic Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-body text-center p-4">
                <div class="text-danger mb-3">
                    <i class="bi bi-exclamation-triangle-fill" style="font-size: 3rem;"></i>
                </div>
                <h5 class="fw-bold mb-2">Confirm Delete</h5>
                <p class="text-muted small mb-4" id="deleteModalMessage">Are you sure you want to delete this record? This action cannot be undone.</p>
                <form id="deleteModalForm" method="POST" action="">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" id="deleteModalId" value="">
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-sm btn-light border px-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-danger px-3">Yes, Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function confirmDelete(url, id, message) {
        const form = document.getElementById('deleteModalForm');
        const idInput = document.getElementById('deleteModalId');
        const msg = document.getElementById('deleteModalMessage');
        
        if (form && idInput) {
            form.action = url;
            idInput.value = id;
            if (message && msg) {
                msg.textContent = message;
            }
            const modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
            modal.show();
        }
    }
</script>
</body>
</html>
