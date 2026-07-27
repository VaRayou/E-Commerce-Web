</div>

<footer class="site-footer">
    <div class="footer-inner">
        <span><i class="bi bi-code-slash me-1"></i>Developed by <strong>WE YOUNG</strong> &mdash; v1.0.2</span>
        <span>Powered by WE YOUNG &copy; <?php echo date("Y"); ?></span>
    </div>
</footer>

<script>
const sidebar = document.getElementById('sidebar');
const mainWrapper = document.getElementById('mainWrapper');
const topbar = document.getElementById('topbar');
const toggle = document.getElementById('sidebarToggle');

toggle.addEventListener('click', () => {
    sidebar.classList.toggle('collapsed');
    mainWrapper.classList.toggle('expanded');
    topbar.classList.toggle('expanded');
});

function checkWidth() {
    if (window.innerWidth < 768) {
        sidebar.classList.add('collapsed');
        mainWrapper.classList.add('expanded');
        topbar.classList.add('expanded');
    } else {
        sidebar.classList.remove('collapsed');
        mainWrapper.classList.remove('expanded');
        topbar.classList.remove('expanded');
    }
}
checkWidth();
window.addEventListener('resize', checkWidth);
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>