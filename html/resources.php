<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Mental Health Resources</title>

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
          keyframes: {
            slideUp: {
              '0%': { transform: 'translateY(20px)', opacity: '0' },
              '100%': { transform: 'translateY(0)', opacity: '1' }
            },
            fadeIn: {
              '0%': { opacity: '0' },
              '100%': { opacity: '1' }
            },
            pulse: {
              '0%, 100%': { transform: 'scale(1)' },
              '50%': { transform: 'scale(1.05)' }
            }
          },
          animation: {
            slideUp: 'slideUp 0.6s ease-out forwards',
            fadeIn: 'fadeIn 0.5s ease-out',
            pulse: 'pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite'
          }
        }
      }
    };
  </script>

  <!-- Load saved theme early -->
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

  <style>
    .resource-card {
      opacity: 0;
      transform: translateY(20px);
      transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1);
    }
    
    .resource-card.visible {
      opacity: 1;
      transform: translateY(0);
    }
    
    .resource-card:hover {
      transform: translateY(-5px) !important;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }
    
    .action-btn {
      position: relative;
      overflow: hidden;
      transition: all 0.3s ease;
    }
    
    .action-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }
    
    .action-btn::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(45deg, transparent, rgba(255,255,255,0.2), transparent);
      transform: translateX(-100%);
    }
    
    .action-btn:hover::after {
      animation: shine 1.5s ease infinite;
    }
    
    @keyframes shine {
      100% { transform: translateX(100%); }
    }
    
    .modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      z-index: 1000;
    }
    
    .modal-content {
      background: white;
      margin: 15% auto;
      padding: 20px;
      border-radius: 8px;
      width: 70%;
      max-width: 500px;
      position: relative;
      max-height: 70vh; /* Limit modal height to 70% of viewport height */
      overflow-y: auto; /* Enable vertical scrolling if content overflows */
    }
    
    .dark .modal-content {
      background: #1f2937;
      color: #d1d5db; /* Light gray for better contrast in dark mode */
    }
    
    .dark .modal-content h3,
    .dark .modal-content h4,
    .dark .modal-content p,
    .dark .modal-content li {
      color: #d1d5db;
    }
    
    .modal .close {
      position: absolute;
      top: 2px;
      right: 4px;
      font-size: 2xl;
      cursor: pointer;
      color: #4b5563;
    }
    
    .dark .modal .close {
      color: #d1d5db;
    }
  </style>
</head>

<body class="bg-amber-100 dark:bg-gray-900 text-gray-900 dark:text-white flex flex-col items-center min-h-screen transition duration-300 relative">

  <!-- Navigation -->
  <nav class="bg-black text-white w-full p-4 flex justify-between items-center relative">
    <!-- Logo on the Left -->
    <div class="flex items-center space-x-2">
      <svg class="w-10 h-10 text-mental-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
      </svg>
      <span class="text-2xl font-bold text-mental-primary">MindCare</span>
    </div>
    
    <!-- Navigation Links on the Right -->
    <div class="flex items-center space-x-4">
      <a href="dash.php" class="text-amber-200 hover:underline">Dashboard</a>
      <a href="login.php" class="text-amber-200 hover:underline">Logout</a>
    </div>
  </nav>

  <!-- Dark Mode Toggle Button -->
  <button id="theme-toggle" class="absolute top-20 right-6 z-50 text-gray-700 dark:text-gray-200 hover:scale-110 transition">
    <svg id="sun-icon" class="w-6 h-6 hidden" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.36 6.36l-.7-.7M6.34 6.34l-.7-.7m12.02 0l.7.7M6.34 17.66l.7.7M12 8a4 4 0 100 8 4 4 0 000-8z" />
    </svg>
    <svg id="moon-icon" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3a7 7 0 0010.59 9.79z" />
    </svg>
  </button>

  <!-- Main Content -->
  <main class="w-[90%] sm:w-[85%] md:w-[80%] lg:w-[75%] xl:w-[70%] my-10">
    <!-- Resource Categories -->
    <section class="mb-12">
      <h2 class="text-3xl font-bold mb-6 text-center">Mental Health Resources</h2>
      
      <div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-8">
        <button class="category-btn action-btn bg-amber-200 dark:bg-amber-600 text-black dark:text-white py-2 px-4 rounded-md font-medium transition-all" data-category="all">
          All Resources
        </button>
        <button class="category-btn action-btn bg-gray-200 dark:bg-gray-700 py-2 px-4 rounded-md font-medium transition-all" data-category="stress">
          Stress
        </button>
        <button class="category-btn action-btn bg-gray-200 dark:bg-gray-700 py-2 px-4 rounded-md font-medium transition-all" data-category="anxiety">
          Anxiety
        </button>
        <button class="category-btn action-btn bg-gray-200 dark:bg-gray-700 py-2 px-4 rounded-md font-medium transition-all" data-category="depression">
          Depression
        </button>
        <button class="category-btn action-btn bg-gray-200 dark:bg-gray-700 py-2 px-4 rounded-md font-medium transition-all" data-category="exercise">
          Exercise
        </button>
        <button class="category-btn action-btn bg-gray-200 dark:bg-gray-700 py-2 px-4 rounded-md font-medium transition-all" data-category="community">
          Community
        </button>
      </div>
    </section>

    <!-- Resources Grid -->
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6" id="resourcesContainer">
      <!-- Resource cards will be dynamically inserted here -->
      <?php
      $resources = [
        [
          'title' => 'Managing Daily Stress',
          'category' => 'stress',
          'image' => '/Project/img/stress.png',
          'content' => 'Learn effective techniques to manage daily stressors and improve your resilience.',
          'link' => '#',
          'details' => '<h4 class="font-bold mb-2">Stress Management Techniques</h4>
                       <ul class="list-disc pl-5 mb-4">
                         <li>Practice deep breathing exercises for 5 minutes when feeling overwhelmed</li>
                         <li>Prioritize tasks using the Eisenhower Matrix (urgent vs important)</li>
                         <li>Take regular breaks using the 20-20-20 rule (every 20 minutes, look at something 20 feet away for 20 seconds)</li>
                         <li>Maintain a consistent sleep schedule (7-9 hours per night)</li>
                       </ul>
                       <h4 class="font-bold mb-2">Recommended Resources</h4>
                       <ul class="list-disc pl-5">
                         <li>Book: "The Stress Solution" by Dr. Rangan Chatterjee</li>
                         <li>App: Headspace (guided meditations)</li>
                         <li>Website: American Psychological Association Stress Resources</li>
                       </ul>'
        ],
        [
          'title' => 'Understanding Anxiety',
          'category' => 'anxiety',
          'image' => '/Project/img/anxiety.png',
          'content' => 'Recognize anxiety symptoms and discover coping strategies that work for you.',
          'link' => '#',
          'details' => '<h4 class="font-bold mb-2">Anxiety Symptoms</h4>
                       <ul class="list-disc pl-5 mb-4">
                         <li>Excessive worry that\'s difficult to control</li>
                         <li>Restlessness or feeling on edge</li>
                         <li>Muscle tension</li>
                         <li>Difficulty concentrating</li>
                         <li>Sleep disturbances</li>
                       </ul>
                       <h4 class="font-bold mb-2">Coping Strategies</h4>
                       <ul class="list-disc pl-5 mb-4">
                         <li>Grounding techniques (5-4-3-2-1 method: Name 5 things you see, 4 you can touch, 3 you hear, 2 you smell, 1 you taste)</li>
                         <li>Progressive muscle relaxation</li>
                         <li>Cognitive Behavioral Therapy techniques</li>
                         <li>Limit caffeine and alcohol intake</li>
                       </ul>
                       <p class="mb-2"><strong>When to seek help:</strong> If anxiety interferes with daily life for more than 2 weeks.</p>'
        ],
        [
          'title' => 'Coping with Depression',
          'category' => 'depression',
          'image' => '/Project/img/depression.png',
          'content' => 'Evidence-based approaches to manage depressive symptoms and find support.',
          'link' => '#',
          'details' => '<h4 class="font-bold mb-2">Depression Symptoms</h4>
                       <ul class="list-disc pl-5 mb-4">
                         <li>Persistent sad, anxious, or "empty" mood</li>
                         <li>Loss of interest in activities once enjoyed</li>
                         <li>Significant weight changes</li>
                         <li>Difficulty sleeping or oversleeping</li>
                         <li>Fatigue or loss of energy</li>
                       </ul>
                       <h4 class="font-bold mb-2">Self-Care Strategies</h4>
                       <ul class="list-disc pl-5 mb-4">
                         <li>Establish a daily routine</li>
                         <li>Set small, achievable goals</li>
                         <li>Practice gratitude journaling</li>
                         <li>Engage in physical activity (even short walks help)</li>
                         <li>Connect with supportive people</li>
                       </ul>
                       <h4 class="font-bold mb-2">Professional Help</h4>
                       <p>Consider reaching out to a mental health professional if symptoms persist for more than 2 weeks.</p>'
        ],
        [
          'title' => 'Mindfulness Meditation',
          'category' => 'stress',
          'image' => '/Project/img/mindfulness.png',
          'content' => 'Guided mindfulness exercises to help center your thoughts and reduce stress.',
          'link' => '#',
          'details' => '<h4 class="font-bold mb-2">Basic Mindfulness Practice</h4>
                       <ol class="list-decimal pl-5 mb-4">
                         <li>Find a quiet, comfortable place to sit</li>
                         <li>Set a timer for 5-10 minutes</li>
                         <li>Focus on your breath (notice the sensation of air entering and leaving your nostrils)</li>
                         <li>When your mind wanders (it will), gently bring attention back to your breath</li>
                         <li>Practice daily for best results</li>
                       </ol>
                       <h4 class="font-bold mb-2">Benefits of Mindfulness</h4>
                       <ul class="list-disc pl-5 mb-4">
                         <li>Reduces stress and anxiety</li>
                         <li>Improves focus and concentration</li>
                         <li>Enhances emotional regulation</li>
                         <li>Increases self-awareness</li>
                       </ul>
                       <h4 class="font-bold mb-2">Recommended Apps</h4>
                       <ul class="list-disc pl-5">
                         <li>Headspace</li>
                         <li>Calm</li>
                         <li>Insight Timer</li>
                       </ul>'
        ],
        [
          'title' => 'Sleep Hygiene Tips',
          'category' => 'stress',
          'image' => '/Project/img/sleep.png',
          'content' => 'Improve your sleep quality with these science-backed recommendations.',
          'link' => '#',
          'details' => '<h4 class="font-bold mb-2">Sleep Hygiene Checklist</h4>
                       <ul class="list-disc pl-5 mb-4">
                         <li>Maintain consistent sleep and wake times (even on weekends)</li>
                         <li>Create a relaxing bedtime routine (warm bath, reading, light stretching)</li>
                         <li>Keep bedroom cool (60-67°F or 15-19°C), dark, and quiet</li>
                         <li>Limit screen time 1 hour before bed (blue light disrupts melatonin)</li>
                         <li>Avoid caffeine after 2pm and large meals before bedtime</li>
                       </ul>
                       <h4 class="font-bold mb-2">When You Can\'t Sleep</h4>
                       <ul class="list-disc pl-5 mb-4">
                         <li>Get out of bed after 20 minutes of wakefulness</li>
                         <li>Do a quiet, non-stimulating activity (read a book, listen to soft music)</li>
                         <li>Try progressive muscle relaxation</li>
                         <li>Write down worries in a journal to "park" them for the night</li>
                       </ul>
                       <p class="text-sm italic">If sleep problems persist for more than a month, consider consulting a sleep specialist.</p>'
        ],
        [
          'title' => 'Building Resilience',
          'category' => 'stress',
          'image' => '/Project/img/Resilience.png',
          'content' => 'Develop mental resilience to better handle life\'s challenges and setbacks.',
          'link' => '#',
          'details' => '<h4 class="font-bold mb-2">Resilience Building Strategies</h4>
                       <ul class="list-disc pl-5 mb-4">
                         <li>Practice reframing challenges as opportunities for growth</li>
                         <li>Develop a strong support network</li>
                         <li>Maintain a hopeful outlook (but realistic expectations)</li>
                         <li>Accept that change is part of life</li>
                         <li>Take decisive actions rather than avoiding problems</li>
                       </ul>
                       <h4 class="font-bold mb-2">Daily Resilience Practices</h4>
                       <ul class="list-disc pl-5 mb-4">
                         <li>Gratitude journaling (3 things you\'re grateful for daily)</li>
                         <li>Mindfulness meditation (even 5 minutes helps)</li>
                         <li>Regular physical activity</li>
                         <li>Setting and working toward realistic goals</li>
                       </ul>
                       <h4 class="font-bold mb-2">Recommended Reading</h4>
                       <ul class="list-disc pl-5">
                         <li>"Option B" by Sheryl Sandberg and Adam Grant</li>
                         <li>"The Resilience Factor" by Karen Reivich and Andrew Shatté</li>
                       </ul>'
        ],
        [
          'title' => 'Yoga for Mental Health',
          'category' => 'exercise',
          'image' => '/Project/img/yoga.png',
          'content' => 'Explore yoga poses to reduce stress and improve mental clarity.',
          'link' => '#',
          'details' => '<h4 class="font-bold mb-2">Beginner Yoga Sequence for Stress Relief</h4>
                       <ol class="list-decimal pl-5 mb-4">
                         <li><strong>Child\'s Pose (Balasana)</strong> - 1 minute</li>
                         <li><strong>Cat-Cow Stretch</strong> - 5-8 breaths</li>
                         <li><strong>Downward-Facing Dog (Adho Mukha Svanasana)</strong> - 30 seconds to 1 minute</li>
                         <li><strong>Standing Forward Bend (Uttanasana)</strong> - 30 seconds</li>
                         <li><strong>Legs-Up-the-Wall Pose (Viparita Karani)</strong> - 5-10 minutes</li>
                       </ol>
                       <h4 class="font-bold mb-2">Benefits of Yoga for Mental Health</h4>
                       <ul class="list-disc pl-5 mb-4">
                         <li>Reduces cortisol (stress hormone) levels</li>
                         <li>Increases GABA (calming neurotransmitter) levels</li>
                         <li>Improves mood and decreases anxiety</li>
                         <li>Enhances body awareness and mindfulness</li>
                       </ul>
                       <h4 class="font-bold mb-2">Online Resources</h4>
                       <ul class="list-disc pl-5">
                         <li>Yoga with Adriene (YouTube)</li>
                         <li>Down Dog Yoga App</li>
                         <li>DoYogaWithMe.com (free classes)</li>
                       </ul>'
        ],
        [
          'title' => 'Join a Support Group',
          'category' => 'community',
          'image' => '/Project/img/support.png',
          'content' => 'Connect with others for emotional support and shared experiences.',
          'link' => '#',
          'details' => '<h4 class="font-bold mb-2">Benefits of Support Groups</h4>
                       <ul class="list-disc pl-5 mb-4">
                         <li>Reduces feelings of isolation</li>
                         <li>Provides practical advice and coping strategies</li>
                         <li>Offers different perspectives on challenges</li>
                         <li>Creates a sense of belonging and community</li>
                       </ul>
                       <h4 class="font-bold mb-2">Finding the Right Group</h4>
                       <ul class="list-disc pl-5 mb-4">
                         <li>Consider whether you prefer in-person or online meetings</li>
                         <li>Look for groups focused on your specific needs (anxiety, depression, grief, etc.)</li>
                         <li>Try a few different groups to find the best fit</li>
                         <li>Check with local hospitals, community centers, or places of worship</li>
                       </ul>
                       <h4 class="font-bold mb-2">Online Support Options</h4>
                       <ul class="list-disc pl-5">
                         <li>NAMI Connection Recovery Support Groups</li>
                         <li>Anxiety and Depression Association of America online groups</li>
                         <li>7 Cups (online peer support)</li>
                         <li>Reddit mental health communities (r/mentalhealth, r/depression, etc.)</li>
                       </ul>'
        ]
      ];

      foreach ($resources as $index => $resource) {
        echo "
        <div class='resource-card bg-white dark:bg-gray-800 rounded-lg overflow-hidden shadow-md category-{$resource['category']}' data-index='{$index}'>
          <img src='{$resource['image']}' alt='{$resource['title']}' class='w-full h-48 object-cover'>
          <div class='p-4'>
            <h3 class='text-xl font-semibold mb-2'>{$resource['title']}</h3>
            <p class='text-gray-600 dark:text-gray-300 mb-4'>{$resource['content']}</p>
            <a href='#' class='action-btn view-resource inline-block bg-amber-200 dark:bg-amber-600 text-black dark:text-white py-2 px-4 rounded-md font-medium'>
              View Resources
            </a>
          </div>
        </div>";
      }
      ?>
    </div>
  </main>

  <!-- Modal for Resource Details -->
  <div id="resourceModal" class="modal">
    <div class="modal-content dark:bg-gray-800 dark:text-white">
      <span class="close absolute top-2 right-4 text-2xl cursor-pointer">×</span>
      <h3 id="modalTitle" class="text-2xl font-bold mb-4"></h3>
      <div id="modalDetails" class="text-gray-700 dark:text-gray-300 mb-4"></div>
      <button id="closeModal" class="bg-amber-200 dark:bg-amber-600 text-black dark:text-white py-2 px-4 rounded-md">Close</button>
    </div>
  </div>

  <script>
    const toggleBtn = document.getElementById('theme-toggle');
    const root = document.documentElement;
    const moonIcon = document.getElementById('moon-icon');
    const sunIcon = document.getElementById('sun-icon');
    const resourceModal = document.getElementById('resourceModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalDetails = document.getElementById('modalDetails');
    const closeModalBtn = document.getElementById('closeModal');
    const modalCloseSpan = document.querySelector('.modal .close');

    function updateIcons(isDark) {
      if (isDark) {
        sunIcon.classList.remove('hidden');
        moonIcon.classList.add('hidden');
      } else {
        sunIcon.classList.add('hidden');
        moonIcon.classList.remove('hidden');
      }
    }

    const isDarkInitial =
      localStorage.theme === 'dark' ||
      (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches);
    updateIcons(isDarkInitial);

    toggleBtn.addEventListener('click', () => {
      const isDarkNow = root.classList.toggle('dark');
      localStorage.theme = isDarkNow ? 'dark' : 'light';
      updateIcons(isDarkNow);
    });

    // Resource card animations
    const resourceCards = document.querySelectorAll('.resource-card');
    const categoryButtons = document.querySelectorAll('.category-btn');

    function animateCards() {
      resourceCards.forEach((card, index) => {
        const rect = card.getBoundingClientRect();
        const isVisible = rect.top < window.innerHeight && rect.bottom >= 0;
        
        if (isVisible) {
          setTimeout(() => {
            card.classList.add('visible');
          }, index * 100);
        }
      });
    }

    // Filter resources by category
    function filterResources(category) {
      resourceCards.forEach(card => {
        if (category === 'all' || card.classList.contains(`category-${category}`)) {
          card.style.display = 'block';
        } else {
          card.style.display = 'none';
        }
      });
      
      // Reset and re-animate visible cards
      resourceCards.forEach(card => {
        if (card.style.display === 'block') {
          card.classList.remove('visible');
          setTimeout(() => {
            const rect = card.getBoundingClientRect();
            if (rect.top < window.innerHeight && rect.bottom >= 0) {
              card.classList.add('visible');
            }
          }, 100);
        }
      });
    }

    // Set up category buttons
    categoryButtons.forEach(button => {
      button.addEventListener('click', () => {
        // Update active button styling
        categoryButtons.forEach(btn => {
          btn.classList.remove('bg-amber-200', 'dark:bg-amber-600', 'text-black', 'dark:text-white');
          btn.classList.add('bg-gray-200', 'dark:bg-gray-700');
        });
        
        button.classList.remove('bg-gray-200', 'dark:bg-gray-700');
        button.classList.add('bg-amber-200', 'dark:bg-amber-600', 'text-black', 'dark:text-white');
        
        // Filter resources
        filterResources(button.dataset.category);
      });
    });

    // View Resource Details
    const viewResourceButtons = document.querySelectorAll('.view-resource');
    viewResourceButtons.forEach(button => {
      button.addEventListener('click', (e) => {
        e.preventDefault();
        const card = button.closest('.resource-card');
        if (!card) return;

        const title = card.querySelector('h3').textContent;
        const index = card.getAttribute('data-index');
        
        // Get details from PHP array
        const resources = <?php echo json_encode($resources); ?>;
        const details = resources[index]?.details || 'No details available';

        modalTitle.textContent = title;
        modalDetails.innerHTML = details;
        resourceModal.style.display = 'flex';
      });
    });

    // Close modal
    function closeModal() {
      resourceModal.style.display = 'none';
    }

    if (closeModalBtn) closeModalBtn.addEventListener('click', closeModal);
    if (modalCloseSpan) modalCloseSpan.addEventListener('click', closeModal);

    // Close modal when clicking outside
    if (resourceModal) {
      resourceModal.addEventListener('click', (e) => {
        if (e.target === resourceModal) {
          closeModal();
        }
      });
    }

    // Initial animation on load
    window.addEventListener('load', () => {
      animateCards();
    });
    
    // Animate on scroll
    window.addEventListener('scroll', () => {
      animateCards();
    });
  </script>

</body>
</html>