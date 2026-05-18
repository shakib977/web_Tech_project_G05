<?php
// views/instructor/pending_enrollments.php — MEMBER 2
require 'views/layout/header.php';
?>

<?php if (empty($pending)): ?>
    <div class="empty-state card" style="padding:60px">
        <div class="empty-icon">✅</div>
        <h3>No pending requests</h3>
        <p>All enrollment requests have been processed.</p>
        <a href="index.php?page=instructor&action=dashboard"
           class="btn btn-primary">← Back to Dashboard</a>
    </div>
<?php else: ?>

<div class="card">
    <div class="card-header">
        <span class="card-title">
            ⏳ Pending Enrollment Requests (<?= count($pending) ?>)
        </span>
        <a href="index.php?page=instructor&action=dashboard"
           class="btn btn-secondary btn-sm">← Dashboard</a>
    </div>
    <div class="card-body" style="padding:0">
        <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Student ID</th>
                    <th>Program</th>
                    <th>Course</th>
                    <th>Requested</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($pending as $p): ?>
            <tr>
                <td>
                    <div style="display:flex;align-items:center;gap:10px">
                        <div style="width:36px;height:36px;border-radius:50%;
                                    background:var(--primary);color:white;
                                    display:flex;align-items:center;
                                    justify-content:center;font-weight:700;
                                    font-size:14px;flex-shrink:0">
                            <?= strtoupper(substr($p['student_name'],0,1)) ?>
                        </div>
                        <div>
                            <div class="fw-600 text-sm">
                                <?= htmlspecialchars($p['student_name']) ?>
                            </div>
                            <div class="text-xs text-muted">
                                <?= htmlspecialchars($p['email']) ?>
                            </div>
                        </div>
                    </div>
                </td>
                <td class="text-sm">
                    <?= htmlspecialchars($p['student_id'] ?: '—') ?>
                </td>
                <td class="text-sm text-gray">
                    <?= htmlspecialchars($p['program'] ?: '—') ?>
                </td>
                <td>
                    <span class="fw-600 text-sm text-primary">
                        <?= htmlspecialchars($p['course_title']) ?>
                    </span>
                </td>
                <td class="text-sm text-muted">
                    <?= date('M d, Y H:i', strtotime($p['enrolled_at'])) ?>
                </td>
                <td>
                    <form method="POST"
                          action="index.php?page=instructor&action=approve_enroll"
                          class="flex gap-2">
                        <input type="hidden" name="enroll_id"
                               value="<?= $p['id'] ?>">
                        <input type="hidden" name="course_id"
                               value="<?= $p['course_id'] ?>">
                        <input type="hidden" name="redirect"
                               value="pending_enrollments">
                        <button name="new_status" value="active"
                                class="btn btn-success btn-sm">
                            ✓ Approve
                        </button>
                        <button name="new_status" value="dropped"
                                class="btn btn-danger btn-sm">
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