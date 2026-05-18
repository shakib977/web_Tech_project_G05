<?php
// views/admin/settings.php — MEMBER 4
require 'views/layout/header.php';

$logo_path = '';
if (!empty($s_map['logo_path'])) {
    $logo_file = 'uploads/' . $s_map['logo_path'];
    if (file_exists($logo_file)) {
        $logo_path = BASE_URL . '/' . $logo_file . '?t=' . time();
    }
}
?>

<?php if (!empty($_GET['saved'])): ?>
<div class="alert alert-success">✅ Settings saved successfully.</div>
<?php endif; ?>

<!-- IMPORTANT: enctype needed for logo upload -->
<form method="POST"
      action="index.php?page=admin&action=save_settings"
      enctype="multipart/form-data">

    <div class="grid-2">

        <!-- Platform Settings -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">⚙️ Platform Policies</span>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Platform Name</label>
                    <input type="text" name="platform_name" class="form-control"
                           value="<?= htmlspecialchars($s_map['platform_name'] ?? 'QuizPro') ?>">
                    <p class="form-text">Shown in the sidebar logo text.</p>
                </div>
                <div class="form-group">
                    <label class="form-label">Max Quiz Duration (minutes)</label>
                    <input type="number" name="max_quiz_duration" class="form-control"
                           value="<?= htmlspecialchars($s_map['max_quiz_duration'] ?? '180') ?>" min="1">
                </div>
                <div class="form-group">
                    <label class="form-label">Default Max Students Per Course</label>
                    <input type="number" name="max_students_per_course" class="form-control"
                           value="<?= htmlspecialchars($s_map['max_students_per_course'] ?? '200') ?>" min="1">
                </div>
                <div class="form-group">
                    <label class="form-label">At-Risk Score Threshold (%)</label>
                    <input type="number" name="at_risk_threshold" class="form-control"
                           value="<?= htmlspecialchars($s_map['at_risk_threshold'] ?? '50') ?>"
                           min="0" max="100">
                    <p class="form-text">Students below this % are shown as at-risk.</p>
                </div>
            </div>
        </div>

        <!-- Platform Logo -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">🖼️ Platform Logo</span>
            </div>
            <div class="card-body">

                <!-- Current logo preview -->
                <?php if ($logo_path): ?>
                <div style="text-align:center;margin-bottom:20px;
                            padding:16px;background:var(--dark-2);
                            border-radius:var(--radius)">
                    <img src="<?= $logo_path ?>" alt="Current Logo"
                         style="max-height:60px;max-width:200px;object-fit:contain">
                    <div class="text-xs" style="color:rgba(255,255,255,.5);margin-top:8px">
                        Current logo (shown in sidebar)
                    </div>
                </div>
                <?php else: ?>
                <div style="text-align:center;padding:20px;background:var(--dark-2);
                            border-radius:var(--radius);margin-bottom:20px">
                    <div style="font-size:24px;font-weight:800;color:white">
                        Quiz<span style="color:var(--primary-light)">Pro</span>
                    </div>
                    <div class="text-xs" style="color:rgba(255,255,255,.4);margin-top:6px">
                        Default text logo (no image set)
                    </div>
                </div>
                <?php endif; ?>

                <div class="form-group">
                    <label class="form-label">Upload New Logo</label>
                    <input type="file" name="platform_logo"
                           class="form-control"
                           accept="image/jpeg,image/png,image/gif,image/webp,image/svg+xml">
                    <p class="form-text">
                        PNG or SVG recommended for best quality.<br>
                        Max 2MB. Displayed in the sidebar for all users.
                    </p>
                </div>

                <div style="background:var(--light);border-radius:var(--radius-sm);
                            padding:12px 14px;font-size:13px;color:var(--gray)">
                    💡 For best results, use a logo with transparent background
                    (PNG) or vector format (SVG), height ~40px.
                </div>
            </div>
        </div>

    </div>

    <div style="margin-top:20px">
        <button type="submit" class="btn btn-primary btn-lg">
            💾 Save All Settings
        </button>
    </div>

</form>

<?php require 'views/layout/footer.php'; ?>