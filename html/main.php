<?php
$score = 0;
$result = '';
$advice = '';
$name = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars($_POST['name'] ?? '');
    $sleep = intval($_POST['sleep'] ?? 0);
    $mood = intval($_POST['mood'] ?? 0);
    $stress = intval($_POST['stress'] ?? 0);
    $social = intval($_POST['social'] ?? 0);
    $appetite = intval($_POST['appetite'] ?? 0);
    
    // Calculate score (higher is worse)
    $score = (10 - $sleep) + (10 - $mood) + $stress + (10 - $social) + (10 - $appetite);
    $percentage = ($score / 50) * 100;
    
    // Determine result
    if ($score <= 10) {
        $result = "Excellent Mental Health";
        $advice = "You're doing great! Keep up your healthy habits and maintain your current lifestyle.";
        $color = "#10B981"; // green
    } elseif ($score <= 20) {
        $result = "Good Mental Health";
        $advice = "You're generally doing well, but there might be some areas to improve. Consider small lifestyle adjustments.";
        $color = "#3B82F6"; // blue
    } elseif ($score <= 30) {
        $result = "Moderate Stress";
        $advice = "You're experiencing some stress. Consider practicing mindfulness, improving sleep habits, or talking to someone.";
        $color = "#F59E0B"; // yellow
    } else {
        $result = "High Stress Level";
        $advice = "Your responses indicate significant stress. Please consider reaching out to a mental health professional for support.";
        $color = "#EF4444"; // red
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mental Health Calculator</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-md overflow-hidden">
            <!-- Header -->
            <div class="bg-blue-600 py-4 px-6">
                <h1 class="text-2xl font-bold text-white">Mental Health Assessment</h1>
                <p class="text-blue-100">Take a quick assessment of your current mental well-being</p>
            </div>
            
            <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
                <!-- Results Section -->
                <div class="p-6 border-b">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Your Results</h2>
                    <?php if ($name): ?>
                        <p class="mb-2"><span class="font-medium">Name:</span> <?php echo $name; ?></p>
                    <?php endif; ?>
                    
                    <div class="flex flex-col md:flex-row items-center justify-between gap-6 mt-6">
                        <!-- Ring Graph -->
                        <div class="w-64 h-64 relative">
                            <canvas id="resultChart"></canvas>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div class="text-center">
                                    <span class="text-3xl font-bold" style="color: <?php echo $color; ?>"><?php echo $score; ?></span>
                                    <span class="block text-sm text-gray-600">out of 50</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Result Text -->
                        <div class="flex-1">
                            <div class="p-4 rounded-lg" style="background-color: <?php echo $color; ?>20; border-color: <?php echo $color; ?>;">
                                <h3 class="font-bold text-lg" style="color: <?php echo $color; ?>"><?php echo $result; ?></h3>
                                <p class="mt-2 text-gray-700"><?php echo $advice; ?></p>
                            </div>
                            
                            <!-- Breakdown -->
                            <div class="mt-4 space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Sleep Quality</span>
                                    <span class="font-medium"><?php echo $sleep; ?>/10</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Mood</span>
                                    <span class="font-medium"><?php echo $mood; ?>/10</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Stress Level</span>
                                    <span class="font-medium"><?php echo $stress; ?>/10</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Social Interaction</span>
                                    <span class="font-medium"><?php echo $social; ?>/10</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Appetite</span>
                                    <span class="font-medium"><?php echo $appetite; ?>/10</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-6">
                        <a href="mental-health-calculator.php" class="text-blue-600 hover:underline">← Take the assessment again</a>
                    </div>
                </div>
                
                <script>
                    // Chart.js configuration for ring graph
                    const ctx = document.getElementById('resultChart');
                    const percentage = <?php echo $percentage; ?>;
                    const color = '<?php echo $color; ?>';
                    
                    new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            datasets: [{
                                data: [percentage, 100 - percentage],
                                backgroundColor: [color, '#E5E7EB'],
                                borderWidth: 0,
                                cutout: '75%'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    enabled: false
                                }
                            },
                            rotation: -90,
                            circumference: 180
                        }
                    });
                </script>
            <?php else: ?>
                <!-- Assessment Form -->
                <form method="post" class="p-6">
                    <div class="mb-6">
                        <label for="name" class="block text-gray-700 font-medium mb-2">Your Name (optional)</label>
                        <input type="text" id="name" name="name" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div class="space-y-6">
                        <!-- Sleep Quality -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-800 mb-2">1. How would you rate your sleep quality this week?</h3>
                            <p class="text-sm text-gray-600 mb-3">(1 = Very poor, 10 = Excellent)</p>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-500">1</span>
                                <input type="range" name="sleep" min="1" max="10" value="5" class="w-3/4 mx-2" oninput="this.nextElementSibling.value = this.value">
                                <span class="text-sm font-medium w-8 text-center">5</span>
                                <span class="text-sm text-gray-500">10</span>
                            </div>
                        </div>
                        
                        <!-- Mood -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-800 mb-2">2. How would you rate your overall mood this week?</h3>
                            <p class="text-sm text-gray-600 mb-3">(1 = Very poor, 10 = Excellent)</p>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-500">1</span>
                                <input type="range" name="mood" min="1" max="10" value="5" class="w-3/4 mx-2" oninput="this.nextElementSibling.value = this.value">
                                <span class="text-sm font-medium w-8 text-center">5</span>
                                <span class="text-sm text-gray-500">10</span>
                            </div>
                        </div>
                        
                        <!-- Stress Level -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-800 mb-2">3. How would you rate your stress level this week?</h3>
                            <p class="text-sm text-gray-600 mb-3">(1 = No stress, 10 = Extremely stressed)</p>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-500">1</span>
                                <input type="range" name="stress" min="1" max="10" value="5" class="w-3/4 mx-2" oninput="this.nextElementSibling.value = this.value">
                                <span class="text-sm font-medium w-8 text-center">5</span>
                                <span class="text-sm text-gray-500">10</span>
                            </div>
                        </div>
                        
                        <!-- Social Interaction -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-800 mb-2">4. How satisfied are you with your social interactions this week?</h3>
                            <p class="text-sm text-gray-600 mb-3">(1 = Very unsatisfied, 10 = Very satisfied)</p>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-500">1</span>
                                <input type="range" name="social" min="1" max="10" value="5" class="w-3/4 mx-2" oninput="this.nextElementSibling.value = this.value">
                                <span class="text-sm font-medium w-8 text-center">5</span>
                                <span class="text-sm text-gray-500">10</span>
                            </div>
                        </div>
                        
                        <!-- Appetite -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-800 mb-2">5. How would you rate your appetite/eating habits this week?</h3>
                            <p class="text-sm text-gray-600 mb-3">(1 = Very poor, 10 = Excellent)</p>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-500">1</span>
                                <input type="range" name="appetite" min="1" max="10" value="5" class="w-3/4 mx-2" oninput="this.nextElementSibling.value = this.value">
                                <span class="text-sm font-medium w-8 text-center">5</span>
                                <span class="text-sm text-gray-500">10</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-8">
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200">
                            Calculate My Mental Health Score
                        </button>
                    </div>
                </form>
            <?php endif; ?>
            
            <!-- Footer -->
            <div class="bg-gray-50 px-6 py-4 text-center">
                <p class="text-sm text-gray-600">Note: This assessment is not a substitute for professional medical advice. If you're in crisis, please contact a mental health professional.</p>
            </div>
        </div>
    </div>
</body>
</html>