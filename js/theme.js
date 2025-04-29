// Theme Management
document.addEventListener('DOMContentLoaded', function() {
    // Check for saved theme preference
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', savedTheme);

    // Initialize theme toggle button
    const themeToggle = document.querySelector('.theme-toggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', toggleTheme);
    }
});

function toggleTheme() {
    const currentTheme = document.documentElement.getAttribute('data-theme');
    const newTheme = currentTheme === 'light' ? 'dark' : 'light';
    
    // Update theme
    document.documentElement.setAttribute('data-theme', newTheme);
    localStorage.setItem('theme', newTheme);
    
    // Update toggle button state
    updateToggleButton(newTheme);
}

function updateToggleButton(theme) {
    const toggle = document.querySelector('.theme-toggle');
    if (toggle) {
        const sunIcon = toggle.querySelector('.fa-sun');
        const moonIcon = toggle.querySelector('.fa-moon');
        
        if (theme === 'dark') {
            sunIcon.style.opacity = '0.5';
            moonIcon.style.opacity = '1';
        } else {
            sunIcon.style.opacity = '1';
            moonIcon.style.opacity = '0.5';
        }
    }
} 