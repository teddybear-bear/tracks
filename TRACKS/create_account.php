<?php
session_start();

$usersFile = 'users.json';
$users = [];

if (file_exists($usersFile)) {
    $users = json_decode(file_get_contents($usersFile), true);
    if (!is_array($users)) {
        $users = [];
    }
}

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm  = trim($_POST['confirm_password'] ?? '');

    if ($username === "" || $password === "" || $confirm === "") {
        $error = "All fields are required.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } elseif (isset($users[$username])) {
        $error = "Username already exists.";
    } else {
        $users[$username] = password_hash($password, PASSWORD_DEFAULT);
        file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT));

        $_SESSION['username'] = $username;

        echo "<script>alert('Created account successfully, ready for log in'); window.location.href = 'login.php';</script>";
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Create Account</title>
<style>
  body {
    margin: 0;
    height: 100vh;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    background: url('https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1920&q=80') no-repeat center center fixed;
    background-size: cover;
    position: relative;
    display: flex;
    justify-content: center;
    align-items: center;
  }

  body::before {
    content: "";
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.3);
    z-index: 0;
  }

  .create-container {
    position: relative;
    z-index: 1;
    max-width: 420px;
    width: 90%;
    background: rgba(255 255 255 / 0.15);
    border-radius: 28px;
    padding: 40px 35px;
    box-shadow: 0 8px 32px rgba(0 0 0 / 0.25);
    backdrop-filter: saturate(180%) blur(24px);
    -webkit-backdrop-filter: saturate(180%) blur(24px);
    border: 1px solid rgba(255 255 255 / 0.3);
    color: white;
    text-align: center;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }

  .create-container:hover {
    transform: translateY(-6px);
    box-shadow: 0 10px 44px rgba(0 0 0 / 0.35);
  }

  .create-container h2 {
    font-weight: 700;
    font-size: 30px;
    margin-bottom: 30px;
  }

  .create-container input {
    width: 92%;
    padding: 16px;
    margin: 14px 0;
    border: none;
    border-radius: 16px;
    background: rgba(255 255 255 / 0.25);
    color: white;
    font-size: 16px;
    text-align: center;
    font-weight: 600;
    outline-offset: 2px;
    transition: background 0.3s ease, box-shadow 0.3s ease, transform 0.2s ease;
  }

  .create-container input::placeholder {
    color: rgba(255 255 255 / 0.7);
  }

  .create-container input:focus {
    background: rgba(255 255 255 / 0.45);
    box-shadow: 0 0 16px rgba(0, 122, 255, 0.85);
    transform: scale(1.02);
  }

  .create-container button {
    width: 95%;
    padding: 16px;
    margin-top: 20px;
    border-radius: 16px;
    border: none;
    background: linear-gradient(135deg, #007aff, #00c6ff);
    color: white;
    font-weight: 700;
    font-size: 18px;
    cursor: pointer;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }

  .create-container button:hover {
    transform: scale(1.07);
    box-shadow: 0 8px 26px rgba(0, 122, 255, 0.8);
  }

  .links {
    margin-top: 26px;
    display: flex;
    justify-content: center;
    gap: 16px;
    flex-wrap: wrap;
  }

  .links a, .links button {
    background: transparent;
    border: none;
    color: #d0e7ff;
    font-weight: 600;
    font-size: 15px;
    cursor: pointer;
    text-decoration: none;
    padding: 0;
    transition: color 0.3s ease, transform 0.3s ease;
  }

  .links a:hover, .links button:hover {
    color: #00c6ff;
    transform: scale(1.05);
  }

  .error {
    margin-bottom: 18px;
    background: rgba(255 0 0 / 0.2);
    color: #ff6b6b;
    padding: 12px;
    border-radius: 12px;
    font-weight: 700;
    animation: shake 0.3s ease;
  }

  @keyframes shake {
    0%, 100% { transform: translateX(0); }
    20%, 60% { transform: translateX(-6px); }
    40%, 80% { transform: translateX(6px); }
  }

  /* Loading modal styles */
  #loadingModal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.45);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    z-index: 1000;
    justify-content: center;
    align-items: center;
  }

  #loadingModal.active {
    display: flex;
  }

  /* iOS style spinner */
  .spinner {
    width: 60px;
    height: 60px;
    border: 6px solid rgba(255, 255, 255, 0.3);
    border-top-color: #00c6ff;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    box-shadow: 0 0 15px #00c6ff;
  }

  @keyframes spin {
    to { transform: rotate(360deg); }
  }
</style>
</head>
<body>

<div class="create-container">
  <h2>Create Account</h2>
  <?php if ($error): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <form method="post" autocomplete="off" id="createForm">
    <input type="text" name="username" placeholder="Username" required autocomplete="username" />
    <input type="password" name="password" id="password" placeholder="Password" required autocomplete="new-password" />
    <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required autocomplete="new-password" />
    <button type="submit">Create Account</button>
  </form>
  <div class="links">
    <a href="login.php">Back to Login</a>
    <button type="button" onclick="togglePassword()">Show Password</button>
  </div>
</div>

<!-- Loading modal -->
<div id="loadingModal">
  <div class="spinner"></div>
</div>

<script>
function togglePassword() {
  const passField = document.getElementById("password");
  const confirmField = document.getElementById("confirm_password");
  const toggleBtn = document.querySelector(".links button");
  if (passField.type === "password") {
    passField.type = "text";
    confirmField.type = "text";
    toggleBtn.textContent = "Hide Password";
  } else {
    passField.type = "password";
    confirmField.type = "password";
    toggleBtn.textContent = "Show Password";
  }
}

// Show loading modal on form submit
document.getElementById('createForm').addEventListener('submit', function(e) {
  // Show modal
  document.getElementById('loadingModal').classList.add('active');
});
</script>

</body>
</html>
