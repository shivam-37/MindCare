<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login</title>

  <!-- Tailwind CDN with extended config -->
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
          keyframes: {
            fadeSlide: {
              '0%': { opacity: '0', transform: 'translateX(20px)' },
              '100%': { opacity: '1', transform: 'translateX(0)' },
            },
            floating: {
              '0%, 100%': { transform: 'translateY(0)' },
              '50%': { transform: 'translateY(-10px)' }
            },
            rotateIn: {
              '0%': { transform: 'rotate(0deg)' },
              '100%': { transform: 'rotate(360deg)' }
            }
          },
          animation: {
            'fade-slide': 'fadeSlide 1s ease-out forwards',
            'floating': 'floating 3s ease-in-out infinite',
            'spin-slow': 'rotateIn 1s ease-in-out'
          }
        }
      }
    };
  </script>

  <!-- Theme Load Script -->
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

  <link rel="stylesheet" href="/Project/src/output.css">  
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
  <button id="theme-toggle" class="absolute top-6 right-6 z-50 text-gray-700 dark:text-gray-200 transition-transform duration-500 hover:scale-125">
    <svg id="sun-icon" class="w-6 h-6 hidden" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.36 6.36l-.7-.7M6.34 6.34l-.7-.7m12.02 0l.7.7M6.34 17.66l.7.7M12 8a4 4 0 100 8 4 4 0 000-8z" />
    </svg>
    <svg id="moon-icon" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3a7 7 0 0010.59 9.79z" />
    </svg>
  </button>

  <?php
  session_start();
  require_once 'db_connection.php';

  // Initialize variables
  $email = $password = '';
  $emailErr = $passwordErr = '';

  if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
      // Validate email
      if (empty($_POST["email"])) {
          $emailErr = "Email is required";
      } else {
          $email = test_input($_POST["email"]);
          if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
              $emailErr = "Invalid email format";
          }
      }
      
      // Validate password
      if (empty($_POST["password"])) {
          $passwordErr = "Password is required";
      } else {
          $password = test_input($_POST["password"]);
      }

      if (empty($emailErr) && empty($passwordErr)) {
          try {
              // Prepare SQL statement
              $stmt = $conn->prepare("SELECT id, email, password FROM users WHERE email = :email");
              $stmt->bindParam(':email', $email);
              $stmt->execute();
              
              // Check if user exists
              if ($stmt->rowCount() == 1) {
                  $user = $stmt->fetch(PDO::FETCH_ASSOC);
                  
                  // Verify password
                  if (password_verify($password, $user['password'])) {
                      $_SESSION['user_id'] = $user['id'];
                      $_SESSION['user_email'] = $user['email'];
                      
                      // Regenerate session ID for security
                      session_regenerate_id(true);
                      
                      header("Location: dash.php");
                      exit();
                  } else {
                      $passwordErr = "Invalid credentials";
                  }
              } else {
                  // Don't reveal whether email exists (security best practice)
                  $passwordErr = "Invalid credentials";
              }
          } catch(PDOException $e) {
              $passwordErr = "Database error. Please try again later.";
              // Log the actual error for debugging
              error_log("Login error: " . $e->getMessage());
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
  <!-- Main Container -->
  <div class="bg-white dark:bg-gray-800 w-[90%] sm:w-[85%] md:w-[80%] lg:w-[75%] xl:w-[70%] 
              my-10 flex flex-wrap items-center justify-center md:justify-between 
              h-auto md:min-h-[80vh] rounded-3xl p-6 md:p-10 shadow-lg relative">

    <!-- Login Form Section -->
    <div class="text-white bg-black w-full sm:w-[350px] p-10 rounded-2xl flex flex-col items-center md:ml-10 animate-fade-slide">
      <h1 class="text-3xl font-bold mb-4">Sign in</h1>
      <p class="text-sm text-center mb-6">Hey, enter your details to login to your account</p>
      
      <form class="w-full space-y-4" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <div>
          <input id="email" name="email" type="text" placeholder="Enter Email" 
                 class="border h-10 w-full px-3 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-100 text-black" 
                 value="<?php echo $email; ?>" required>
          <span class="text-red-500 text-sm"><?php echo $emailErr; ?></span>
        </div>
        
        <div>
          <input id="password" name="password" type="password" placeholder="Password" 
                 class="border h-10 w-full px-3 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-100 text-black" required>
          <span class="text-red-500 text-sm"><?php echo $passwordErr; ?></span>
        </div>

        <button type="submit" name="login" class="cursor-pointer w-full bg-amber-200 text-black h-10 rounded-md 
                                                    hover:bg-gray-100 transition-all duration-300 hover:scale-105">
          Login
        </button>
      </form>
      
      <p class="text-center my-2">--Or sign in with--</p>

      <!-- Social Icons with Animation -->
      <div class="flex justify-center space-x-3">
        <a href="facebook_login.php" 
           class="cursor-pointer border bg-amber-50 text-black flex items-center justify-center 
                  px-4 py-2 rounded-md w-24 transition-all duration-300 hover:scale-105 hover:bg-gray-100">
          <img src="/Project/img/facebook.png" alt="Facebook" class="h-6">
        </a>

        <a href="google_login.php" 
           class="cursor-pointer border bg-amber-50 text-black flex items-center justify-center 
                  px-4 py-2 rounded-md w-24 transition-all duration-300 hover:scale-105 hover:bg-gray-100">
          <img src="/Project/img/google.png" alt="Google" class="h-6">
        </a>

        <a href="twitter_login.php" 
           class="cursor-pointer border bg-amber-50 text-black flex items-center justify-center 
                  px-4 py-2 rounded-md w-24 transition-all duration-300 hover:scale-105 hover:bg-gray-100">
          <img src="/Project/img/twiter.png" alt="Twitter" class="h-6 w-6">
        </a>
      </div>

      <p class="text-center mt-4">Don't have an account? 
        <a href="/Project/html/signup.php" target="_blank" class="text-amber-200 font-bold">Sign up</a>
      </p>
    </div>

    <!-- Image Section -->
    <div class="w-full md:w-[50%] mt-6 md:mt-0 md:ml-10 rounded-2xl overflow-hidden min-h-[300px] max-h-[500px] flex justify-center items-center animate-floating">
      <img src="/Project/img/loginpage.png" alt="Login Image"
           class="w-full h-full object-cover rounded-2xl shadow-lg" />
    </div>
  </div>

  <!-- Dark Mode Toggle Logic -->
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
      toggleBtn.classList.add('animate-spin-slow');
      setTimeout(() => toggleBtn.classList.remove('animate-spin-slow'), 500);
    });

    // Initial sync
    updateIcons();
  </script>
</body>
</html>