<?php require 'views/layout/header.php'; ?>

<?php if (!empty($error)):   ?><div class="alert alert-danger">⚠️ <?= $error ?></div><?php endif; ?>
<?php if (!empty($success)): ?><div class="alert alert-success">✓ <?= $success ?></div><?php endif; ?>

<div class="grid-2">
    <!-- Create Session -->
    <div class="card">
        <div class="card-header"><span class="card-title">➕ Schedule Doubt Session</span></div>
        <div class="card-body">
            <form method="POST" action="index.php?page=ta&action=create_session">
                <div class="form-group">
                    <label class="form-label">Course <span class="required">*</span></label>
                    <select name="course_id" class="form-control" required>
                        <option value="">Select course...</option>
                        <?php foreach ($courses as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Session Title <span class="required">*</span></label>
                    <input type="text" name="title" class="form-control" placeholder="e.g. Mid-term doubt session" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Date & Time <span class="required">*</span></label>
                        <input type="datetime-local" name="scheduled_at" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Duration (min)</label>
                        <input type="number" name="duration_minutes" class="form-control" value="60" min="15">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Location / Link</label>
                        <input type="text" name="location_or_link" class="form-control" placeholder="Room 101 or Zoom link">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Max Attendees</label>
                        <input type="number" name="max_attendees" class="form-control" value="20" min="1">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Schedule Session</button>
            </form>
        </div>
    </div>

    <!-- Sessions List -->
    <div>
        <?php foreach ($sessions as $s): ?>
        <div class="session-card">
            <div class="session-date">📅 <?= date('D, M d Y • H:i', strtotime($s['scheduled_at'])) ?></div>
            <div class="fw-bold" style="margin-bottom:4px"><?= htmlspecialchars($s['title']) ?></div>
            <p class="text-sm text-muted" style="margin-bottom:8px">
                <?= htmlspecialchars($s['course_title']) ?> •
                <?= $s['bookings'] ?>/<?= $s['max_attendees'] ?> booked
                <?php if ($s['is_cancelled']): ?><span class="badge badge-danger ml-2" style="margin-left:8px">Cancelled</span><?php endif; ?>
            </p>
            <?php if (!$s['is_cancelled']): ?>
            <div class="flex gap-2">
                <a href="index.php?page=ta&action=session_bookings&session_id=<?= $s['id'] ?>" class="btn btn-outline btn-sm">View Bookings</a>
                <button class="btn btn-danger btn-sm" onclick="openModal('cancel_<?= $s['id'] ?>')">Cancel</button>
            </div>
            <?php endif; ?>
        </div>

        <!-- Cancel Modal -->
        <div class="modal-overlay" id="cancel_<?= $s['id'] ?>">
            <div class="modal">
                <div class="modal-header"><span class="modal-title">Cancel Session</span><button class="modal-close" onclick="closeModal('cancel_<?= $s['id'] ?>')">✕</button></div>
                <form method="POST" action="index.php?page=ta&action=cancel_session">
                    <input type="hidden" name="session_id" value="<?= $s['id'] ?>">
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="form-label">Reason for Cancellation</label>
                            <textarea name="reason" class="form-control" rows="3" placeholder="Explain why..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="closeModal('cancel_<?= $s['id'] ?>')">Back</button>
                        <button type="submit" class="btn btn-danger">Confirm Cancel</button>
                    </div>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($sessions)): ?>
            <div class="empty-state card" style="padding:40px">
                <div class="empty-icon">🎓</div><h3>No sessions yet</h3><p>Schedule your first doubt session.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require 'views/layout/footer.php'; ?>