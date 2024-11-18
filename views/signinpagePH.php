<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <link rel="stylesheet" href="../public/styles/SignInPH.css" />
    <title> Login Page </title>
</head>

<body>
    <div class="container" id="container">
        <div class="form-container sign-up">
            <form action="../controllers/RegisterPH.php" method="post">
                <h1>Create Account</h1>
                <div class="social-icons">
                </div>

                <input type="text" name="pharmacy_name" placeholder="Pharmacy Name" />

                <input type="text" name="pharmacy_liscense" placeholder="Pharmacy Liscence Number">

                <input type="tel" id="phone" name="phone" placeholder="Phone Number" />

                <input type="email" name="email" placeholder="Email" />

                <input type="password" name="password" placeholder="Password" />

                <button>Sign Up</button>
            </form>
        </div>


        <div class="form-container sign-in">
            <form action="../controllers/LogInPH.php" method="post">
                <h1>Sign In</h1>
                <div class="social-icons">

                </div>
                <input type="email" name="email" placeholder="Email" />

                <input type="password" name="password" placeholder="Password" />

                <a href="forgetpass.php">Forget Your Password?</a>
                <button>Sign In</button>
            </form>
        </div>
        <div class="toggle-container">
            <div class="toggle">
                <div class="toggle-panel toggle-left">
                    <h1>Ready to join ?</h1>
                    <p>Fill in your personal information to access all that we have to offer</p>
                    <button class="hidden" id="login">Sign In</button>
                </div>
                <div class="toggle-panel toggle-right">
                    <h1>Welcome Back!</h1>
                    <p>
                        Register to access all that we have to offer
                    </p>
                    <button class="hidden" id="register">Sign Up</button>
                </div>
            </div>
        </div>
    </div>

    <script src="../controllers/SignInpagePH.js"></script>
</body>

</html>