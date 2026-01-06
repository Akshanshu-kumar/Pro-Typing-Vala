<?php
$pageTitle = "Premium Subscription";
require_once 'includes/header.php';
?>

<div class="container my-5">
    <div class="text-center mb-5">
        <h1 class="fw-bold">Upgrade to Premium</h1>
        <p class="text-muted lead">Unlock advanced features, ad-free experience, and certified exams.</p>
    </div>

    <div class="row justify-content-center">
        <!-- Free Plan -->
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header bg-white text-center py-4">
                    <h4 class="fw-bold">Basic</h4>
                    <h1 class="display-4 fw-bold">Free</h1>
                    <p class="text-muted">Forever</p>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-3"><i class="bi bi-check-circle text-success me-2"></i> Unlimited Typing Practice</li>
                        <li class="mb-3"><i class="bi bi-check-circle text-success me-2"></i> Standard Statistics</li>
                        <li class="mb-3"><i class="bi bi-check-circle text-success me-2"></i> English & Hindi Typing</li>
                        <li class="mb-3 text-muted"><i class="bi bi-x-circle me-2"></i> No Certificates</li>
                        <li class="mb-3 text-muted"><i class="bi bi-x-circle me-2"></i> Contains Ads</li>
                    </ul>
                </div>
                <div class="card-footer bg-white border-0 text-center pb-4">
                    <a href="typing-test.php" class="btn btn-outline-primary btn-lg w-100">Start Practice</a>
                </div>
            </div>
        </div>

        <!-- Pro Plan -->
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow border-primary">
                <div class="position-absolute top-0 start-50 translate-middle">
                    <span class="badge rounded-pill bg-primary px-3 py-2">MOST POPULAR</span>
                </div>
                <div class="card-header bg-primary text-white text-center py-4">
                    <h4 class="fw-bold">Pro</h4>
                    <h1 class="display-4 fw-bold">₹199</h1>
                    <p class="opacity-75">/ month</p>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-3"><i class="bi bi-check-circle-fill text-primary me-2"></i> <strong>Ad-Free Experience</strong></li>
                        <li class="mb-3"><i class="bi bi-check-circle-fill text-primary me-2"></i> <strong>Exam Mode (SSC, RRB)</strong></li>
                        <li class="mb-3"><i class="bi bi-check-circle-fill text-primary me-2"></i> <strong>Download Certificates</strong></li>
                        <li class="mb-3"><i class="bi bi-check-circle-fill text-primary me-2"></i> Advanced Analytics</li>
                        <li class="mb-3"><i class="bi bi-check-circle-fill text-primary me-2"></i> Priority Support</li>
                    </ul>
                </div>
                <div class="card-footer bg-white border-0 text-center pb-4">
                    <button onclick="alert('Payment Gateway Integration Pending')" class="btn btn-primary btn-lg w-100 shadow">Upgrade Now</button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="text-center mt-4">
        <p class="text-muted">Secure payment powered by Razorpay / Stripe</p>
        <div class="fs-4">
            <i class="bi bi-shield-check text-success"></i>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
