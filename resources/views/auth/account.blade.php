@extends('layouts.master')

@section('page_name', $page['name'])

@section('page_title', $page['title'])

@section('page_script')
    <script type="text/javascript" src="/js/accounts.js"></script>
@endsection

@section('content')
	<div class="container">

		@include('layouts.message')
		<div class="row mt-2">
			<div class="col-md-6">
				<div class="card h-100">
					<div class="card-header d-flex justify-content-between align-items-center">
						<label class="text-primary m-0">Mobile Authorization QR</label>
						<a
							href=""
							class="btn btn-sm btn-outline-primary"
						>
						Refresh
					</a>
					</div>
					<div class="card-body text-center">
						<div id="qr-code-wrapper" class="mb-3">{!! $qrData['qr_svg'] !!}</div>
						<p class="text-muted mb-1">Scan this QR code in the mobile app to prefill account details.</p>
						<small class="text-secondary d-block">{{ $qrData['payload']['username'] }}</small>
						<small class="text-secondary d-block">{{ $qrData['payload']['schoolName'] ?: 'No school assigned' }}</small>
					</div>
				</div>
			</div>

			<div class="col-md-6 mt-3 mt-md-0">
				<div class="card h-100">
					<div class="card-header">
						<label class="text-primary m-0">Change Password</label>
					</div>
					<form class="form" method="POST" action="{{ route('account.update_password') }}">
						@csrf()
						<div class="card-body">
							<label class="text-muted">Password</label>
							<div class="input-group mb-2">
								<input type="password" class="form-control" id="password" name="password" required>
								<span class="input-group-append">
									<a type="button" class="btn btn-secondary show-password" data-val="0">
										<i class="fa fa-eye"></i>
									</a>
								</span>
							</div>

							<label class="text-muted">Confirm Password</label>
							<div class="input-group mb-2">
								<input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
								<span class="input-group-append">
									<a type="button" class="btn btn-secondary show-password-confirm" data-val="0">
										<i class="fa fa-eye"></i>
									</a>
								</span>
							</div>

							<div class="col-md-12 mt-2 text-center">
								<button type="submit" class="btn-submit btn btn-primary">Save Changes</button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>	
@endsection