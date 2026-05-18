<?php
// views/instructor/create_course.php — MEMBER 2
require 'views/layout/header.php';
?>

<?php if ($error): ?><div class="alert alert-danger">⚠️ <?= $error ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success">✓ <?= $success ?></div><?php endif; ?>

<div style="max-width:700px">
<div class="card">
    <div class="card-header">
        <span class="card-title">➕ Create New Course</span>
        <a href="index.php?page=instructor&action=courses" class="btn btn-secondary btn-sm">← Back</a>
    </div>
    <div class="card-body">
        <form method="POST" action="index.php?page=instructor&action=create_course">
            <div class="form-group">
                <label class="form-label">Course Title <span class="required">*</span></label>
                <input type="text" name="title" class="form-control" placeholder="e.g. Introduction to Data Structures"
                       value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Subject <span class="required">*</span></label>
                    <select name="subject_id" class="form-control" required>
                        <option value="">Select subject...</option>
                        <?php foreach ($subjects as $s): ?>
                        <option value="<?= $s['id'] ?>" <?= ($_POST['subject_id'] ?? 0) == $s['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Max Students</label>
                    <input type="number" name="max_students" class="form-control" value="<?= htmlspecialchars($_POST['max_students'] ?? '100') ?>" min="1">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="4" placeholder="What will students learn in this course?"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Enrollment Type</label>
                    <select name="enrollment_type" class="form-control">
                        <option value="open" <?= ($_POST['enrollment_type'] ?? '') === 'open' ? 'selected' : '' ?>>Open (anyone can join)</option>
                        <option value="approval" <?= ($_POST['enrollment_type'] ?? '') === 'approval' ? 'selected' : '' ?>>Approval Required</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        <option value="draft">Save as Draft</option>
                        <option value="active">Publish Now</option>
                    </select>
                </div>
            </div>
            <div class="flex gap-3">
                <button type="submit" name="save_quiz" class="btn btn-primary">Create Course</button>
                <a href="index.php?page=instructor&action=courses" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
</div>

<?php require 'views/layout/footer.php'; ?>
