<?php
$pageTitle = "Font Converter";
require_once 'includes/header.php';
?>

<div class="container my-5">
    <div class="text-center mb-5">
        <h1 class="fw-bold">Universal Font Converter</h1>
        <p class="text-muted">Convert text between Unicode (Mangal) and Kruti Dev, Chanakya, etc.</p>
    </div>

    <div class="row">
        <!-- Input Section -->
        <div class="col-md-5">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    Source Text
                </div>
                <div class="card-body">
                    <textarea id="sourceText" class="form-control" rows="10" placeholder="Paste your text here..."></textarea>
                    <div class="mt-2 text-end text-muted small">
                        <span id="sourceCount">0</span> characters
                    </div>
                </div>
            </div>
        </div>

        <!-- Controls -->
        <div class="col-md-2 d-flex flex-column justify-content-center align-items-center gap-3 my-3 my-md-0">
            <select class="form-select" id="conversionType">
                <option value="unicode_to_krutidev">Unicode to Kruti Dev</option>
                <option value="krutidev_to_unicode">Kruti Dev to Unicode</option>
                <option value="unicode_to_chanakya">Unicode to Chanakya</option>
                <option value="chanakya_to_unicode">Chanakya to Unicode</option>
            </select>
            
            <button class="btn btn-primary w-100" id="convertBtn">
                Convert <i class="bi bi-arrow-right"></i>
            </button>
            
            <button class="btn btn-outline-secondary w-100" id="swapBtn">
                <i class="bi bi-arrow-left-right"></i> Swap
            </button>
            
            <button class="btn btn-outline-danger w-100" id="clearBtn">
                <i class="bi bi-trash"></i> Clear
            </button>
        </div>

        <!-- Output Section -->
        <div class="col-md-5">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <span>Converted Text</span>
                    <button class="btn btn-sm btn-light text-success" id="copyBtn">
                        <i class="bi bi-clipboard"></i> Copy
                    </button>
                </div>
                <div class="card-body">
                    <textarea id="destText" class="form-control" rows="10" readonly></textarea>
                    <div class="mt-2 text-end text-muted small">
                        <span id="destCount">0</span> characters
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo BASE_URL; ?>assets/js/converters/converter-map.js"></script>
<script src="<?php echo BASE_URL; ?>assets/js/converters/converter-ui.js"></script>

<?php require_once 'includes/footer.php'; ?>
