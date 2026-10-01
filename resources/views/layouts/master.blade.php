<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="csrf-token" content="{{ csrf_token() }}" />
	{{-- <title>{{ config('app.name') }} | @yield('page_name')</title> --}}
	<title>{{ config('app.name') }} | {{$page['title']}}</title>
	<!-- Google Font: Source Sans Pro -->
	<link rel="stylesheet" href="/css/googlefonts.css">
	<!-- Font Awesome -->
	<link rel="stylesheet" href="/vendor/AdminLTE3/plugins/fontawesome-free/css/all.min.css">
	<link rel="icon" href="{{ url('images/favicon.png') }}">
	<!-- Theme style -->
	<link rel="stylesheet" href="/vendor/AdminLTE3/dist/css/adminlte_modified.css">
	<!-- Select2 -->
  	<link rel="stylesheet" href="/vendor/AdminLTE3/plugins/select2/css/select2.min.css">
  	<link rel="stylesheet" href="/vendor/AdminLTE3/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
  	<!-- DataTables -->
	<link rel="stylesheet" href="/vendor/DataTables/datatables.min.css">

	{{-- pace --}}
	<link rel="stylesheet" href="/vendor/AdminLTE3/plugins/pace-progress/themes/blue/pace-theme-flash.css">
	{{-- Toastr --}}
	<link rel="stylesheet" href="/vendor/AdminLTE3/plugins/toastr/toastr.min.css">
	<link rel="stylesheet" href="/css/app.css">
	{{-- Modern table skin: must load last so it overrides the vendor
	     DataTables stylesheet and app.css. --}}
	<link rel="stylesheet" href="/css/datatables-modern.css">
    @yield('page_css')
</head>
<body class="hold-transition sidebar-mini">
<!-- Site wrapper -->
	<div class="wrapper" style="height:100%;">
	  <!-- Navbar -->
		
		<!-- /.navbar -->

		@include('layouts.header')	
		<!-- Main Sidebar Container -->
		@include('layouts.sidebar')

		<!-- Content Wrapper. Contains page content -->
		<div class="content-wrapper">
			{{-- @include('layouts.header') --}}

			<!-- Main content -->
			<section class="content-header">
			   <div class="container-fluid">
					<div class="row mb-2">
						<div class="col-sm-6">
							<h1>@yield('page_title')</h1>
						</div>
						<div class="col-sm-6">
							<ol class="breadcrumb float-sm-right">
								@include('layouts.crumb')
							</ol>
						</div>
					</div>
				</div><!-- /.container-fluid -->
			</section>
			<section class="content">
				@yield('content')
			</section>
			<!-- /.content -->
		</div>
		  <!-- /.content-wrapper -->
		  {{-- Footer --}}
		@include('layouts.footer')
	</div>

{{-- Section Script --}}
<script src="/vendor/AdminLTE3/plugins/jquery/jquery.min.js"></script>
<script src="/vendor/AdminLTE3/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="/vendor/AdminLTE3/dist/js/adminlte.min.js"></script>
{{-- select 2 --}}
<script src="/vendor/AdminLTE3/plugins/select2/js/select2.full.min.js"></script>
{{-- DataTables 3.1.2 combined build (core + Bootstrap 4 + Buttons, Responsive,
     Select, ColumnControl, SearchBuilder, StateRestore). Replaces the old
     AdminLTE3 1.11.4 plugins; loading only one build avoids double registration. --}}
<script src="/vendor/DataTables/datatables.min.js"></script>
{{-- Shared defaults (empty state, control wording, loading indicator) for the
     modern table skin. Loaded before the page scripts so every table inherits
     them. --}}
<script src="/js/datatables-modern.js"></script>
<script src="/vendor/AdminLTE3/plugins/jszip/jszip.min.js"></script>
<script src="/vendor/AdminLTE3/plugins/pdfmake/pdfmake.min.js"></script>
<script src="/vendor/AdminLTE3/plugins/pdfmake/vfs_fonts.js"></script>
<script src="/vendor/AdminLTE3/plugins/pace-progress/pace.min.js"></script>
<script src="/vendor/AdminLTE3/plugins/toastr/toastr.min.js"></script>
<script src="/vendor/AdminLTE3/plugins/bs-custom-file-input/bs-custom-file-input.min.js"></script>
<script src="/js/app.js"></script>

@yield('page_script')

</body>
</html>
