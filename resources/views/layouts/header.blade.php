
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
<!-- Left navbar links -->
	<ul class="navbar-nav">
		<li class="nav-item">
			<a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
		</li>
		<li class="nav-item d-none d-sm-inline-block">
			<a href="/vendor/AdminLTE3/index3.html" class="nav-link">Home</a>
		</li>
	</ul>
	<ul class="navbar-nav ml-auto">
      <!-- Navbar Search -->
	    <li class="nav-item">
	        <a class="nav-link"data-toggle="modal" data-target="#logout_modal" href="#">
	         	<i class="fa fa-power-off"></i>
	        </a>
	    </li>
  	</ul>
</nav>

<div class="modal fade" tabindex="-1" role="dialog"  id="logout_modal">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
        <div class="modal-content">
        	<div class="modal-header">
        		<label class="text-danger">Logout</label>
        	</div>
            <div class="modal-body">
                <center>
                	<br>
                	Are you sure you want to logout?
                	<br>
                </center>
            </div>
            <div class="modal-footer">
			     <a type="button" class="btn btn-sm btn-secondary mr-auto" data-dismiss="modal">Close</a>
			     <a href="/logout" class="btn btn-sm btn-danger">Logout</a>
			</div>
        </div>
    </div>
</div>