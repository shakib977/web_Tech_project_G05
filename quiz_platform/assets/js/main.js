
/* =============================================
   QUIZPRO - Main JS (All Members)
   ============================================= */

// ── TOAST ──────────────────────────────────────
function showToast(msg, type = 's') {
    let box = document.querySelector('.toast-box');
    if (!box) { box = document.createElement('div'); box.className = 'toast-box'; document.body.appendChild(box); }

    const t = document.createElement('div');
    const icons = { s: '✓', e: '✕', i: 'ℹ' };
    t.className = `toast toast-${type}`;
    t.innerHTML = `<span>${icons[type]||'•'}</span> <span>${escHtml(msg)}</span>`;
    box.appendChild(t);

    setTimeout(() => {
        t.style.cssText = 'opacity:0;transform:translateX(110%);transition:all .3s ease';
        setTimeout(() => t.remove(), 300);
    }, 3200);
}

// ── AJAX HELPER (uses XMLHttpRequest as required) ──
function doAjax(url, data, cb, method = 'POST') {
    const xhr = new XMLHttpRequest();
    xhr.open(method, url, true);
    if (method === 'POST' && typeof data === 'string') {
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    }
    xhr.onreadystatechange = function () {
        if (xhr.readyState !== 4) return;
        if (xhr.status === 200) {
            try { cb(null, JSON.parse(xhr.responseText)); }
            catch (e) { cb(e, null); }
        } else {
            cb(new Error('HTTP ' + xhr.status), null);
        }
    };
    if (data instanceof FormData) { xhr.send(data); }
    else { xhr.send(data || null); }
}

// ── MODAL ──────────────────────────────────────
function openModal(id) {
    const el = document.getElementById(id);
    if (el) { el.style.display = 'flex'; setTimeout(() => el.classList.add('show'), 10); }
}
function closeModal(id) {
    const el = document.getElementById(id);
    if (el) { el.classList.remove('show'); setTimeout(() => el.style.display = 'none', 200); }
}
document.addEventListener('click', e => {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('show');
        setTimeout(() => e.target.style.display = 'none', 200);
    }
});

// ── QUIZ TIMER (real-time, survives back button + tab switch) ──
function startTimer(endTimestampMs, spanId, formId) {
    const span = document.getElementById(spanId);
    const wrap = span ? span.closest('.timer') : null;
    let submitted = false;

    // Warn user before leaving the page
    function beforeUnloadWarning(e) {
        e.preventDefault();
        e.returnValue = 'Quiz in progress! If you leave, the timer keeps running.';
    }
    window.addEventListener('beforeunload', beforeUnloadWarning);

    // Remove warning when form submits (normal submission)
    const form = document.getElementById(formId);
    if (form) {
        form.addEventListener('submit', function() {
            window.removeEventListener('beforeunload', beforeUnloadWarning);
            submitted = true;
        });
    }

    function tick() {
        if (submitted) return;

        // Always calculate from real clock — survives back button & tab switch
        const rem = Math.max(0, Math.floor((endTimestampMs - Date.now()) / 1000));

        // Format as MM:SS or HH:MM:SS
        const h = Math.floor(rem / 3600);
        const m = Math.floor((rem % 3600) / 60);
        const s = rem % 60;

        const timeStr = h > 0
            ? `${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`
            : `${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;

        if (span) span.textContent = timeStr;

        // Color warning
        if (wrap) {
            if (rem <= 60)       wrap.className = 'timer urgent';
            else if (rem <= 180) wrap.className = 'timer warn';
            else                 wrap.className = 'timer';
        }

        if (rem <= 0) {
            window.removeEventListener('beforeunload', beforeUnloadWarning);
            showToast('⏰ Time is up! Submitting your quiz...', 'i');
            setTimeout(() => {
                submitted = true;
                document.getElementById(formId)?.submit();
            }, 1000);
            return;
        }

        setTimeout(tick, 500); // Check every 500ms for accuracy
    }

    tick();

    // When user comes BACK to this tab/page, recalculate immediately
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden && !submitted) {
            tick();
        }
    });
}

// ── OPTION SELECTION ───────────────────────────
function initOptions() {
    document.querySelectorAll('.option-item').forEach(item => {
        item.addEventListener('click', function () {
            const radio = this.querySelector('input[type="radio"]');
            if (!radio) return;
            radio.checked = true;
            this.closest('.question-card')
                .querySelectorAll('.option-item')
                .forEach(i => i.classList.remove('selected'));
            this.classList.add('selected');
        });
    });
}

// ── LEADERBOARD via AJAX (Member 1 — Student) ──
function loadLeaderboard(quizId, containerId) {
    const box = document.getElementById(containerId);
    if (!box) return;
    box.innerHTML = '<div class="flex-center" style="padding:24px"><div class="spinner"></div></div>';

    doAjax('api/leaderboard.php?quiz_id=' + encodeURIComponent(quizId), null, (err, data) => {
        if (err || !data || !data.success) {
            box.innerHTML = '<p class="text-center text-muted" style="padding:20px">Could not load leaderboard.</p>';
            return;
        }
        if (data.data.length === 0) {
            box.innerHTML = '<p class="text-center text-muted" style="padding:20px">No attempts yet.</p>';
            return;
        }
        let html = '';
        data.data.forEach((r, i) => {
            const cls = i < 3 ? 'rank-' + (i + 1) : 'rank-n';
            html += `<div class="lb-item">
                <div class="lb-rank ${cls}">${i + 1}</div>
                <div style="flex:1">
                    <div class="fw-600" style="font-size:14px">${escHtml(r.name)}</div>
                    <div class="text-xs text-muted">${escHtml(r.program || '')}</div>
                </div>
                <div class="fw-bold text-primary">${r.score}</div>
            </div>`;
        });
        box.innerHTML = html;
    }, 'GET');
}

// ── ENROLL via AJAX (Member 1 — Student) ───────
function enrollCourse(courseId, btn) {
    btn.disabled = true;
    btn.textContent = 'Processing…';

    doAjax('api/enroll.php', 'course_id=' + encodeURIComponent(courseId), (err, res) => {
        if (err || !res) {
            showToast('Enrollment failed. Please try again.', 'e');
            btn.disabled = false; btn.textContent = 'Enroll';
            return;
        }
        if (!res.success) {
            showToast(res.message || 'Error', 'e');
            btn.disabled = false; btn.textContent = 'Enroll';
            return;
        }
        showToast(res.message, 's');
        btn.textContent = res.status === 'pending' ? '⏳ Pending Approval' : '✓ Enrolled';
        btn.classList.replace('btn-primary', 'btn-secondary');
    });
}

// ── TOGGLE USER (Member 4 — Admin) ─────────────
function toggleUser(userId, btn) {
    if (!confirm('Are you sure you want to change this user\'s status?')) return;
    btn.disabled = true;

    doAjax('api/toggle_user.php', 'user_id=' + encodeURIComponent(userId), (err, res) => {
        btn.disabled = false;
        if (err || !res || !res.success) { showToast('Action failed', 'e'); return; }
        showToast(res.message, 's');
        const badge = document.getElementById('status-badge-' + userId);
        if (badge) {
            if (res.is_active) {
                badge.className = 'badge badge-success'; badge.textContent = 'Active';
                btn.className = btn.className.replace('btn-success','btn-danger');
                btn.textContent = 'Deactivate';
            } else {
                badge.className = 'badge badge-danger'; badge.textContent = 'Inactive';
                btn.className = btn.className.replace('btn-danger','btn-success');
                btn.textContent = 'Activate';
            }
        }
    });
}

// ── FLAG STUDENT (Member 3 — TA) ───────────────
function flagStudent(userId, courseId, btn) {
    doAjax('api/flag_student.php',
        'user_id=' + encodeURIComponent(userId) + '&course_id=' + encodeURIComponent(courseId),
        (err, res) => {
            if (err || !res || !res.success) { showToast('Failed to flag', 'e'); return; }
            showToast('Student flagged for instructor review', 's');
            btn.disabled = true; btn.textContent = '🚩 Flagged';
        }
    );
}

// ── GRADE ANALYTICS (Member 2 — Instructor) ────
function loadGradeAnalytics(quizId, containerId) {
    const box = document.getElementById(containerId);
    if (!box) return;
    box.innerHTML = '<div class="flex-center" style="padding:24px"><div class="spinner"></div></div>';
    doAjax('api/grade_analytics.php?quiz_id=' + encodeURIComponent(quizId), null, (err, res) => {
        if (err || !res || !res.success) {
            box.innerHTML = '<p class="text-muted text-center" style="padding:20px">Could not load analytics.</p>';
            return;
        }
        const d = res.data;
        box.innerHTML = `
        <div class="stats-grid" style="margin-bottom:0">
            <div class="stat-card"><div class="stat-icon icon-blue">📊</div><div class="stat-info"><h3>${d.avg_score}</h3><p>Class Average</p></div></div>
            <div class="stat-card"><div class="stat-icon icon-green">⬆️</div><div class="stat-info"><h3>${d.highest}</h3><p>Highest Score</p></div></div>
            <div class="stat-card"><div class="stat-icon icon-red">⬇️</div><div class="stat-info"><h3>${d.lowest}</h3><p>Lowest Score</p></div></div>
            <div class="stat-card"><div class="stat-icon icon-yellow">🎯</div><div class="stat-info"><h3>${d.pass_rate}%</h3><p>Pass Rate</p></div></div>
        </div>`;
    }, 'GET');
}

// ── UTILS ──────────────────────────────────────
function escHtml(str) {
    const d = document.createElement('div');
    d.appendChild(document.createTextNode(str || ''));
    return d.innerHTML;
}

function confirmDo(msg, url) {
    if (confirm(msg)) window.location.href = url;
}

// ── INIT ───────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    initOptions();

    // Auto-dismiss alerts
    document.querySelectorAll('.alert').forEach(a => {
        setTimeout(() => {
            a.style.cssText = 'opacity:0;transition:opacity .5s;';
            setTimeout(() => a.remove(), 500);
        }, 4000);
    });

    // File input preview
    const picInput = document.getElementById('profile_pic_input');
    if (picInput) {
        picInput.addEventListener('change', function () {
            const f = this.files[0];
            if (!f) return;
            const reader = new FileReader();
            reader.onload = e => {
                const prev = document.getElementById('pic_preview');
                if (prev) prev.src = e.target.result;
            };
            reader.readAsDataURL(f);
        });
    }
});