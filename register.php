<?php
include 'db_connect.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if ($password !== $confirm) {
        $error = "❌ Passwords do not match.";
    } elseif (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $error = "⚠️ Password must be at least 8 characters, include 1 uppercase letter and 1 number.";
    } else {
        // Check if username or email exists
        $check = mysqli_query($con, "SELECT id FROM users WHERE username='$username' OR email='$email'");
        if (mysqli_num_rows($check) > 0) {
            $error = "🚫 Username or email already exists.";
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $insert = mysqli_query($con, "INSERT INTO users (username, email, password_hash) VALUES ('$username', '$email', '$hash')");
            if ($insert) {
                $success = "✅ Account created!";
            } else {
                $error = "❗ Something went wrong. Try again.";
            }
        }
    }
}
?>

<?php $hideAuthLinks = true; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Register - Inteli01</title>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600&display=swap" rel="stylesheet" />
<link rel="stylesheet" href="style.css" />
<style>
body {
  font-family: 'Orbitron', sans-serif;
  background: #0e0e1a;
  color: #f4f4f4;
  margin: 0;
  padding: 0;
}

.auth-container {
  display: flex;
  justify-content: center;
  align-items: center;
  height:83vh;
}


.auth-card {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid #00ffe7;
  border-radius: 15px;
  padding: 30px;
  width: 100%;
  max-width: 400px;
  box-shadow: 0 0 30px rgba(255, 0, 204, 0.3);
  backdrop-filter: blur(12px);
}

.auth-card h2 {
  text-align: center;
  margin-bottom: 20px;
  color: #00ffe7;
  text-shadow: 0 0 10px #00ffe7;
}

.auth-card input {
  padding: 12px;
  margin-bottom: 15px;
  border: none;
  border-radius: 8px;
  width: 100%;
  background-color: rgba(255, 255, 255, 0.1);
  color: #fff;
  font-size: 14px;
  outline: none;
}

.auth-card input::placeholder {
  color: #ccc;
}

.btn-glow {
  background: linear-gradient(90deg, #ff00cc, #3333ff);
  color: white;
  padding: 12px;
  border: none;
  border-radius: 50px;
  cursor: pointer;
  text-shadow: 0 0 5px #fff;
  box-shadow: 0 0 10px #ff00cc, 0 0 40px #3333ff;
  transition: all 0.3s ease;
  width: 100%;
}

.btn-glow:hover {
  transform: scale(1.05);
}

.switch-link {
  text-align: center;
  margin-top: 12px;
}

.switch-link a {
  color: #00ffe7;
  text-decoration: none;
  font-weight: bold;
}

.message {
  text-align: center;
  margin-bottom: 15px;
  padding: 10px;
  border-radius: 6px;
  font-size: 14px;
}

.error {
  background-color: rgba(255, 0, 0, 0.1);
  color: #ff4d4d;
  border: 1px solid #ff4d4d;
}

.success {
  background-color: rgba(0, 255, 0, 0.1);
  color: #33ff99;
  border: 1px solid #33ff99;
}
</style>

</head>
<body>
<header>
  <?php include 'navbar.php'; ?>
</header>
    
<div class="auth-container">
    <form class="auth-card" method="POST" action="">

        <h2>Create Account</h2>

        <?php if ($error): ?>
            <div class="message error"><?= $error ?></div>
        <?php elseif ($success): ?>
            <div class="message success"><?= $success ?></div>
        <?php endif; ?>

        <input type="text" name="username" placeholder="Username" required />
        <input type="email" name="email" placeholder="Email" required />
        <input type="password" name="password" placeholder="Password" required />
        <input type="password" name="confirm_password" placeholder="Confirm Password" required />

        <button type="submit" class="btn-glow">Register</button>
        <p class="switch-link">Already have an account? <a href="login.php">Login</a></p>
    </form>
</div>
<footer>
    <p>&copy; <?php echo date("Y"); ?> Inteli01. All Rights Reserved.</p>
  </footer>
</body>
</html>
