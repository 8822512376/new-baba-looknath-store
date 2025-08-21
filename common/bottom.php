<?php
// common/bottom.php
$current_page = basename($_SERVER['PHP_SELF']);
?>
        </main> <!-- End main-content -->

        <!-- Bottom Navigation -->
        <nav class="fixed bottom-0 left-0 right-0 w-full max-w-md mx-auto bg-white border-t z-40 flex justify-around">
            <a href="index.php" class="flex-1 text-center py-3 text-gray-600 hover:text-indigo-600 <?= ($current_page == 'index.php') ? 'active-nav' : '' ?>">
                <i class="fas fa-home text-xl"></i>
                <span class="block text-xs">Home</span>
            </a>
            <a href="cart.php" class="flex-1 text-center py-3 text-gray-600 hover:text-indigo-600 <?= ($current_page == 'cart.php') ? 'active-nav' : '' ?>">
                <i class="fas fa-shopping-cart text-xl"></i>
                <span class="block text-xs">Cart</span>
            </a>
            <a href="order.php" class="flex-1 text-center py-3 text-gray-600 hover:text-indigo-600 <?= ($current_page == 'order.php') ? 'active-nav' : '' ?>">
                <i class="fas fa-box text-xl"></i>
                <span class="block text-xs">Orders</span>
            </a>
            <a href="profile.php" class="flex-1 text-center py-3 text-gray-600 hover:text-indigo-600 <?= ($current_page == 'profile.php') ? 'active-nav' : '' ?>">
                <i class="fas fa-user-circle text-xl"></i>
                <span class="block text-xs">Profile</span>
            </a>
        </nav>

    </div> <!-- End app-container -->

    <!-- Loading Modal -->
    <div id="loading-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
        <div class="bg-white p-5 rounded-lg flex items-center">
            <div class="loader ease-linear rounded-full border-4 border-t-4 border-gray-200 h-12 w-12 mr-3"></div>
            <p class="text-gray-700">Processing...</p>
        </div>
        <style>.loader { border-top-color: #4f46e5; animation: spin 1s linear infinite; } @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }</style>
    </div>

    <script>
        // Global JS
        document.addEventListener('DOMContentLoaded', function() {
            // Disable context menu, selection, and zoom
            document.addEventListener('contextmenu', e => e.preventDefault(), false);
            document.addEventListener('keydown', e => {
                if ((e.ctrlKey || e.metaKey) && ['+', '-', '0'].includes(e.key)) e.preventDefault();
            });
            document.addEventListener('wheel', e => {
                if (e.ctrlKey) e.preventDefault();
            }, { passive: false });

            // Sidebar toggle
            const menuBtn = document.getElementById('menu-btn');
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');

            if (menuBtn) {
                menuBtn.addEventListener('click', toggleSidebar);
                overlay.addEventListener('click', toggleSidebar);
            }

            function toggleSidebar() {
                sidebar.classList.toggle('-translate-x-full');
                overlay.classList.toggle('hidden');
            }
        });

        // Utility functions for AJAX and modals
        function showLoader() { document.getElementById('loading-modal').classList.remove('hidden'); }
        function hideLoader() { document.getElementById('loading-modal').classList.add('hidden'); }

        function showToast(message, isSuccess = true) {
            const toast = document.createElement('div');
            toast.className = `fixed bottom-24 left-1/2 -translate-x-1/2 px-6 py-3 rounded-lg shadow-lg text-white ${isSuccess ? 'bg-green-500' : 'bg-red-500'}`;
            toast.innerText = message;
            document.body.appendChild(toast);
            setTimeout(() => {
                toast.remove();
            }, 3000);
        }
    </script>
</body>
</html>
