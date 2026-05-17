<?php
// views/student/qa_board.php — MEMBER 1
require 'views/layout/header.php';
?>

<div class="flex-between mb-4" style="margin-bottom:16px">
    <div>
        <h3 class="fw-bold"><?= htmlspecialchars($course['title'] ?? '') ?> — Q&A</h3>
        <p class="text-sm text-muted"><?= count($questions) ?> question<?= count($questions)!=1?'s':'' ?></p>
    </div>
    <button class="btn btn-primary" onclick="openModal('ask_modal')">+ Ask a Question</button>
</div>

<?php if (empty($questions)): ?>
    <div class="empty-state card" style="padding:60px">
        <div class="empty-icon">❓</div>
        <h3>No questions yet</h3>
        <p>Be the first to ask a question about this course.</p>
        <button class="btn btn-primary" onclick="openModal('ask_modal')">Ask a Question</button>
    </div>
<?php else: ?>
    <?php foreach ($questions as $q): ?>
    <div class="qa-item">
        <div class="flex-between">
            <div class="qa-title">
                <?= htmlspecialchars($q['title']) ?>
                <?php if ($q['is_resolved']): ?>
                    <span class="badge badge-success" style="margin-left:8px;font-size:11px">✓ Resolved</span>
                <?php endif; ?>
            </div>
            <?php if ($q['student_id'] == $_SESSION['user_id'] && !$q['is_resolved']): ?>
            <a href="index.php?page=student&action=mark_resolved&q_id=<?= $q['id'] ?>&course_id=<?= $course_id ?>"
               class="btn btn-success btn-sm"
               onclick="return confirm('Mark this as resolved?')">Mark Resolved</a>
            <?php endif; ?>
        </div>
        <div class="qa-body"><?= nl2br(htmlspecialchars($q['body'])) ?></div>
        <div class="qa-meta">
            <span>👤 <?= htmlspecialchars($q['student_name']) ?></span>
            <span>📅 <?= date('M d, Y H:i', strtotime($q['created_at'])) ?></span>
            <span>💬 <?= count($q['answers']) ?> answer<?= count($q['answers'])!=1?'s':'' ?></span>
        </div>

        <?php if (!empty($q['answers'])): ?>
        <div style="margin-top:12px">
            <?php foreach ($q['answers'] as $a): ?>
            <div class="qa-answer <?= $a['is_endorsed'] ? 'endorsed' : '' ?>">
                <div class="flex-between mb-2" style="margin-bottom:6px">
                    <span class="fw-600 text-sm">
                        <?= htmlspecialchars($a['author_name']) ?>
                        <?php if ($a['author_role'] === 'instructor'): ?>
                            <span class="badge badge-info" style="font-size:10px;margin-left:4px">Instructor</span>
                        <?php elseif ($a['author_role'] === 'ta'): ?>
                            <span class="badge badge-purple" style="font-size:10px;margin-left:4px">TA</span>
                        <?php endif; ?>
                        <?php if ($a['is_endorsed']): ?>
                            <span class="badge badge-success" style="font-size:10px;margin-left:4px">⭐ Endorsed</span>
                        <?php endif; ?>
                    </span>
                    <span class="text-xs text-muted"><?= date('M d, H:i', strtotime($a['created_at'])) ?></span>
                </div>
                <?= nl2br(htmlspecialchars($a['body'])) ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
<?php endif; ?>

<!-- Ask Question Modal -->
<div class="modal-overlay" id="ask_modal">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">Ask a Question</span>
            <button class="modal-close" onclick="closeModal('ask_modal')">✕</button>
        </div>
        <form method="POST" action="index.php?page=student&action=post_question">
            <input type="hidden" name="course_id" value="<?= $course_id ?>">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Title <span class="required">*</span></label>
                    <input type="text" name="title" class="form-control" placeholder="Brief summary of your question" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Details <span class="required">*</span></label>
                    <textarea name="body" class="form-control" rows="4" placeholder="Explain your question in detail..." required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('ask_modal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Post Question</button>
            </div>
        </form>
    </div>
</div>

<?php require 'views/layout/footer.php'; ?>
