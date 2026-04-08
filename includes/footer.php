<script>
// Mobile sidebar toggle
const mobileToggle = document.getElementById('mobileToggle');
const sidebar = document.querySelector('.sidebar');

if(mobileToggle && sidebar) {
    mobileToggle.addEventListener('click', function() {
        sidebar.classList.toggle('open');
    });
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(event) {
        if(window.innerWidth <= 768) {
            if(sidebar && !sidebar.contains(event.target) && !mobileToggle.contains(event.target)) {
                sidebar.classList.remove('open');
            }
        }
    });
}
</script>
</body>
</html>