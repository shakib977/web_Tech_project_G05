<?php
// views/instructor/my_quizzes.php — MEMBER 2
require 'views/layout/header.php';
?>

<!-- Filter Bar -->
<div class="flex-between flex-wrap gap-3" style="margin-bottom:20px">
    <form method="GET" action="index.php"
          class="flex gap-2 flex-wrap" style="align-items:center">
        <input type="hidden" name="page"   value="instructor">
        <input type="hidden" name="action" value="my_quizzes">

        <!-- Course filter -->
        <select name="course_id" class="form-control" style="width:200px"
                onchange="this.form.submit()">
            <option value="">All Courses</option>
            <?php foreach ($my_courses as $c): ?>
            <option value="<?= $c['id'] ?>"
                <?= $course_id==$c['id']?'selected':'' ?>>
                <?= htmlspecialchars($c['title']) ?>
            </option>
            <?php endforeach; ?>
        </select>

        <!-- Type filter -->
        <select name="quiz_type_filter" class="form-control" style="width:160px"
                onchange="this.form.submit()">
            <option value="">All Types</option>
            <option value="graded"
                <?= ($_GET['quiz_type_filter']??'')==='graded'?'selected':'' ?>>
                📊 Graded Only
            </option>
            <option value="practice"
                <?= ($_GET['quiz_type_filter']??'')==='practice'?'selected':'' ?>>
                📝 Practice Only
            </option>
        </select>

        <!--  filter -->
<div style="display:flex;align-items:center;gap:6px">
    <span class="text-sm text-muted" style="white-space:nowrap">From</span>
    <input type="date" name="date_from" class="form-control" style="width:145px"
           value="<?= htmlspecialchars($_GET['date_from'] ?? '') ?>"
           onchange="this.form.submit()">
</div>
<div style="display:flex;align-items:center;gap:6px">
    <span class="text-sm text-muted" style="white-space:nowrap">To</span>
    <input type="date" name="date_to" class="form-control" style="width:145px"
           value="<?= htmlspecialchars($_GET['date_to'] ?? '') ?>"
           onchange="this.form.submit()">
</div>

        <!-- Status filter -->
        <select name="status_filter" class="form-control" style="width:150px"
                onchange="this.form.submit()">
            <option value="">All Status</option>
            <option value="published"
                <?= ($_GET['status_filter']??'')==='published'?'selected':'' ?>>
                ✅ Published
            </option>
            <option value="draft"
                <?= ($_GET['status_filter']??'')==='draft'?'selected':'' ?>>
                📋 Draft
            </option>
        </select>

        <a href="index.php?page=instructor&action=my_quizzes"
           class="btn btn-secondary">Reset</a>
    </form>

    <div>
        <?php if ($course_id): ?>
        <a href="index.php?page=instructor&action=create_quiz&course_id=<?= $course_id ?>"
           class="btn btn-primary">+ Create Quiz</a>
        <?php else: ?>
        <span class="text-sm text-muted">Select a course to create a quiz</span>
        <?php endif; ?>
    </div>
</div>

<?php
// Apply type + status filters client-side (already filtered by course in controller)
$quiz_type_filter  = $_GET['quiz_type_filter']  ?? '';
$status_filter     = $_GET['status_filter']     ?? '';

$filtered = array_filter($quizzes, function($q) use ($quiz_type_filter, $status_filter) {
    if ($quiz_type_filter && $q['quiz_type'] !== $quiz_type_filter) return false;
    if ($status_filter    && $q['status']    !== $status_filter)    return false;
    return true;
});
?>

<!-- Results count -->
<p class="text-sm text-muted" style="margin-bottom:16px">
    Showing <strong><?= count($filtered) ?></strong>
    quiz<?= count($filtered)!=1?'zes':'' ?>
    <?php if ($quiz_type_filter): ?>
        <span class="badge badge-<?= $quiz_type_filter==='graded'?'info':'purple' ?>" style="margin-left:6px">
            <?= ucfirst($quiz_type_filter) ?>
        </span>
    <?php endif; ?>
</p>

<?php if (empty($filtered)): ?>
    <div class="empty-state card" style="padding:60px">
        <div class="empty-icon">📝</div>
        <h3>No quizzes found</h3>
        <p>
            <?php if ($course_id): ?>
                No quizzes match your filters for this course.
            <?php else: ?>
                Select a course and create your first quiz.
            <?php endif; ?>
        </p>
        <?php if ($course_id): ?>
        <a href="index.php?page=instructor&action=create_quiz&course_id=<?= $course_id ?>"
           class="btn btn-primary">Create Quiz</a>
        <?php endif; ?>
    </div>
<?php else: ?>
<div style="display:flex;flex-direction:column;gap:14px">
<?php foreach ($filtered as $q): ?>

<div class="card" style="margin-bottom:0">
    <div class="card-body" style="padding:0">
        <div style="display:flex;align-items:stretch;min-height:90px">

            <!-- Status color bar -->
            <div style="width:6px;flex-shrink:0;
                        border-radius:var(--radius) 0 0 var(--radius);
                        background:<?= $q['status']==='published'?'var(--success)':'var(--warning)' ?>">
            </div>

            <div style="flex:1;padding:16px 20px">
                <div style="display:flex;align-items:flex-start;
                            justify-content:space-between;gap:12px;flex-wrap:wrap">

                    <div style="flex:1;min-width:180px">
                        <div style="font-size:16px;font-weight:700;
                                    color:var(--dark);margin-bottom:4px">
                            <?= htmlspecialchars($q['title']) ?>
                        </div>
                        <div style="font-size:13px;color:var(--primary);
                                    font-weight:600;margin-bottom:8px">
                            📚 <?= htmlspecialchars($q['course_title']) ?>
                        </div>
                        <div style="display:flex;flex-wrap:wrap;gap:14px;
                                    font-size:12px;color:var(--gray)">
                            <span>⏱ <?= $q['time_limit_minutes'] ?>min</span>
                            <span>📊 <?= $q['total_marks'] ?> marks</span>
                            <span>👥 <?= $q['attempt_count'] ?> attempt<?= $q['attempt_count']!=1?'s':'' ?></span>
                            <span>
                                <?php if ($q['question_count']==0): ?>
                                    <span style="color:var(--danger);font-weight:600">
                                        ⚠️ No questions
                                    </span>
                                <?php else: ?>
                                    ❓ <?= $q['question_count'] ?> question<?= $q['question_count']!=1?'s':'' ?>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>

                    <div style="display:flex;flex-direction:column;
                                align-items:flex-end;gap:8px;flex-shrink:0">
                        <div class="flex gap-2">
                            <span class="badge badge-<?= $q['quiz_type']==='graded'?'info':'purple' ?>">
                                <?= $q['quiz_type']==='graded' ? '📊 Graded' : '📝 Practice' ?>
                            </span>
                            <span class="badge badge-<?= $q['status']==='published'?'success':'warning' ?>">
                                <?= $q['status']==='published' ? '✅ Published' : '📋 Draft' ?>
                            </span>
                        </div>
                        <div class="flex gap-2">
                            <a href="index.php?page=instructor&action=manage_quiz&quiz_id=<?= $q['id'] ?>"
                               class="btn btn-primary btn-sm">
                                ✏️ Edit Quiz
                            </a>
                            <a href="index.php?page=instructor&action=quiz_attempts&quiz_id=<?= $q['id'] ?>"
                               class="btn btn-secondary btn-sm">
                                📊 Results
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<?php endforeach; ?>
</div>
<?php endif; ?>

<?php require 'views/layout/footer.php'; ?>