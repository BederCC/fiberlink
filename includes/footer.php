    <script>
        // User Data Display
        const userStr = localStorage.getItem('user');
        if (userStr) {
            const user = JSON.parse(userStr);
            const nameDisplay = document.getElementById('user-name-display');
            const roleDisplay = document.getElementById('user-role-display');
            const avatarDisplay = document.getElementById('user-avatar');

            if(nameDisplay) nameDisplay.textContent = user.full_name;
            if(roleDisplay) roleDisplay.textContent = user.role.charAt(0).toUpperCase() + user.role.slice(1);
            if(avatarDisplay) avatarDisplay.textContent = user.username.charAt(0).toUpperCase();
        }

        // Dropdown Toggle
        const userMenuBtn = document.getElementById('user-menu-button');
        const userDropdown = document.getElementById('dropdown-user');

        if(userMenuBtn && userDropdown) {
            userMenuBtn.addEventListener('click', () => {
                userDropdown.classList.toggle('hidden');
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', (e) => {
                if (!userMenuBtn.contains(e.target) && !userDropdown.contains(e.target)) {
                    userDropdown.classList.add('hidden');
                }
            });
        }

        // Sidebar Toggle for Mobile
        const drawerBtn = document.querySelector('[data-drawer-toggle="logo-sidebar"]');
        const sidebar = document.getElementById('logo-sidebar');
        
        if(drawerBtn && sidebar) {
            drawerBtn.addEventListener('click', () => {
                sidebar.classList.toggle('-translate-x-full');
            });
        }

        function logout() {
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            window.location.href = '../index.php';
        }
    </script>
</body>
</html>
