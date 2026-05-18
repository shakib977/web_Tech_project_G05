<?php require 'views/layout/header.php'; ?>

<!-- AJAX Grade Analytics (Member 2 Feature) -->
<div class="card" style="margin-bottom:20px">
    <div class="card-header"><span class="card-title">📊 Live Grade Analytics (AJAX)</span></div>
    <div class="card-body">
        <div class="form-group">
            <label class="form-label">Select Quiz for Live Analysis</label>
            <select class="form-control" onchange="loadGradeAnalytics(this.value,'live_analytics_box')">
                <option value="">Choose a quiz...</option>
                <?php foreach ($quizzes as $q): ?>
                <option value="<?= $q['id'] ?>"><?= htmlspecialchars($q['course_title'].' → '.$q['title']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div id="live_analytics_box">
            <p class="text-center text-muted text-sm" style="padding:16px">Select a quiz to see live statistics.</p>
        </div>
    </div>
</div>

<!-- Static Analytics Table -->
<div class="card">
    <div class="card-header"><span class="card-title">📋 All Quiz Statistics</span></div>
    <div class="card-body" style="padding:0">
        <div class="table-wrap">
        <table>
            <thead><tr><th>Quiz</th><th>Course</th><th>Attempts</th><th>Avg Score</th><th>Highest</th><th>Lowest</th><th>Pass Rate</th></tr></thead>
            <tbody>
            <?php if (empty($quizzes)): ?>
            <tr><td colspan="7" class="text-center text-muted" style="padding:30px">No quiz data yet.</td></tr>
            <?php else: ?>
            <?php foreach ($quizzes as $q): ?>
            <?php
                $pass_rate = $q['attempt_count']>0 ? round(
                    $conn->query("SELECT COUNT(*) FROM attempts WHERE quiz_id={$q['id']} AND score>={$q['pass_mark']} AND completed_at IS NOT NULL")->fetch_row()[0]
                    / $q['attempt_count'] * 100
                ) : 0;
            ?>
            <tr>
                <td class="fw-600 text-sm"><?= htmlspecialchars($q['title']) ?></td>
                <td class="text-sm text-gray"><?= htmlspecialchars($q['course_title']) ?></td>
                <td><?= $q['attempt_count'] ?></td>
                <td><?= $q['avg_score'] ?></td>
                <td class="text-success fw-bold"><?= $q['highest'] ?></td>
                <td class="text-danger fw-bold"><?= $q['lowest'] ?></td>
                <td><span class="badge <?= $pass_rate>=60?'badge-success':'badge-danger' ?>"><?= $pass_rate ?>%</span></td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<?php require 'views/layout/footer.php'; ?>