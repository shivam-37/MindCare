<?php
session_start();
require_once 'db_connection.php';

// Initialize all variables
$name = $email = $phone = $password = $confirm_password = '';
$nameErr = $emailErr = $phoneErr = $passwordErr = $confirmPasswordErr = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['signup'])) {
    // Validate name
    if (empty($_POST["name"])) {
        $nameErr = "Name is required";
    } else {
        $name = test_input($_POST["name"]);
        if (!preg_match("/^[a-zA-Z ]*$/", $name)) {
            $nameErr = "Only letters and white space allowed";
        }
    }
    
    // Validate email
    if (empty($_POST["email"])) {
        $emailErr = "Email is required";
    } else {
        $email = test_input($_POST["email"]);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emailErr = "Invalid email format";
        }
    }
    
    // Validate phone
    if (empty($_POST["phone"])) {
        $phoneErr = "Phone is required";
    } else {
        $phone = test_input($_POST["phone"]);
        if (!preg_match("/^[0-9]{10,15}$/", $phone)) {
            $phoneErr = "Invalid phone number";
        }
    }
    
    // Validate password
    if (empty($_POST["password"])) {
        $passwordErr = "Password is required";
    } else {
        $password = test_input($_POST["password"]);
        if (strlen($password) < 8) {
            $passwordErr = "Password must be at least 8 characters";
        }
    }
    
    // Validate confirm password
    if (empty($_POST["confirm-password"])) {
        $confirmPasswordErr = "Please confirm password";
    } else {
        $confirm_password = test_input($_POST["confirm-password"]);
        if ($password !== $confirm_password) {
            $confirmPasswordErr = "Passwords do not match";
        }
    }

    if (empty($nameErr) && empty($emailErr) && empty($phoneErr) && empty($passwordErr) && empty($confirmPasswordErr)) {
        try {
            // Check if email already exists
            $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
            $checkStmt->bindParam(':email', $email);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() > 0) {
                $emailErr = "Email already registered";
            } else {
                // Hash the password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert new user
                $insertStmt = $conn->prepare("INSERT INTO users (name, email, phone, password) 
                                            VALUES (:name, :email, :phone, :password)");
                $insertStmt->bindParam(':name', $name);
                $insertStmt->bindParam(':email', $email);
                $insertStmt->bindParam(':phone', $phone);
                $insertStmt->bindParam(':password', $hashed_password);
                $insertStmt->execute();
                
                $_SESSION['success_message'] = "Signup successful! Please log in.";
                header("Location: login.php");
                exit();
            }
        } catch(PDOException $e) {
            $emailErr = "Database error: " . $e->getMessage();
        }
    }
}

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Sign Up</title>

  <!-- Tailwind CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          colors: {
            mental: {
              light: '#FEF3C7',
              primary: '#F59E0B',
              dark: '#B45309'
            }
          },
          animation: {
            float: 'float 4s ease-in-out infinite',
            fadeInUp: 'fadeInUp 0.8s ease-out',
          },
          keyframes: {
            float: {
              '0%, 100%': { transform: 'translateY(0)' },
              '50%': { transform: 'translateY(-10px)' },
            },
            fadeInUp: {
              '0%': { opacity: 0, transform: 'translateY(20px)' },
              '100%': { opacity: 1, transform: 'translateY(0)' },
            },
          },
        },
      }
    }
  </script>

  <!-- Theme loader -->
  <script>
    if (
      localStorage.theme === 'dark' ||
      (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)
    ) {
      document.documentElement.classList.add('dark');
    } else {
      document.documentElement.classList.remove('dark');
    }
  </script>
</head>
<body class="bg-amber-100 dark:bg-black flex justify-center items-center min-h-screen text-gray-900 dark:text-white transition duration-300 relative">

  <!-- Logo at Top-Left Corner -->
  <a href="/home" class="absolute top-6 left-6 flex items-center space-x-2 z-50">
    <svg class="w-10 h-10 text-mental-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
    </svg>
    <span class="text-2xl font-bold text-mental-primary">MindCare</span>
  </a>

  <!-- Dark Mode Toggle Button -->
  <button id="theme-toggle" class="absolute top-6 right-6 z-50 text-gray-700 dark:text-gray-200">
    <svg id="sun-icon" class="w-6 h-6 hidden" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.36 6.36l-.7-.7M6.34 6.34l-.7-.7m12.02 0l.7.7M6.34 17.66l.7.7M12 8a4 4 0 100 8 4 4 0 000-8z" />
    </svg>
    <svg id="moon-icon" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3a7 7 0 0010.59 9.79z" />
    </svg>
  </button>

  <!-- Main Container -->
  <div class="bg-white dark:bg-gray-800 w-[90%] sm:w-[85%] md:w-[80%] lg:w-[75%] xl:w-[70%] 
              my-10 flex flex-wrap items-center justify-center md:justify-between 
              h-auto md:min-h-[80vh] rounded-3xl p-6 md:p-10 shadow-lg relative">

    <!-- Signup Form -->
    <div class="text-white bg-black dark:bg-black w-full sm:w-[350px] p-10 rounded-2xl flex flex-col items-center md:ml-10">
      <h1 class="text-3xl font-bold mb-4">Sign Up</h1>
      <p class="text-sm text-center mb-6">Create an account to get started</p>

      <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="w-full space-y-4">
        <div>
          <input id="name" name="name" type="text" placeholder="Full Name" value="<?php echo $name; ?>" 
                 class="border h-10 w-full px-3 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-100 
                        bg-white dark:bg-gray-900 dark:text-white">
          <span class="text-red-500 text-sm"><?php echo $nameErr; ?></span>
        </div>
        <div>
          <input id="email" name="email" type="email" placeholder="Email" value="<?php echo $email; ?>" 
                 class="border h-10 w-full px-3 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-100 
                        bg-white dark:bg-gray-900 dark:text-white">
          <span class="text-red-500 text-sm"><?php echo $emailErr; ?></span>
        </div>
        <div>
          <input id="phone" name="phone" type="text" placeholder="Phone Number" value="<?php echo $phone; ?>" 
                 class="border h-10 w-full px-3 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-100 
                        bg-white dark:bg-gray-900 dark:text-white">
          <span class="text-red-500 text-sm"><?php echo $phoneErr; ?></span>
        </div>
        <div>
          <input id="password" name="password" type="password" placeholder="Password" 
                 class="border h-10 w-full px-3 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-100 
                        bg-white dark:bg-gray-900 dark:text-white">
          <span class="text-red-500 text-sm"><?php echo $passwordErr; ?></span>
        </div>
        <div>
          <input id="confirm-password" name="confirm-password" type="password" placeholder="Confirm Password" 
                 class="border h-10 w-full px-3 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-100 
                        bg-white dark:bg-gray-900 dark:text-white">
          <span class="text-red-500 text-sm"><?php echo $confirmPasswordErr; ?></span>
        </div>

        <button type="submit" name="signup" 
          class="cursor-pointer w-full bg-amber-200 dark:bg-amber-200 text-black dark:text-black h-10 
                 rounded-md hover:bg-gray-100 dark:hover:bg-gray-100 transition duration-500 
                 ease-in-out transform hover:scale-105 animate-fadeInUp">
          Sign Up
        </button>
      </form>

      <p class="text-center mt-4">Already have an account? 
        <a href="login.php" class="text-amber-200 font-bold">Sign in</a>
      </p>
    </div>

    <!-- Signup Page Image -->
    <img class="w-full md:w-[50%] mt-6 md:mt-0 md:ml-10 object-cover rounded-2xl animate-float" 
         src="/Project/img/signup.png" alt="Signup Image">
  </div>

  <!-- Dark Mode Toggle Script -->
  <script>
    const toggleBtn = document.getElementById('theme-toggle');
    const root = document.documentElement;
    const moonIcon = document.getElementById('moon-icon');
    const sunIcon = document.getElementById('sun-icon');

    function updateIcons() {
      const isDark = root.classList.contains('dark');
      if (isDark) {
        moonIcon.classList.add('hidden');
        sunIcon.classList.remove('hidden');
      } else {
        sunIcon.classList.add('hidden');
        moonIcon.classList.remove('hidden');
      }
    }

    toggleBtn.addEventListener('click', () => {
      const isDark = root.classList.toggle('dark');
      localStorage.theme = isDark ? 'dark' : 'light';
      updateIcons();
    });

    updateIcons();
  </script>
</body>
</html>