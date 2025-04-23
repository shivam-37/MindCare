<?php
session_start();

// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Include database connection
require_once 'db_connection.php';

// Initialize variables
$assessmentResults = [];
$error = '';

// Validate user exists in database
try {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE id = :user_id");
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        error_log("User ID {$_SESSION['user_id']} not found in users table");
        session_destroy();
        header("Location: login.php");
        exit();
    }
} catch (PDOException $e) {
    error_log("Database validation error: " . $e->getMessage());
    $error = "Database error. Please try again later.";
}

// Fetch the latest assessment if it exists (to persist results after login)
if (empty($assessmentResults)) {
    try {
        $stmt = $conn->prepare("SELECT * FROM assessments WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 1");
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->execute();
        $latestAssessment = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($latestAssessment) {
            $assessmentResults = [
                'score' => round($latestAssessment['score']),
                'percentage' => $latestAssessment['score'],
                'result' => $latestAssessment['result_category'],
                'advice' => $latestAssessment['advice'],
                'color' => $latestAssessment['score'] >= 80 ? '#10B981' : ($latestAssessment['score'] >= 60 ? '#3B82F6' : ($latestAssessment['score'] >= 40 ? '#F59E0B' : '#EF4444')),
                'sleep' => $latestAssessment['sleep_quality'],
                'mood' => $latestAssessment['mood'],
                'stress' => $latestAssessment['stress_level'],
                'social' => $latestAssessment['social_interaction'],
                'appetite' => $latestAssessment['appetite'],
                'timestamp' => $latestAssessment['created_at']
            ];
            $_SESSION['last_assessment'] = $assessmentResults; // Store in session for consistency
        }
    } catch (PDOException $e) {
        error_log("Error fetching latest assessment: " . $e->getMessage());
    }
}

// Process new assessment if submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_assessment'])) {
    // Validate inputs
    $inputs = ['sleep', 'mood', 'stress', 'social', 'appetite'];
    $data = [];
    foreach ($inputs as $input) {
        if (!isset($_POST[$input]) || !is_numeric($_POST[$input]) || $_POST[$input] < 1 || $_POST[$input] > 10) {
            $error = "Invalid input for $input. Please use a value between 1 and 10.";
            break;
        }
        $data[$input] = intval($_POST[$input]);
    }

    if (empty($error)) {
        $sleep = $data['sleep'];
        $mood = $data['mood'];
        $stress = $data['stress'];
        $social = $data['social'];
        $appetite = $data['appetite'];

        // Calculate weighted score (0-100 scale)
        $score = (
            ($sleep * 0.25) +        // Sleep quality 25% weight
            ($mood * 0.25) +         // Mood 25% weight
            ((10 - $stress) * 0.20) + // Reverse stress scale (20% weight)
            ($social * 0.15) +       // Social 15% weight
            ($appetite * 0.15)       // Appetite 15% weight
        ) * 10; // Ensures 0-100 scale

        // Determine result category
        if ($score >= 80) {
            $result = "Excellent Mental Health";
            $advice = "You're doing great! Keep up your healthy habits and maintain your current lifestyle.";
            $color = "#10B981"; // green
        } elseif ($score >= 60) {
            $result = "Good Mental Health";
            $advice = "You're generally doing well, but there might be some areas to improve. Consider small lifestyle adjustments.";
            $color = "#3B82F6"; // blue
        } elseif ($score >= 40) {
            $result = "Moderate Stress";
            $advice = "You're experiencing some stress. Consider practicing mindfulness, improving sleep habits, or talking to someone.";
            $color = "#F59E0B"; // yellow
        } else {
            $result = "High Stress Level";
            $advice = "Your responses indicate significant stress. Please consider reaching out to a mental health professional for support.";
            $color = "#EF4444"; // red
        }

        // Store results in database
        try {
            $stmt = $conn->prepare("INSERT INTO assessments 
                                  (user_id, sleep_quality, mood, stress_level, social_interaction, appetite, score, result_category, advice, created_at) 
                                  VALUES 
                                  (:user_id, :sleep, :mood, :stress, :social, :appetite, :score, :result, :advice, NOW())");
            
            $stmt->bindParam(':user_id', $_SESSION['user_id']);
            $stmt->bindParam(':sleep', $sleep);
            $stmt->bindParam(':mood', $mood);
            $stmt->bindParam(':stress', $stress);
            $stmt->bindParam(':social', $social);
            $stmt->bindParam(':appetite', $appetite);
            $stmt->bindParam(':score', $score);
            $stmt->bindParam(':result', $result);
            $stmt->bindParam(':advice', $advice);

            $stmt->execute();

            // Update session with new results
            $assessmentResults = [
                'score' => round($score),
                'percentage' => $score,
                'result' => $result,
                'advice' => $advice,
                'color' => $color,
                'sleep' => $sleep,
                'mood' => $mood,
                'stress' => $stress,
                'social' => $social,
                'appetite' => $appetite,
                'timestamp' => date('Y-m-d H:i:s')
            ];
            $_SESSION['last_assessment'] = $assessmentResults;

        } catch (PDOException $e) {
            error_log("Error saving assessment: " . $e->getMessage());
            $error = "Failed to save assessment. Please try again.";
        }
    }
}

// Get user's last 3 assessments for history (excluding the latest if just submitted)
$assessmentHistory = [];
try {
    $stmt = $conn->prepare("SELECT * FROM assessments 
                           WHERE user_id = :user_id 
                           ORDER BY created_at DESC 
                           LIMIT 3");
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $assessmentHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching assessment history: " . $e->getMessage());
    // Silently fail - empty history will be displayed
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Mental Health Dashboard</title>

    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-10px)' }
                        },
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' }
                        },
                        slideUp: {
                            '0%': { transform: 'translateY(20px)', opacity: '0' },
                            '100%': { transform: 'translateY(0)', opacity: '1' }
                        },
                        pulse: {
                            '0%, 100%': { transform: 'scale(1)' },
                            '50%': { transform: 'scale(1.05)' }
                        }
                    },
                    animation: {
                        float: 'float 3s ease-in-out infinite',
                        fadeIn: 'fadeIn 0.5s ease-out',
                        slideUp: 'slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards',
                        pulse: 'pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite'
                    }
                }
            }
        };
    </script>

    <style>
        .dashboard-card {
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }
        
        .dashboard-card.visible {
            opacity: 1;
            transform: translateY(0);
        }
        
        .action-card {
            transition: all 0.3s ease;
        }
        
        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .action-btn {
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(245, 158, 11, 0.3);
        }
        
        .action-btn::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.3), transparent);
            transform: translateX(-100%);
        }
        
        .action-btn:hover::after {
            animation: shine 1.5s ease infinite;
        }
        
        @keyframes shine {
            100% { transform: translateX(100%); }
        }
        
        .progress-ring__circle {
            transition: stroke-dashoffset 0.8s ease;
            transform: rotate(-90deg);
            transform-origin: 50% 50%;
        }
    </style>
</head>

<body class="bg-mental-light dark:bg-gray-900 text-gray-800 dark:text-gray-100 min-h-screen transition-colors">

    <!-- Dark Mode Toggle Button -->
    <button id="theme-toggle" class="fixed top-20 right-6 z-50 bg-white dark:bg-gray-700 p-2 rounded-full shadow-lg hover:scale-110 transition"
            aria-label="Toggle dark mode">
        <svg id="sun-icon" class="w-6 h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.36 6.36l-.7-.7M6.34 6.34l-.7-.7m12.02 0l.7.7M6.34 17.66l.7.7M12 8a4 4 0 100 8 4 4 0 000-8z" />
        </svg>
        <svg id="moon-icon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12.79A9 9 0 1111.21 3a7 7 0 0010.59 9.79z" />
        </svg>
    </button>

    <!-- Navigation -->
    <nav class="bg-black text-white w-full p-4 flex justify-between items-center shadow-md">
        <div class="flex items-center space-x-2">
            <svg class="w-8 h-8 text-mental-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
            </svg>
            <h1 class="text-xl font-bold">MindCare</h1>
        </div>
        <div class="flex items-center space-x-4">
            <a href="profile.php" class="flex items-center space-x-1 hover:text-mental-primary transition">
                <span>Hi, <?php echo htmlspecialchars($_SESSION['user_email'] ?? 'User'); ?></span>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
            </a>
            <a href="logout.php" class="text-mental-primary hover:underline">Logout</a>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo $error; ?></span>
            </div>
        <?php endif; ?>

        <!-- Welcome Section -->
        <section class="mb-10 dashboard-card">
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-lg">
                <h2 class="text-2xl md:text-3xl font-bold mb-2">Welcome Back!</h2>
                <p class="text-gray-600 dark:text-gray-300 mb-4">Here's your mental health overview</p>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Mood Tracker -->
                    <div class="bg-mental-light dark:bg-gray-700 p-4 rounded-xl flex items-center">
                        <div class="w-16 h-16 bg-white dark:bg-gray-600 rounded-full flex items-center justify-center mr-4">
                            <svg class="w-8 h-8 text-mental-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold">Today's Mood</h3>
                            <p class="text-mental-primary font-medium">
                                <?php 
                                if (!empty($assessmentResults)) {
                                    if ($assessmentResults['mood'] >= 8) echo "Happy";
                                    elseif ($assessmentResults['mood'] >= 5) echo "Neutral";
                                    else echo "Sad";
                                } else {
                                    echo "Not assessed";
                                }
                                ?>
                            </p>
                            <button class="text-sm text-mental-dark dark:text-mental-primary mt-1 hover:underline" onclick="toggleAssessmentModal()">Update Mood</button>
                        </div>
                    </div>
                    
                    <!-- Progress -->
                    <div class="bg-mental-light dark:bg-gray-700 p-4 rounded-xl flex items-center">
                        <div class="relative w-16 h-16 mr-4">
                            <svg class="w-16 h-16" viewBox="0 0 36 36">
                                <path d="M18 2.0845
                                    a 15.9155 15.9155 0 0 1 0 31.831
                                    a 15.9155 15.9155 0 0 1 0 -31.831"
                                    fill="none"
                                    stroke="#E5E7EB"
                                    stroke-width="3"
                                />
                                <path class="progress-ring__circle"
                                    d="M18 2.0845
                                    a 15.9155 15.9155 0 0 1 0 31.831
                                    a 15.9155 15.9155 0 0 1 0 -31.831"
                                    fill="none"
                                    stroke="<?php echo !empty($assessmentResults) ? $assessmentResults['color'] : '#F59E0B'; ?>"
                                    stroke-width="3"
                                    stroke-dasharray="100, 100"
                                    stroke-dashoffset="<?php echo !empty($assessmentResults) ? 100 - $assessmentResults['percentage'] : 35; ?>"
                                />
                            </svg>
                            <span class="absolute inset-0 flex items-center justify-center text-sm font-bold">
                                <?php echo !empty($assessmentResults) ? round($assessmentResults['percentage']) : '65'; ?>%
                            </span>
                        </div>
                        <div>
                            <h3 class="font-semibold">Mental Health Score</h3>
                            <p class="text-gray-600 dark:text-gray-300 text-sm">
                                <?php 
                                if (!empty($assessmentResults)) {
                                    echo "Score: {$assessmentResults['score']}/100";
                                } else {
                                    echo "Complete assessment to see";
                                }
                                ?>
                            </p>
                        </div>
                    </div>
                    
                    <!-- Activity -->
                    <div class="bg-mental-light dark:bg-gray-700 p-4 rounded-xl flex items-center">
                        <div class="w-16 h-16 bg-white dark:bg-gray-600 rounded-full flex items-center justify-center mr-4">
                            <svg class="w-8 h-8 text-mental-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold">Recent Activity</h3>
                            <p class="text-gray-600 dark:text-gray-300 text-sm">
                                <?php 
                                if (!empty($assessmentResults)) {
                                    echo "Completed assessment";
                                } else {
                                    echo "No recent activity";
                                }
                                ?>
                            </p>
                            <button class="text-sm text-mental-dark dark:text-mental-primary mt-1 hover:underline" onclick="toggleAssessmentModal()">Take Assessment</button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <?php if (!empty($assessmentResults)): ?>
        <!-- Assessment Results Section -->
        <section class="mb-10 dashboard-card">
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-lg">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-bold">Your Mental Health Assessment</h2>
                    <span class="text-sm text-gray-500"><?php echo date('M j, Y g:i A', strtotime($assessmentResults['timestamp'])); ?></span>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Results Overview -->
                    <div class="flex flex-col items-center">
                        <div class="w-48 h-48 relative mb-4">
                            <canvas id="resultChart"></canvas>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div class="text-center">
                                    <span class="text-3xl font-bold" style="color: <?php echo $assessmentResults['color']; ?>"><?php echo $assessmentResults['score']; ?></span>
                                    <span class="block text-sm text-gray-600">out of 100</span>
                                </div>
                            </div>
                        </div>
                        <div class="text-center">
                            <h3 class="text-xl font-semibold mb-2" style="color: <?php echo $assessmentResults['color']; ?>"><?php echo $assessmentResults['result']; ?></h3>
                            <p class="text-gray-600 dark:text-gray-300"><?php echo $assessmentResults['advice']; ?></p>
                        </div>
                    </div>
                    
                    <!-- Detailed Breakdown -->
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Detailed Breakdown</h3>
                        <div class="space-y-4">
                            <div>
                                <div class="flex justify-between mb-1">
                                    <span class="text-gray-700 dark:text-gray-300">Sleep Quality</span>
                                    <span class="font-medium"><?php echo $assessmentResults['sleep']; ?>/10</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                                    <div class="bg-blue-600 h-2.5 rounded-full" style="width: <?php echo $assessmentResults['sleep'] * 10; ?>%"></div>
                                </div>
                            </div>
                            
                            <div>
                                <div class="flex justify-between mb-1">
                                    <span class="text-gray-700 dark:text-gray-300">Mood</span>
                                    <span class="font-medium"><?php echo $assessmentResults['mood']; ?>/10</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                                    <div class="bg-purple-600 h-2.5 rounded-full" style="width: <?php echo $assessmentResults['mood'] * 10; ?>%"></div>
                                </div>
                            </div>
                            
                            <div>
                                <div class="flex justify-between mb-1">
                                    <span class="text-gray-700 dark:text-gray-300">Stress Level</span>
                                    <span class="font-medium"><?php echo $assessmentResults['stress']; ?>/10</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                                    <div class="bg-red-600 h-2.5 rounded-full" style="width: <?php echo $assessmentResults['stress'] * 10; ?>%"></div>
                                </div>
                            </div>
                            
                            <div>
                                <div class="flex justify-between mb-1">
                                    <span class="text-gray-700 dark:text-gray-300">Social Interaction</span>
                                    <span class="font-medium"><?php echo $assessmentResults['social']; ?>/10</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                                    <div class="bg-green-600 h-2.5 rounded-full" style="width: <?php echo $assessmentResults['social'] * 10; ?>%"></div>
                                </div>
                            </div>
                            
                            <div>
                                <div class="flex justify-between mb-1">
                                    <span class="text-gray-700 dark:text-gray-300">Appetite</span>
                                    <span class="font-medium"><?php echo $assessmentResults['appetite']; ?>/10</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                                    <div class="bg-yellow-600 h-2.5 rounded-full" style="width: <?php echo $assessmentResults['appetite'] * 10; ?>%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- Quick Actions -->
        <section class="mb-10 dashboard-card">
            <h2 class="text-2xl font-bold mb-6">Quick Actions</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <button onclick="toggleAssessmentModal()" class="action-card bg-white dark:bg-gray-800 rounded-xl p-6 shadow-md text-center group cursor-pointer">
                    <div class="w-16 h-16 bg-mental-light dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-mental-primary group-hover:text-white transition">
                        <svg class="w-8 h-8 text-mental-primary group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                    <h3 class="font-semibold mb-1">Self-Assessment</h3>
                    <p class="text-gray-600 dark:text-gray-300 text-sm">Check your current state</p>
                </button>
                
                <a href="resources.php" class="action-card bg-white dark:bg-gray-800 rounded-xl p-6 shadow-md text-center group">
                    <div class="w-16 h-16 bg-mental-light dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-mental-primary group-hover:text-white transition">
                        <svg class="w-8 h-8 text-mental-primary group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <h3 class="font-semibold mb-1">Resources</h3>
                    <p class="text-gray-600 dark:text-gray-300 text-sm">Helpful materials</p>
                </a>
                
                <a href="resources.php" class="action-card bg-white dark:bg-gray-800 rounded-xl p-6 shadow-md text-center group">
                    <div class="w-16 h-16 bg-mental-light dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-mental-primary group-hover:text-white transition">
                        <svg class="w-8 h-8 text-mental-primary group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                    </div>
                    <h3 class="font-semibold mb-1">Exercises</h3>
                    <p class="text-gray-600 dark:text-gray-300 text-sm">Practice techniques</p>
                </a>
                
                <a href="resources.php" class="action-card bg-white dark:bg-gray-800 rounded-xl p-6 shadow-md text-center group">
                    <div class="w-16 h-16 bg-mental-light dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-mental-primary group-hover:text-white transition">
                        <svg class="w-8 h-8 text-mental-primary group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <h3 class="font-semibold mb-1">Community</h3>
                    <p class="text-gray-600 dark:text-gray-300 text-sm">Connect with others</p>
                </a>
            </div>
        </section>

        <!-- Recent Activity -->
        <section class="dashboard-card">
            <h2 class="text-2xl font-bold mb-6">Recent Activity</h2>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-6">
                    <?php if (!empty($assessmentResults)): ?>
                    <div class="flex items-start">
                        <div class="bg-mental-light dark:bg-gray-700 p-3 rounded-full mr-4">
                            <svg class="w-6 h-6 text-mental-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold">Completed Assessment</h3>
                            <p class="text-gray-600 dark:text-gray-300 text-sm"><?php echo date('M j, Y g:i A', strtotime($assessmentResults['timestamp'])); ?></p>
                            <p class="text-sm mt-1">Score: <?php echo $assessmentResults['score']; ?>/100 - <?php echo $assessmentResults['result']; ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php foreach ($assessmentHistory as $history): ?>
                    <div class="flex items-start">
                        <div class="bg-mental-light dark:bg-gray-700 p-3 rounded-full mr-4">
                            <svg class="w-6 h-6 text-mental-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold">Completed Assessment</h3>
                            <p class="text-gray-600 dark:text-gray-300 text-sm"><?php echo date('M j, Y g:i A', strtotime($history['created_at'])); ?></p>
                            <p class="text-sm mt-1">Score: <?php echo $history['score']; ?>/100 - <?php echo $history['result_category']; ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-6 py-3 text-right">
                    <a href="activity.php" class="text-mental-primary hover:underline">View All Activity</a>
                </div>
            </div>
        </section>
    </main>

    <!-- Assessment Modal -->
    <div id="assessmentModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-bold">Mental Health Assessment</h2>
                <button onclick="toggleAssessmentModal()" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form method="post" class="space-y-6">
                <div>
                    <label for="sleep" class="text-lg font-medium text-gray-800 dark:text-gray-200 mb-2">1. How would you rate your sleep quality this week?</label>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">(1 = Very poor, 10 = Excellent)</p>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">1</span>
                        <input type="range" id="sleep" name="sleep" min="1" max="10" value="5" class="w-3/4 mx-2" oninput="this.nextElementSibling.value = this.value">
                        <output class="text-sm font-medium w-8 text-center">5</output>
                        <span class="text-sm text-gray-500">10</span>
                    </div>
                </div>
                
                <div>
                    <label for="mood" class="text-lg font-medium text-gray-800 dark:text-gray-200 mb-2">2. How would you rate your overall mood this week?</label>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">(1 = Very poor, 10 = Excellent)</p>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">1</span>
                        <input type="range" id="mood" name="mood" min="1" max="10" value="5" class="w-3/4 mx-2" oninput="this.nextElementSibling.value = this.value">
                        <output class="text-sm font-medium w-8 text-center">5</output>
                        <span class="text-sm text-gray-500">10</span>
                    </div>
                </div>
                
                <div>
                    <label for="stress" class="text-lg font-medium text-gray-800 dark:text-gray-200 mb-2">3. How would you rate your stress level this week?</label>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">(1 = No stress, 10 = Extremely stressed)</p>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">1</span>
                        <input type="range" id="stress" name="stress" min="1" max="10" value="5" class="w-3/4 mx-2" oninput="this.nextElementSibling.value = this.value">
                        <output class="text-sm font-medium w-8 text-center">5</output>
                        <span class="text-sm text-gray-500">10</span>
                    </div>
                </div>
                
                <div>
                    <label for="social" class="text-lg font-medium text-gray-800 dark:text-gray-200 mb-2">4. How satisfied are you with your social interactions this week?</label>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">(1 = Very unsatisfied, 10 = Very satisfied)</p>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">1</span>
                        <input type="range" id="social" name="social" min="1" max="10" value="5" class="w-3/4 mx-2" oninput="this.nextElementSibling.value = this.value">
                        <output class="text-sm font-medium w-8 text-center">5</output>
                        <span class="text-sm text-gray-500">10</span>
                    </div>
                </div>
                
                <div>
                    <label for="appetite" class="text-lg font-medium text-gray-800 dark:text-gray-200 mb-2">5. How would you rate your appetite/eating habits this week?</label>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">(1 = Very poor, 10 = Excellent)</p>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">1</span>
                        <input type="range" id="appetite" name="appetite" min="1" max="10" value="5" class="w-3/4 mx-2" oninput="this.nextElementSibling.value = this.value">
                        <output class="text-sm font-medium w-8 text-center">5</output>
                        <span class="text-sm text-gray-500">10</span>
                    </div>
                </div>
                
                <div class="mt-8">
                    <button type="submit" name="submit_assessment" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200">
                        Submit Assessment
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Theme toggle
        const toggleBtn = document.getElementById('theme-toggle');
        const root = document.documentElement;
        const moonIcon = document.getElementById('moon-icon');
        const sunIcon = document.getElementById('sun-icon');

        function initializeTheme() {
            const storedTheme = localStorage.getItem('theme');
            const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (storedTheme) {
                root.classList.toggle('dark', storedTheme === 'dark');
            } else if (systemPrefersDark) {
                root.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            } else {
                root.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            }
            updateIcons();
        }

        function updateIcons(isDark) {
            const isDarkMode = root.classList.contains('dark');
            if (isDarkMode) {
                sunIcon.classList.remove('hidden');
                moonIcon.classList.add('hidden');
            } else {
                sunIcon.classList.add('hidden');
                moonIcon.classList.remove('hidden');
            }
        }

        toggleBtn.addEventListener('click', () => {
            const isDark = root.classList.toggle('dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
            updateIcons(isDark);
        });

        // Initialize theme on load
        initializeTheme();

        // Animate dashboard cards on scroll
        const animateOnScroll = () => {
            const cards = document.querySelectorAll('.dashboard-card');
            cards.forEach((card, index) => {
                const rect = card.getBoundingClientRect();
                if (rect.top < window.innerHeight - 100) {
                    setTimeout(() => {
                        card.classList.add('visible');
                    }, index * 150);
                }
            });
        };

        window.addEventListener('load', animateOnScroll);
        window.addEventListener('scroll', animateOnScroll);

        // Progress ring animation
        document.addEventListener('DOMContentLoaded', () => {
            const circle = document.querySelector('.progress-ring__circle');
            if (circle) {
                const radius = circle.r.baseVal.value;
                const circumference = radius * 2 * Math.PI;
                circle.style.strokeDasharray = `${circumference} ${circumference}`;
                circle.style.strokeDashoffset = circumference;
                const offset = circumference - (parseInt(circle.getAttribute('stroke-dashoffset')) / 100 * circumference);
                setTimeout(() => {
                    circle.style.strokeDashoffset = offset;
                }, 500);
            }
        });

        // Assessment modal toggle
        function toggleAssessmentModal() {
            const modal = document.getElementById('assessmentModal');
            modal.classList.toggle('hidden');
        }

        // Initialize result chart if assessment exists
        <?php if (!empty($assessmentResults)): ?>
        document.addEventListener('DOMContentLoaded', () => {
            const ctx = document.getElementById('resultChart').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    datasets: [{
                        data: [<?php echo $assessmentResults['percentage']; ?>, 100 - <?php echo $assessmentResults['percentage']; ?>],
                        backgroundColor: ['<?php echo $assessmentResults['color']; ?>', '#E5E7EB'],
                        borderWidth: 0,
                        cutout: '75%'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { enabled: false }
                    },
                    rotation: -90,
                    circumference: 180
                }
            });
        });
        <?php endif; ?>
    </script>
</body>
</html>