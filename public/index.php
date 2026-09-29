<?php
require_once __DIR__ . '/../app/middleware/remember-me.php';

if (isset($_SESSION['email'])) {
    header("Location: /my_doctor/public/" . $_SESSION['usertype'] . "/");
    exit;
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

      <?php
        if (isset($_SESSION['login_error'])) {
          echo '<p class="error-msg">' . $_SESSION['login_error'] . '</p>';
          unset($_SESSION['login_error']);
        }
      ?>

      <input type="email" name="email" placeholder="Email" required />
      <input type="password" name="password" placeholder="Password" required />
      <p class="forgot-pass">
        <a href="forgot_password.php">Forgot password?</a>
      </p>

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
        <?php
        if (isset($_SESSION['register_error'])) {
          echo '<p class="error-msg">' . $_SESSION['register_error'] . '</p>';
          unset($_SESSION['register_error']);
        }
        ?>
        <select id="usertype" name="usertype" onchange="switchForm()">
          <option value="admin">Admin</option>
          <option value="doctor">Doctor</option>
          <option value="patient">Patient</option>
        </select>

        <div id="patientForm" class="form-grid">
          <input type="text" name="name" placeholder="Full Name" required />
          <input type="email" name="email" placeholder="Email" required />
          <input type="tel" name="phone" placeholder="Phone" required />
          <input type="password" name="password" placeholder="Password" required />
          <input type="date" name="dob" placeholder="Date of Birth" required />
          <input type="text" name="gender" placeholder="Gender" required />
          <input type="text" name="address" placeholder="Address" required />
          <input type="text" name="city" placeholder="City" required />
          <input type="text" name="state" placeholder="State" required />
          <input type="text" name="zip" placeholder="Pin Code" required />
        </div>

        <div id="doctorForm" class="form-grid" style="display: none;">
          <input type="text" name="specialization" placeholder="Specialization" required />
          <input type="text" name="qualification" placeholder="Education" required />
          <input type="text" name="experience" placeholder="Experience" required />
          <input type="text" name="medical_reg_no" placeholder="License Number" required />
          <input type="text" name="about" placeholder="About Me" />
          <input type="text" name="working_time" placeholder="Working Time" />
        </div>

        <button class="submit" name="register">Register</button>
      </from>
    </div>
  </div>

  <script src="assets/js/main.js"></script>
</body>

</html>