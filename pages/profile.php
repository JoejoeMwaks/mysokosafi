<?php
// pages/profile.php

if (!isset($_SESSION['user'])) {
    echo '
    <section class="container py-5">
        <div class="text-center py-5">
            <div class="mb-4">
                <i class="fas fa-lock fa-4x text-muted"></i>
            </div>
            <h2 class="fw-bold mb-3">My Profile</h2>
            <p class="text-muted mb-4">Please login to view your profile</p>
            <a href="index.php?page=login" class="btn btn-primary btn-lg">
                <i class="fas fa-sign-in-alt me-2"></i>Login to Continue
            </a>
        </div>
    </section>';
    return;
}

$user = $_SESSION['user'];
$is_admin = isset($user['roles']) && in_array('admin', $user['roles']);

?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <h2 class="mb-4">My Profile</h2>
            
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="card-title fw-semibold mb-3">Account Details</h5>
                    <div class="mb-3">
                        <label class="text-muted small mb-1">Name</label>
                        <p class="mb-0 fw-medium"><?php echo htmlspecialchars($user['name']); ?></p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small mb-1">Email Address</label>
                        <p class="mb-0 fw-medium"><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>
                    <div>
                        <label class="text-muted small mb-1">Account Type</label>
                        <p class="mb-0 fw-medium">
                            <?php if ($is_admin): ?>
                                <span class="badge bg-primary text-white">Administrator</span>
                            <?php
else: ?>
                                <span class="badge bg-secondary text-white">Customer</span>
                            <?php
endif; ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Danger Zone -->
            <div class="card border-danger shadow-sm">
                <div class="card-body p-4">
                    <h5 class="card-title fw-semibold text-danger mb-3">Danger Zone</h5>
                    <p class="text-muted small mb-4">Once you delete your account, there is no going back. Please be certain.</p>
                    
                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                        Delete Account
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Delete Account Modal -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-danger text-white border-0">
        <h5 class="modal-title" id="deleteAccountModalLabel">Confirm Account Deletion</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <p class="mb-0">Are you absolutely sure you want to permanently delete your account? All your personal information will be removed.</p>
      </div>
      <div class="modal-footer bg-light border-0">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <form method="post" action="index.php?page=profile">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <input type="hidden" name="action" value="delete_account">
            <button type="submit" class="btn btn-danger">Yes, Delete My Account</button>
        </form>
      </div>
    </div>
  </div>
</div>
