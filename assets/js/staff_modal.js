// Revoke Staff Modal Functionality
let currentRevokeUserId = null;
let currentConfirmUserId = null;
let currentRejectUserId = null;

console.log('Staff modal script loaded');

// Confirm Staff Modal
function showConfirmModal(userId) {
    console.log('showConfirmModal called with userId:', userId);
    currentConfirmUserId = userId;
    const modal = document.getElementById('confirmModal');
    console.log('confirmModal element:', modal);
    if (modal) {
        modal.style.display = 'flex';
    }
}

function closeConfirmModal() {
    const modal = document.getElementById('confirmModal');
    if (modal) {
        modal.style.display = 'none';
    }
    currentConfirmUserId = null;
}

function confirmStaff() {
    console.log('confirmStaff called with userId:', currentConfirmUserId);
    if (currentConfirmUserId) {
        const form = document.getElementById('confirmForm' + currentConfirmUserId);
        console.log('confirmForm element:', form);
        if (form) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'confirm_staff';
            input.value = '1';
            form.appendChild(input);
            form.submit();
        }
    }
}

// Reject Staff Modal
function showRejectModal(userId) {
    console.log('showRejectModal called with userId:', userId);
    currentRejectUserId = userId;
    const modal = document.getElementById('rejectModal');
    console.log('rejectModal element:', modal);
    if (modal) {
        modal.style.display = 'flex';
    }
}

function closeRejectModal() {
    const modal = document.getElementById('rejectModal');
    if (modal) {
        modal.style.display = 'none';
    }
    currentRejectUserId = null;
}

function confirmReject() {
    console.log('confirmReject called with userId:', currentRejectUserId);
    if (currentRejectUserId) {
        const form = document.getElementById('rejectForm' + currentRejectUserId);
        console.log('rejectForm element:', form);
        if (form) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'reject_staff';
            input.value = '1';
            form.appendChild(input);
            form.submit();
        }
    }
}

// Revoke Staff Modal
function showRevokeModal(userId) {
    console.log('showRevokeModal called with userId:', userId);
    currentRevokeUserId = userId;
    const modal = document.getElementById('revokeModal');
    console.log('revokeModal element:', modal);
    if (modal) {
        modal.style.display = 'flex';
    }
}

function closeRevokeModal() {
    const modal = document.getElementById('revokeModal');
    if (modal) {
        modal.style.display = 'none';
    }
    currentRevokeUserId = null;
}

function confirmRevoke() {
    console.log('confirmRevoke called with userId:', currentRevokeUserId);
    if (currentRevokeUserId) {
        const form = document.getElementById('revokeForm' + currentRevokeUserId);
        console.log('revokeForm element:', form);
        if (form) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'revoke_staff';
            input.value = '1';
            form.appendChild(input);
            form.submit();
        }
    }
}

// Close modals when clicking outside
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOMContentLoaded - setting up modal click handlers');
    const confirmModal = document.getElementById('confirmModal');
    const rejectModal = document.getElementById('rejectModal');
    const revokeModal = document.getElementById('revokeModal');
    
    console.log('Modal elements found:', {
        confirmModal: !!confirmModal,
        rejectModal: !!rejectModal,
        revokeModal: !!revokeModal
    });
    
    if (confirmModal) {
        confirmModal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeConfirmModal();
            }
        });
    }
    
    if (rejectModal) {
        rejectModal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeRejectModal();
            }
        });
    }
    
    if (revokeModal) {
        revokeModal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeRevokeModal();
            }
        });
    }
});
