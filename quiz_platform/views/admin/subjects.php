<?php require 'views/layout/header.php'; ?>

<?php if (!empty($error)):   ?><div class="alert alert-danger">⚠️ <?= $error ?></div><?php endif; ?>
<?php if (!empty($success)): ?><div class="alert alert-success">✓ <?= $success ?></div><?php endif; ?>

<div class="grid-2">
    <!-- Subject List -->
    <div class="card">
        <div class="card-header"><span class="card-title">📂 Subjects (<?= count($subjects) ?>)</span></div>
        <div class="card-body" style="padding:0">
            <?php if (empty($subjects)): ?>
                <div class="empty-state"><div class="empty-icon">📂</div><p>No subjects yet.</p></div>
            <?php else: ?>
            <?php foreach ($subjects as $s): ?>
            <div style="display:flex;align-items:center;gap:12px;padding:14px 20px;border-bottom:1px solid var(--border)">
                <div style="flex:1">
                    <div class="fw-600"><?= htmlspecialchars($s['name']) ?></div>
                    <div class="text-xs text-muted"><?= htmlspecialchars($s['description'] ?: 'No description') ?> • <?= $s['course_count'] ?> course<?= $s['course_count']!=1?'s':'' ?></div>
                </div>
                <div class="flex gap-2">
                    <button class="btn btn-outline btn-sm" onclick="openEditSubject(<?= $s['id'] ?>, '<?= addslashes($s['name']) ?>', '<?= addslashes($s['description']) ?>')">Edit</button>
                    <?php if ($s['course_count'] == 0): ?>
                    <a href="index.php?page=admin&action=delete_subject&id=<?= $s['id'] ?>"
                       class="btn btn-danger btn-sm"
                       onclick="return confirm('Delete subject \'<?= htmlspecialchars($s['name']) ?>\'?')">Delete</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add Subject -->
    <div>
        <div class="card">
            <div class="card-header"><span class="card-title">➕ Add Subject</span></div>
            <div class="card-body">
                <form method="POST" action="index.php?page=admin&action=add_subject">
                    <div class="form-group">
                        <label class="form-label">Subject Name <span class="required">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Computer Science" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Brief description..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Subject</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal-overlay" id="edit_subject_modal">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">Edit Subject</span>
            <button class="modal-close" onclick="closeModal('edit_subject_modal')">✕</button>
        </div>
        <form method="POST" action="index.php?page=admin&action=edit_subject">
            <div class="modal-body">
                <input type="hidden" name="id" id="edit_subject_id">
                <div class="form-group">
                    <label class="form-label">Name <span class="required">*</span></label>
                    <input type="text" name="name" id="edit_subject_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" id="edit_subject_desc" class="form-control" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('edit_subject_modal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditSubject(id, name, desc) {
    document.getElementById('edit_subject_id').value = id;
    document.getElementById('edit_subject_name').value = name;
    document.getElementById('edit_subject_desc').value = desc;
    openModal('edit_subject_modal');
}
</script>

<?php require 'views/layout/footer.php'; ?>