// Dark mode functionality
document.addEventListener('DOMContentLoaded', () => {
    const darkModeToggle = document.getElementById('darkModeToggle');
    const html = document.documentElement;
    const header = document.querySelector('header');
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.querySelector('main');

    // Apply initial theme based on saved preference
    const savedTheme = localStorage.getItem('theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    const shouldBeDark = savedTheme === 'dark' || (!savedTheme && prefersDark);

    if (shouldBeDark) {
        enableDarkMode();
    }

    // Toggle dark mode
    darkModeToggle?.addEventListener('click', () => {
        const isDark = html.classList.contains('dark');
        if (isDark) {
            disableDarkMode();
        } else {
            enableDarkMode();
        }

        // Save theme preference
        localStorage.setItem('theme', isDark ? 'light' : 'dark');

        // Dispatch custom event for other components
        const event = new CustomEvent('themeChanged', { detail: { isDark: !isDark } });
        document.dispatchEvent(event);
    });

    // Function to enable dark mode
    function enableDarkMode() {
        html.classList.add('dark');
        darkModeToggle?.classList.add('dark');
        header?.classList.remove('header-light');
        header?.classList.add('header-dark');
        
        // Update sidebar
        if (sidebar) {
            sidebar.classList.remove('bg-white');
            sidebar.classList.add('bg-gray-900');
        }

        // Update main content background if it exists
        if (mainContent) {
            mainContent.classList.remove('bg-gray-50');
            mainContent.classList.add('bg-gray-900');
        }

        // Update all cards
        document.querySelectorAll('.bg-white').forEach(element => {
            element.classList.add('dark:bg-gray-800');
        });

        // Update all text colors
        document.querySelectorAll('.text-gray-800, .text-gray-900').forEach(element => {
            element.classList.add('dark:text-white');
        });

        document.querySelectorAll('.text-gray-600, .text-gray-500').forEach(element => {
            element.classList.add('dark:text-gray-400');
        });

        // Update borders
        document.querySelectorAll('.border-gray-200').forEach(element => {
            element.classList.add('dark:border-gray-700');
        });

        // Update table elements
        document.querySelectorAll('thead').forEach(element => {
            element.classList.add('dark:bg-gray-700', 'dark:text-gray-300');
        });

        document.querySelectorAll('tbody').forEach(element => {
            element.classList.add('dark:bg-gray-800', 'dark:divide-gray-700');
        });

        // Update form elements
        document.querySelectorAll('input, select, textarea').forEach(element => {
            element.classList.add('dark:bg-gray-700', 'dark:text-white', 'dark:border-gray-600');
        });
    }

    // Function to disable dark mode
    function disableDarkMode() {
        html.classList.remove('dark');
        darkModeToggle?.classList.remove('dark');
        header?.classList.add('header-light');
        header?.classList.remove('header-dark');
        
        // Update sidebar
        if (sidebar) {
            sidebar.classList.add('bg-white');
            sidebar.classList.remove('bg-gray-900');
        }

        // Update main content background if it exists
        if (mainContent) {
            mainContent.classList.add('bg-gray-50');
            mainContent.classList.remove('bg-gray-900');
        }
    }

    // Listen for system theme changes
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
        if (!localStorage.getItem('theme')) {  // Only auto-switch if user hasn't manually set a preference
            if (e.matches) {
                enableDarkMode();
            } else {
                disableDarkMode();
            }
        }
    });
}); 