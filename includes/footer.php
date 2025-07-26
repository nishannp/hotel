<?php
// includes/footer.php
?>
        <!-- You can add more content here for each page -->
    </main> <!-- End of .main-content -->

    <!-- Toast Notification Component -->
    <div id="toast-container"></div>

    <style>
    #toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .toast {
        display: flex;
        align-items: center;
        padding: 12px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        color: white;
        font-family: 'Poppins', sans-serif;
        font-size: 0.95rem;
        opacity: 0;
        transform: translateX(100%);
        transition: all 0.4s cubic-bezier(0.215, 0.610, 0.355, 1.000);
    }
    .toast.show {
        opacity: 1;
        transform: translateX(0);
    }
    .toast.hide {
        opacity: 0;
        transform: translateX(100%);
    }
    .toast .icon {
        margin-right: 10px;
        font-size: 24px;
    }
    .toast.success { background-color: #10b981; }
    .toast.error { background-color: #ef4444; }
    .toast.info { background-color: #3b82f6; }
    </style>
    
    <!-- You could include JavaScript files here if needed -->
   <!-- JQuery (required for Select2) and Select2 JS -->
    <script src="https://code.jquery.com/jquery-3.6.3.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
    // Global Toast Notification Function
    function showToast(message, type = 'info') { // types: success, error, info
        const container = document.getElementById('toast-container');
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        
        let icon = 'info';
        if (type === 'success') icon = 'check_circle';
        if (type === 'error') icon = 'error';

        toast.innerHTML = `
            <span class="material-icons-outlined icon">${icon}</span>
            <span>${message}</span>
        `;
        
        container.appendChild(toast);
        
        // Animate in
        setTimeout(() => {
            toast.classList.add('show');
        }, 10); // Small delay to allow element to be added to DOM

        // Animate out and remove
        setTimeout(() => {
            toast.classList.remove('show');
            toast.classList.add('hide');
            toast.addEventListener('transitionend', () => toast.remove());
        }, 4000); // Hide after 4 seconds
    }
    </script>
</body>
</html>
