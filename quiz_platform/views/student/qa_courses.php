<?php
// views/student/qa_courses.php — MEMBER 1
require 'views/layout/header.php';
?>

<p class="text-sm text-muted" style="margin-bottom:16px">
    Select a course to view or post questions.
</p>

<?php if (empty($courses)): ?>
    <div class="empty-state card" style="padding:60px">
        <div class="empty-icon">❓</div>
        <h3>No enrolled courses</h3>
        <p>Enroll in a course to access its Q&A board.</p>
        <a href="index.php?page=student&action=browse_courses"
           class="btn btn-primary">Browse Courses</a>
    </div>
<?php else: ?>
<div style="display:flex;flex-direction:column;gap:12px">
    <?php foreach ($courses as $c): ?>
    <a href="index.php?page=student&action=qa_board&course_id=<?= $c['id'] ?>"
       style="text-decoration:none">
        <div class="card" style="margin-bottom:0;transition:var(--transition)"
             onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='var(--shadow-md)'"
             onmouseout="this.style.transform='';this.style.boxShadow=''">
            <div class="card-body"
                 style="display:flex;align-items:center;gap:16px;padding:18px 20px">
                <div style="width:46px;height:46px;border-radius:12px;
                            background:linear-gradient(135deg,var(--primary),var(--secondary));
                            display:flex;align-items:center;justify-content:center;
                            font-size:20px;flex-shrink:0">❓</div>
                <div style="flex:1">
                    <div class="fw-bold" style="font-size:15px;color:var(--dark)">
                        <?= htmlspecialchars($c['title']) ?>
                    </div>
                    <div class="text-sm text-muted" style="margin-top:2px">
                        <?= htmlspecialchars($c['subject']) ?> •
                        <?= $c['total_questions'] ?> question<?= $c['total_questions']!=1?'s':'' ?> total
                        <?php if ($c['my_questions']): ?>
                        • <span class="text-primary"><?= $c['my_questions'] ?> from you</span>
                        <?php endif; ?>
                    </div>
                </div>
                <span style="color:var(--primary);font-size:20px">→</span>
            </div>
        </div>
    </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php require 'views/layout/footer.php'; ?>