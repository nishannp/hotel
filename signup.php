<?php
// signup.php
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
    <title>Admin Signup</title>
</head>
<body>

    <h2>Admin Signup</h2>
    <p>Create a new administrator account.</p>

    <!-- The form for signup -->
    <form id="signupForm">
        <div>
            <label for="username">Username:</label><br>
            <input type="text" id="username" name="username" required>
        </div>
        <br>
        <div>
            <label for="phone_number">Phone Number:</label><br>
            <input type="tel" id="phone_number" name="phone_number" required>
        </div>
        <br>
        <div>
            <label for="password">Password:</label><br>
            <input type="password" id="password" name="password" required>
        </div>
        <br>
        <div>
            <label for="confirm_password">Confirm Password:</label><br>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>
        <br>
        <button type="submit">Sign Up</button>
    </form>

    <!-- This div will display messages from the server (errors or success) -->
    <div id="message" style="margin-top: 15px;"></div>

    <p>Already have an account? <a href="login.php">Log in here</a>.</p>

    <script>
        // Wait for the document to be fully loaded
        document.addEventListener('DOMContentLoaded', function () {
            const signupForm = document.getElementById('signupForm');
            const messageDiv = document.getElementById('message');

            // Add an event listener for the form submission
            signupForm.addEventListener('submit', function (event) {
                // Prevent the default form submission (which would reload the page)
                event.preventDefault();

                // Create a FormData object from the form
                const formData = new FormData(signupForm);

                // Use the Fetch API to send the form data to the server (AJAX)
                fetch('ajax/ajax_process_signup.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json()) // Parse the JSON response from the server
                .then(data => {
                    // Display the message from the server
                    messageDiv.textContent = data.message;

                    if (data.success) {
                        messageDiv.style.color = 'green';
                        signupForm.reset(); // Clear the form on success
                        // Optional: redirect to login after a delay
                        setTimeout(() => {
                            window.location.href = 'login.php';
                        }, 2000);
                    } else {
                        messageDiv.style.color = 'red';
                    }
                })
                .catch(error => {
                    // Handle network errors
                    console.error('Error:', error);
                    messageDiv.textContent = 'An error occurred. Please try again.';
                    messageDiv.style.color = 'red';
                });
            });
        });
    </script>

</body>
</html>