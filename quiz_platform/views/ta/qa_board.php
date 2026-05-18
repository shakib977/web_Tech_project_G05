<?php require 'views/layout/header.php'; ?>

<div class="flex-between mb-4" style="margin-bottom:16px">
    <div>
        <h3 class="fw-bold"><?= htmlspecialchars($course['title']) ?> — Q&A</h3>
        <p class="text-sm text-muted"><?= count($questions) ?> question<?= count($questions)!=1?'s':'' ?></p>
    </div>
</div>

<?php if (empty($questions)): ?>
    <div class="empty-state card" style="padding:60px"><div class="empty-icon">❓</div><p>No questions yet.</p></div>
<?php else: ?>
<?php foreach ($questions as $q): ?>
<div class="qa-item">
    <div class="flex-between">
        <div class="qa-title">
            <?= htmlspecialchars($q['title']) ?>
            <?php if ($q['is_resolved']): ?><span class="badge badge-success" style="margin-left:8px;font-size:11px">✓ Resolved</span><?php endif; ?>
        </div>
    </div>
    <div class="qa-body"><?= nl2br(htmlspecialchars($q['body'])) ?></div>
    <div class="qa-meta">
        <span>👤 <?= htmlspecialchars($q['student_name']) ?></span>
        <span>📅 <?= date('M d, H:i', strtotime($q['created_at'])) ?></span>
    </div>

    <?php foreach ($q['answers'] as $a): ?>
    <div class="qa-answer <?= $a['is_endorsed']?'endorsed':'' ?>">
        <div class="flex-between mb-1" style="margin-bottom:4px">
            <span class="fw-600 text-sm">
                <?= htmlspecialchars($a['author_name']) ?>
                <?php if ($a['role']==='instructor'): ?><span class="badge badge-info" style="font-size:10px;margin-left:4px">Instructor</span><?php endif; ?>
                <?php if ($a['is_endorsed']): ?><span class="badge badge-success" style="font-size:10px;margin-left:4px">⭐</span><?php endif; ?>
            </span>
            <?php if (!$a['is_endorsed']): ?>
            <a href="index.php?page=ta&action=endorse_answer&ans_id=<?= $a['id'] ?>&course_id=<?= $course['id'] ?>" class="btn btn-outline btn-sm">⭐ Endorse</a>
            <?php endif; ?>
        </div>
        <?= nl2br(htmlspecialchars($a['body'])) ?>
    </div>
    <?php endforeach; ?>

    <form method="POST" action="index.php?page=ta&action=answer_question" style="margin-top:10px">
        <input type="hidden" name="qa_question_id" value="<?= $q['id'] ?>">
        <input type="hidden" name="course_id"      value="<?= $course['id'] ?>">
        <div class="flex gap-2">
            <input type="text" name="body" class="form-control" placeholder="Your answer..." required>
            <button type="submit" class="btn btn-primary" style="white-space:nowrap">Reply</button>
        </div>
    </form>
</div>
<?php endforeach; ?>
<?php endif; ?>

<?php require 'views/layout/footer.php'; ?>