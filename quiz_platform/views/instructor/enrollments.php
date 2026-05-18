<?php require 'views/layout/header.php'; ?>

<div class="card">
    <div class="card-header">
        <span class="card-title">📋 Enrollments — <?= htmlspecialchars($course['title']) ?></span>
        <a href="index.php?page=instructor&action=edit_course&course_id=<?= $course['id'] ?>" class="btn btn-secondary btn-sm">← Back</a>
    </div>
    <div class="card-body" style="padding:0">
        <div class="table-wrap">
        <table>
            <thead><tr><th>Name</th><th>Email</th><th>Student ID</th><th>Status</th><th>Date</th><th>Action</th></tr></thead>
            <tbody>
            <?php if (empty($enrollments)): ?>
            <tr><td colspan="6" class="text-center text-muted" style="padding:30px">No enrollment requests.</td></tr>
            <?php else: ?>
            <?php foreach ($enrollments as $e): ?>
            <tr>
                <td class="fw-600"><?= htmlspecialchars($e['name']) ?></td>
                <td class="text-sm text-gray"><?= htmlspecialchars($e['email']) ?></td>
                <td class="text-sm"><?= htmlspecialchars($e['student_id']) ?></td>
                <td>
                    <span class="badge badge-<?= $e['status']==='active'?'success':($e['status']==='pending'?'warning':'danger') ?>">
                        <?= ucfirst($e['status']) ?>
                    </span>
                </td>
                <td class="text-sm text-muted"><?= date('M d, Y', strtotime($e['enrolled_at'])) ?></td>
                <td>
                    <?php if ($e['status']==='pending'): ?>
                    <form method="POST" action="index.php?page=instructor&action=approve_enroll" class="flex gap-2">
                        <input type="hidden" name="enroll_id"  value="<?= $e['id'] ?>">
                        <input type="hidden" name="course_id"  value="<?= $course['id'] ?>">
                        <button name="new_status" value="active"  class="btn btn-success btn-sm">✓ Approve</button>
                        <button name="new_status" value="dropped" class="btn btn-danger  btn-sm">✕ Reject</button>
                    </form>
                    <?php else: ?>
                        <span class="text-xs text-muted">—</span>
                    <?php endif; ?>
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