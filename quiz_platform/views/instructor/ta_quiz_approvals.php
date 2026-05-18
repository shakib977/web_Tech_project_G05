<?php
// views/instructor/ta_quiz_approvals.php — MEMBER 2
require 'views/layout/header.php';
?>

<?php if (!empty($_GET['done'])): ?>
<div class="alert alert-success">✅ Quiz approval processed.</div>
<?php endif; ?>

<?php if (empty($pending_quizzes)): ?>
    <div class="empty-state card" style="padding:60px">
        <div class="empty-icon">✅</div>
        <h3>No pending quiz approvals</h3>
        <p>All TA practice quizzes have been reviewed.</p>
    </div>
<?php else: ?>
<div class="card">
    <div class="card-header">
        <span class="card-title">📨 TA Quiz Approval Requests (<?= count($pending_quizzes) ?>)</span>
    </div>
    <div class="card-body" style="padding:0">
        <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Quiz Title</th>
                    <th>Course</th>
                    <th>Created By</th>
                    <th>Questions</th>
                    <th>Marks</th>
                    <th>Submitted</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($pending_quizzes as $q): ?>
            <tr>
                <td class="fw-600"><?= htmlspecialchars($q['title']) ?></td>
                <td class="text-sm text-gray"><?= htmlspecialchars($q['course_title']) ?></td>
                <td>
                    <div class="fw-600 text-sm"><?= htmlspecialchars($q['ta_name']) ?></div>
                    <div class="text-xs text-muted"><?= htmlspecialchars($q['ta_email']) ?></div>
                </td>
                <td><?= $q['question_count'] ?> questions</td>
                <td><?= $q['total_marks'] ?> marks</td>
                <td class="text-sm text-muted"><?= date('M d, Y', strtotime($q['id'])) ?></td>
                <td>
                    <form method="POST"
                          action="index.php?page=instructor&action=approve_ta_quiz"
                          class="flex gap-2">
                        <input type="hidden" name="quiz_id" value="<?= $q['id'] ?>">
                        <button name="approval_action" value="approve"
                                class="btn btn-success btn-sm"
                                onclick="return confirm('Approve and publish this quiz?')">
                            ✓ Approve
                        </button>
                        <button name="approval_action" value="reject"
                                class="btn btn-danger btn-sm"
                                onclick="return confirm('Reject and delete this quiz?')">
                            ✕ Reject
                        </button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require 'views/layout/footer.php'; ?>