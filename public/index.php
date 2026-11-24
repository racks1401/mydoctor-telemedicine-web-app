<?php
require_once __DIR__ . '/../app/middleware/remember-me.php';

$validRoles = ['admin', 'doctor', 'patient'];
if (!empty($_SESSION['user_id']) && in_array($_SESSION['usertype'] ?? '', $validRoles, true)) {
    header("Location: /my_doctor/public/" . $_SESSION['usertype'] . "/");
    exit;
}

/** Prints a one-time flash message from the session, safely escaped. */
function flashMessage(string $key, string $class): void {
    if (!empty($_SESSION[$key])) {
        echo '<p class="' . $class . '">' . htmlspecialchars($_SESSION[$key], ENT_QUOTES, 'UTF-8') . '</p>';
        unset($_SESSION[$key]);
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Registration/Login</title>
  <link rel="stylesheet" href="assets/css/main.css" />
</head>

<body>
  <div class="cont">
    <form class="form sign-in" action="login_register.php" method="POST">
      <h2>Login</h2>

      <?php flashMessage('login_error', 'error-msg'); ?>
      <?php flashMessage('register_msg', 'success-msg'); ?>

      <input type="email" name="email" placeholder="Email" required />
      <input type="password" name="password" placeholder="Password" required />

      <label class="remember-me" style="display:flex; align-items:center; justify-content:center; gap:8px; margin-top:12px; font-size:14px; cursor:pointer;">
        <input type="checkbox" name="remember_me" value="1" style="width:auto; margin:0;" />
        Remember me for 30 days
      </label>

      <button class="submit" name="login">Sign In</button>
    </form>


    <div class="sub-cont">
      <div class="img">
        <div class="img__text m--up">
          <h2>New here?</h2>
          <p>Register as a doctor or patient!</p>
        </div>
        <div class="img__text m--in">
          <h2>Welcome Back!</h2>
          <p>If you already have an account, login here.</p>
        </div>
        <div class="img__btn" id="toggle">
          <span class="m--up">Register</span>
          <span class="m--in">Login</span>
        </div>
      </div>

      <form class="form sign-up" action="login_register.php" method="POST">
        <h2>Register</h2>
        <?php flashMessage('register_error', 'error-msg'); ?>

        <select id="usertype" name="usertype">
          <option value="patient">Patient</option>
          <option value="doctor">Doctor</option>
        </select>

        <div id="patientForm" class="form-grid">
          <input type="text" name="name" placeholder="Full Name" required />
          <input type="email" name="email" placeholder="Email" required />
          <input type="tel" name="phone" placeholder="Phone" required />
          <input type="password" name="password" placeholder="Password (min 6 characters)" minlength="6" required />
          <input type="date" name="dob" placeholder="Date of Birth" required />
          <select name="gender" required>
            <option value="" disabled selected>Gender</option>
            <option value="male">Male</option>
            <option value="female">Female</option>
            <option value="other">Other</option>
          </select>
          <input type="text" name="address" placeholder="Address" required />
          <input type="text" name="city" placeholder="City" required />
          <input type="text" name="state" placeholder="State" required />
          <input type="text" name="zip" placeholder="Pin Code" required />
        </div>

        <div id="doctorForm" class="form-grid" style="display: none;">
          <input type="text" name="specialization" placeholder="Specialization" data-required />
          <input type="text" name="qualification" placeholder="Education" data-required />
          <input type="number" name="experience" placeholder="Experience (years)" min="0" data-required />
          <input type="text" name="medical_reg_no" placeholder="License Number" data-required />
          <input type="text" name="about" placeholder="About Me" />
          <input type="text" name="working_time" placeholder="Working Time" />
        </div>

        <button class="submit" name="register">Register</button>
      </form>
    </div>
  </div>

  <script src="assets/js/main.js"></script>
</body>

</html>
