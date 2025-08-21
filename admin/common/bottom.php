<?php
// admin/common/bottom.php
if(isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit();
}
?>
            </main>
        </div>
    </div>
    
    <!-- Loading Modal -->
    <div id="loading-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
        <div class="bg-white p-5 rounded-lg flex items-center shadow-xl">
            <div class="loader ease-linear rounded-full border-4 border-t-4 border-gray-200 h-12 w-12 mr-3"></div>
            <p class="text-gray-700">Processing...</p>
        </div>
        <style>.loader { border-top-color: #4f46e5; animation: spin 1s linear infinite; } @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }</style>
    </div>

    <!-- NEW & IMPROVED TOAST NOTIFICATION -->
    <div id="toast-notification" class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 p-6 rounded-lg shadow-2xl text-white text-lg font-semibold z-[999] hidden opacity-0 transition-opacity duration-300">
        <p id="toast-message"></p>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
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
    
    // Global helper functions
    function showLoader() { document.getElementById('loading-modal').classList.remove('hidden'); }
    function hideLoader() { document.getElementById('loading-modal').classList.add('hidden'); }

    // --- NEW showToast FUNCTION ---
    function showToast(message, isSuccess = true) {
        const toast = document.getElementById('toast-notification');
        const toastMessage = document.getElementById('toast-message');

        // Set message and style
        toastMessage.textContent = message;
        toast.className = toast.className.replace(/bg-\w+-500/, ''); // Remove old color class
        if (isSuccess) {
            toast.classList.add('bg-green-500');
        } else {
            toast.classList.add('bg-red-500');
        }

        // Show toast with fade-in effect
        toast.classList.remove('hidden');
        setTimeout(() => {
            toast.classList.remove('opacity-0');
        }, 10); // small delay to allow transition to work

        // Hide toast after 2.5 seconds with fade-out effect
        setTimeout(() => {
            toast.classList.add('opacity-0');
            setTimeout(() => {
                toast.classList.add('hidden');
            }, 300); // Wait for fade-out to complete
        }, 2500);
    }
    </script>
</body>
</html>
