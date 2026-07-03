<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>STARS | Error 403</title>
    <link rel="icon" href="{{ url('images/favicon.png') }}">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="/vendor/AdminLTE3/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="/vendor/AdminLTE3/dist/css/adminlte_modified.css">
</head>
<body class="hold-transition lockscreen">
<div class="lockscreen-wrapper">
    <div class="lockscreen-logo">
        <a href="/dashboard"><b><img src="/images/logo.png" width="50" height="50"> STARS</b></a>
    </div>
    <section class="content">
        <div class="error-page">

            <h2 class="headline text-danger">
                403
            </h2>
            <div class="error-content">

              <h3><i class="fas fa-exclamation-triangle text-danger"></i> Oops! Unathorized Action.</h3>
               
                <span class="text-bold">Hello {{Auth::user()->person->first_name}} , </span>
                Looks like you're trying to access a page without permission.
                Meanwhile, you may return to <a href="/dashboard" class="text-bold">DASHBOARD 
                    <i class="fa fa-arrow-right"></i></a>
              
            </div>
        </div>
    </section>
   
</div>



<script src="/vendor/AdminLTE3/plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="/vendor/AdminLTE3/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
