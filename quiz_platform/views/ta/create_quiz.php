<?php require 'views/layout/header.php'; ?>

<?php if (!empty($error)):   ?><div class="alert alert-danger">⚠️ <?= $error ?></div><?php endif; ?>
<?php if (!empty($success)): ?><div class="alert alert-success">✓ <?= $success ?></div><?php endif; ?>

<div style="max-width:600px">
<div class="card">
    <div class="card-header">
        <span class="card-title">➕ Create Practice Quiz — <?= htmlspecialchars($course['title']) ?></span>
        <a href="index.php?page=ta&action=course_detail&course_id=<?= $course['id'] ?>" class="btn btn-secondary btn-sm">← Back</a>
    </div>
    <div class="card-body">
        <div class="alert alert-info">ℹ️ Practice quizzes require instructor approval before students can see them.</div>
        <form method="POST" action="index.php?page=ta&action=create_quiz">
            <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
            <div class="form-group">
                <label class="form-label">Quiz Title <span class="required">*</span></label>
                <input type="text" name="title" class="form-control" placeholder="e.g. Practice Set 1" required>
            </div>
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3"></textarea>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Time Limit (min)</label>
                    <input type="number" name="time_limit_minutes" class="form-control" value="30" min="1">
                </div>
                <div class="form-group">
                    <label class="form-label">Total Marks</label>
                    <input type="number" name="total_marks" class="form-control" value="100" min="1">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Pass Mark</label>
                    <input type="number" name="pass_mark" class="form-control" value="50" min="0">
                </div>
                <div class="form-group">
                    <label class="form-label">Available From</label>
                    <input type="datetime-local" name="available_from" class="form-control">
                </div>
            </div>
            <button type="submit" name="save_quiz" class="btn btn-primary">Create Practice Quiz</button>
        </form>
    </div>
</div>
</div>

<?php require 'views/layout/footer.php'; ?>