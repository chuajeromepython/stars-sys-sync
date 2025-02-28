@extends('layouts.master')

@section('page_name', $page['name'])

@section('page_title', $page['title'])

@section('page_script')
    <script type="text/javascript" src="/js/accounts.js"></script>
@endsection

@section('content')
	<div class="container">

		@include('layouts.message')
		<div class="card mt-2">
			<div class="card-header">
				<label class="text-primary">Change password</label>
			</div>
			<form class="form" action="/account/update_password">
				@csrf()
				<div class="card-body">
					<div class="row">
						<div class="col-md-3">
							<center>
								<img src="/images/Unlock.png" width="150">
							</center>
						</div>
						<div class="col-md-9">
							<div class="row">
								<label class="text-muted">Password</label>
								<div class="input-group mb-2">
									<input type="password" class="form-control" id="password" name="password">
									<span class="input-group-append">
										<a type="button" class="btn btn-secondary show-password" data-val="0">
											<i class="fa fa-eye"></i>
										</a>
									</span>
								</div>
								<label class="text-muted">Confirm Password</label>
								<div class="input-group mb-2">
									<input type="password" class="form-control" id="password_confirm" name="password_confirm">
									<span class="input-group-append">
										<a type="button" class="btn btn-secondary show-password-confirm" data-val="0">
											<i class="fa fa-eye"></i>
										</a>
									</span>
								</div>
								<div class="col-md-12 mt-2">
									<center>
										<button type="submit" id="" class="btn-submit btn btn-primary">Save Changes</button>
									</center>
								</div>
							</div>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>	
@endsection