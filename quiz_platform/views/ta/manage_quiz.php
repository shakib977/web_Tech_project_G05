<?php
// views/ta/manage_quiz.php — MEMBER 3
require 'views/layout/header.php';
?>

<?php if (!empty($_GET['created'])): ?>
<div class="alert alert-success">✅ Quiz created! Now add your questions below, then request instructor approval.</div>
<?php endif; ?>
<?php if (!empty($_GET['added'])): ?>
<div class="alert alert-success">✅ Question added.</div>
<?php endif; ?>
<?php if (!empty($_GET['deleted'])): ?>
<div class="alert alert-info">🗑 Question deleted.</div>
<?php endif; ?>
<?php if (!empty($_GET['requested'])): ?>
<div class="alert alert-info">📨 Approval request sent to instructor! They will review and publish the quiz.</div>
<?php endif; ?>

<!-- Header -->
<div style="background:linear-gradient(135deg,var(--warning),var(--secondary));
            border-radius:var(--radius);padding:18px 24px;margin-bottom:20px;
            display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px">
    <div>
        <div style="color:rgba(255,255,255,.7);font-size:12px;margin-bottom:2px">
            Practice Quiz · <?= htmlspecialchars($quiz['course_title']) ?>
        </div>
        <div style="color:white;font-size:17px;font-weight:800">
            <?= htmlspecialchars($quiz['title']) ?>
        </div>
        <div style="color:rgba(255,255,255,.8);font-size:13px;margin-top:3px">
            <?= count($questions) ?> question<?= count($questions)!=1?'s':'' ?> •
            <?= $quiz['total_marks'] ?> total marks •
            <?= $quiz['time_limit_minutes'] ?>min
        </div>
    </div>
    <div class="flex gap-2 flex-wrap">
        <span class="badge" style="background:rgba(255,255,255,.2);color:white">
            <?= $quiz['status']==='published' ? '✅ Approved & Published' : '⏳ Awaiting Approval' ?>
        </span>
        <a href="index.php?page=ta&action=courses"
           style="background:rgba(255,255,255,.15);color:white;
                  border:1px solid rgba(255,255,255,.3);
                  padding:7px 14px;border-radius:6px;font-size:13px;text-decoration:none">
            ← My Courses
        </a>
    </div>
</div>

<?php if ($quiz['status'] !== 'published' && count($questions) > 0): ?>
<!-- Send for approval -->
<div style="background:#FEF3C7;border:1px solid #FCD34D;border-radius:var(--radius);
            padding:16px 20px;margin-bottom:20px;
            display:flex;align-items:center;justify-content:space-between;gap:12px">
    <div>
        <div class="fw-bold" style="color:#92400E;margin-bottom:2px">
            📨 Ready to submit for instructor approval?
        </div>
        <div class="text-sm" style="color:#B45309">
            You have <?= count($questions) ?> question<?= count($questions)!=1?'s':'' ?>.
            The instructor will review and publish this quiz.
        </div>
    </div>
    <form method="POST" action="index.php?page=ta&action=request_approval" style="flex-shrink:0">
        <input type="hidden" name="quiz_id" value="<?= $quiz['id'] ?>">
        <button type="submit" class="btn btn-warning"
                onclick="return confirm('Send this quiz to instructor for approval?')">
            📨 Request Approval
        </button>
    </form>
</div>
<?php endif; ?>

<!-- Two-column layout -->
<div style="display:flex;gap:20px;align-items:flex-start">

    <!-- LEFT: Add Question (40%) -->
    <div style="width:40%;flex-shrink:0">
        <div class="card">
            <div class="card-header">
                <span class="card-title">➕ Add Question</span>
                <span class="badge badge-gray" style="font-size:11px"><?= count($questions) ?> so far</span>
            </div>
            <div class="card-body">
                <form method="POST" action="index.php?page=ta&action=add_question">
                    <input type="hidden" name="quiz_id"   value="<?= $quiz['id'] ?>">
                    <input type="hidden" name="course_id" value="<?= $quiz['course_id'] ?>">
                    <div class="form-group">
                        <label class="form-label">Question <span class="required">*</span></label>
                        <textarea name="question_text" class="form-control"
                                  rows="3" placeholder="Type your question..."
                                  required></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Marks</label>
                        <input type="number" name="marks" class="form-control"
                               value="1" min="1" style="width:90px">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Options <span class="required">*</span></label>
                        <p class="form-text" style="margin-bottom:10px">
                            🔘 Select the <strong>correct</strong> answer
                        </p>
                        <?php for ($i = 0; $i < 4; $i++): ?>
                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px">
                            <input type="radio" name="correct_option" value="<?= $i ?>"
                                   <?= $i===0?'checked':'' ?>
                                   style="width:18px;height:18px;accent-color:var(--success);flex-shrink:0">
                            <input type="text" name="options[]" class="form-control"
                                   placeholder="Option <?= chr(65+$i) ?>" required>
                        </div>
                        <?php endfor; ?>
                    </div>
                    <button type="submit" class="btn btn-success btn-full">➕ Add Question</button>
                </form>
            </div>
        </div>
    </div>

    <!-- RIGHT: Questions list (60%) -->
    <div style="flex:1;min-width:0">
        <div class="card" style="position:sticky;top:76px">
            <div class="card-header">
                <span class="card-title">
                    📋 Questions
                    <span style="background:var(--warning);color:white;border-radius:20px;
                                 padding:2px 10px;font-size:12px;margin-left:6px">
                        <?= count($questions) ?>
                    </span>
                </span>
            </div>
            <div style="max-height:65vh;overflow-y:auto">
                <?php if (empty($questions)): ?>
                <div style="text-align:center;padding:50px 20px">
                    <div style="font-size:44px;opacity:.3;margin-bottom:12px">❓</div>
                    <p class="fw-600">No questions yet</p>
                    <p class="text-sm text-muted">Add questions using the form on the left.</p>
                </div>
                <?php else: ?>
                <?php foreach ($questions as $i => $q): ?>
                <div style="padding:16px 20px;border-bottom:1px solid var(--border)">
                    <div style="display:flex;justify-content:space-between;
                                align-items:flex-start;gap:8px;margin-bottom:8px">
                        <span style="font-size:11px;font-weight:700;color:var(--warning);
                                     text-transform:uppercase;letter-spacing:.5px">
                            Q<?= $i+1 ?> · <?= $q['marks'] ?> mark<?= $q['marks']!=1?'s':'' ?>
                        </span>
                        <a href="index.php?page=ta&action=delete_question&q_id=<?= $q['id'] ?>&quiz_id=<?= $quiz['id'] ?>"
                           style="color:var(--danger);font-size:12px;text-decoration:none;
                                  padding:3px 8px;border:1px solid var(--danger);border-radius:4px;
                                  white-space:nowrap"
                           onclick="return confirm('Delete Q<?= $i+1 ?>?')">
                            🗑 Delete
                        </a>
                    </div>
                    <p style="font-size:14px;font-weight:600;margin-bottom:10px;line-height:1.5">
                        <?= nl2br(htmlspecialchars($q['question_text'])) ?>
                    </p>
                    <div style="display:flex;flex-direction:column;gap:5px">
                        <?php foreach ($q['options'] as $opt): ?>
                        <div style="display:flex;align-items:center;gap:8px;
                                    padding:6px 10px;border-radius:5px;font-size:13px;
                                    background:<?= $opt['is_correct']?'#D1FAE5':'var(--light)' ?>;
                                    border:1px solid <?= $opt['is_correct']?'#6EE7B7':'var(--border)' ?>">
                            <span><?= $opt['is_correct']?'✅':'○' ?></span>
                            <span><?= htmlspecialchars($opt['option_text']) ?></span>
                            <?php if ($opt['is_correct']): ?>
                            <span style="margin-left:auto;font-size:10px;color:#059669;font-weight:700">CORRECT</span>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require 'views/layout/footer.php'; ?>