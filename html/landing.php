<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mind Care</title>
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
                        floating: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-10px)' }
                        },
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' }
                        },
                        slideIn: {
                            '0%': { transform: 'translateY(30px)', opacity: '0' },
                            '100%': { transform: 'translateY(0)', opacity: '1' }
                        }
                    },
                    animation: {
                        floating: 'floating 3s ease-in-out infinite',
                        fade: 'fadeIn 0.3s ease-out',
                        slideIn: 'slideIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards'
                    }
                }
            }
        };
    </script>
    <style>
        .animate-card {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.6s ease-out, transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }
        
        .animate-card.animated {
            opacity: 1;
            transform: translateY(0);
        }
        
        .animate-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="bg-amber-100 dark:bg-black text-gray-900 dark:text-white transition-colors min-h-screen">
    <!-- Navbar -->
    <nav class="w-full bg-white dark:bg-gray-800 shadow-md fixed top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 py-3 flex justify-between items-center">
            <a href="/home" class="flex items-center space-x-2">
                <svg class="w-8 h-8 text-mental-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                </svg>
                <span class="text-xl font-bold">MindCare</span>
            </a>
            <div class="space-x-4">
                <a href="#home" class="hover:text-blue-500 dark:hover:text-blue-400 relative group">Home<span class="absolute left-0 bottom-0 w-0 h-0.5 bg-blue-500 dark:bg-blue-400 transition-all group-hover:w-full"></span></a>
                <a href="#library" class="hover:text-blue-500 dark:hover:text-blue-400 relative group">Library<span class="absolute left-0 bottom-0 w-0 h-0.5 bg-blue-500 dark:bg-blue-400 transition-all group-hover:w-full"></span></a>
                <a href="/Project/html/login.php" class="hover:text-blue-500 dark:hover:text-blue-400 relative group">Login<span class="absolute left-0 bottom-0 w-0 h-0.5 bg-blue-500 dark:bg-blue-400 transition-all group-hover:w-full"></span></a>
            </div>
        </div>
    </nav>

    <!-- Theme Toggle -->
    <button id="theme-toggle" class="fixed top-16 right-4 z-40 text-gray-700 dark:text-gray-200 hover:scale-110 transition">
        <svg id="sun-icon" class="w-6 h-6 hidden dark:inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.36 6.36l-.7-.7M6.34 6.34l-.7-.7m12.02 0l.7.7M6.34 17.66l.7.7M12 8a4 4 0 100 8 4 4 0 000-8z"/></svg>
        <svg id="moon-icon" class="w-6 h-6 dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3a7 7 0 0010.59 9.79z"/></svg>
    </button>

    <!-- Hero Section -->
    <main class="pt-20">
        <section id="home" class="bg-white dark:bg-gray-800 w-[90%] max-w-6xl mx-auto my-10 rounded-3xl p-6 md:p-10 shadow-lg">
            <div class="flex flex-col md:flex-row items-center">
                <div class="md:w-1/2 text-center md:text-left mb-6 md:mb-0">
                    <h1 class="text-4xl font-bold">Self-Identifying Mental Health</h1>
                    <p class="text-lg mt-4 text-gray-700 dark:text-gray-300">Assess your mental health and receive guidance for support.</p>
                    <a href="/Project/html/login.php" class="mt-6 inline-block bg-black text-white px-6 py-3 rounded-lg hover:bg-gray-900 dark:hover:bg-gray-700 transition">Get Started</a>
                </div>
                <div class="md:w-1/2 animate-floating">
                    <img class="rounded-2xl" src="/Project/img/firstpage.jpg" alt="Mental Health Illustration">
                </div>
            </div>
        </section>

        <!-- Library Section -->
        <section class="max-w-6xl mx-auto px-4 py-8" id="library">
            <h2 class="text-2xl font-semibold mb-4">Explore Our Resources</h2>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php
                $articles = [
                    ['title' => 'Managing Stress', 'image' => '/Project/img/stress.png', 'content' => 'Learn stress management techniques...'],
                    ['title' => 'Understanding Anxiety', 'image' => '/Project/img/anxiety.png', 'content' => 'About anxiety disorders...'],
                    ['title' => 'Coping with Depression', 'image' => '/Project/img/depression.png', 'content' => 'Depression support...'],
                    ['title' => 'Sleep Hygiene', 'image' => '/Project/img/sleep.png', 'content' => 'Improve your sleep...'],
                    ['title' => 'Mindfulness', 'image' => '/Project/img/mindfulness.png', 'content' => 'Mindfulness techniques...'],
                    ['title' => 'Healthy Relationships', 'image' => '/Project/img/relationships.png', 'content' => 'Building connections...']
                ];
                foreach ($articles as $i => $a) {
                    echo "<div class='bg-white dark:bg-gray-700 rounded-lg p-4 shadow-md animate-card' data-index='$i'>
                            <img src='{$a['image']}' class='rounded h-40 w-full object-cover mb-4' alt='{$a['title']}'>
                            <h3 class='text-xl font-semibold mb-2'>{$a['title']}</h3>
                            <button onclick='openModal($i)' class='text-blue-500 hover:underline'>Read More</button>
                        </div>";
                }
                ?>
            </div>
        </section>

        <!-- Experts Section -->
        <section class="max-w-6xl mx-auto px-4 py-8">
            <h2 class="text-2xl font-semibold mb-4">Meet Our Experts</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <div class="bg-white dark:bg-gray-700 rounded-lg p-4 shadow-md animate-card">
                    <img src="/Project/img/expert1.png" alt="Expert 1" class="rounded h-30 w-full object-cover mb-4">
                    <h3 class="text-xl font-semibold">Dr. John Doe</h3>
                    <p class="text-gray-700 dark:text-gray-300">Psychologist, specializing in anxiety disorders</p>
                </div>
                <div class="bg-white dark:bg-gray-700 rounded-lg p-4 shadow-md animate-card">
                    <img src="/Project/img/expert2.png" alt="Expert 2" class="rounded h-30 w-full object-cover mb-4">
                    <h3 class="text-xl font-semibold">Dr. Jane Smith</h3>
                    <p class="text-gray-700 dark:text-gray-300">Therapist, specializing in depression and stress management</p>
                </div>
                <div class="bg-white dark:bg-gray-700 rounded-lg p-4 shadow-md animate-card">
                    <img src="/Project/img/expert3.png" alt="Expert 3" class="rounded h-30 w-full object-cover mb-4">
                    <h3 class="text-xl font-semibold">Dr. Emily White</h3>
                    <p class="text-gray-700 dark:text-gray-300">Clinical Psychologist, specializing in mindfulness therapy</p>
                </div>
                <div class="bg-white dark:bg-gray-700 rounded-lg p-4 shadow-md animate-card">
                    <img src="/Project/img/expert4.png" alt="Expert 4" class="rounded h-30 w-full object-cover mb-4">
                    <h3 class="text-xl font-semibold">Dr. Mark Brown</h3>
                    <p class="text-gray-700 dark:text-gray-300">Counselor, specializing in relationship therapy</p>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-gray-400 mt-12">
        <div class="max-w-7xl mx-auto px-4 py-12 grid md:grid-cols-4 gap-8">
            <div><h3 class="text-xl font-bold mb-4 text-white">About Us</h3><p>Mental health resources and tools.</p></div>
            <div><h3 class="text-xl font-bold mb-4 text-white">Quick Links</h3><ul class="space-y-2"><li><a href="#" class="hover:text-white">Home</a></li><li><a href="#" class="hover:text-white">Resources</a></li></ul></div>
            <div><h3 class="text-xl font-bold mb-4 text-white">Contact</h3><p>support@mentalhealth.com</p></div>
            <div><h3 class="text-xl font-bold mb-4 text-white">Newsletter</h3><form class="flex"><input type="email" placeholder="Email" class="px-3 py-2 bg-gray-700 rounded-l w-full text-white"><button class="bg-blue-600 px-4 py-2 rounded-r text-white">Subscribe</button></form></div>
        </div>
        <div class="border-t border-gray-800 pt-8 text-center text-gray-500">© 2025 Mental Health Resources</div>
    </footer>

    <!-- Modal -->
    <div id="modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-lg w-full">
            <h3 id="modal-title" class="text-2xl font-bold mb-4"></h3>
            <p id="modal-content" class="text-gray-700 dark:text-gray-300"></p>
            <button onclick="closeModal()" class="mt-4 text-blue-500 hover:underline">Close</button>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        // Theme toggle
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
        
        document.getElementById('theme-toggle').addEventListener('click', () => {
            document.documentElement.classList.toggle('dark');
            localStorage.theme = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
        });

        // Modal functions
        const modal = document.getElementById('modal');
        const modalTitle = document.getElementById('modal-title');
        const modalContent = document.getElementById('modal-content');
        const articles = <?php echo json_encode($articles); ?>;
        
        function openModal(index) {
            modalTitle.textContent = articles[index].title;
            modalContent.textContent = articles[index].content;
            modal.classList.remove('hidden');
        }
        
        function closeModal() {
            modal.classList.add('hidden');
        }

        // Scroll animation with Intersection Observer
        const animateCards = document.querySelectorAll('.animate-card');
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animated');
                    // Reset animation when element leaves viewport
                    setTimeout(() => {
                        if (!entry.isIntersecting) {
                            entry.target.classList.remove('animated');
                        }
                    }, 1000);
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -100px 0px'
        });

        // Observe all animate-cards
        animateCards.forEach(card => {
            observer.observe(card);
        });

        // Re-animate when scrolling back up
        window.addEventListener('scroll', () => {
            animateCards.forEach(card => {
                const rect = card.getBoundingClientRect();
                if (rect.top > window.innerHeight || rect.bottom < 0) {
                    card.classList.remove('animated');
                }
            });
        });
    </script>
</body>
</html>