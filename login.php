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
    <title>Radhe Radhe Hotel - Admin Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tangerine:wght@700&family=Montserrat:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        /* --- Theme: "Lotus at Dawn" --- */
        :root {
            --bg-gradient-start: #FFF8E1; /* Pale Sunrise Yellow */
            --bg-gradient-end: #FFECB3;   /* Soft Peach */
            --form-bg: rgba(255, 255, 255, 0.4);
            --form-border-glow: rgba(236, 64, 122, 0.3); /* Soft Pink accent */
            --text-color: #5D4037; /* Warm, earthy brown */
            --title-color: #D81B60; /* Deep Lotus Pink/Magenta */
            --input-bg: rgba(255, 255, 255, 0.5);
            --input-border: rgba(216, 27, 96, 0.4);
            --button-bg-start: #EC407A; /* Bright Pink */
            --button-bg-end: #D81B60;   /* Deeper Magenta */
            --button-text: #ffffff;
            --error-color: #c53939;
            --font-title: 'Tangerine', cursive;
            --font-body: 'Montserrat', sans-serif;
        }

        /* --- Base Setup & Animated Gradient Background --- */
        body {
            margin: 0;
            padding: 0;
            font-family: var(--font-body);
            color: var(--text-color);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, var(--bg-gradient-start), var(--bg-gradient-end));
            background-size: 200% 200%;
            animation: gradient-flow 20s ease infinite;
            overflow: hidden; /* Hide scrollbars */
        }

        @keyframes gradient-flow {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* --- Glassmorphism Login Panel --- */
        .login-panel {
            position: relative;
            background: var(--form-bg);
            padding: 50px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--form-border-glow);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px); /* For Safari */
            width: 100%;
            max-width: 400px;
            text-align: center;
            z-index: 10;
        }
        
        /* --- Decorative Lotus Icon --- */
        .lotus-icon {
            width: 70px;
            height: 70px;
            margin: -90px auto 15px; /* Pull the icon up */
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 80'%3E%3Cdefs%3E%3ClinearGradient id='lotusGradient' x1='0%25' y1='0%25' x2='0%25' y2='100%25'%3E%3Cstop offset='0%25' style='stop-color:%23EC407A;stop-opacity:1' /%3E%3Cstop offset='100%25' style='stop-color:%23D81B60;stop-opacity:1' /%3E%3C/linearGradient%3E%3C/defs%3E%3Cpath fill='url(%23lotusGradient)' d='M50,80 C80,80 90,55 90,40 C90,25 80,0 50,0 C20,0 10,25 10,40 C10,55 20,80 50,80 Z' opacity='0.2'/%3E%3Cpath fill='%23F8BBD0' d='M50,75 C75,75 85,55 85,40 C85,25 75,5 50,5 C25,5 15,25 15,40 C15,55 25,75 50,75 Z'/%3E%3Cpath fill='url(%23lotusGradient)' d='M50,30 C60,30 65,20 65,15 C65,10 60,0 50,0 C40,0 35,10 35,15 C35,20 40,30 50,30 Z'/%3E%3Cpath fill='url(%23lotusGradient)' d='M25,50 C35,55 40,65 40,70 C40,75 35,80 25,80 C15,80 10,75 10,70 C10,65 15,55 25,50 Z'/%3E%3Cpath fill='url(%23lotusGradient)' d='M75,50 C65,55 60,65 60,70 C60,75 65,80 75,80 C85,80 90,75 90,70 C90,65 85,55 75,50 Z'/%3E%3C/svg%3E");
            background-size: contain;
            background-repeat: no-repeat;
            opacity: 0.9;
            transform: scale(1.2);
        }

        /* --- Title Styling --- */
        .login-panel h2 {
            font-family: var(--font-title);
            color: var(--title-color);
            font-size: 5rem; /* 80px */
            font-weight: 700;
            margin: 0;
            line-height: 1;
            text-shadow: 0 2px 15px rgba(216, 27, 96, 0.2);
        }

        .login-panel p {
            font-size: 0.9rem;
            margin-bottom: 35px;
            letter-spacing: 0.5px;
            font-weight: 400;
        }

        /* --- Form Elements --- */
        .input-group {
            position: relative;
            margin-bottom: 30px;
            text-align: left;
        }

        label {
            position: absolute;
            top: 13px;
            left: 15px;
            font-size: 1rem;
            color: var(--text-color);
            opacity: 0.7;
            pointer-events: none;
            transition: all 0.3s ease;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            background: var(--input-bg);
            border: none;
            border-bottom: 1px solid var(--input-border);
            border-radius: 8px 8px 0 0;
            font-family: var(--font-body);
            font-size: 1rem;
            color: var(--text-color);
            font-weight: 500;
            box-sizing: border-box;
            transition: background 0.3s, border-color 0.3s;
        }
        
        /* Floating label effect */
        input[type="text"]:focus + label,
        input[type="text"]:valid + label,
        input[type="password"]:focus + label,
        input[type="password"]:valid + label {
            top: -18px;
            left: 0;
            font-size: 0.8rem;
            color: var(--title-color);
            opacity: 1;
            font-weight: 500;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.8);
            border-bottom: 1px solid var(--title-color);
        }

        /* --- Submit Button --- */
        button[type="submit"] {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 8px;
            background: linear-gradient(45deg, var(--button-bg-start), var(--button-bg-end));
            color: var(--button-text);
            font-size: 1.1rem;
            font-weight: 500;
            font-family: var(--font-body);
            letter-spacing: 1px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(216, 27, 96, 0.3);
            margin-top: 10px;
        }

        button[type="submit"]:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(216, 27, 96, 0.4);
            filter: brightness(1.1);
        }

        /* --- Message & Signup Link --- */
        #message {
            margin-top: 20px;
            color: var(--error-color);
            font-weight: 500;
            min-height: 1.2em;
        }

        .signup-link {
            margin-top: 25px;
            font-size: 0.9rem;
        }

        .signup-link a {
            color: var(--title-color);
            font-weight: 500;
            text-decoration: none;
            transition: text-decoration 0.3s, filter 0.3s;
        }

        .signup-link a:hover {
            text-decoration: underline;
            filter: brightness(1.1);
        }
        
        /* --- Background Floating Particles --- */
        #particles-js {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: 1;
        }

    </style>
</head>
<body>
    <!-- Canvas for the particle effect -->
    <canvas id="particles-js"></canvas>

    <div class="login-panel">
        <div class="lotus-icon"></div>
        <h2>Radhe Radhe</h2>
        <p>Hotel Management</p>

        <!-- The form for login -->
        <form id="loginForm" novalidate>
            <div class="input-group">
                <input type="text" id="username" name="username" required>
                <label for="username">Username</label>
            </div>

            <div class="input-group">
                <input type="password" id="password" name="password" required>
                <label for="password">Password</label>
            </div>

            <button type="submit">Greet the Dawn</button>
        </form>

        <!-- This div will display error messages -->
        <div id="message"></div>

      
    </div>

    <script>
        // --- Lightweight Particle Effect Script ---
        const canvas = document.getElementById('particles-js');
        const ctx = canvas.getContext('2d');
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;

        let particles = [];
        const particleCount = 40;

        class Particle {
            constructor() {
                this.x = Math.random() * canvas.width;
                this.y = Math.random() * canvas.height;
                this.size = Math.random() * 2.5 + 1;
                this.speedX = Math.random() * 0.5 - 0.25;
                this.speedY = Math.random() * 0.5 - 0.25;
                // Particles are now soft white/pinkish to look like light motes
                this.color = `rgba(255, 236, 241, ${Math.random() * 0.6 + 0.2})`;
            }
            update() {
                this.x += this.speedX;
                this.y += this.speedY;

                if (this.size > 0.1) this.size -= 0.02;
                if (this.x < 0 || this.x > canvas.width) this.speedX *= -1;
                if (this.y < 0 || this.y > canvas.height) this.speedY *= -1;
            }
            draw() {
                ctx.fillStyle = this.color;
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
                ctx.fill();
            }
        }

        function initParticles() {
            for (let i = 0; i < particleCount; i++) {
                particles.push(new Particle());
            }
        }

        function animateParticles() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            for (let i = 0; i < particles.length; i++) {
                if(particles[i].size <= 0.1) {
                    particles.splice(i, 1);
                    particles.push(new Particle());
                    i--;
                } else {
                    particles[i].update();
                    particles[i].draw();
                }
            }
            requestAnimationFrame(animateParticles);
        }

        window.addEventListener('resize', () => {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
            particles = [];
            initParticles();
        });

        initParticles();
        animateParticles();

        // --- Form Submission Logic ---
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.getElementById('loginForm');
            const messageDiv = document.getElementById('message');

            loginForm.addEventListener('submit', function(event) {
                event.preventDefault();
                const formData = new FormData(loginForm);
                messageDiv.textContent = '';

                fetch('ajax/ajax_process_login.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = 'dashboard.php';
                    } else {
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
