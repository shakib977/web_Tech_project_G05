<?php
// views/admin/courses.php — MEMBER 4
require 'views/layout/header.php';
?>

<div class="card">
    <div class="card-header">
        <span class="card-title">📚 All Courses (<?= count($courses) ?>)</span>
    </div>

    <!-- Filters -->
    <div class="card-body" style="padding:14px 20px;border-bottom:1px solid var(--border)">
        <form method="GET" action="index.php" class="flex gap-3 flex-wrap">
            <input type="hidden" name="page"   value="admin">
            <input type="hidden" name="action" value="courses">
            <select name="subject_id" class="form-control" style="width:180px">
                <option value="">All Subjects</option>
                <?php foreach ($subjects as $s): ?>
                <option value="<?= $s['id'] ?>"
                    <?= ($_GET['subject_id']??0)==$s['id']?'selected':'' ?>>
                    <?= htmlspecialchars($s['name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
            <select name="status" class="form-control" style="width:140px">
                <option value="">All Status</option>
                <option value="active"   <?= ($_GET['status']??'')==='active'  ?'selected':'' ?>>Active</option>
                <option value="draft"    <?= ($_GET['status']??'')==='draft'   ?'selected':'' ?>>Draft</option>
                <option value="archived" <?= ($_GET['status']??'')==='archived'?'selected':'' ?>>Archived</option>
            </select>
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="index.php?page=admin&action=courses" class="btn btn-secondary">Reset</a>
        </form>
    </div>

    <div class="card-body" style="padding:0">
        <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Course</th>
                    <th>Subject</th>
                    <th>Instructor</th>
                    <th>Students</th>
                    <th>Quizzes</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($courses)): ?>
            <tr>
                <td colspan="7" class="text-center text-muted" style="padding:40px">
                    No courses found.
                </td>
            </tr>
            <?php else: ?>
            <?php foreach ($courses as $c): ?>
            <tr>
                <td>
                    <div class="fw-600"><?= htmlspecialchars($c['title']) ?></div>
                    <div class="text-xs text-muted"><?= date('M d, Y', strtotime($c['created_at'])) ?></div>
                </td>
                <td class="text-sm text-gray"><?= htmlspecialchars($c['subject']) ?></td>
                <td class="text-sm"><?= htmlspecialchars($c['instructor']) ?></td>
                <td>
                    <a href="index.php?page=admin&action=course_students&course_id=<?= $c['id'] ?>"
                       class="fw-bold text-primary"
                       style="text-decoration:none"
                       title="View enrolled students">
                        👥 <?= $c['enrolled_count'] ?>
                    </a>
                </td>
                <td><?= $c['quiz_count'] ?></td>
                <td>
                    <span class="badge badge-<?= $c['status']==='active'?'success':($c['status']==='archived'?'gray':'warning') ?>">
                        <?= ucfirst($c['status']) ?>
                    </span>
                </td>
                <td>
                    <div class="flex gap-2">
                        <!-- MANAGE button — opens full course management page -->
                        <a href="index.php?page=admin&action=course_manage&course_id=<?= $c['id'] ?>"
                           class="btn btn-primary btn-sm">
                            ⚙️ Manage
                        </a>
                        <!-- STUDENTS button — dedicated student list -->
                        <a href="index.php?page=admin&action=course_students&course_id=<?= $c['id'] ?>"
                           class="btn btn-info btn-sm">
                            👥 Students
                        </a>
                    </div>
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