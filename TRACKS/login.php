<?php
session_start();
ob_start();

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $usersFile = 'users.json';
    $users = file_exists($usersFile) ? json_decode(file_get_contents($usersFile), true) : [];

    if (isset($users[$username]) && password_verify($password, $users[$username])) {
        $_SESSION['username'] = $username;
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid username or password.";
    }
}

ob_end_flush();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Login</title>
<style>
  /* Reset & basics */
  body {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    margin: 0;
    height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    background: url('https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1920&q=80') no-repeat center center fixed;
    background-size: cover;
    position: relative;
  }

  /* Dark overlay to improve contrast */
  body::before {
    content: "";
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(0, 0, 0, 0.3);
    z-index: 0;
  }

  /* Glassmorphism login box */
  .login-container {
    position: relative;
    z-index: 1;
    max-width: 400px;
    width: 100%;
    padding: 40px 30px;
    border-radius: 24px;
    background: rgba(255 255 255 / 0.15); /* 15% white */
    box-shadow: 0 8px 32px rgba(0 0 0 / 0.25);
    backdrop-filter: saturate(180%) blur(24px);
    -webkit-backdrop-filter: saturate(180%) blur(24px);
    border: 1px solid rgba(255 255 255 / 0.3);
    color: white;
    text-align: center;
    animation: fadeIn 1.2s ease, float 2.7s ease-in-out infinite;
    transition: animation 0.3s ease;
  }

  /* Stop float animation kapag may input */
  .login-container.no-float {
    animation: fadeIn 1.2s ease;
  }

  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(30px);}
    to { opacity: 1; transform: translateY(0);}
  }
  @keyframes float {
    0%, 100% { transform: translateY(0);}
    50% { transform: translateY(-8px);}
  }

  .login-container h2 {
    margin-bottom: 24px;
    font-weight: 700;
    font-size: 28px;
  }

  .login-container input {
    width: 90%;
    padding: 14px;
    margin: 12px 0;
    border: none;
    border-radius: 14px;
    outline: none;
    background: rgba(255 255 255 / 0.25);
    color: white;
    font-size: 16px;
    text-align: center;
    transition: background 0.3s, box-shadow 0.3s;
    font-weight: 600;
  }

  .login-container input::placeholder {
    color: rgba(255 255 255 / 0.7);
  }

  .login-container input:focus {
    background: rgba(255 255 255 / 0.4);
    box-shadow: 0 0 12px rgba(0, 122, 255, 0.9);
  }

  .login-container button {
    width: 95%;
    padding: 14px;
    background: linear-gradient(135deg, #007aff, #00c6ff);
    border: none;
    border-radius: 14px;
    color: white;
    font-weight: 700;
    font-size: 18px;
    cursor: pointer;
    margin-top: 20px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }

  .login-container button:hover {
    transform: scale(1.07);
    box-shadow: 0 6px 24px rgba(0, 122, 255, 0.8);
  }

  .links {
    margin-top: 18px;
    display: flex;
    justify-content: space-around;
    flex-wrap: wrap;
  }

  .links a {
    color: #d0e7ff;
    font-size: 15px;
    text-decoration: none;
    transition: color 0.3s;
    font-weight: 600;
  }

  .links a:hover {
    color: #00c6ff;
  }

  .error {
    color: #ff6b6b;
    background: rgba(255 0 0 / 0.2);
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 16px;
    font-weight: 600;
    animation: shake 0.3s ease;
  }

  @keyframes shake {
    0%, 100% { transform: translateX(0);}
    20%, 60% { transform: translateX(-5px);}
    40%, 80% { transform: translateX(5px);}
  }
</style>
</head>
<body>

<div class="login-container" id="loginBox">
  <h2>Login</h2>
  <?php if (!empty($error)): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <form method="post" autocomplete="off">
    <input type="text" name="username" id="username" placeholder="Username" required autocomplete="username" />
    <input type="password" name="password" id="password" placeholder="Password" required autocomplete="current-password" />
    <button type="submit">Login</button>
  </form>
  <div class="links">
    <a href="create_account.php">Create Account</a>
    <a href="forgot_password.php">Forgot Password?</a>
    <a href="#" onclick="togglePassword(event)">Show Password</a>
  </div>
</div>

<script>
function togglePassword(e) {
  e.preventDefault();
  const passField = document.getElementById("password");
  const toggleLink = e.target;
  if (passField.type === "password") {
    passField.type = "text";
    toggleLink.textContent = "Hide Password";
  } else {
    passField.type = "password";
    toggleLink.textContent = "Show Password";
  }
}

// Stop float animation kapag may input
const usernameInput = document.getElementById('username');
const passwordInput = document.getElementById('password');
const loginBox = document.getElementById('loginBox');

function checkInputs() {
  if (usernameInput.value.trim() !== "" || passwordInput.value.trim() !== "") {
    loginBox.classList.add("no-float");
  } else {
    loginBox.classList.remove("no-float");
  }
}

usernameInput.addEventListener("input", checkInputs);
passwordInput.addEventListener("input", checkInputs);
</script>

</body>
</html>
