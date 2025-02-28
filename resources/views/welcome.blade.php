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
    {{-- <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet"> --}}
    <style>
        @font-face {
            font-family: "Roboto";
            src: url("/fonts/Roboto-Regular.ttf");
        }
        @font-face {
            font-family: "Poppins";
            src: url("/fonts/Poppins-Regular.ttf");
        }
        * {
            font-family: "Roboto" !important;
        }
    </style>
    <!-- Vendor CSS Files -->
    <link href="/vendor/FlexStart/assets/vendor/aos/aos.css" rel="stylesheet">
    <link href="/vendor/FlexStart/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="/vendor/FlexStart/assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="/vendor/FlexStart/assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
    <link href="/vendor/FlexStart/assets/vendor/remixicon/remixicon.css" rel="stylesheet">
    <link href="/vendor/FlexStart/assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

    <!-- Template Main CSS File -->
    <link href="/vendor/FlexStart/assets/css/style.css" rel="stylesheet">

    <!-- =======================================================
    * Template Name: FlexStart
    * Updated: Mar 10 2023 with Bootstrap v5.2.3
    * Template URL: https://bootstrapmade.com/flexstart-bootstrap-startup-template/
    * Author: BootstrapMade.com
    * License: https://bootstrapmade.com/license/
    ======================================================== -->
</head>

<body>

    <!-- ======= Header ======= -->
<header id="header" class="header fixed-top">
    <div class="container-fluid container-xl d-flex align-items-center justify-content-between">
        <a href="index.html" class="logo d-flex align-items-center">
            <img src="/images/logo_xs.png" alt="">
            <span>STARS</span>
        </a>
        <nav id="navbar" class="navbar">
            <ul>
                <li><a class="nav-link scrollto active" href="#hero">Home</a></li>
                <li><a class="nav-link scrollto" href="#about">About</a></li>
                <li><a class="nav-link scrollto" href="#features">Features</a></li>
                <li><a class="nav-link scrollto" href="#faq">FAQ</a></li>
                <li><a class="nav-link scrollto" href="#team">Team</a></li>
                <li><a class="nav-link scrollto" href="#contact">Contact</a></li>
                <li><a class="getstarted " href="/login">Login</a></li>
            </ul>
            <i class="bi bi-list mobile-nav-toggle"></i>
        </nav><!-- .navbar -->
    </div>
</header><!-- End Header -->

<!-- ======= Hero Section ======= -->
<section id="hero" class="hero d-flex align-items-center">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 d-flex flex-column justify-content-center">
                {{-- <div class="row mb-3">
                    <div class="col-md-2 col-4">
                        <img src="/images/deped_national_logo.png" alt="" width="75" data-aos="fade-up">
                    </div>
                    <div class="col-md-2 col-4">
                        <img src="/images/deped_logo.png" alt="" width="75" data-aos="fade-up">
                    </div>
                    
                    <div class="col-md-2 col-4">
                        <img src="/images/deped_logo.png" alt="" width="75" data-aos="fade-up">
                    </div>
                </div> --}}
                
                <img src="/images/landing/banner.png" class="text-center" width="300" data-aos="fade-up">
                <h1 data-aos="fade-up">Students Test Analysis Record System</h1>
                <h4 data-aos="fade-up" data-aos-delay="400">
                    A system that provides comprehensive solution to the demand of assessment result computation as well as student academic records
                </h4> 
                <div data-aos="fade-up" data-aos-delay="600">
                    <div class="text-center text-lg-start">
                        <a href="#about" class="btn-get-started scrollto d-inline-flex align-items-center justify-content-center align-self-center">
                        <span>Get Started</span>
                        <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 hero-img" data-aos="zoom-out" data-aos-delay="200">
                <img src="/images/logo.png" class="img-fluid" alt="">
            </div>
        </div>
    </div>
</section>
<!-- End Hero -->

<main id="main">
    <!-- ======= About Section ======= -->
    <section id="about" class="about">
        <div class="container" data-aos="fade-up">
            <div class="row gx-0">

                <div class="col-lg-6 d-flex flex-column justify-content-center" data-aos="fade-up" data-aos-delay="200">
                    <div class="content">
                        <h3>THE PARTNERSHIP</h3>
                        <h2>ABOUT</h2>
                        <p>
                            The <a href="https://depedlaguna.com.ph" target="_blank"><b>Department of Education - Division of Laguna</b></a> 
                            and <a href="https://laguna.gov.ph/miso" target="_blank"><b>Provincial Government of Laguna – Management Information Systems Office</b></a>  
                            agreed to develop the Students Test Analysis and Record System (STARS) 
                            to ease the analyzation of student’s assessment result and provide automated reports containing 
                            data that can be used in the decision-making of the institution.
                            
                        </p>
                        <div class="text-center text-lg-start">
                        <a href="#features" class="btn-read-more scrollto d-inline-flex align-items-center justify-content-center align-self-center">
                            <span>Features</span>
                            <i class="bi bi-arrow-right"></i>
                        </a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 d-flex align-items-center" data-aos="zoom-out" data-aos-delay="200">
                    <img src="/images/landing/pgl.jpg" class="img-fluid" alt="">
                </div>
            </div>
        </div>
    </section><!-- End About Section -->

    <!-- ======= features Section ======= -->
    <section id="features" class="values features">
        <div class="container" data-aos="fade-up">
            <header class="section-header">
                <h2>What the system has to offer?</h2>
                <p>Features</p>
            </header>
            <div class="row">
                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="box">
                        <img src="/images/landing/scanner.png" class="img-fluid" alt="">
                        <h3>Answer Sheet Scanner</h3>
                        <p>Automated checking of answer sheets through mobile application. 
                            <br><b><a href="https://exscanner.edu.vn/deped-ph" target="_blank">ExScanner</a></b>
                        </p>
                    </div>
                </div>
                <div class="col-lg-4 mt-4 mt-lg-0" data-aos="fade-up" data-aos-delay="400">
                    <div class="box">
                        <img src="/images/landing/reports.png" class="img-fluid" alt="">
                        <h3>Automated Reports</h3>
                        <p>Downloadable and printable reports such as Assessment Result, Score Analysis, Item Analysis and Discrimination Index</p>
                    </div>
                </div>
                <div class="col-lg-4 mt-4 mt-lg-0" data-aos="fade-up" data-aos-delay="600">
                    <div class="box">
                        <img src="/images/landing/records.png" class="img-fluid" alt="">
                        <h3>Record Management</h3>
                        <p>An efficient and systematic control of managing students' academic records.</p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-5 p-5">
                    <img src="/images/landing/more.png" class="img-fluid" alt="">
                </div>
                <div class="col-lg-7 mt-5 mt-lg-0 d-flex">
                    <div class="row align-self-center gy-4">

                        <div class="col-md-6" data-aos="zoom-out" data-aos-delay="200">
                        <div class="feature-box d-flex align-items-center">
                            <i class="bi bi-check"></i>
                            <h3>User-Friendly Interface</h3>
                        </div>
                        </div>

                        <div class="col-md-6" data-aos="zoom-out" data-aos-delay="300">
                        <div class="feature-box d-flex align-items-center">
                            <i class="bi bi-check"></i>
                            <h3>Mobile View Compatible</h3>
                        </div>
                        </div>

                        <div class="col-md-6" data-aos="zoom-out" data-aos-delay="400">
                        <div class="feature-box d-flex align-items-center">
                            <i class="bi bi-check"></i>
                            <h3>Effective and Efficient</h3>
                        </div>
                        </div>

                        <div class="col-md-6" data-aos="zoom-out" data-aos-delay="500">
                        <div class="feature-box d-flex align-items-center">
                            <i class="bi bi-check"></i>
                            <h3>Continuous Support</h3>
                        </div>
                        </div>

                        <div class="col-md-6" data-aos="zoom-out" data-aos-delay="600">
                        <div class="feature-box d-flex align-items-center">
                            <i class="bi bi-check"></i>
                            <h3>Zero Cost Development</h3>
                        </div>
                        </div>

                        <div class="col-md-6" data-aos="zoom-out" data-aos-delay="700">
                        <div class="feature-box d-flex align-items-center">
                            <i class="bi bi-check"></i>
                            <h3>More Family Time &#x2764;</h3>
                        </div>
                        </div>

                    </div>
                </div>
            </div> <!-- / row -->
        </div>
    </section><!-- End Features Section -->


<!-- ======= Counts Section ======= -->
{{-- <section id="counts" class="counts">
    <div class="container" data-aos="fade-up">

    <div class="row gy-4">

        <div class="col-lg-4 col-md-6">
            <div class="count-box">
                <i class="bi bi-buildings" style="color: #332FD0"></i>
            <div>
            <span data-purecounter-start="0" data-purecounter-end="{{$schools}}" data-purecounter-duration="1" class="purecounter"></span>
            <p>Registered Schools</p>
            </div>
        </div>
        </div>
 
        <div class="col-lg-4 col-md-6">
            <div class="count-box">
                <i class="bi bi-journal" style="color: #332FD0;"></i>
                <div>
                <span data-purecounter-start="0" data-purecounter-end="{{$competencies}}" data-purecounter-duration="1" class="purecounter"></span>
                <p>Competencies</p>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="count-box">
                <i class="bi bi-people" style="color: #332FD0;"></i>
                <div>
                <span data-purecounter-start="0" data-purecounter-end="{{$users}}" data-purecounter-duration="1" class="purecounter"></span>
                <p>Users</p>
                </div>
            </div>
        </div>

    </div>

    </div>
</section><!-- End Counts Section --> --}}

<!-- ======= F.A.Q Section ======= -->
<section id="faq" class="faq">

    <div class="container" data-aos="fade-up">

    <header class="section-header">
        <h2>F.A.Q</h2>
        <p>Frequently Asked Questions</p>
    </header>

    <div class="row">
        <div class="col-lg-6">
        <!-- F.A.Q List 1-->
        <div class="accordion accordion-flush" id="faqlist1">
            <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq-content-1">
                Where can I get the template of Uploaders?
                </button>
            </h2>
            <div id="faq-content-1" class="accordion-collapse collapse" data-bs-parent="#faqlist1">
                <div class="accordion-body">
                    After login in, go to your dashboard and select the "Uploader Widget" to get the necessary template.
                </div>
            </div>
            </div>

            <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq-content-2">
                Where should I print the answer sheet?
                </button>
            </h2>
            <div id="faq-content-2" class="accordion-collapse collapse" data-bs-parent="#faqlist1">
                <div class="accordion-body">
                    Answer sheet must be printed on A4-sized paper that is scaled into Actual Size or Default.
                </div>
            </div>
            </div>

        </div>
        </div>

        <div class="col-lg-6">

        <!-- F.A.Q List 2-->
        <div class="accordion accordion-flush" id="faqlist2">

            <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2-content-1">
                Is there a community for the system support?
                </button>
            </h2>
            <div id="faq2-content-1" class="accordion-collapse collapse" data-bs-parent="#faqlist2">
                <div class="accordion-body">
                There's a public group available on Facebook. You can join by clicking this
                <a href="https://www.facebook.com/groups/841376010423855/?hoisted_section_header_type=recently_seen&multi_permalinks=883910942837028"
                target="_blank">Link</a> 
                </div>
            </div>
            </div>

            

            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq-content-3">
                        Is there a pencil that the application prefer to use for shading the answer sheet?
                    </button>
                </h2>
                <div id="faq-content-3" class="accordion-collapse collapse" data-bs-parent="#faqlist1">
                    <div class="accordion-body">
                        A No.2 Pencil or any equivalent is required in shading the answer sheets.
                    </div>
                </div>
            </div>
            

        </div>
        </div>

    </div>

    </div>

</section><!-- End F.A.Q Section -->


<!-- ======= Team Section ======= -->
<section id="team" class="team">

    <div class="container" data-aos="fade-up">

    <header class="section-header">
        <h2>Team</h2>
        <p>Our hard working team</p>
    </header>

    <div class="row gy-4">
        <div class="col-lg-2 col-md-6 d-flex align-items-stretch" data-aos="fade-up" data-aos-delay="100">
            <div class="member">
                <div class="member-img">
                <img src="/images/landing/team1.png" class="img-fluid" alt="">
              
                </div>
                <div class="member-info">
                <h4>Editha M. Atendido, CESO V</h4>
                <span>Schools Division Superintendent</span>
                <p>
                    {{-- Description --}}
                </p>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-md-6 d-flex align-items-stretch" data-aos="fade-up" data-aos-delay="200">
            <div class="member">
                <div class="member-img">
                <img src="/images/landing/team2.png" class="img-fluid" alt="">
              
                </div>
                <div class="member-info">
                    <h4>Arlene S. Ricasata</h4>
                    <span>Asst. Schools Division Superintendent</span>
                    <p>
                        {{-- Description --}}
                    </p>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-md-6 d-flex align-items-stretch" data-aos="fade-up" data-aos-delay="300">
            <div class="member">
                <div class="member-img">
                <img src="/images/landing/team3.png" class="img-fluid" alt="">
              
                </div>
                <div class="member-info">
                    <h4>Elvira B. Catangay</h4>
                    <span>Asst. Schools Division Superintendent</span>
                    <p>
                        {{-- Description --}}
                    </p> 
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-md-6 d-flex align-items-stretch" data-aos="fade-up" data-aos-delay="400">
            <div class="member">
                <div class="member-img">
                <img src="/images/landing/team4.png" class="img-fluid" alt="">
              
                </div>
                <div class="member-info">
                    <h4>Orlando T. Valvuerde</h4>
                    <span>Chief - CID</span>
                    <p>
                        {{-- Description --}}
                    </p> 
                </div>
            </div>
        </div>

        
        <div class="col-lg-2 col-md-6 d-flex align-items-stretch" data-aos="fade-up" data-aos-delay="400">
            <div class="member">
                <div class="member-img">
                <img src="/images/landing/team5.png" class="img-fluid" alt="">
              
                </div>
                <div class="member-info">
                    <h4>Darwin S. Talumbayan</h4>
                    <span>Chief - SGOD</span>
                    <p>
                        {{-- Description --}}
                    </p> 
                </div>
            </div>
        </div>

        
        <div class="col-lg-2 col-md-6 d-flex align-items-stretch" data-aos="fade-up" data-aos-delay="400">
            <div class="member">
                <div class="member-img">
                <img src="/images/landing/team6.png" class="img-fluid" alt="">
              
                </div>
                <div class="member-info">
                    <h4>Angela F. Latina</h4>
                    <span>Administrative Officer V</span>
                    <p>
                        {{-- Description --}}
                    </p> 
                </div>
            </div>
        </div>

    </div>

    
    <header class="section-header  mt-4"  data-aos="fade-up"> 
        <br>
        <p>System Development Team</p>
    </header>

    <div class="row gy-4">
        <div class="col-lg-3 col-md-6 d-flex align-items-stretch" data-aos="fade-up" data-aos-delay="600">
            <div class="member">
                <div class="member-img">
                <img src="/images/landing/zaide.jpg " class="img-fluid" alt="">
              
                </div>
                <div class="member-info">
                    <h4>Frederick B. Zaide</h4>
                    <span>Education Program Supervisor</span>
                    <p>
                        
                        Project Development Head
                    </p> 
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 d-flex align-items-stretch" data-aos="fade-up" data-aos-delay="600">
            <div class="member">
                <div class="member-img">
                <img src="/images/landing/garcia.png" class="img-fluid" alt="">
              
                </div>
                <div class="member-info">
                    <h4>Jaime M. Garcia</h4>
                    <span>Officer In Charge</span>
                    <p>
                        Provincial Government  of Laguna - Management Information Systems Office
                    </p>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 d-flex align-items-stretch" data-aos="fade-up" data-aos-delay="600">
            <div class="member">
                <div class="member-img">
                <img src="/images/landing/abrazaldo.png" class="img-fluid" alt="">
              
                </div>
                <div class="member-info">
                    <h4>Roi Vinson D. Abrazaldo</h4>
                    <span>System Development Team Head</span>
                    <p>
                        Provincial Government  of Laguna - Management Information Systems Office
                    </p> 
                </div>
            </div>
        </div>

        
        <div class="col-lg-3 col-md-6 d-flex align-items-stretch" data-aos="fade-up" data-aos-delay="600">
            <div class="member">
                <div class="member-img">
                <img src="/images/landing/afurong.jpg" class="img-fluid" alt="">
                </div>
                <div class="member-info">
                    <h4>Glenn Nerrie A. Afurong</h4>
                    <span>Main System Developer</span>
                    <p>
                        Provincial Government  of Laguna - Management Information Systems Office
                    </p> 
                </div>
            </div>
        </div>

    </div>
    </div>

</section><!-- End Team Section -->



<!-- ======= Contact Section ======= -->
<section id="contact" class="contact">

    <div class="container" data-aos="fade-up">

    <header class="section-header">
        <h2>Contact</h2>
        <p>Contact Us</p>
    </header>

    <div class="row gy-4">

        <div class="col-md-3">
        <div class="info-box h-100">
            <i class="bi bi-geo-alt"></i>
            <h3>Address</h3>
            <b>DepEd - Laguna</b>
            <p>DepEd Building, Provincial Capitol Compound <br> Santa Cruz, Laguna 4009</p><br>
            <b>PGL - MISO</b>
            <p>2F San Luis Bldg., Provincial Capitol Compound <br> Santa Cruz, Laguna 4009</p>
        </div>
        </div>
        <div class="col-md-3">
        <div class="info-box h-100">
            <i class="bi bi-telephone"></i>
            <h3>Call Us</h3>
            <b>DepEd - Laguna</b>
            <p>049-831-9062 <br>049-831-9064</p><br>
            <b>PGL - MISO</b>
            <p>501-4261</p>
        </div>
        </div>
        <div class="col-md-3">
        <div class="info-box h-100">
            <i class="bi bi-envelope"></i>
            <h3>Email Us</h3>
            <b>DepEd - Laguna</b>
            <p>laguna@deped.gov.ph</p><br>
            <b>PGL - MISO</b>
            <p>miso@laguna.gov.ph</p>
        </div>
        </div>
        <div class="col-md-3">
        <div class="info-box h-100">
            <i class="bi bi-clock"></i>
            <h3>Open Hours</h3>
            <b>DepEd - Laguna</b>
            <p>Monday - Friday<br>8:00AM - 05:00PM</p><br>
            <b>PGL - MISO</b>
            <p>Monday - Friday<br>8:00AM - 05:00PM</p>
        </div>
        </div>

    </div>

    </div>

</section><!-- End Contact Section -->

</main><!-- End #main -->

<!-- ======= Footer ======= -->
<footer id="footer" class="footer">
<div class="footer-top">
    <div class="container">
    <div class="row gy-4">
        <div class="col-lg-3 col-md-12 footer-info">
        <a href="index.html" class="logo d-flex align-items-center">
            <img src="/images/logo_xs.png" alt="">
            <span>STARS</span>
        </a>
        <p>Students Test Analysis and Records System</p>
        <div class="social-links mt-3">
            <a href="#" class="twitter"><i class="bi bi-twitter"></i></a>
            <a href="#" class="facebook"><i class="bi bi-facebook"></i></a>
            <a href="#" class="instagram"><i class="bi bi-instagram"></i></a>
            <a href="#" class="linkedin"><i class="bi bi-linkedin"></i></a>
        </div>
        </div>
        <div class="col-lg-3 col-3 footer-links">
            <h4>Useful Links</h4>
            <ul>
                <li><i class="bi bi-chevron-right"></i> <a href="https://depedlaguna.com.ph">DepEd Laguna</a></li>
                <li><i class="bi bi-chevron-right"></i> <a href="https://laguna.gov.ph">Provincial Government of Laguna</a></li>
                <li><i class="bi bi-chevron-right"></i> <a href="https://exscanner.edu.vn/deped-ph/">ExScanner</a></li>
            </ul>
        </div>
        <div class="col-lg-3 col-3 footer-links">
            <center>
                
            <img src="/images/una.png" style="height:200px">
            </center>
        </div>
        <div class="col-lg-3 col-3 footer-links">
            <img src="/images/matatag.png" style="width:100%">
        </div>
    </div>
    </div>
</div>

<div class="container">
    <div class="copyright">
    &copy; Copyright <strong><span>STARS</span></strong>. All Rights Reserved
    </div>
    <div class="credits">
    <!-- All the links in the footer should remain intact. -->
    <!-- You can delete the links only if you purchased the pro version. -->
    <!-- Licensing information: https://bootstrapmade.com/license/ -->
    <!-- Purchase the pro version with working PHP/AJAX contact form: https://bootstrapmade.com/flexstart-bootstrap-startup-template/ -->
    Template by <a href="#">BootstrapMade</a>
    </div>
</div>
</footer><!-- End Footer -->

<a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

<!-- Vendor JS Files -->
<script src="/vendor/FlexStart/assets/vendor/purecounter/purecounter_vanilla.js"></script>
<script src="/vendor/FlexStart/assets/vendor/aos/aos.js"></script>
<script src="/vendor/FlexStart/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="/vendor/FlexStart/assets/vendor/glightbox/js/glightbox.min.js"></script>
<script src="/vendor/FlexStart/assets/vendor/isotope-layout/isotope.pkgd.min.js"></script>
<script src="/vendor/FlexStart/assets/vendor/swiper/swiper-bundle.min.js"></script>
<script src="/vendor/FlexStart/assets/vendor/php-email-form/validate.js"></script>

<!-- Template Main JS File -->
<script src="/vendor/FlexStart/assets/js/main.js"></script>
{{-- <script src="/js/logo.js"></script> --}}


</body>

</html>