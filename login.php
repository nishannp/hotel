<?php
// login.php
session_start();

// If the user is already logged in, redirect them to the dashboard
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
</head>
<body>

    <h2>Admin Login</h2>

    <!-- The form for login -->
    <form id="loginForm">
        <div>
            <label for="username">Username:</label><br>
            <input type="text" id="username" name="username" required>
        </div>
        <br>
        <div>
            <label for="password">Password:</label><br>
            <input type="password" id="password" name="password" required>
        </div>
        <br>
        <button type="submit">Log In</button>
    </form>

    <!-- This div will display error messages -->
    <div id="message" style="margin-top: 15px; color: red;"></div>

    <p>Don't have an account? <a href="signup.php">Sign up here</a>.</p>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const loginForm = document.getElementById('loginForm');
            const messageDiv = document.getElementById('message');

            loginForm.addEventListener('submit', function (event) {
                event.preventDefault(); // Stop default form submission

                const formData = new FormData(loginForm);

                // Send data to the login processor
                fetch('ajax/ajax_process_login.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // On successful login, redirect to the dashboard
                        window.location.href = 'dashboard.php';
                    } else {
                        // On failure, show the error message
                        messageDiv.textContent = data.message;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    messageDiv.textContent = 'A network error occurred. Please try again.';
                });
            });
        });
    </script>

</body>
</html>