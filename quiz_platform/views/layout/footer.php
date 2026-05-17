
<!-- ── CUSTOM CONFIRM MODAL ── -->
</div><!-- /page-content -->
</div><!-- /main-content -->
</div><!-- /app-layout -->

<div class="toast-box"></div>

<!-- ── CUSTOM CONFIRM MODAL ── -->
<div class="cconfirm-overlay" id="cconfirm_overlay">
    <div class="cconfirm-box">
        <div class="cconfirm-icon"  id="cconfirm_icon">❓</div>
        <div class="cconfirm-title" id="cconfirm_title">Are you sure?</div>
        <div class="cconfirm-msg"   id="cconfirm_msg"></div>
        <div class="cconfirm-btns">
            <button class="btn btn-secondary btn-lg"
                    style="min-width:110px"
                    onclick="cconfirmClose()">
                Cancel
            </button>
            <button class="btn btn-primary btn-lg"
                    style="min-width:110px"
                    id="cconfirm_ok">
                Confirm
            </button>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
<script>
// ── Global custom confirm ──────────────────────
function cconfirmShow(opts) {
    // opts: icon, title, msg, okText, okClass, onOk
    document.getElementById('cconfirm_icon').textContent  = opts.icon    || '❓';
    document.getElementById('cconfirm_title').textContent = opts.title   || 'Are you sure?';
    document.getElementById('cconfirm_msg').textContent   = opts.msg     || '';
    var ok = document.getElementById('cconfirm_ok');
    ok.textContent  = opts.okText  || 'Confirm';
    ok.className    = 'btn btn-lg ' + (opts.okClass || 'btn-primary');
    ok.style.minWidth = '110px';
    ok.onclick = function() {
        cconfirmClose();
        if (opts.onOk) opts.onOk();
    };
    document.getElementById('cconfirm_overlay').classList.add('show');
}
function cconfirmClose() {
    document.getElementById('cconfirm_overlay').classList.remove('show');
}
// Close on backdrop click
document.getElementById('cconfirm_overlay').addEventListener('click', function(e) {
    if (e.target === this) cconfirmClose();
});

// ── Logout confirmation ──────────────────────
function askLogout(url) {
    cconfirmShow({
        icon:    '👋',
        title:   'Logout',
        msg:     'Are you sure you want to logout?',
        okText:  'Yes, Logout',
        okClass: 'btn-danger',
        onOk:    function() { window.location.href = url; }
    });
}
</script>
<?php ob_end_flush(); ?>
</body>
</html>