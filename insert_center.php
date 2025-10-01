<?php
session_start();
require_once('DBconnect.php');

if(isset($_POST['name'], $_POST['address'], $_POST['contact'], $_POST['available_vaccines'])) {

    $name = $_POST['name'];
    $address = $_POST['address'];
    $contact = $_POST['contact'];
    $available_vaccines = $_POST['available_vaccines'];
    $stmt = $conn->prepare("INSERT INTO centers (Name, Address, Contact, AvailableVaccines) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $address, $contact, $available_vaccines);

    if($stmt->execute()){
        header("Location: show_centers.php");
        exit();
    } else {
        echo "❌ Insertion Failed: " . $stmt->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insert Vaccine - Medical Administration</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/insert_center.css">
</head>
<body>
    <div class="page-loader">
        <div class="loader-icon">
            <i class="fas fa-hospital"></i>
        </div>
        <div class="loader-text">Initializing Medical Interface...</div>
    </div>

    <div class="medical-bg">
        <div class="medical-icon"><i class="fas fa-pills"></i></div>
        <div class="medical-icon"><i class="fas fa-stethoscope"></i></div>
        <div class="medical-icon"><i class="fas fa-heartbeat"></i></div>
        <div class="medical-icon"><i class="fas fa-user-md"></i></div>
        <div class="medical-icon"><i class="fas fa-hospital"></i></div>
        <div class="medical-icon"><i class="fas fa-first-aid"></i></div>
    </div>

    <div class="container">
        <div class="header">
            <h1 class="header-title">Center Registration</h1>
            <p class="header-subtitle">Medical Administration System</p>
        </div>

        <div class="form-container">
            <h2 class="form-title">Add New Center</h2>
            
            <form method="post" id="centerForm">
                <div class="form-grid">
                    <div class="form-group" data-field="name">
                        <label for="name">Center Name</label>
                        <input type="text" id="name" name="name" required placeholder="Enter center name...">
                        <div class="input-highlight"></div>
                    </div>

                    <div class="form-group" data-field="address">
                        <label for="address">Address</label>
                        <input type="text" id="address" name="address" required placeholder="Enter center address...">
                        <div class="input-highlight"></div>
                    </div>

                    <div class="form-group" data-field="contact">
                        <label for="contact">Contact</label>
                        <input type="text" id="contact" name="contact" required placeholder="Enter contact number...">
                        <div class="input-highlight"></div>
                    </div>

                    <div class="form-group" data-field="available_vaccines">
                        <label for="available_vaccines">Available Vaccines</label>
                        <textarea id="available_vaccines" name="available_vaccines" placeholder="Enter available vaccines..."></textarea>
                        <div class="input-highlight"></div>
                    </div>
                </div>

                <div class="submit-section">
                    <button type="submit" class="btn-submit" id="submitBtn">Insert Center</button>
                    <br>
                    <a href="show_centers.php" class="view-link">View All Centers</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Form enhancement JavaScript
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('centerForm');
            const submitBtn = document.getElementById('submitBtn');
            
            // Add focus enhancement to form fields
            const formInputs = document.querySelectorAll('input, textarea');
            formInputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.style.transform = 'translateY(-2px)';
                    this.parentElement.style.boxShadow = '0 10px 25px rgba(103, 126, 234, 0.1)';
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.style.transform = 'translateY(0)';
                    this.parentElement.style.boxShadow = 'none';
                });

                // Add typing animation
                input.addEventListener('input', function() {
                    this.style.transform = 'scale(1.01)';
                    setTimeout(() => {
                        this.style.transform = 'scale(1)';
                    }, 100);
                });
            });

            // Form submission enhancement
            form.addEventListener('submit', function(e) {
                // Add loading state
                document.body.classList.add('loading');
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                
                // Validate required fields
                const requiredFields = form.querySelectorAll('[required]');
                let isValid = true;
                
                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        isValid = false;
                        field.style.borderColor = '#e74c3c';
                        field.style.boxShadow = '0 0 0 4px rgba(231, 76, 60, 0.1)';
                        
                        setTimeout(() => {
                            field.style.borderColor = '';
                            field.style.boxShadow = '';
                        }, 3000);
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                    document.body.classList.remove('loading');
                    submitBtn.innerHTML = 'Insert Center';
                }
            });

            // Add smooth scroll to form
            const formContainer = document.querySelector('.form-container');
            if (formContainer) {
                formContainer.scrollIntoView({ behavior: 'smooth' });
            }

            // Particle animation on successful submission
            function createSuccessParticles() {
                for (let i = 0; i < 20; i++) {
                    const particle = document.createElement('div');
                    particle.style.cssText = `
                        position: fixed;
                        top: 50%;
                        left: 50%;
                        width: 10px;
                        height: 10px;
                        background: #38ef7d;
                        border-radius: 50%;
                        pointer-events: none;
                        z-index: 9999;
                        animation: explode 1s ease-out forwards;
                    `;
                    
                    const angle = (i * 18) * Math.PI / 180;
                    const velocity = 50 + Math.random() * 50;
                    
                    particle.style.setProperty('--x', Math.cos(angle) * velocity + 'px');
                    particle.style.setProperty('--y', Math.sin(angle) * velocity + 'px');
                    
                    document.body.appendChild(particle);
                    
                    setTimeout(() => particle.remove(), 1000);
                }
            }

            // Add explosion animation CSS
            const style = document.createElement('style');
            style.textContent = `
                @keyframes explode {
                    0% {
                        transform: translate(0, 0) scale(1);
                        opacity: 1;
                    }
                    100% {
                        transform: translate(var(--x), var(--y)) scale(0);
                        opacity: 0;
                    }
                }
            `;
            document.head.appendChild(style);

            // Trigger particle effect on form submission
            if (window.location.search.includes('success')) {
                setTimeout(createSuccessParticles, 500);
            }
        });

        // Add parallax effect to medical icons
        document.addEventListener('mousemove', function(e) {
            const icons = document.querySelectorAll('.medical-icon');
            const mouseX = e.clientX / window.innerWidth;
            const mouseY = e.clientY / window.innerHeight;

            icons.forEach((icon, index) => {
                const speed = (index + 1) * 0.5;
                const x = mouseX * speed * 20;
                const y = mouseY * speed * 20;
                icon.style.transform += ` translate(${x}px, ${y}px)`;
            });
        });

        // Auto-focus first input
        window.addEventListener('load', function() {
            setTimeout(() => {
                document.getElementById('name').focus();
            }, 2500);
        });
    </script>
</body>
</html>