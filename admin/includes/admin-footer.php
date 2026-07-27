        </div>
    </div>

    <button class="admin-menu-toggle" id="menuToggle">
        <i class="fas fa-bars"></i>
    </button>

    <script>
    (function(){
        var sidebar = document.getElementById('adminSidebar');
        var overlay = document.getElementById('adminOverlay');
        var toggle = document.getElementById('menuToggle');
        var icon = toggle.querySelector('i');

        function openMenu(){
            sidebar.classList.add('open');
            overlay.classList.add('active');
            icon.className = 'fas fa-times';
        }
        function closeMenu(){
            sidebar.classList.remove('open');
            overlay.classList.remove('active');
            icon.className = 'fas fa-bars';
        }

        toggle.addEventListener('click', function(){
            sidebar.classList.contains('open') ? closeMenu() : openMenu();
        });
        overlay.addEventListener('click', closeMenu);

        document.querySelectorAll('.admin-sidebar-nav a').forEach(function(a){
            a.addEventListener('click', function(){
                if(window.innerWidth < 1024) closeMenu();
            });
        });

        window.addEventListener('resize', function(){
            if(window.innerWidth >= 1024) closeMenu();
        });
    })();
    </script>
</body>
</html>
