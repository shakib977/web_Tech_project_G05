<?php
// views/instructor/dashboard.php — MEMBER 2
require 'views/layout/header.php';
?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon icon-blue">📚</div>
        <div class="stat-info"><h3><?= $active_courses ?></h3><p>Active Courses</p></div>
    </div>

    <div class="stat-card">
        <div class="stat-icon icon-yellow">📝</div>
        <div class="stat-info"><h3><?= $total_quizzes ?></h3><p>Total Quizzes</p></div>
    </div>
    <a href="index.php?page=instructor&action=pending_enrollments"
   style="text-decoration:none">
    <div class="stat-card" style="cursor:pointer;border:2px solid <?= $pending_enrollments>0?'var(--warning)':'var(--border)' ?>">
        <div class="stat-icon icon-yellow">⏳</div>
        <div class="stat-info">
            <h3 style="color:<?= $pending_enrollments>0?'var(--warning)':'var(--dark)' ?>">
                <?= $pending_enrollments ?>
            </h3>
            <p>
                Pending Enrollments
                <?php if ($pending_enrollments > 0): ?>
                <span style="display:block;font-size:11px;
                             color:var(--warning);font-weight:700;
                             margin-top:2px">
                    Click to review →
                </span>
                <?php endif; ?>
            </p>
        </div>
    </div>
</a>
</div>

<div class="grid-2">

    <!-- My Courses -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">📚 My Courses</span>
            <a href="index.php?page=instructor&action=create_course" class="btn btn-primary btn-sm">+ New Course</a>
        </div>
        <div class="card-body" style="padding:0">
            <?php if (empty($recent_courses)): ?>
                <div class="empty-state">
                    <div class="empty-icon">📚</div>
                    <h3>No courses yet</h3>
                    <p>Create your first course to get started.</p>
                    <a href="index.php?page=instructor&action=create_course" class="btn btn-primary">Create Course</a>
                </div>
            <?php else: ?>
            <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Course</th>
                        <th>Subject</th>
                        <th>Students</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($recent_courses as $c): ?>
                <tr>
                    <td class="fw-600"><?= htmlspecialchars($c['title']) ?></td>
                    <td class="text-gray text-sm"><?= htmlspecialchars($c['subject']) ?></td>
                    <td><?= $c['enrolled'] ?></td>
                    <td>
                        <span class="badge badge-<?= $c['status']==='active' ? 'success' : ($c['status']==='archived' ? 'gray' : 'warning') ?>">
                            <?= ucfirst($c['status']) ?>
                        </span>
                    </td>
                    <td>
                        <div class="flex gap-2">
                            <a href="index.php?page=instructor&action=edit_course&course_id=<?= $c['id'] ?>"
                               class="btn btn-outline btn-sm">Manage</a>
                            <a href="index.php?page=instructor&action=create_quiz&course_id=<?= $c['id'] ?>"
                               class="btn btn-info btn-sm">+ Quiz</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
            <?php endif; ?>
        </div>
        <?php if (!empty($recent_courses)): ?>
        <div class="card-footer">
            <a href="index.php?page=instructor&action=courses" class="btn btn-secondary btn-sm">View All Courses →</a>
        </div>
        <?php endif; ?>
    </div>

    <!-- Right Column -->
    <div>
        <!-- Quick Actions -->
        <div class="card" style="margin-bottom:20px">
            <div class="card-header"><span class="card-title">⚡ Quick Actions</span></div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:10px">
                <a href="index.php?page=instructor&action=create_course" class="btn btn-primary">📚 Create New Course</a>
                <a href="index.php?page=instructor&action=announcements" class="btn btn-info">📣 Post Announcement</a>
                <a href="index.php?page=instructor&action=analytics"     class="btn btn-secondary">📊 View Analytics</a>
                <?php if ($pending_enrollments > 0): ?>
                <a href="index.php?page=instructor&action=courses" class="btn btn-warning">
                    ⏳ <?= $pending_enrollments ?> Pending Enrollment<?= $pending_enrollments != 1 ? 's' : '' ?>
                </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Live Grade Analytics — AJAX Feature (Member 2) -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">📊 Live Grade Analytics</span>
                <span class="badge badge-info" style="font-size:11px">AJAX</span>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Select a Quiz</label>
                    <select class="form-control"
                            onchange="loadGradeAnalytics(this.value, 'analytics_box')">
                        <option value="">Choose a quiz to analyse...</option>
                        <?php
                        $q_res = $conn->query(
                            "SELECT q.id, q.title, c.title AS ct
                             FROM quizzes q
                             JOIN courses c ON q.course_id = c.id
                             WHERE c.instructor_id = {$uid}
                             ORDER BY c.title, q.title"
                        );
                        while ($qr = $q_res->fetch_assoc()):
                        ?>
                        <option value="<?= $qr['id'] ?>">
                            <?= htmlspecialchars($qr['ct'] . ' → ' . $qr['title']) ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div id="analytics_box">
                    <p class="text-center text-muted text-sm" style="padding:16px">
                        Select a quiz to see live class statistics.
                    </p>
                </div>
            </div>
        </div>
    </div>

</div>

<?php require 'views/layout/footer.php'; ?>