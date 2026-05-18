<?php
// views/student/attempt_history.php — MEMBER 1
require 'views/layout/header.php';

function getGrade($pct) {
    if ($pct >= 90) return ['A+', '#059669'];
    if ($pct >= 80) return ['B+', '#2563EB'];
    if ($pct >= 70) return ['C+', '#D97706'];
    if ($pct >= 50) return ['D+', '#7C3AED'];
    return ['F',   '#DC2626'];
}

function fmtDuration($seconds) {
    $h = floor($seconds / 3600);
    $m = floor(($seconds % 3600) / 60);
    $s = $seconds % 60;
    if ($h > 0) return "{$h}h {$m}m {$s}s";
    if ($m > 0) return "{$m}m {$s}s";
    return "{$s}s";
}
?>

<div class="card">
    <div class="card-header">
        <span class="card-title">
            📋 Attempt History (<?= count($attempts) ?>) 
        </span>
        <form method="GET" action="index.php" class="flex gap-2">
            <input type="hidden" name="page"   value="student">
            <input type="hidden" name="action" value="attempt_history">
            <select name="course_id" class="form-control" style="width:200px"
                    onchange="this.form.submit()">
                <option value="">All Courses</option>
                <?php foreach ($enrolled as $e): ?>
                <option value="<?= $e['id'] ?>"
                    <?= $course_id==$e['id']?'selected':'' ?>>
                    <?= htmlspecialchars($e['title']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <div class="card-body" style="padding:0">
        <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Quiz</th>
                    <th>Course</th>
                    <th>Type</th>
                    <th>Score</th>
                    <th>Grade</th>
                    <th>Result</th>
                    <th>Time Taken</th>
                    <th>Date & Time</th>
                    <th></th>
                </tr>
            </thead>
            <tbody> 
            <?php if (empty($attempts)): ?>
            <tr>
                <td colspan="9" class="text-center text-muted"
                    style="padding:40px">
                    No attempts found.
                </td>
            </tr>
            <?php else: ?>
            <?php foreach ($attempts as $a): ?>
            <?php
                $pass  = $a['score'] >= $a['pass_mark'];
                $pct   = $a['total_marks'] > 0
                       ? round(($a['score'] / $a['total_marks']) * 100)
                       : 0;
                [$grade, $gc] = getGrade($pct);
                $dur = strtotime($a['completed_at'])
                     - strtotime($a['started_at']);
            ?>
            <tr>
                <td>
                    <div class="fw-600 text-sm">
                        <?= htmlspecialchars($a['quiz_title']) ?>
                    </div>
                </td>
                <td class="text-sm text-gray">
                    <?= htmlspecialchars($a['course_title']) ?>
                </td>
                <td>
                    <span class="badge badge-<?= $a['quiz_type']==='graded'?'info':'purple' ?>">
                        <?= ucfirst($a['quiz_type']) ?>
                    </span>
                </td>
                <td class="fw-bold">
                    <?= round($a['score']) ?> / <?= $a['total_marks'] ?>
                </td>
                <td>
                    <span style="font-weight:900;font-size:16px;color:<?= $gc ?>">
                        <?= $grade ?>
                    </span>
                </td>
                <td>
                    <span class="badge badge-<?= $pass?'success':'danger' ?>">
                        <?= $pass ? 'Pass' : 'Fail' ?>
                    </span>
                </td>
                <td class="text-sm">
                    <?= fmtDuration($dur) ?>
                </td>
                <td class="text-sm text-muted">
                    <?= date('M d, Y — H:i:s', strtotime($a['completed_at'])) ?>
                </td>
                <td>
                    <a href="index.php?page=student&action=quiz_result&attempt_id=<?= $a['id'] ?>"
                       class="btn btn-outline btn-sm">View</a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<?php require 'views/layout/footer.php'; ?>