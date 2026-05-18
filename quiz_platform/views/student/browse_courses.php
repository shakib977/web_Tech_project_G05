<?php
// views/student/browse_courses.php — MEMBER 1
require 'views/layout/header.php';
?>

<!-- Filter Bar -->
<!-- Filter Bar — auto submit on change, no button -->
<div class="card" style="margin-bottom:20px">
    <div class="card-body">
        <form method="GET" action="index.php" id="browse_filter_form">
            <input type="hidden" name="page"   value="student"> 
            <input type="hidden" name="action" value="browse_courses">
            <div class="flex gap-3 flex-wrap">

                <!-- Search — auto-submit after 600ms typing pause --> 
                <div style="flex:2;min-width:200px">
                    <input type="text" name="search" id="search_input"
                           class="form-control"
                           placeholder="🔍 Search courses, instructors..."
                           value="<?= htmlspecialchars($search) ?>">
                </div>

                <!-- Subject — auto-submit on change -->
                <div style="flex:1;min-width:150px">
                    <select name="subject_id" class="form-control"
                            onchange="document.getElementById('browse_filter_form').submit()">
                        <option value="">All Subjects</option>
                        <?php foreach ($subjects as $s): ?>
                        <option value="<?= $s['id'] ?>"
                            <?= $subject_id==$s['id']?'selected':'' ?>> 
                            <?= htmlspecialchars($s['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Enrolled only toggle — auto-submit -->
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;
                              font-size:14px;font-weight:600;white-space:nowrap;
                              padding:10px 16px;border-radius:var(--radius-sm);
                              border:1.5px solid <?= $enrolled_only?'var(--primary)':'var(--border)' ?>;
                              background:<?= $enrolled_only?'#EEF2FF':'var(--white)' ?>;
                              color:<?= $enrolled_only?'var(--primary)':'var(--gray)' ?>">
                    <input type="checkbox" name="enrolled_only" value="1"
                           <?= $enrolled_only?'checked':'' ?>
                           onchange="document.getElementById('browse_filter_form').submit()" 
                           style="width:16px;height:16px;accent-color:var(--primary)">
                    ✅ Enrolled  
                </label>

                <!-- Reset -->
                <a href="index.php?page=student&action=browse_courses"
                   class="btn btn-secondary">Reset</a>
            </div>
        </form>
    </div> 
</div>

<script>
// Auto-submit search after user stops typing (600ms)
var searchTimer; 
document.getElementById('search_input').addEventListener('input', function() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(function() { 
        document.getElementById('browse_filter_form').submit();
    }, 600);
}); 
</script>
 
<!-- Results count -->
<p class="text-sm text-muted" style="margin-bottom:16px">
    Showing <strong><?= count($courses) ?></strong> course<?= count($courses)!=1?'s':'' ?>
    <?php if ($enrolled_only): ?> 
        <span style="background:#EEF2FF;color:var(--primary); 
                     padding:2px 10px;border-radius:20px;font-size:12px; 
                     font-weight:600;margin-left:8px">
            Enrolled only
        </span> 
    <?php endif; ?>
</p>

<?php if (empty($courses)): ?>
    <div class="empty-state card" style="padding:60px">
        <div class="empty-icon">🔍</div>
        <h3>No courses found</h3> 
        <p>Try different filters or search terms.</p>
        <a href="index.php?page=student&action=browse_courses"
           class="btn btn-primary">Clear Filters</a>
    </div>
<?php else: ?>
<div class="course-grid">
    <?php foreach ($courses as $c): ?>  
    <div class="course-card">
        <div class="course-card-banner">
            <h3><?= htmlspecialchars($c['title']) ?></h3>
            <p>📂 <?= htmlspecialchars($c['subject']) ?></p>
        </div>
        <div class="course-card-body">
            <p class="text-sm text-gray"
               style="margin-bottom:12px;line-height:1.5;
                      display:-webkit-box;-webkit-line-clamp:2;
                      -webkit-box-orient:vertical;overflow:hidden">
                <?= htmlspecialchars($c['description'] ?: 'No description.') ?>
            </p>
            <div class="course-card-meta">
                <span class="meta-item">👨‍🏫 <?= htmlspecialchars($c['instructor']) ?></span>
                <span class="meta-item">👥 <?= $c['enrolled_count'] ?>/<?= $c['max_students'] ?></span>
                <span class="meta-item">
                    <?php if ($c['enrollment_type']==='open'): ?>
                        <span class="badge badge-success">Open</span>
                    <?php else: ?>
                        <span class="badge badge-warning">Approval</span>
                    <?php endif; ?>
                </span>
            </div>
        </div>
        <div class="course-card-footer">
            <?php if ($c['my_status']==='active'): ?>
                <a href="index.php?page=student&action=course_detail&course_id=<?= $c['id'] ?>"
                   class="btn btn-success btn-sm">View Course →</a>
                <span class="badge badge-success">✅ Enrolled</span>
            <?php elseif ($c['my_status']==='pending'): ?>
                <span class="badge badge-warning">⏳ Pending Approval</span>
                <span class="text-xs text-muted">Awaiting instructor</span>
            <?php else: ?>
                <button class="btn btn-primary btn-sm"
                        onclick="enrollCourse(<?= $c['id'] ?>, this)">
                    Enroll<?= $c['enrollment_type']==='approval'?' (Request)':'' ?>
                </button>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>


<?php require 'views/layout/footer.php'; ?>