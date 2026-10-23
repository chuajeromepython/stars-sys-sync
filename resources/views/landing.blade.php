<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>STARS - Students Test Analysis and Records System</title>
    <meta content="" name="description">
    <meta content="" name="keywords">

    <!-- Favicons -->
    <link href="/images/favicon.png" rel="icon">
    <link href="/images/favicon.png" rel="apple-touch-icon">

    <!-- Google Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        poppins: ['Poppins', 'sans-serif'],
                    },
                    colors: {
                        primary: '#012970',
                        secondary: '#1e3a8a',
                        accent: '#2563eb',
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.6s ease-out forwards',
                        'float': 'float 6s ease-in-out infinite',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': {
                                opacity: '0',
                                transform: 'translateY(20px)'
                            },
                            '100%': {
                                opacity: '1',
                                transform: 'translateY(0)'
                            },
                        },
                        float: {
                            '0%, 100%': {
                                transform: 'translateY(0)'
                            },
                            '50%': {
                                transform: 'translateY(-20px)'
                            },
                        },
                    },
                },
            },
        }
    </script>

    <!-- Custom Styles -->
    <style>
        .glass {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
        }

        .glass-dark {
            background: rgba(1, 41, 112, 0.9);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        .card-modern {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card-modern:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.1);
        }

        .gradient-text {
            background: linear-gradient(135deg, #012970 0%, #1e3a8a 50%, #2563eb 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .btn-modern {
            background: linear-gradient(135deg, #012970 0%, #1e3a8a 100%);
            transition: all 0.3s ease;
        }

        .btn-modern:hover {
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -5px rgba(1, 41, 112, 0.3);
        }

        .nav-link {
            transition: color 0.2s ease;
        }

        .nav-link:hover {
            color: #2563eb !important;
        }

        .section-padding {
            padding: 100px 0;
        }

        .collaborator-scrollbar-hidden {
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .collaborator-scrollbar-hidden::-webkit-scrollbar {
            display: none;
        }
    </style>
</head>

<!-- Navigation -->
<nav aria-label="Main navigation" class="fixed top-0 w-full z-50 glass">
    <div class="max-w-7xl mx-auto px-6 lg:px-12">
        <div class="flex items-center justify-between h-20">
            <a href="#" class="flex items-center gap-2" aria-label="STARS Home">
                <img src="/images/logo_xs.png" alt="STARS Logo" class="h-8 w-auto">
                <span class="font-poppins font-bold text-xl tracking-wide text-primary">STARS</span>
            </a>

            <!-- Desktop Menu -->
            <div class="hidden lg:flex items-center gap-8">
                <a href="#hero" class="nav-link text-sm font-medium text-gray-600 hover:text-primary">Home</a>
                <a href="#about" class="nav-link text-sm font-medium text-gray-600 hover:text-primary">About</a>
                <a href="#features" class="nav-link text-sm font-medium text-gray-600 hover:text-primary">Features</a>
                <a href="#content" class="nav-link text-sm font-medium text-gray-600 hover:text-primary">Content</a>
                <a href="#team" class="nav-link text-sm font-medium text-gray-600 hover:text-primary">Team</a>
                <a href="#contact" class="nav-link text-sm font-medium text-gray-600 hover:text-primary">Contact</a>
                <a href="/login" class="nav-link text-sm font-medium text-primary hover:text-primary">Login</a>
            </div>

            <!-- Mobile Menu Button -->
            <button id="mobile-menu-toggle" type="button"
                class="lg:hidden p-2 text-gray-600 hover:text-primary focus:outline-none focus-visible:ring-2 focus-visible:ring-primary rounded-lg"
                aria-label="Open navigation menu" aria-controls="mobile-navigation" aria-expanded="false">
                <i class="bi bi-list text-xl" aria-hidden="true"></i>
            </button>
        </div>

        <div id="mobile-navigation" class="hidden lg:hidden border-t border-gray-200/70">
            <div class="flex flex-col py-3">
                <a href="#hero"
                    class="nav-link rounded-lg px-4 py-3 text-sm font-medium text-gray-700 hover:bg-blue-50 hover:text-primary">Home</a>
                <a href="#about"
                    class="nav-link rounded-lg px-4 py-3 text-sm font-medium text-gray-700 hover:bg-blue-50 hover:text-primary">About</a>
                <a href="#features"
                    class="nav-link rounded-lg px-4 py-3 text-sm font-medium text-gray-700 hover:bg-blue-50 hover:text-primary">Features</a>
                <a href="#content"
                    class="nav-link rounded-lg px-4 py-3 text-sm font-medium text-gray-700 hover:bg-blue-50 hover:text-primary">Content</a>
                <a href="#team"
                    class="nav-link rounded-lg px-4 py-3 text-sm font-medium text-gray-700 hover:bg-blue-50 hover:text-primary">Team</a>
                <a href="#contact"
                    class="nav-link rounded-lg px-4 py-3 text-sm font-medium text-gray-700 hover:bg-blue-50 hover:text-primary">Contact</a>
            </div>
        </div>
    </div>
</nav>
<!-- Hero Section -->
<section id="hero" class="relative pt-32 pb-20 lg:pt-48 lg:pb-32 overflow-hidden bg-white">
    <div class="max-w-7xl mx-auto px-6 lg:px-12">
        <div
            class="grid grid-cols-1 sm:grid-cols-[minmax(0,1.2fr)_minmax(0,0.8fr)] lg:grid-cols-2 gap-8 lg:gap-12 items-center">

            <div class="space-y-6">
                <div
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-blue-50 text-primary font-semibold text-sm border border-blue-100">
                    <i class="bi bi-shield-check"></i>
                    <span>Trusted by DepEd Laguna</span>
                </div>
                <div class="mt-8 flex items-center gap-8">
                    <img src="/images/landing/banner.png" alt="DepEd National"
                        class="h-28 w-auto opacity-80 hover:opacity-100 transition-opacity">
                </div>
                <h1 class="text-4xl lg:text-6xl font-poppins font-bold leading-tight text-gray-900">
                    Students Test Analysis Record System -
                    <span class="text-primary">STARS</span>
                </h1>

                <p class="text-lg lg:text-xl text-gray-600 leading-relaxed max-w-lg">
                    A system that provides comprehensive solution to the demand of assessment result computation as well
                    as student academic records.
                </p>

                <div class="flex flex-wrap gap-4">
                    <a href="#features"
                        class="btn-modern px-8 py-4 rounded-xl text-white font-semibold flex items-center gap-2">
                        Explore Features
                        <i class="bi bi-arrow-right"></i>
                    </a>
                    <a href="#contact"
                        class="px-8 py-4 rounded-xl border border-gray-200 text-gray-700 font-semibold hover:bg-gray-50 hover:text-primary transition-all">
                        Contact Us
                    </a>
                </div>
            </div>

            <div class="relative">
                <img src="/images/logo.png" alt="STARS Logo"
                    class="relative w-full max-w-xs sm:max-w-none lg:max-w-md mx-auto hover:scale-105 transition-transform duration-500">
            </div>

        </div>
    </div>
</section>

<main>
    <!-- About Section -->
    <section id="about" class="bg-white border-y border-gray-100">
        <div class="max-w-7xl mx-auto px-6 lg:px-12 section-padding">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div class="order-2 lg:order-1">
                    <div class="text-center lg:text-left">
                        <h2 class="text-3xl lg:text-4xl font-poppins font-bold text-gray-900 mb-6">
                            The Partnership Behind STARS
                        </h2>
                        <p class="text-gray-600 leading-relaxed mb-6">
                            The <a href="https://depedlaguna.com.ph" target="_blank"
                                class="text-accent font-semibold hover:underline"><b>Department of Education - Division
                                    of Laguna</b></a> and <a href="https://laguna.gov.ph/miso" target="_blank"
                                class="text-accent font-semibold hover:underline"><b>Provincial Government of Laguna –
                                    Management Information Systems Office</b></a> agreed to develop the Students Test
                            Analysis and Record System (STARS) to ease the analyzation of student's assessment result
                            and provide automated reports containing data that can be used in the decision-making of the
                            institution.
                        </p>
                        <a href="#features"
                            class="btn-modern px-8 py-3 rounded-lg text-white font-semibold inline-flex items-center gap-2">
                            Explore Features
                            <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <div class="order-1 lg:order-2">
                    <div class="relative">
                        <img src="/images/landing/pgl.jpg" alt="Partnership"
                            class="rounded-2xl shadow-2xl border border-gray-200 w-full">
                        <div
                            class="absolute -bottom-6 -left-6 bg-white p-6 rounded-xl shadow-xl border border-gray-100">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-primary">
                                    <i class="bi bi-building text-xl"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-semibold text-gray-900">DepEd - Laguna</div>
                                    <div class="text-xs text-gray-500">Partner Institution</div>
                                </div>
                            </div>
                        </div>
                        <div
                            class="absolute -bottom-6 -right-6 bg-white p-6 rounded-xl shadow-xl border border-gray-100">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center text-purple-600">
                                    <i class="bi bi-cpu text-xl"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-semibold text-gray-900">PGL - MISO</div>
                                    <div class="text-xs text-gray-500">System Developer</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Features Section (System Functions) -->
    <section id="features" class="bg-gray-50">
        <div class="max-w-7xl mx-auto px-6 lg:px-12 section-padding">
            <div class="text-center mb-16">
                <h2 class="text-3xl lg:text-4xl font-poppins font-bold text-gray-900 mb-4">What the System Has to
                    Offer?</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">Explore the core functions of STARS designed to streamline
                    student test analysis and records management.</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <!-- Feature Card 1: Answer Sheet Scanner -->
                <div class="card-modern bg-white p-8 rounded-2xl border border-gray-100 shadow-sm group">
                    <div
                        class="w-16 h-16 rounded-xl bg-blue-50 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <img src="/images/landing/scanner.png" alt="Answer Sheet Scanner" class="w-8 h-8">
                    </div>
                    <h3 class="text-xl font-poppins font-bold text-gray-900 mb-4">Answer Sheet Scanner</h3>
                    <p class="text-gray-600 leading-relaxed mb-6">
                        Automated checking of answer sheets through mobile application.
                    </p>
                    <a href="#" target="_blank"
                        class="text-accent font-semibold inline-flex items-center gap-1">
                        STARS APP
                        <i class="bi bi-box-arrow-up-right text-xs"></i>
                    </a>
                </div>

                <!-- Feature Card 2: Automated Reports -->
                <div class="card-modern bg-white p-8 rounded-2xl border border-gray-100 shadow-sm group">
                    <div
                        class="w-16 h-16 rounded-xl bg-purple-50 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <img src="/images/landing/reports.png" alt="Automated Reports" class="w-8 h-8">
                    </div>
                    <h3 class="text-xl font-poppins font-bold text-gray-900 mb-4">Automated Reports</h3>
                    <p class="text-gray-600 leading-relaxed mb-6">
                        Downloadable and printable reports such as Assessment Result, Score Analysis, Item Analysis and
                        Discrimination Index.
                    </p>
                </div>

                <!-- Feature Card 3: Record Management -->
                <div class="card-modern bg-white p-8 rounded-2xl border border-gray-100 shadow-sm group">
                    <div
                        class="w-16 h-16 rounded-xl bg-green-50 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <img src="/images/landing/records.png" alt="Record Management" class="w-8 h-8">
                    </div>
                    <h3 class="text-xl font-poppins font-bold text-gray-900 mb-4">Record Management</h3>
                    <p class="text-gray-600 leading-relaxed mb-6">
                        An efficient and systematic control of managing students' academic records.
                    </p>
                </div>
            </div>
            <!-- Additional Features Grid -->
            <div class="mt-16">
                <div class="flex flex-col lg:flex-row gap-12 items-center">
                    <div class="lg:w-1/2">
                        <div class="relative group">
                            <div
                                class="absolute -inset-1 bg-gradient-to-r from-primary via-secondary to-accent rounded-3xl blur opacity-20 animate-pulse">
                            </div>
                            <div
                                class="relative p-1 bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">
                                <div class="p-8 lg:p-12 flex items-center justify-center">
                                    <img src="/images/landing/more.png" alt="More Features"
                                        class="w-full max-w-xs h-auto">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="lg:w-1/2 space-y-6">
                        <div class="flex items-start gap-4">
                            <div
                                class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 shrink-0 mt-1">
                                <i class="bi bi-check2 text-sm"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900">User-Friendly Interface</h4>
                                <p class="text-sm text-gray-500 mt-1">Intuitive design for all users</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div
                                class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 shrink-0 mt-1">
                                <i class="bi bi-check2 text-sm"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900">Mobile View Compatible</h4>
                                <p class="text-sm text-gray-500 mt-1">Accessible on any device</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div
                                class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 shrink-0 mt-1">
                                <i class="bi bi-check2 text-sm"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900">Effective and Efficient</h4>
                                <p class="text-sm text-gray-500 mt-1">Streamlined assessment workflow</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div
                                class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 shrink-0 mt-1">
                                <i class="bi bi-check2 text-sm"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900">Continuous Support</h4>
                                <p class="text-sm text-gray-500 mt-1">Dedicated assistance always</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div
                                class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 shrink-0 mt-1">
                                <i class="bi bi-check2 text-sm"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900">Zero Cost Development</h4>
                                <p class="text-sm text-gray-500 mt-1">No development fees required</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div
                                class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center text-red-600 shrink-0 mt-1">
                                <i class="bi bi-heart-fill text-sm"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900">More Family Time &#x2764;</h4>
                                <p class="text-sm text-gray-500 mt-1">Save time with automation</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Content / Output Section -->
    <section id="content" class="bg-white">
        <div class="max-w-7xl mx-auto px-6 lg:px-12 section-padding">
            <div class="grid min-w-0 lg:grid-cols-2 gap-12 items-center">
                <div class="lg:order-1">
                    <div class="space-y-6">
                        <div class="flex items-center gap-3 mb-2">
                            <div
                                class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                                <i class="bi bi-files text-xl"></i>
                            </div>
                            <span class="text-xs font-semibold uppercase tracking-wider text-primary">System
                                Output</span>
                        </div>
                        <h2 class="text-3xl lg:text-4xl font-poppins font-bold text-gray-900 mb-6">Rich Content &amp;
                            Output</h2>
                        <p class="text-gray-600 leading-relaxed mb-8 max-w-lg">
                            STARS generates downloadable and printable reports containing actionable data for
                            institutional decision-making:
                        </p>
                    </div>
                </div>
                <div class="lg:order-2">
                    <div class="relative">
                        <div
                            class="absolute -inset-1 bg-gradient-to-r from-primary via-secondary to-accent rounded-3xl blur opacity-20 animate-pulse">
                        </div>
                        <div
                            class="relative p-4 bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 text-center">
                                <div class="min-w-0 p-3 sm:p-4 rounded-2xl bg-blue-50 border border-blue-100">
                                    <div
                                        class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center text-primary mx-auto mb-4">
                                        <i class="bi bi-file-earmark-text text-xl"></i>
                                    </div>
                                    <div class="text-sm md:text-base font-poppins font-bold text-gray-900 break-words">
                                        Assessment Result</div>
                                    <div class="text-sm text-gray-500 mt-1">Automated report</div>
                                </div>
                                <div class="min-w-0 p-3 sm:p-4 rounded-2xl bg-indigo-50 border border-indigo-100">
                                    <div
                                        class="w-12 h-12 rounded-xl bg-indigo-100 flex items-center justify-center text-indigo-600 mx-auto mb-4">
                                        <i class="bi bi-file-earmark-bar-graph text-xl"></i>
                                    </div>
                                    <div class="text-sm md:text-base font-poppins font-bold text-gray-900 break-words">
                                        Score Analysis</div>
                                    <div class="text-sm text-gray-500 mt-1">Performance breakdown</div>
                                </div>
                                <div class="min-w-0 p-3 sm:p-4 rounded-2xl bg-green-50 border border-green-100">
                                    <div
                                        class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center text-green-600 mx-auto mb-4">
                                        <i class="bi bi-file-earmark-chart text-xl"></i>
                                    </div>
                                    <div class="text-sm md:text-base font-poppins font-bold text-gray-900 break-words">
                                        Item Analysis</div>
                                    <div class="text-sm text-gray-500 mt-1">Per-item insights</div>
                                </div>
                                <div class="min-w-0 p-3 sm:p-4 rounded-2xl bg-purple-50 border border-purple-100">
                                    <div
                                        class="w-12 h-12 rounded-xl bg-purple-100 flex items-center justify-center text-purple-600 mx-auto mb-4">
                                        <i class="bi bi-file-earmark-line text-xl"></i>
                                    </div>
                                    <div class="text-sm md:text-base font-poppins font-bold text-gray-900 break-words">
                                        Discrimination Index
                                    </div>
                                    <div class="text-sm text-gray-500 mt-1">Item quality metric</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Team Section -->
    <section id="team" class="bg-gray-50">
        <div class="max-w-7xl mx-auto px-6 lg:px-12 section-padding">
            <div class="text-center mb-16">
                <h2 class="text-3xl lg:text-4xl font-poppins font-bold text-gray-900 mb-4">Division of Laguna</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">Meet the dedicated people behind the STARS system.</p>
            </div>

            <!-- Superintendent Profile -->
            <div class="mb-16">
                <div
                    class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col lg:flex-row">
                    <div
                        class="w-full lg:w-1/3 min-h-0 h-72 sm:h-96 lg:h-auto bg-gray-100 overflow-hidden relative flex items-center justify-center">
                        <img src="/images/landing/SUSAN-DL.jpg"
                            alt="Susan DL. Oribiana, Schools Division Superintendent"
                            class="w-full h-full object-contain lg:object-cover">
                    </div>
                    <div class="min-w-0 p-6 sm:p-8 lg:w-2/3 lg:p-12 flex flex-col justify-center">
                        <h3 id="superintendent-name" class="text-2xl font-poppins font-bold text-gray-900 mb-2">Susan
                            DL. Oribiana, CESO V</h3>
                        <p class="text-primary font-semibold mb-4">Schools Division Superintendent</p>
                        <blockquote class="italic text-gray-600 border-l-4 border-primary pl-4 py-2">
                            Superintendent's statement will appear here.
                        </blockquote>
                    </div>
                </div>
            </div>

            <div class="text-center mb-12">
                <h4 class="text-xl font-poppins font-semibold text-gray-700">Division Leadership Team</h4>
                <p class="text-gray-500 text-sm">Our hard working team</p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6 mb-16">
                <div class="card-modern bg-white rounded-2xl border border-gray-100 text-center overflow-hidden">
                    <img src="/images/landing/Punzalan.jpg"
                        alt="Dr. Randy D. Punzalan, Assistant Schools Division Superintendent"
                        class="w-full aspect-square object-cover object-center">
                    <div class="p-5">
                        <h4 class="font-poppins font-bold text-gray-900 mb-1">Dr. Randy D. Punzalan, <br> CESO VI</h4>
                        <p class="text-primary text-sm font-medium">Asst. Schools Division Superintendent</p>
                    </div>
                </div>
                <div class="card-modern bg-white rounded-2xl border border-gray-100 text-center overflow-hidden">
                    <img src="/images/landing/REPIA.png"
                        alt="Buddy Chester M. Repia, Assistant Schools Division Superintendent"
                        class="w-full aspect-square object-cover object-center">
                    <div class="p-5">
                        <h4 class="font-poppins font-bold text-gray-900 mb-1">Buddy Chester M. Repia, <br> CESO VI</h4>
                        <p class="text-primary text-sm font-medium">Asst. Schools Division Superintendent</p>
                    </div>
                </div>
                <div class="card-modern bg-white rounded-2xl border border-gray-100 text-center overflow-hidden">
                    <img src="/images/landing/team4.png" alt="Orlando T. Valverde, Chief - CID"
                        class="w-full aspect-square object-cover object-center">
                    <div class="p-5">
                        <h4 class="font-poppins font-bold text-gray-900 mb-1">Orlando T. Valverde, EdD</h4>
                        <p class="text-primary text-sm font-medium">Chief - CID</p>
                    </div>
                </div>
                <div class="card-modern bg-white rounded-2xl border border-gray-100 text-center overflow-hidden">
                    <img src="/images/landing/team5.png" alt="Darwin S. Talumbayan, Chief - SGOD"
                        class="w-full aspect-square object-cover object-center">
                    <div class="p-5">
                        <h4 class="font-poppins font-bold text-gray-900 mb-1">Darwin S. Talumbayan</h4>
                        <p class="text-primary text-sm font-medium">Chief - SGOD</p>
                    </div>
                </div>
                <div class="card-modern bg-white rounded-2xl border border-gray-100 text-center overflow-hidden">
                    <img src="/images/landing/team6.png" alt="Angela F. Latina, Administrative Officer V"
                        class="w-full aspect-square object-cover object-center">
                    <div class="p-5">
                        <h4 class="font-poppins font-bold text-gray-900 mb-1">Angela F. Latina</h4>
                        <p class="text-primary text-sm font-medium">Administrative Officer V</p>
                    </div>
                </div>
            </div>

            <div class="text-center mb-12">
                <h4 class="text-xl font-poppins font-semibold text-gray-700">System Development Team</h4>
                <p class="text-gray-500 text-sm">Provincial Government of Laguna - Management Information Systems
                    Office</p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="card-modern bg-white p-6 rounded-2xl border border-gray-100 text-center">
                    <div class="relative group">
                        <img src="/images/landing/zaide.jpg" alt="Frederick B. Zaide"
                            class="w-24 h-24 rounded-full mx-auto mb-4 object-cover border-4 border-gray-100 group-hover:border-primary transition-colors">
                    </div>
                    <h4 class="font-poppins font-bold text-gray-900 mb-1">Frederick B. Zaide</h4>
                    <p class="text-primary text-sm font-medium mb-1">Education Program Supervisor</p>
                    <p class="text-gray-500 text-xs">Project Development Head</p>
                </div>
                <div class="card-modern bg-white p-6 rounded-2xl border border-gray-100 text-center">
                    <div class="relative group">
                        <img src="/images/landing/garcia.png" alt="Jaime M. Garcia"
                            class="w-24 h-24 rounded-full mx-auto mb-4 object-cover border-4 border-gray-100 group-hover:border-primary transition-colors">
                    </div>
                    <h4 class="font-poppins font-bold text-gray-900 mb-1">Jaime M. Garcia</h4>
                    <p class="text-primary text-sm font-medium mb-1">Officer In Charge - MISO</p>
                    <p class="text-gray-500 text-xs">Provincial Government of Laguna - Management Information Systems
                        Office</p>
                </div>
                <div class="card-modern bg-white p-6 rounded-2xl border border-gray-100 text-center">
                    <div class="relative group">
                        <img src="/images/landing/abrazaldo.png" alt="Roi Vinson D. Abrazaldo"
                            class="w-24 h-24 rounded-full mx-auto mb-4 object-cover border-4 border-gray-100 group-hover:border-primary transition-colors">
                    </div>
                    <h4 class="font-poppins font-bold text-gray-900 mb-1">Roi Vinson D. Abrazaldo</h4>
                    <p class="text-primary text-sm font-medium mb-1">Acting OIC - MISO</p>
                    <p class="text-gray-500 text-xs">Provincial Government of Laguna - Management Information Systems
                        Office</p>
                </div>
                <div class="card-modern bg-white p-6 rounded-2xl border border-gray-100 text-center">
                    <div class="relative group">
                        <img src="/images/landing/rein.jpg" alt="Reinald M. De Luna"
                            class="w-24 h-24 rounded-full mx-auto mb-4 object-cover border-4 border-gray-100 group-hover:border-primary transition-colors">
                    </div>
                    <h4 class="font-poppins font-bold text-gray-900 mb-1">Reinald M. De Luna</h4>
                    <p class="text-primary text-sm font-medium mb-1">Information Systems Researcher - II</p>
                    <p class="text-gray-500 text-xs">Provincial Government of Laguna - Management Information Systems
                        Office</p>
                </div>
            </div>

            <div class="text-center mt-16 mb-12">
                <h4 class="text-xl font-poppins font-semibold text-gray-700">Collaborators</h4>
                <p class="text-gray-500 text-sm">Organizations supporting the STARS system</p>
            </div>
            <div class="relative" data-collaborator-carousel>
                <button type="button" data-collaborator-scroll="left" aria-label="Scroll collaborators left"
                    class="hidden absolute left-3 top-1/2 z-10 grid h-11 w-11 -translate-y-1/2 place-items-center rounded-full bg-white/90 text-primary opacity-75 shadow-lg backdrop-blur-sm transition hover:opacity-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
                    <i class="bi bi-chevron-left text-lg" aria-hidden="true"></i>
                </button>
                <div role="region" aria-label="Collaborator profiles" tabindex="0"
                    class="collaborator-scrollbar-hidden overflow-x-auto overscroll-x-contain snap-x snap-mandatory scroll-smooth rounded-2xl px-1 py-4 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
                    <div class="flex w-max min-w-full gap-6">
                        <div
                            class="card-modern w-72 shrink-0 snap-start bg-white p-6 rounded-2xl border border-gray-100 text-center">
                            <div class="relative group">
                                <img src="/images/landing/afurong.jpg" alt="Glenn Nerrie A. Afurong"
                                    class="w-24 h-24 rounded-full mx-auto mb-4 object-cover border-4 border-gray-100 group-hover:border-primary transition-colors">
                            </div>
                            <h4 class="font-poppins font-bold text-gray-900 mb-1"><a
                                    href="https://github.com/thecoderistkid">Glenn Nerrie A. Afurong</a></h4>
                            <p class="text-primary text-sm font-medium mb-1">Web Application</p>
                            <p class="text-gray-500 text-xs">Former Developer</p>
                        </div>
                        <div
                            class="card-modern w-72 shrink-0 snap-start bg-white p-6 rounded-2xl border border-gray-100 text-center">
                            <div class="relative group">
                                <img src="/images/landing/jerome_chua.jpg" alt="Jerome Chua"
                                    class="w-24 h-24 rounded-full mx-auto mb-4 object-cover border-4 border-gray-100 group-hover:border-primary transition-colors">
                            </div>
                            <h4 class="font-poppins font-bold text-gray-900 mb-1"><a
                                    href="https://github.com/chuajeromepython">Jerome S. Chua</a></h4>
                            <p class="text-primary text-sm font-medium mb-1">Mobile Application</p>
                            <p class="text-gray-500 text-xs">ACTS Computer College</p>
                        </div>
                    </div>
                </div>
                <button type="button" data-collaborator-scroll="right" aria-label="Scroll collaborators right"
                    class="hidden absolute right-3 top-1/2 z-10 grid h-11 w-11 -translate-y-1/2 place-items-center rounded-full bg-white/90 text-primary opacity-75 shadow-lg backdrop-blur-sm transition hover:opacity-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
                    <i class="bi bi-chevron-right text-lg" aria-hidden="true"></i>
                </button>
            </div>
        </div>
    </section>
    <!-- Contact Section -->
    <section id="contact" class="bg-white">
        <div class="max-w-7xl mx-auto px-6 lg:px-12 section-padding">
            <div class="grid lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 space-y-8">
                    <div>
                        <h2 class="text-3xl lg:text-4xl font-poppins font-bold text-gray-900 mb-4">Get In Touch</h2>
                        <p class="text-gray-600 leading-relaxed">
                            Have questions about STARS or want to implement it in your institution? Reach out to us
                            using the contact details below.
                        </p>
                    </div>

                    <div class="grid md:grid-cols-2 gap-8">
                        <div class="info-box h-100 p-6 rounded-2xl bg-gray-50 border border-gray-100">
                            <div
                                class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center text-primary mb-4">
                                <i class="bi bi-geo-alt text-xl"></i>
                            </div>
                            <h3 class="font-poppins font-bold text-gray-900 mb-2">DepEd - Laguna</h3>
                            <p class="text-gray-600 text-sm">Monday - Friday<br>8:00AM - 05:00PM</p>
                        </div>
                        <div class="info-box h-100 p-6 rounded-2xl bg-gray-50 border border-gray-100">
                            <div
                                class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center text-primary mb-4">
                                <i class="bi bi-building text-xl"></i>
                            </div>
                            <h3 class="font-poppins font-bold text-gray-900 mb-2">PGL - MISO</h3>
                            <p class="text-gray-600 text-sm">Monday - Friday<br>8:00AM - 05:00PM</p>
                        </div>
                        <div class="info-box h-100 p-6 rounded-2xl bg-gray-50 border border-gray-100">
                            <div
                                class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center text-primary mb-4">
                                <i class="bi bi-envelope text-xl"></i>
                            </div>
                            <h3 class="font-poppins font-bold text-gray-900 mb-2">Email Us</h3>
                            <p class="text-gray-600 text-sm">stars@depedlaguna.com.ph</p>
                        </div>
                        <div class="info-box h-100 p-6 rounded-2xl bg-gray-50 border border-gray-100">
                            <div
                                class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center text-primary mb-4">
                                <i class="bi bi-telephone text-xl"></i>
                            </div>
                            <h3 class="font-poppins font-bold text-gray-900 mb-2">Call Us</h3>
                            <p class="text-gray-600 text-sm">+63 49 536 0000</p>
                        </div>
                    </div>
                </div>

                <div class="hidden lg:block">
                    <img src="/images/logo.png" alt="STARS" class="w-full max-w-md opacity-20 grayscale">
                </div>
            </div>
        </div>
    </section>
</main>

<!-- Footer -->
<footer class="bg-primary text-gray-300 pt-16 pb-8 border-t border-white/10">
    <div class="max-w-7xl mx-auto px-6 lg:px-12">
        <div class="grid lg:grid-cols-4 gap-8 mb-12">
            <div class="lg:col-span-2">
                <a href="#" class="flex items-center gap-2 mb-4" aria-label="STARS Home">
                    <img src="/images/logo_xs.png" alt="STARS Logo" class="h-6 w-auto">
                    <span class="font-poppins font-bold text-xl tracking-wide">STARS</span>
                </a>
                <p class="text-gray-300 text-sm leading-relaxed mb-4">
                    Students Test Analysis and Records System
                </p>
                <div class="flex gap-4">
                    <a href="#" class="text-gray-400 hover:text-white transition-colors"
                        aria-label="Twitter"><i class="bi bi-twitter text-xl"></i></a>
                    <a href="#" class="text-gray-400 hover:text-white transition-colors"
                        aria-label="Facebook"><i class="bi bi-facebook text-xl"></i></a>
                    <a href="#" class="text-gray-400 hover:text-white transition-colors"
                        aria-label="Instagram"><i class="bi bi-instagram text-xl"></i></a>
                    <a href="#" class="text-gray-400 hover:text-white transition-colors"
                        aria-label="LinkedIn"><i class="bi bi-linkedin text-xl"></i></a>
                </div>
            </div>
            <div>
                <h4 class="font-poppins font-bold mb-6 text-lg">Useful Links</h4>
                <ul class="space-y-3 text-sm text-gray-300">
                    <li><a href="https://depedlaguna.com.ph" target="_blank"
                            class="hover:text-white transition-colors"><i
                                class="bi bi-chevron-right text-xs mr-2"></i> DepEd Laguna</a></li>
                    <li><a href="https://laguna.gov.ph" target="_blank" class="hover:text-white transition-colors"><i
                                class="bi bi-chevron-right text-xs mr-2"></i> Provincial Government of Laguna</a></li>
                    <li><a href="#" class="hover:text-white transition-colors"><i
                                class="bi bi-chevron-right text-xs mr-2"></i> STARS APP</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-poppins font-bold mb-6 text-lg">System</h4>
                <ul class="space-y-3 text-sm text-gray-300">
                    <li><a href="#features" class="hover:text-white transition-colors"><i
                                class="bi bi-chevron-right text-xs mr-2"></i> Features</a></li>
                    <li><a href="#about" class="hover:text-white transition-colors"><i
                                class="bi bi-chevron-right text-xs mr-2"></i> About</a></li>
                    <li><a href="#team" class="hover:text-white transition-colors"><i
                                class="bi bi-chevron-right text-xs mr-2"></i> Team</a></li>
                    <li><a href="#contact" class="hover:text-white transition-colors"><i
                                class="bi bi-chevron-right text-xs mr-2"></i> Contact</a></li>
                    <li><a href="/login" class="hover:text-white transition-colors"><i
                                class="bi bi-chevron-right text-xs mr-2"></i> Login</a></li>
                </ul>
            </div>
            <div class="flex flex-col items-center lg:items-start justify-center lg:justify-start h-full">
                <img src="/images/matatag.png" alt="Matatag" class="w-full max-w-60 mb-4">
                <div class="w-full max-w-32 h-0.5 bg-white/20"></div>
            </div>
        </div>

        <div class="border-t border-white/10 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="text-sm text-gray-400">
                &copy; Copyright <strong class="text-gray font-semibold">STARS</strong>. All Rights Reserved
            </div>
            <div class="text-xs text-gray-500">
                Template by BootstrapMade
            </div>
        </div>
    </div>
</footer>

<!-- Back to Top -->
<a href="#"
    class="back-to-top fixed bottom-8 right-8 bg-primary text-white w-12 h-12 rounded-full flex items-center justify-center shadow-lg hover:bg-accent transition-all z-50"
    aria-label="Back to top">
    <i class="bi bi-arrow-up"></i>
</a>

<!-- Vendor JS Files -->
<script src="/vendor/FlexStart/assets/vendor/aos/aos.js"></script>
<script src="/vendor/FlexStart/assets/js/main.js"></script>

<script>
    // Initialize AOS animations
    AOS.init({
        duration: 800,
        once: true,
        easing: 'ease-out'
    });

    const mobileMenuButton = document.getElementById('mobile-menu-toggle');
    const mobileNavigation = document.getElementById('mobile-navigation');
    const mobileMenuIcon = mobileMenuButton.querySelector('i');

    const setMobileNavigationOpen = (isOpen) => {
        mobileNavigation.classList.toggle('hidden', !isOpen);
        mobileMenuButton.setAttribute('aria-expanded', String(isOpen));
        mobileMenuButton.setAttribute('aria-label', isOpen ? 'Close navigation menu' : 'Open navigation menu');
        mobileMenuIcon.classList.toggle('bi-list', !isOpen);
        mobileMenuIcon.classList.toggle('bi-x', isOpen);
    };

    mobileMenuButton.addEventListener('click', () => {
        setMobileNavigationOpen(mobileNavigation.classList.contains('hidden'));
    });

    mobileNavigation.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => setMobileNavigationOpen(false));
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            setMobileNavigationOpen(false);
        }
    });

    document.addEventListener('click', (event) => {
        if (!event.target.closest('nav[aria-label="Main navigation"]')) {
            setMobileNavigationOpen(false);
        }
    });

    window.matchMedia('(min-width: 1024px)').addEventListener('change', (event) => {
        if (event.matches) {
            setMobileNavigationOpen(false);
        }
    });

    const collaboratorCarousel = document.querySelector('[data-collaborator-carousel]');

    if (collaboratorCarousel) {
        const collaboratorScroller = collaboratorCarousel.querySelector('[aria-label="Collaborator profiles"]');
        const collaboratorTrack = collaboratorScroller.firstElementChild;
        const leftScrollButton = collaboratorCarousel.querySelector('[data-collaborator-scroll="left"]');
        const rightScrollButton = collaboratorCarousel.querySelector('[data-collaborator-scroll="right"]');

        const updateCollaboratorScrollButtons = () => {
            const maxScrollLeft = collaboratorScroller.scrollWidth - collaboratorScroller.clientWidth;
            const hasOverflow = maxScrollLeft > 1;
            const edgeTolerance = 8;

            leftScrollButton.classList.toggle('hidden', !hasOverflow || collaboratorScroller.scrollLeft <=
                edgeTolerance);
            rightScrollButton.classList.toggle('hidden', !hasOverflow || collaboratorScroller.scrollLeft >=
                maxScrollLeft - edgeTolerance);
        };

        leftScrollButton.addEventListener('click', () => {
            collaboratorScroller.scrollBy({
                left: -collaboratorScroller.clientWidth * 0.8,
                behavior: 'smooth'
            });
        });

        rightScrollButton.addEventListener('click', () => {
            collaboratorScroller.scrollBy({
                left: collaboratorScroller.clientWidth * 0.8,
                behavior: 'smooth'
            });
        });

        collaboratorScroller.addEventListener('scroll', updateCollaboratorScrollButtons, {
            passive: true
        });

        const collaboratorResizeObserver = new ResizeObserver(updateCollaboratorScrollButtons);
        collaboratorResizeObserver.observe(collaboratorScroller);
        collaboratorResizeObserver.observe(collaboratorTrack);

        const collaboratorMutationObserver = new MutationObserver(updateCollaboratorScrollButtons);
        collaboratorMutationObserver.observe(collaboratorTrack, {
            childList: true,
            subtree: true
        });

        updateCollaboratorScrollButtons();
    }
</script>

</body>

</html>
