// Madam Dimsum - Dapur JS
function toggleUserDropdown() {
    const dropdown = document.getElementById('userDropdown');
    if (dropdown) {
        dropdown.classList.toggle('hidden');
    }
}

window.addEventListener('click', function(e) {
    const dropdown = document.getElementById('userDropdown');
    if (dropdown && !e.target.closest('.relative')) {
        dropdown.classList.add('hidden');
    }
});

