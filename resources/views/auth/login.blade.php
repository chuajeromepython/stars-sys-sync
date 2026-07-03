<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>STARS | Log in</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="/css/googlefonts.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="/vendor/AdminLTE3/plugins/fontawesome-free/css/all.min.css">
    <!-- icheck bootstrap -->
    <link rel="stylesheet" href="/vendor/AdminLTE3/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <!-- Theme style -->

    <link rel="icon" href="{{ url('images/favicon.png') }}">
    <link rel="stylesheet" href="/vendor/AdminLTE3/dist/css/adminlte_modified.css">
    
</head>
<body class="hold-transition login-page" style="background-image: url('images/login-bg.png');">
    <div class="login-box">
        <div class="card">
            <div class="card-body login-card-body">
                <div class="login-logo">
                    <img src="/images/logo.png" height="50" width="50">
                    <a href="#">STARS</a>
                </div>
                <p class="login-box-msg text-primary text-bold">Students Test Analysis Record System</p>
                @include('layouts.message')
                <form action="/authenticate" method="post">
                    @csrf()
                    <div class="input-group mb-3">
                      <input type="text" name="username" class="form-control" placeholder="Username">
                      <div class="input-group-append">
                        <div class="input-group-text">
                          <span class="fas fa-user"></span>
                        </div>
                      </div>
                    </div>
                    <div class="input-group mb-3">
                      <input type="password" class="form-control" name="password" placeholder="Password">
                      <div class="input-group-append">
                        <div class="input-group-text">
                          <span class="fas fa-lock"></span>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-block">Sign In</button>
                      </div>
                      <!-- /.col -->
                    </div>
                </form>
            </div>
        </div>
          {{-- <center>
            <img src="/images/login-footer.png" class="mt-3" width="250">
          </center> --}}
    </div>
<script src="/vendor/AdminLTE3/plugins/jquery/jquery.min.js"></script>
<script src="/vendor/AdminLTE3/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="/vendor/AdminLTE3/dist/js/adminlte.min.js"></script>
</body>
</html>
