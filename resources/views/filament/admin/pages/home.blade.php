<x-filament-panels::page>



    <style>.card-shadow {
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .card-shadow:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

    </style>

    <main class=" mx-auto px-4 sm:px-6 lg:px-8 ">
        <!-- Welcome Section -->
        <div class="text-center mb-12">
            <h1 class="text-9xl font-bold   mb-4">Welcome {{getEmployee()?->fullName}}</h1>
            <h3 class="text-xl  /80 max-w-2xl mx-auto">
                Start your day with purpose and make every moment count. Your dedication drives our success.
            </h3>
        </div>

        <!-- Dashboard Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 gap-8 mb-12">
            <!-- Quick Actions Card -->
            <div class="bg-white/10 backdrop-blur-md rounded-2xl p-6 card-shadow border border-white/20">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-blue-500/20 rounded-xl flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold ">Quick Actions</h3>
                </div>
                <div class="space-y-3">
                    <a href="https://erp.ariatarget.com/admin/1"
                        class="w-full bg-white/10 hover:bg-white/20  py-3 px-4 rounded-lg transition-all duration-200 text-left">
                        📊 View Dashboard
                    </a>

                </div>
            </div>

            <!-- Today's Info Card -->
            <div class="bg-white/10 backdrop-blur-md rounded-2xl p-6 card-shadow border border-white/20">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-green-500/20 rounded-xl flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold ">Today's Info</h3>
                </div>
                <div class="space-y-3 /80">
                    <div class="flex justify-between">
                        <span>Date:</span>
                        <span id="todayDate" class="font-medium "></span>
                    </div>
                    <div class="flex justify-between">
                        <span>Day:</span>
                        <span id="todayDay" class="font-medium "></span>
                    </div>
                    <div class="flex justify-between">
                        <span>Time:</span>
                        <span id="currentTime" class="font-medium "></span>
                    </div>
                </div>
            </div>

            <!-- Notifications Card -->
            <div class="bg-white/10 backdrop-blur-md rounded-2xl p-6 card-shadow border border-white/20">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-purple-500/20 rounded-xl flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 17h5l-5 5v-5zM4 19h6v-2H4v2zM4 15h8v-2H4v2zM4 11h10V9H4v2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold ">Last Activity</h3>
                </div>
                <div class="space-y-3 lastActivity ">

                </div>
            </div>
        </div>

        <!-- Motivational Quotes Section -->
        <div class="bg-white/10 backdrop-blur-md rounded-2xl p-8 card-shadow border border-white/20">
            <div class="text-center mb-8">
                <h3 class="text-3xl font-bold  mb-2">Daily Inspiration</h3>
                <p class="/70">Fuel your motivation with today's quote</p>
            </div>

            <div class="max-w-4xl mx-auto">
                <div id="quoteContainer" class="quote-animation" style="transition: opacity 0.3s, transform 0.3s;">
                    <blockquote class="text-center">
                        <p class="text-2xl md:text-3xl font-light  mb-6 leading-relaxed" id="quoteText">
                            "Success is not final, failure is not fatal: it is the courage to continue that counts."
                        </p>
                        <footer class="/70 text-lg" id="quoteAuthor">
                            — Winston Churchill —
                        </footer>
                    </blockquote>
                </div>
            </div>
        </div>
    </main>
    

    <script>
        // Array of motivational quotes
        const quotes = [
            {
                text: "Success is not final, failure is not fatal: it is the courage to continue that counts.",
                author: "Winston Churchill"
            },
            {
                text: "The way to get started is to quit talking and begin doing.",
                author: "Walt Disney"
            },
            {
                text: "Innovation distinguishes between a leader and a follower.",
                author: "Steve Jobs"
            },
            {
                text: "Your work is going to fill a large part of your life, and the only way to be truly satisfied is to do what you believe is great work.",
                author: "Steve Jobs"
            },
            {
                text: "The future belongs to those who believe in the beauty of their dreams.",
                author: "Eleanor Roosevelt"
            },
            {
                text: "Don't be afraid to give up the good to go for the great.",
                author: "John D. Rockefeller"
            },
            {
                text: "The only way to do great work is to love what you do.",
                author: "Steve Jobs"
            },
            {
                text: "Success is walking from failure to failure with no loss of enthusiasm.",
                author: "Winston Churchill"
            },
            {
                text: "Believe you can and you're halfway there.",
                author: "Theodore Roosevelt"
            },
            {
                text: "Excellence is never an accident. It is always the result of high intention, sincere effort, and intelligent execution.",
                author: "Aristotle"
            },
            {
                text: "Life is what happens when you're busy making other plans.",
                author: "John Lennon"
            },
            {
                text: "The only limit to our realization of tomorrow is our doubts of today.",
                author: "Franklin D. Roosevelt"
            },
            {
                text: "Do not wait to strike till the iron is hot, but make it hot by striking.",
                author: "William Butler Yeats"
            },
            {
                text: "Whether you think you can or you think you can't, you're right.",
                author: "Henry Ford"
            },
            {
                text: "The best revenge is massive success.",
                author: "Frank Sinatra"
            },
            {
                text: "You miss 100% of the shots you don't take.",
                author: "Wayne Gretzky"
            },
            {
                text: "I have not failed. I've just found 10,000 ways that won't work.",
                author: "Thomas Edison"
            },
            {
                text: "A person who never made a mistake never tried anything new.",
                author: "Albert Einstein"
            },
            {
                text: "The secret of getting ahead is getting started.",
                author: "Mark Twain"
            },
            {
                text: "It does not matter how slowly you go as long as you do not stop.",
                author: "Confucius"
            },
            {
                text: "Everything you’ve ever wanted is on the other side of fear.",
                author: "George Addair"
            },
            {
                text: "Dream big and dare to fail.",
                author: "Norman Vaughan"
            },
            {
                text: "The only person you are destined to become is the person you decide to be.",
                author: "Ralph Waldo Emerson"
            },
            {
                text: "Act as if what you do makes a difference. It does.",
                author: "William James"
            },
            {
                text: "The harder I work, the luckier I get.",
                author: "Samuel Goldwyn"
            },
            {
                text: "You become what you believe.",
                author: "Oprah Winfrey"
            },
            {
                text: "I would rather die of passion than of boredom.",
                author: "Vincent van Gogh"
            },
            {
                text: "If you want to lift yourself up, lift up someone else.",
                author: "Booker T. Washington"
            },
            {
                text: "The best time to plant a tree was 20 years ago. The second best time is now.",
                author: "Chinese Proverb"
            },
            {
                text: "Eighty percent of success is showing up.",
                author: "Woody Allen"
            },
            {
                text: "The mind is everything. What you think you become.",
                author: "Buddha"
            },
            {
                text: "Either you run the day, or the day runs you.",
                author: "Jim Rohn"
            },
            {
                text: "Nothing is impossible, the word itself says ‘I’m possible’!",
                author: "Audrey Hepburn"
            },
            {
                text: "The only way to achieve the impossible is to believe it is possible.",
                author: "Charles Kingsleigh"
            },
            {
                text: "Don’t count the days, make the days count.",
                author: "Muhammad Ali"
            },
            {
                text: "You can’t fall if you don’t climb. But there’s no joy in living your whole life on the ground.",
                author: "Unknown"
            },
            {
                text: "The two most important days in your life are the day you are born and the day you find out why.",
                author: "Mark Twain"
            },
            {
                text: "Life is either a daring adventure or nothing at all.",
                author: "Helen Keller"
            },
            {
                text: "What you get by achieving your goals is not as important as what you become by achieving your goals.",
                author: "Zig Ziglar"
            },
            {
                text: "You must be the change you wish to see in the world.",
                author: "Mahatma Gandhi"
            },
            {
                text: "The only thing standing between you and your goal is the story you keep telling yourself as to why you can't achieve it.",
                author: "Jordan Belfort"
            },
            {
                text: "Success usually comes to those who are too busy to be looking for it.",
                author: "Henry David Thoreau"
            },
            {
                text: "If you are not willing to risk the usual, you will have to settle for the ordinary.",
                author: "Jim Rohn"
            },
            {
                text: "The successful warrior is the average man, with laser-like focus.",
                author: "Bruce Lee"
            },
            {
                text: "I find that the harder I work, the more luck I seem to have.",
                author: "Thomas Jefferson"
            },
            {
                text: "The only place where success comes before work is in the dictionary.",
                author: "Vidal Sassoon"
            },
            {
                text: "The road to success and the road to failure are almost exactly the same.",
                author: "Colin R. Davis"
            },
            {
                text: "Opportunities don't happen. You create them.",
                author: "Chris Grosser"
            },
            {
                text: "Don’t let yesterday take up too much of today.",
                author: "Will Rogers"
            },
            {
                text: "The secret of success is to know something nobody else knows.",
                author: "Aristotle Onassis"
            },
            {
                text: "The only thing worse than being blind is having sight but no vision.",
                author: "Helen Keller"
            },
            {
                text: "It’s not whether you get knocked down, it’s whether you get up.",
                author: "Vince Lombardi"
            },
            {
                text: "The best way to predict the future is to invent it.",
                author: "Alan Kay"
            },
            {
                text: "The difference between a successful person and others is not a lack of strength, not a lack of knowledge, but rather a lack in will.",
                author: "Vince Lombardi"
            },
            {
                text: "The only thing that overcomes hard luck is hard work.",
                author: "Harry Golden"
            },
            {
                text: "The biggest risk is not taking any risk. In a world that's changing quickly, the only strategy that is guaranteed to fail is not taking risks.",
                author: "Mark Zuckerberg"
            },
            {
                text: "If you can dream it, you can do it.",
                author: "Walt Disney"
            },
            {
                text: "The best revenge is to be unlike him who performed the injury.",
                author: "Marcus Aurelius"
            },
            {
                text: "The only true wisdom is in knowing you know nothing.",
                author: "Socrates"
            },
            {
                text: "The journey of a thousand miles begins with one step.",
                author: "Lao Tzu"
            },
            {
                text: "We become what we think about.",
                author: "Earl Nightingale"
            },
            {
                text: "The only thing we have to fear is fear itself.",
                author: "Franklin D. Roosevelt"
            },
            {
                text: "The best and most beautiful things in the world cannot be seen or even touched - they must be felt with the heart.",
                author: "Helen Keller"
            },
            {
                text: "The best preparation for tomorrow is doing your best today.",
                author: "H. Jackson Brown Jr."
            },
            {
                text: "The only source of knowledge is experience.",
                author: "Albert Einstein"
            },
            {
                text: "You can’t use up creativity. The more you use, the more you have.",
                author: "Maya Angelou"
            },
            {
                text: "The best way out is always through.",
                author: "Robert Frost"
            },
            {
                text: "The only thing standing between you and your dream is the will to try and the belief that it is actually possible.",
                author: "Joel Brown"
            },
            {
                text: "The future starts today, not tomorrow.",
                author: "Pope John Paul II"
            },
            {
                text: "The only person you should try to be better than is the person you were yesterday.",
                author: "Unknown"
            },
            {
                text: "The only real mistake is the one from which we learn nothing.",
                author: "Henry Ford"
            },
            {
                text: "The best dreams happen when you're awake.",
                author: "Cherie Gilderbloom"
            },
            {
                text: "The only thing that will stop you from fulfilling your dreams is you.",
                author: "Tom Bradley"
            },
            {
                text: "The best way to predict your future is to create it.",
                author: "Abraham Lincoln"
            },
            {
                text: "The only limit is the one you set yourself.",
                author: "Unknown"
            },
            {
                text: "The best time for new beginnings is now.",
                author: "Unknown"
            },
            {
                text: "The only way to have a good day is to start it with a positive attitude.",
                author: "Unknown"
            },
            {
                text: "The best things in life are free.",
                author: "Unknown"
            },
            {
                text: "The only thing that is constant is change.",
                author: "Heraclitus"
            },
            {
                text: "The best is yet to come.",
                author: "Frank Sinatra"
            },
            {
                text: "The only way to discover the limits of the possible is to go beyond them into the impossible.",
                author: "Arthur C. Clarke"
            },
            {
                text: "The best way to cheer yourself up is to cheer somebody else up.",
                author: "Mark Twain"
            },
            {
                text: "The only real failure in life is not to be true to the best one knows.",
                author: "Buddha"
            },
            {
                text: "The best things come to those who wait.",
                author: "Unknown"
            },
            {
                text: "The only thing we can control is our effort.",
                author: "Unknown"
            },
            {
                text: "The best is always yet to come.",
                author: "Unknown"
            },
            {
                text: "The only way to do great things is to love what you do.",
                author: "Steve Jobs"
            },
            {
                text: "The best dreams are the ones you make happen.",
                author: "Unknown"
            },
            {
                text: "The only thing that can stop you is you.",
                author: "Unknown"
            },
            {
                text: "The best way to be happy is to make others happy.",
                author: "Unknown"
            },
            {
                text: "The only way to achieve the impossible is to believe it is possible.",
                author: "Charles Kingsleigh"
            },
            {
                text: "The best is yet to be.",
                author: "Robert Browning"
            },
            {
                text: "The only thing that can limit you is your own mind.",
                author: "Unknown"
            },
            {
                text: "The best way to predict the future is to create it.",
                author: "Peter Drucker"
            },
            {
                text: "The only thing that stands between you and your dream is the will to try.",
                author: "Unknown"
            },
            {
                text: "The best is always within you.",
                author: "Unknown"
            },
            {
                text: "The only thing that can hold you back is your own fear.",
                author: "Unknown"
            },
            {
                text: "The best way to find yourself is to lose yourself in the service of others.",
                author: "Mahatma Gandhi"
            },
            {
                text: "The only thing that can stop you is yourself.",
                author: "Unknown"
            },
            {
                text: "The best dreams are the ones you never give up on.",
                author: "Unknown"
            },
            {
                text: "The only thing that can bring you down is your own attitude.",
                author: "Unknown"
            },
            {
                text: "The best way to live is to love what you do.",
                author: "Unknown"
            },
            {
                text: "The only thing that can make you happy is you.",
                author: "Unknown"
            },
            {
                text: "The best is always ahead.",
                author: "Unknown"
            },
            {
                text: "The only thing that can change your life is you.",
                author: "Unknown"
            },
            {
                text: "The best way to succeed is to never give up.",
                author: "Unknown"
            },
            {
                text: "The only thing that can make you great is your own effort.",
                author: "Unknown"
            },
            {
                text: "The best is always within reach.",
                author: "Unknown"
            },
            {
                text: "The only thing that can bring you success is hard work.",
                author: "Unknown"
            },
            {
                text: "The best way to be successful is to believe in yourself.",
                author: "Unknown"
            },
            {
                text: "The only thing that can stop your dreams is you.",
                author: "Unknown"
            },
            {
                text: "The best is always yet to come.",
                author: "Unknown"
            }
        ];

        function getRandomInt(max) {
            return Math.floor(Math.random() * max);
        }

        let currentQuoteIndex = getRandomInt(112);

        // Function to update date and time
        function updateDateTime() {
            const now = new Date();


            // Format date for today's info
            const todayDate = now.toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            });

            const todayDay = now.toLocaleDateString('en-US', {weekday: 'long'});
            const currentTime = now.toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit'
            });

            document.getElementById('todayDate').textContent = todayDate;
            document.getElementById('todayDay').textContent = todayDay;
            document.getElementById('currentTime').textContent = currentTime;
        }

        // Function to get a new quote
        function getNewQuote() {
            currentQuoteIndex = (currentQuoteIndex + 1) % quotes.length;
            const quote = quotes[currentQuoteIndex];

            const container = document.getElementById('quoteContainer');
            container.style.opacity = '0';
            container.style.transform = 'translateY(20px)';

            setTimeout(() => {
                document.getElementById('quoteText').textContent = `"${quote.text}"`;
                document.getElementById('quoteAuthor').textContent = `— ${quote.author}—`;

                container.style.opacity = '1';
                container.style.transform = 'translateY(0)';
            }, 200);
        }

        getNewQuote()
        updateDateTime();
        setInterval(updateDateTime, 60000);

        document.getElementById('quoteContainer').style.transition = 'opacity 0.3s ease, transform 0.3s ease';
        console.log();
        let lastActivity = document.querySelector('.lastActivity');

        JSON.parse(localStorage.getItem('visitedUrls')|| '[]').forEach((item) => {
            const div = document.createElement('div');
            div.className = 'bg-white/5 rounded-lg p-1';
            const link = document.createElement('a');
            link.href = item;
            link.className = "opacity-80 text-sm";
            link.textContent = item;
            div.appendChild(link);
            lastActivity.appendChild(div)
        })



    </script>


</x-filament-panels::page>
