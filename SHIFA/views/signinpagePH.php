<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <!-- BOXICONS -->
    <link
      href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css"
      rel="stylesheet"
    />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- CSS -->
    <link rel="stylesheet" href="../public/styles/signinpagePH.css" />
    <title>Login & Register | Ludiflex</title>
  </head>
  <body>
  

  <header>
    <div class="logo">
        <i class="fa-solid fa-pills"></i> SHIFA <span>Online</span>
    </div>

    <div class="header-buttons">
        <button class="chat-btn">About Us</button>
        <a href="AppUsers.php" class="login"> LOG IN <i class="fa-solid fa-user"></i></a>
    </div>
</header>



    <div class="wrapper">
      <div class="form-header">
        <div class="titles">
          <div class="title-login">Login</div>
          <div class="title-register">Register</div>
        </div>
      </div>
      <!-- LOGIN FORM -->
      <form action="#" class="login-form" autocomplete="off">
        <div class="input-box">
          <input type="text" class="input-field" id="log-email" required />
          <label for="log-email" class="label">Email</label>
          <i class="bx bx-envelope icon"></i>
        </div>
        <div class="input-box">
          <input type="password" class="input-field" id="log-pass" required />
          <label for="log-pass" class="label">Password</label>
          <i class="bx bx-lock-alt icon"></i>
        </div>
        <div class="form-cols">
          <div class="col-1"></div>
          <div class="col-2">
            <a href="#">Forgot password?</a>
          </div>
        </div>
        <div class="input-box">
          <button class="btn-submit" id="SignInBtn">
            Sign In <i class="bx bx-log-in"></i>
          </button>
        </div>
        <div class="switch-form">
          <span
            >Don't have an account?
            <a href="#" onclick="registerFunction()">Register</a></span>
        </div>
      </form>

      <!-- REGISTER FORM -->
      <form action="#" class="register-form" autocomplete="off">
        <div class="input-boxx">
          <input type="text" class="input-fielld" id="pharmacy_name" required />
          <label for="pharmacy_name" class="label">Pharmacy Name</label>
          <i class="bx bx-user icon"></i>
        </div>

        <div class="input-boxx">
          <input type="text" class="input-fielld" id="pharmacy_liscense" required />
          <label for="pharmacy_liscense" class="label">Pharmacy Liscence Number</label>
          <i class="bx bx-id-card icon"></i>
        </div>
        
        <div class="input-boxx">
          <input type="text" class="input-fielld" id="reg-phone" required />
          <label for="reg-phone" class="label">Phone number</label>
          <i class="bx bx-phone icon"></i>
        </div>
         
        <div class="input-boxx">
          <input type="text" class="input-fielld" id="address" required />
          <label for="address" class="label">Pharmacy Address</label>
          <i class="bx bx-map icon"></i>
        </div>

        <div class="input-boxx">
          <input type="text" class="input-fielld" id="reg-email" required />
          <label for="reg-email" class="label">Email</label>
          <i class="bx bx-envelope icon"></i>
        </div>

        <div class="input-boxx">
          <input type="password" class="input-fielld" id="reg-pass" required />
          <label for="reg-pass" class="label">Password</label>
          <i class="bx bx-lock-alt icon"></i>
        </div>
          
        

        <div class="form-cols">
          <div class="col-1">
            <input type="checkbox" id="agree" />
            <label for="agree"> I agree to terms & conditions</label>
          </div>
          <div class="col-2"></div>
        </div>
        <div class="input-boxx">
          <button class="btn-submit" id="SignUpBtn">
            Sign Up <i class="bx bx-user-plus"></i>
          </button>
        </div>
        <div class="switch-form">
          <span
            >Already have an account?
            <a href="#" onclick="loginFunction()">Login</a></span
          >
        </div>
      </form>
    </div>

    <script src="../controllers/Javascript/Signinpage.js"></script>
  </body>
</html>
