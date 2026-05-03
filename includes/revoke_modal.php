<!-- Revoke Confirmation Modal -->
<div class="modal-overlay" id="revokeModal">
    <div class="modal-content confirm-modal">
        <div class="confirm-modal-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h3>Revoke Staff Access?</h3>
        <p>This staff member will not be able to log in until their account is re-approved. Are you sure you want to continue?</p>
        <div class="confirm-modal-actions">
            <button type="button" class="btn-cancel" onclick="closeRevokeModal()">Cancel</button>
            <button type="button" class="btn-warning" onclick="confirmRevoke()">
                <i class="fas fa-ban"></i> Revoke Access
            </button>
        </div>
    </div>
</div>

<script src="../assets/js/staff_modal.js"></script>
