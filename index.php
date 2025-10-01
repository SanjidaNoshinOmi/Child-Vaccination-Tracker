<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&family=Comic+Neue:wght@300;400;700&display=swap"
      rel="stylesheet"
    />
	<link rel="stylesheet" href="css/index.css"> 
    <title>Child Vaccination Tracker</title>
  </head>
<body>
    <div class="medical-icons">
      <div class="medical-icon">💉</div>
      <div class="medical-icon">🏥</div>
      <div class="medical-icon">👶</div>
      <div class="medical-icon">🩺</div>
    </div>

    <div class="floating-elements">
      <div class="floating-element">🧸</div>
      <div class="floating-element">🎈</div>
      <div class="floating-element">⭐</div>
      <div class="floating-element">🌈</div>
    </div>
    <header></header>

    <main>
      <section class="login">
        <div class="brand-section">
          <h1 class="brand-title">VacciTrack</h1>
          <p class="brand-description">
            Keep your little ones safe and healthy with our comprehensive vaccination tracking system. 
            Never miss an important vaccine appointment again!
          </p>
          
          <div class="features">
            <div class="feature">
              <span class="feature-icon">📅</span>
              <span>Track vaccination schedules</span>
            </div>
            <div class="feature">
              <span class="feature-icon">🩺</span>
              <span>Digital health card for each child</span>
            </div>
            <div class="feature">
              <span class="feature-icon">📊</span>
              <span>Monitor health progress</span>
            </div>
            <div class="feature">
              <span class="feature-icon">👨‍⚕️</span>
              <span>Easily locate nearby vaccination centers</span>
            </div>
          </div>
        </div>

        <!-- Login Box -->
        <div class="login_box">
          <h1>Login</h1>
          <form class="login_form" action="login.php" method="post">
            <input type="text" name="userid" placeholder="👤 User ID" required />
            <input
              type="password"
              name="password"
              placeholder="🔒 Password"
              required
            />
            <input type="submit" value="Login" />
          </form>
          <p><a href="forgot_password.php">Forgotten password?</a></p>
          <button class="create_account_btn" onclick="window.location.href='user_register.php'">
            Create new account
          </button>
        </div>
      </section>
    </main>
  </body>
</html>
