<?php
// views/instructor/manage_quiz.php — MEMBER 2
require 'views/layout/header.php';
?>

<?php if (!empty($_GET['added'])): ?>
<div class="alert alert-success" style="animation:none!important;opacity:1!important">
    ✅ Question added.
</div>
<?php endif; ?>
<?php if (!empty($_GET['deleted'])): ?>
<div class="alert alert-info">🗑 Question deleted.</div>
<?php endif; ?>

<!-- Quiz Header -->
<div style="background:linear-gradient(135deg,var(--primary),var(--secondary));
            border-radius:var(--radius);padding:18px 24px;margin-bottom:20px;
            display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px">
    <div>
        <div style="color:rgba(255,255,255,.7);font-size:12px;margin-bottom:2px">
            <?= htmlspecialchars($quiz['course_title']) ?>
        </div>
        <div style="color:white;font-size:17px;font-weight:800">
            <?= htmlspecialchars($quiz['title']) ?>
        </div>
        <div style="color:rgba(255,255,255,.8);font-size:13px;margin-top:3px">
            <?= count($questions) ?> question<?= count($questions)!=1?'s':'' ?> •
            <?= $quiz['total_marks'] ?> total marks •
            Pass: <?= $quiz['pass_mark'] ?> •
            <?= $quiz['time_limit_minutes'] ?>min
        </div>
    </div>
    <div class="flex gap-2 flex-wrap">
        <span class="badge" style="background:rgba(255,255,255,.2);color:white;font-size:12px">
            <?= $quiz['status']==='published' ? '✅ Published' : '📋 Draft' ?>
        </span>
        <a href="index.php?page=instructor&action=my_quizzes"
           style="background:rgba(255,255,255,.15);color:white;border:1px solid rgba(255,255,255,.3);
                  padding:7px 14px;border-radius:6px;font-size:13px;text-decoration:none;font-weight:600">
            ← All Quizzes
        </a>
    </div>
</div>

<!-- MAIN TWO-COLUMN LAYOUT -->
<div style="display:flex;gap:20px;align-items:flex-start">

    <!-- LEFT COLUMN: Add Question + Settings (40%) -->
    <div style="width:40%;flex-shrink:0">

        <!-- Add Question Form -->
        <div class="card" style="margin-bottom:16px">
            <div class="card-header">
                <span class="card-title">➕ Add Question</span>
                <span class="badge badge-gray" style="font-size:11px">
                    <?= count($questions) ?> so far
                </span>
            </div>
            <div class="card-body">
                <form method="POST" action="index.php?page=instructor&action=add_question">
                    <input type="hidden" name="quiz_id" value="<?= $quiz['id'] ?>">
                    <div class="form-group">
                        <label class="form-label">Question <span class="required">*</span></label>
                        <textarea name="question_text" class="form-control"
                                  rows="3" placeholder="Type question here..."
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
                            <input type="text" name="options[]"
                                   class="form-control"
                                   placeholder="Option <?= chr(65+$i) ?>"
                                   required>
                        </div>
                        <?php endfor; ?>
                    </div>
                    <button type="submit" class="btn btn-success btn-full">
                        ➕ Add Question
                    </button>
                </form>
            </div>
        </div>

        <!-- Quick Settings -->
        <div class="card">
            <div class="card-header"><span class="card-title">⚙️ Settings</span></div>
            <div class="card-body">
                <!-- Marks info (read-only) -->
                <div style="background:var(--light);border-radius:var(--radius-sm);
                            padding:12px;border:1px solid var(--border);margin-bottom:16px">
                    <div style="display:flex;justify-content:space-between;margin-bottom:6px">
                        <span class="text-sm text-muted">Total Marks (auto)</span>
                        <span class="fw-bold text-primary"><?= $quiz['total_marks'] ?></span>
                    </div>
                    <div style="display:flex;justify-content:space-between">
                        <span class="text-sm text-muted">Pass Mark (50% auto)</span>
                        <span class="fw-bold text-success"><?= $quiz['pass_mark'] ?></span>
                    </div>
                </div>

                <form method="POST" action="index.php?page=instructor&action=edit_quiz">
                    <input type="hidden" name="quiz_id"     value="<?= $quiz['id'] ?>">
                    <input type="hidden" name="title"       value="<?= htmlspecialchars($quiz['title']) ?>">
                    <input type="hidden" name="description" value="<?= htmlspecialchars($quiz['description'] ?? '') ?>">
                    <input type="hidden" name="total_marks" value="<?= $quiz['total_marks'] ?>">
                    <input type="hidden" name="pass_mark"   value="<?= $quiz['pass_mark'] ?>">
                    <input type="hidden" name="available_from"  value="<?= $quiz['available_from'] ?? '' ?>">
                    <input type="hidden" name="available_until" value="<?= $quiz['available_until'] ?? '' ?>">

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Time (min)</label>
                            <input type="number" name="time_limit_minutes"
                                   class="form-control" value="<?= $quiz['time_limit_minutes'] ?>" min="1">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Type</label>
                            <select name="quiz_type" class="form-control">
                                <option value="graded"   <?= $quiz['quiz_type']==='graded'  ?'selected':'' ?>>Graded</option>
                                <option value="practice" <?= $quiz['quiz_type']==='practice'?'selected':'' ?>>Practice</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="draft"     <?= $quiz['status']==='draft'    ?'selected':'' ?>>📋 Draft</option>
                            <option value="published" <?= $quiz['status']==='published'?'selected':'' ?>>✅ Published</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary btn-full">💾 Save</button>
                </form>
            </div>
        </div>
    </div>

    <!-- RIGHT COLUMN: Questions List (60%) -->
    <div style="flex:1;min-width:0">
        <div class="card" style="position:sticky;top:76px">
            <div class="card-header">
                <span class="card-title">
                    📋 Questions
                    <span style="background:var(--primary);color:white;border-radius:20px;
                                 padding:2px 10px;font-size:12px;margin-left:6px">
                        <?= count($questions) ?>
                    </span>
                </span>
                <?php if ($quiz['status']==='draft' && count($questions)>0): ?>
                <form method="POST" action="index.php?page=instructor&action=edit_quiz" style="display:inline">
                    <input type="hidden" name="quiz_id"          value="<?= $quiz['id'] ?>">
                    <input type="hidden" name="title"            value="<?= htmlspecialchars($quiz['title']) ?>">
                    <input type="hidden" name="description"      value="<?= htmlspecialchars($quiz['description'] ?? '') ?>">
                    <input type="hidden" name="total_marks"      value="<?= $quiz['total_marks'] ?>">
                    <input type="hidden" name="pass_mark"        value="<?= $quiz['pass_mark'] ?>">
                    <input type="hidden" name="time_limit_minutes" value="<?= $quiz['time_limit_minutes'] ?>">
                    <input type="hidden" name="quiz_type"        value="<?= $quiz['quiz_type'] ?>">
                    <input type="hidden" name="status"           value="published">
                    <input type="hidden" name="available_from"   value="<?= $quiz['available_from'] ?? '' ?>">
                    <input type="hidden" name="available_until"  value="<?= $quiz['available_until'] ?? '' ?>">
                    <button type="submit" class="btn btn-success btn-sm">✅ Publish Now</button>
                </form>
                <?php endif; ?>
            </div>

            <div style="max-height:65vh;overflow-y:auto">
                <?php if (empty($questions)): ?>
                <div style="text-align:center;padding:50px 20px">
                    <div style="font-size:44px;opacity:.3;margin-bottom:12px">❓</div>
                    <p class="fw-600" style="margin-bottom:4px">No questions yet</p>
                    <p class="text-sm text-muted">Use the form on the left to add your first question.</p>
                </div>
                <?php else: ?>
                <?php foreach ($questions as $i => $q): ?>
                <div style="padding:16px 20px;border-bottom:1px solid var(--border)">
                    <div style="display:flex;justify-content:space-between;
                                align-items:flex-start;gap:8px;margin-bottom:8px">
                        <span style="font-size:11px;font-weight:700;color:var(--primary);
                                     text-transform:uppercase;letter-spacing:.5px">
                            Q<?= $i+1 ?> · <?= $q['marks'] ?> mark<?= $q['marks']!=1?'s':'' ?>
                        </span>
                        <a href="index.php?page=instructor&action=delete_question&q_id=<?= $q['id'] ?>&quiz_id=<?= $quiz['id'] ?>"
                           style="color:var(--danger);font-size:12px;text-decoration:none;
                                  padding:3px 8px;border:1px solid var(--danger);border-radius:4px;
                                  white-space:nowrap;flex-shrink:0"
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
                            <span style="margin-left:auto;font-size:10px;
                                         color:#059669;font-weight:700">CORRECT</span>
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