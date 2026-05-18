<?php
// views/ta/qa_pick_course.php — MEMBER 3
require 'views/layout/header.php';
?>

<div style="max-width:600px;margin:0 auto">
    <div class="card">
        <div class="card-header"><span class="card-title">❓ Q&A Board — Select a Course</span></div>
        <div class="card-body" style="padding:0">
            <?php if (empty($my_courses)): ?>
                <div class="empty-state" style="padding:40px">
                    <div class="empty-icon">📚</div>
                    <h3>No courses assigned</h3>
                    <p>You haven't been assigned to any courses yet.</p>
                </div>
            <?php else: ?>
            <?php foreach ($my_courses as $c): ?>
            <a href="index.php?page=ta&action=qa_board&course_id=<?= $c['id'] ?>"
               style="display:flex;align-items:center;justify-content:space-between;
                      padding:16px 20px;border-bottom:1px solid var(--border);
                      text-decoration:none;transition:var(--transition)"
               onmouseover="this.style.background='var(--light)'"
               onmouseout="this.style.background=''">
                <div style="display:flex;align-items:center;gap:14px">
                    <div style="width:42px;height:42px;border-radius:10px;
                                background:linear-gradient(135deg,var(--warning),var(--secondary));
                                display:flex;align-items:center;justify-content:center;font-size:20px">❓</div>
                    <div>
                        <div style="font-weight:600;font-size:14px;color:var(--dark)"><?= htmlspecialchars($c['title']) ?></div>
                        <div style="font-size:12px;color:var(--gray);margin-top:2px">Click to open Q&A board</div>
                    </div>
                </div>
                <?php if ($c['unresolved'] > 0): ?>
                    <span class="badge badge-danger"><?= $c['unresolved'] ?> unanswered</span>
                <?php else: ?>
                    <span class="badge badge-success">All resolved ✓</span>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require 'views/layout/footer.php'; ?>