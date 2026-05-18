<?php
// views/instructor/create_quiz.php — MEMBER 2
require 'views/layout/header.php';
?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger">⚠️ <?= $error ?></div>
<?php endif; ?>

<div style="max-width:620px">
<div class="card">
    <div class="card-header">
        <span class="card-title">📝 Create Quiz — <?= htmlspecialchars($course['title']) ?></span>
        <a href="index.php?page=instructor&action=my_quizzes"
           class="btn btn-secondary btn-sm">← All Quizzes</a>
    </div>
    <div class="card-body">
        <form method="POST" action="index.php?page=instructor&action=create_quiz">
            <input type="hidden" name="course_id" value="<?= $course['id'] ?>">

            <div class="form-group">
                <label class="form-label">Quiz Title <span class="required">*</span></label>
                <input type="text" name="title" class="form-control"
                       placeholder="e.g. Week 3 Quiz, Midterm Exam"
                       value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3"
                          placeholder="What topics does this quiz cover?"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Time Limit (minutes)</label>
                    <input type="number" name="time_limit_minutes" class="form-control"
                           value="30" min="1" max="300">
                </div>
                <div class="form-group">
                    <label class="form-label">Quiz Type</label>
                    <select name="quiz_type" class="form-control">
                        <option value="graded">📊 Graded</option>
                        <option value="practice">📝 Practice</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Available From</label>
                    <input type="datetime-local" name="available_from" class="form-control">
                    <p class="form-text">Leave blank = available immediately</p>
                </div>
                <div class="form-group">
                    <label class="form-label">Available Until</label>
                    <input type="datetime-local" name="available_until" class="form-control">
                    <p class="form-text">Leave blank = no deadline</p>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Initial Status</label>
                <select name="status" class="form-control">
                    <option value="draft">📋 Draft (hidden from students)</option>
                    <option value="published">✅ Published (visible to students)</option>
                </select>
            </div>

            <button type="submit" name="save_quiz" class="btn btn-primary btn-lg btn-full">
                Create Quiz & Add Questions →
            </button>
        </form>
    </div>
</div>
</div>

<?php require 'views/layout/footer.php'; ?>