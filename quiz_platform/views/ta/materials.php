<?php require 'views/layout/header.php'; ?>

<?php if (!empty($error)):   ?><div class="alert alert-danger">⚠️ <?= $error ?></div><?php endif; ?>
<?php if (!empty($success)): ?><div class="alert alert-success">✓ <?= $success ?></div><?php endif; ?>

<div class="grid-2">
    <div class="card">
        <div class="card-header"><span class="card-title">📤 Upload Material</span></div>
        <div class="card-body">
            <form method="POST" action="index.php?page=ta&action=upload_material" enctype="multipart/form-data">
                <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                <div class="form-group">
                    <label class="form-label">Title <span class="required">*</span></label>
                    <input type="text" name="title" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Type</label>
                    <select name="material_type" class="form-control" onchange="toggleMaterialInput(this.value)">
                        <option value="document">📄 Document</option>
                        <option value="link">🔗 Link</option>
                        <option value="video">🎥 Video</option>
                    </select>
                </div>
                <div class="form-group" id="file_input_group">
                    <input type="file" name="file" class="form-control">
                </div>
                <div class="form-group hidden" id="link_input_group">
                    <input type="url" name="link" class="form-control" placeholder="https://...">
                </div>
                <button type="submit" class="btn btn-primary">Upload</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><span class="card-title">📁 My Uploads (<?= count($materials) ?>)</span></div>
        <div class="card-body" style="padding:0">
            <?php if (empty($materials)): ?>
                <div class="empty-state"><div class="empty-icon">📁</div><p>No materials uploaded yet.</p></div>
            <?php else: ?>
            <?php foreach ($materials as $m): ?>
            <div style="display:flex;align-items:center;gap:12px;padding:13px 20px;border-bottom:1px solid var(--border)">
                <span style="font-size:20px"><?= $m['material_type']==='document'?'📄':($m['material_type']==='video'?'🎥':'🔗') ?></span>
                <div style="flex:1"><div class="fw-600 text-sm"><?= htmlspecialchars($m['title']) ?></div></div>
                <a href="index.php?page=ta&action=delete_material&mat_id=<?= $m['id'] ?>&course_id=<?= $course['id'] ?>"
                   class="btn btn-danger btn-sm" onclick="return confirm('Delete?')">Delete</a>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function toggleMaterialInput(type) {
    document.getElementById('file_input_group').classList.toggle('hidden', type==='link');
    document.getElementById('link_input_group').classList.toggle('hidden', type!=='link');
}
</script>

<?php require 'views/layout/footer.php'; ?>