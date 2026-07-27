</div><!-- /.main-wrapper -->

<!-- Footer -->
<footer class="site-footer">
    <div class="footer-inner">
        <span><i class="bi bi-code-slash me-1"></i>Developed by <strong>WE YOUNG</strong> &mdash; v1.0.2</span>
        <span>Powered by WE YOUNG &copy; <?php echo date("Y"); ?></span>
    </div>
</footer>

<!-- Scripts -->
<script>
// Sidebar toggle
const sidebar = document.getElementById('sidebar');
const mainWrapper = document.getElementById('mainWrapper');
const topbar = document.getElementById('topbar');
const toggle = document.getElementById('sidebarToggle');

toggle.addEventListener('click', () => {
    sidebar.classList.toggle('collapsed');
    mainWrapper.classList.toggle('expanded');
    topbar.classList.toggle('expanded');
});

// Responsive auto-collapse on mobile
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

<!-- Chart.js Bar Chart -->
<script>
if (document.getElementById('myBarChart')) {
    const barCtx = document.getElementById('myBarChart').getContext('2d');
    new Chart(barCtx, {
        type: 'bar',
        data: {
            labels: ['Italy', 'France', 'Spain', 'USA', 'Argentina'],
            datasets: [{
                label: 'Production (M)',
                data: [55, 49, 44, 24, 15],
                backgroundColor: ['#4361ee','#3f37c9','#4895ef','#4cc9f0','#7209b7'],
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                title: { display: true, text: 'World Wine Production 2018', font: { size: 14, family: 'Inter' }, color: '#94a3b8' }
            },
            scales: {
                x: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#94a3b8' } },
                y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#94a3b8' } }
            }
        }
    });
}

if (document.getElementById('myChart')) {
    const doughCtx = document.getElementById('myChart').getContext('2d');
    new Chart(doughCtx, {
        type: 'doughnut',
        data: {
            labels: ['Italy', 'France', 'Spain', 'USA', 'Argentina'],
            datasets: [{
                data: [55, 49, 44, 24, 15],
                backgroundColor: ['#4361ee','#3f37c9','#4895ef','#4cc9f0','#7209b7'],
                borderWidth: 0,
                hoverOffset: 8
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom', labels: { color: '#94a3b8', font: { family: 'Inter' } } },
                title: { display: true, text: 'Wine Production Share', font: { size: 14, family: 'Inter' }, color: '#94a3b8' }
            }
        }
    });
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>