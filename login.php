<?php
include 'db_connect.php'; // your db connection (like mysqli_connect)

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usernameOrEmail = trim($_POST['username']);
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE username = ? OR email = ?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, 'ss', $usernameOrEmail, $usernameOrEmail);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($user = mysqli_fetch_assoc($result)) {
        if (password_verify($password, $user['password_hash'])) {
            // Login success – you can start a session here
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['is_admin'] = $user['is_admin'];

            $success = "✅ Logged in successfully!";
            header("Location: dashboard.php"); // redirect to dashboard or home
            exit;
        } else {
            $error = "❌ Invalid password.";
        }
    } else {
        $error = "❌ User not found.";
    }
}
?>
<?php $hideAuthLinks = true; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Login - Inteli01</title>
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

<h2>Welcome Back</h2>

<?php if ($error): ?>
  <div class="message error"><?= $error ?></div>
<?php elseif ($success): ?>
  <div class="message success"><?= $success ?></div>
<?php endif; ?>

<input type="text" name="username" placeholder="Username or Email" required />
<input type="password" name="password" placeholder="Password" required />
<button type="submit" class="btn-glow">Login</button>

<p class="switch-link">Don't have an account? <a href="register.php">Register</a></p>
</form>
</div>
<footer>
    <p>&copy; <?php echo date("Y"); ?> Inteli01. All Rights Reserved.</p>
  </footer>
</body>
</html>
