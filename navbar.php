<!-- navbar.php -->
<div class="container nav">
  <div class="logo">Inteli<span>01</span></div>
  <nav>
    <?php if (!isset($hideAuthLinks)): ?>
      <a href="login.php">Login</a>
      <a href="register.php" class="btn-glow">Register</a>
    <?php endif; ?>
  </nav>
</div>
