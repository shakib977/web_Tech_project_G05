<?php
// views/student/quiz_result.php — MEMBER 1
require 'views/layout/header.php';

$pass = $attempt['score'] >= $attempt['pass_mark'];
$pct  = $attempt['total_marks'] > 0
      ? round(($attempt['score'] / $attempt['total_marks']) * 100)
      : 0;

function getGrade($pct) {
    if ($pct >= 90) return ['A+', '#059669', '#D1FAE5'];
    if ($pct >= 80) return ['B+', '#2563EB', '#DBEAFE'];
    if ($pct >= 70) return ['C+', '#D97706', '#FEF3C7'];
    if ($pct >= 50) return ['D+', '#7C3AED', '#EDE9FE'];
    return ['F',   '#DC2626', '#FEE2E2'];
}
[$grade, $grade_color, $grade_bg] = getGrade($pct);

// All attempts for this quiz
$uid = $_SESSION['user_id'];
$prev_stmt = $conn->prepare(
    "SELECT a.id, a.score, a.started_at, a.completed_at
     FROM attempts a
     WHERE a.quiz_id=? AND a.student_id=? AND a.completed_at IS NOT NULL
     ORDER BY a.completed_at DESC"
);
$prev_stmt->bind_param('ii', $attempt['quiz_id'], $uid);
$prev_stmt->execute();
$all_attempts = $prev_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$prev_stmt->close();

// Duration helper
function fmtDuration($seconds) {
    $h = floor($seconds / 3600);
    $m = floor(($seconds % 3600) / 60);
    $s = $seconds % 60;
    if ($h > 0) return "{$h}h {$m}m {$s}s";
    if ($m > 0) return "{$m}m {$s}s";
    return "{$s}s";
}
?>

<div style="max-width:780px;margin:0 auto">

    <!-- Result Summary -->
    <div class="card" style="margin-bottom:20px">
        <div class="card-body" style="padding:36px 28px;text-align:center">

            <!-- Score + Grade side by side -->
            <div style="display:flex;align-items:center;justify-content:center;
                        gap:32px;flex-wrap:wrap;margin-bottom:24px">

                <!-- Score circle -->
                <div class="result-circle <?= $pass?'pass':'fail' ?>">
                    <div class="result-num"><?= round($attempt['score']) ?></div>
                    <div class="result-lbl">/ <?= $attempt['total_marks'] ?></div>
                </div>

                <!-- Grade badge (big) -->
                <div>
                    <div style="width:100px;height:100px;border-radius:50%;
                                background:<?= $grade_bg ?>;
                                border:5px solid <?= $grade_color ?>;
                                display:flex;align-items:center;justify-content:center;
                                margin:0 auto 8px">
                        <span style="font-size:36px;font-weight:900;
                                     color:<?= $grade_color ?>">
                            <?= $grade ?>
                        </span>
                    </div>
                    <div style="font-size:13px;color:var(--gray);font-weight:600">
                        Grade
                    </div>
                </div>

            </div>

            <!-- Pass/Fail -->
            <h2 style="font-size:22px;font-weight:800;margin-bottom:8px">
                <?= $pass ? '🎉 Congratulations!' : '😔 Better luck next time' ?>
            </h2>
            <p style="color:var(--gray);font-size:14px;margin-bottom:20px">
                <?= htmlspecialchars($attempt['title']) ?> —
                Score: <strong><?= round($attempt['score']) ?>/<?= $attempt['total_marks'] ?></strong> —
                <span style="font-weight:700;
                             color:<?= $pass?'var(--success)':'var(--danger)' ?>">
                    <?= $pass ? 'PASSED' : 'FAILED' ?>
                </span>
            </p>

            <!-- Stats -->
            <?php
            $dur = strtotime($attempt['completed_at']) - strtotime($attempt['started_at']);
            ?>
            <div style="display:flex;justify-content:center;gap:32px;flex-wrap:wrap;
                        padding-top:16px;border-top:1px solid var(--border)">
                <div>
                    <div style="font-size:22px;font-weight:800;
                                color:<?= $grade_color ?>">
                        <?= $grade ?>
                    </div>
                    <div style="font-size:12px;color:var(--gray)">Grade</div>
                </div>
                <div>
                    <div style="font-size:22px;font-weight:800">
                        <?= $attempt['pass_mark'] ?>
                    </div>
                    <div style="font-size:12px;color:var(--gray)">Pass Mark</div>
                </div>
                <div>
                    <div style="font-size:22px;font-weight:800">
                        <?= fmtDuration($dur) ?>
                    </div>
                    <div style="font-size:12px;color:var(--gray)">Time Taken</div>
                </div>
                <div>
                    <div style="font-size:22px;font-weight:800">
                        <?= count($all_attempts) ?>
                    </div>
                    <div style="font-size:12px;color:var(--gray)">
                        Total Attempt<?= count($all_attempts)!=1?'s':'' ?>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- All Attempts -->
    <?php if (count($all_attempts) >= 1): ?>
    <div class="card" style="margin-bottom:20px">
        <div class="card-header">
            <span class="card-title">
                🕒 All Attempts (<?= count($all_attempts) ?>)
            </span>
        </div>
        <div class="card-body" style="padding:0">
            <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Score</th>
                        <th>Grade</th>
                        <th>Result</th>
                        <th>Time Taken</th>
                        <th>Date & Time</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($all_attempts as $idx => $att): ?>
                <?php
                    $a_pct  = $attempt['total_marks'] > 0
                            ? round(($att['score'] / $attempt['total_marks']) * 100)
                            : 0;
                    $a_pass = $att['score'] >= $attempt['pass_mark'];
                    [$a_grade, $a_gc] = getGrade($a_pct);
                    $a_dur  = strtotime($att['completed_at'])
                            - strtotime($att['started_at']);
                    $is_cur = ($att['id'] == $attempt['id']);
                ?>
                <tr style="<?= $is_cur ? 'background:#EEF2FF' : '' ?>">
                    <td class="text-muted text-sm">
                        <?= count($all_attempts) - $idx ?>
                        <?php if ($is_cur): ?>
                        <span style="font-size:10px;color:var(--primary);
                                     font-weight:700"> ← now</span>
                        <?php endif; ?>
                    </td>
                    <td class="fw-bold">
                        <?= round($att['score']) ?>/<?= $attempt['total_marks'] ?>
                    </td>
                    <td>
                        <span style="font-weight:900;font-size:16px;
                                     color:<?= $a_gc ?>">
                            <?= $a_grade ?>
                        </span>
                    </td>
                    <td>
                        <span class="badge badge-<?= $a_pass?'success':'danger' ?>">
                            <?= $a_pass ? 'Pass' : 'Fail' ?>
                        </span>
                    </td>
                    <td class="text-sm"><?= fmtDuration($a_dur) ?></td>
                    <td class="text-sm text-muted">
                        <?= date('M d, Y — H:i:s', strtotime($att['completed_at'])) ?>
                    </td>
                    <td>
                        <?php if (!$is_cur): ?>
                        <a href="index.php?page=student&action=quiz_result&attempt_id=<?= $att['id'] ?>"
                           class="btn btn-outline btn-sm">View</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Question Breakdown -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">📋 Question Breakdown</span>
            <span class="text-sm text-muted">
                <?= count(array_filter($breakdown, fn($b) => $b['is_correct'])) ?>
                / <?= count($breakdown) ?> correct
            </span>
        </div>
        <div class="card-body" style="padding:0">
            <?php foreach ($breakdown as $i => $b): ?>
            <div style="padding:20px;border-bottom:1px solid var(--border)">
                <div style="display:flex;align-items:center;
                            justify-content:space-between;margin-bottom:10px">
                    <span style="font-size:12px;font-weight:700;
                                 color:var(--primary);text-transform:uppercase;
                                 letter-spacing:1px">
                        Question <?= $i+1 ?>
                    </span>
                    <?php if ($b['is_correct']): ?>
                        <span class="badge badge-success">
                            ✓ +<?= $b['marks'] ?> mark<?= $b['marks']!=1?'s':'' ?>
                        </span>
                    <?php elseif ($b['selected_option_id']): ?>
                        <span class="badge badge-danger">✕ 0 marks</span>
                    <?php else: ?>
                        <span class="badge badge-gray">— Skipped</span>
                    <?php endif; ?>
                </div>

                <p style="font-size:15px;font-weight:600;
                           margin-bottom:14px;line-height:1.6">
                    <?= nl2br(htmlspecialchars($b['question_text'])) ?>
                </p>

                <?php if ($b['selected_option_id'] && !$b['is_correct']): ?>
                <div class="option-item wrong-ans"
                     style="cursor:default;margin-bottom:8px">
                    <span>✕</span>
                    <span>
                        Your answer: <?= htmlspecialchars($b['selected_text']) ?>
                    </span>
                </div>
                <?php endif; ?>

                <?php if (!$b['is_correct']): ?>
                <div class="option-item correct-ans" style="cursor:default">
                    <span>✓</span>
                    <span>
                        Correct answer: <?= htmlspecialchars($b['correct_text']) ?>
                    </span>
                </div>
                <?php elseif ($b['selected_option_id']): ?>
                <div class="option-item correct-ans" style="cursor:default">
                    <span>✓</span>
                    <span><?= htmlspecialchars($b['selected_text']) ?></span>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="card-footer flex-between">
            <a href="index.php?page=student&action=course_detail&course_id=<?= $attempt['course_id'] ?>"
               class="btn btn-outline">← Back to Course</a>
            <a href="index.php?page=student&action=attempt_history"
               class="btn btn-secondary">📋 All My Attempts</a>
        </div>
    </div>

</div>

<?php require 'views/layout/footer.php'; ?>