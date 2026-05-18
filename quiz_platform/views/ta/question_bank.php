<?php require 'views/layout/header.php'; ?>

<div class="grid-2">
    <!-- Add Question -->
    <div class="card">
        <div class="card-header"><span class="card-title">➕ Add Question</span></div>
        <div class="card-body">
            <?php
            $q_stmt = $conn->prepare("SELECT id, title FROM quizzes WHERE course_id=? AND created_by=?");
            $q_stmt->bind_param('ii', $course['id'], $_SESSION['user_id']); $q_stmt->execute();
            $my_quizzes = $q_stmt->get_result()->fetch_all(MYSQLI_ASSOC); $q_stmt->close();
            ?>
            <?php if (empty($my_quizzes)): ?>
                <div class="alert alert-warning">⚠️ Create a quiz first before adding questions.</div>
                <a href="index.php?page=ta&action=create_quiz&course_id=<?= $course['id'] ?>" class="btn btn-primary">Create Quiz</a>
            <?php else: ?>
            <form method="POST" action="index.php?page=ta&action=add_question">
                <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                <div class="form-group">
                    <label class="form-label">Quiz <span class="required">*</span></label>
                    <select name="quiz_id" class="form-control" required>
                        <?php foreach ($my_quizzes as $q): ?>
                        <option value="<?= $q['id'] ?>"><?= htmlspecialchars($q['title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Question <span class="required">*</span></label>
                    <textarea name="question_text" class="form-control" rows="3" required></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Marks</label>
                    <input type="number" name="marks" class="form-control" value="1" min="1" style="width:80px">
                </div>
                <div class="form-group">
                    <label class="form-label">Options (select correct one)</label>
                    <?php for ($i=0;$i<4;$i++): ?>
                    <div class="flex gap-2 mb-2" style="margin-bottom:8px;align-items:center">
                        <input type="radio" name="correct_option" value="<?= $i ?>" <?= $i===0?'checked':'' ?> style="width:16px;height:16px;accent-color:var(--primary)">
                        <input type="text" name="options[]" class="form-control" placeholder="Option <?= $i+1 ?>" required>
                    </div>
                    <?php endfor; ?>
                </div>
                <button type="submit" class="btn btn-success">Add Question</button>
            </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Question List -->
    <div class="card">
        <div class="card-header"><span class="card-title">📋 My Questions (<?= count($questions) ?>)</span></div>
        <div class="card-body" style="padding:0">
            <?php if (empty($questions)): ?>
                <div class="empty-state"><div class="empty-icon">❓</div><p>No questions added yet.</p></div>
            <?php else: ?>
            <?php foreach ($questions as $i => $q): ?>
            <div style="padding:14px 20px;border-bottom:1px solid var(--border)">
                <div class="flex-between mb-1" style="margin-bottom:6px">
                    <span class="text-xs fw-bold text-primary" style="text-transform:uppercase;letter-spacing:.5px"><?= htmlspecialchars($q['quiz_title']) ?></span>
                    <a href="index.php?page=ta&action=delete_question&q_id=<?= $q['id'] ?>&course_id=<?= $course['id'] ?>"
                       class="btn btn-danger btn-sm" onclick="return confirm('Delete?')">Delete</a>
                </div>
                <p class="fw-600 text-sm"><?= nl2br(htmlspecialchars($q['question_text'])) ?></p>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require 'views/layout/footer.php'; ?>