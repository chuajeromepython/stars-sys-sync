<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title></title>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
	<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.7/dist/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
</head>
<style type="text/css">
	body{
		font-family: "Bahnschrift";
		font-size: 11px;
	}
	table.table-bordered > thead > tr > th{
	  border:1px solid black;
	}
	table.table-bordered > tbody > tr > td{
	  border:1px solid black;
	}
	.bg-primary{
		color: white !important;
		background-color: #332FD0 !important;
	}
</style>
<body>
	<div class="container">
		<div class="row">
			@foreach($tables as $name => $columns)
				<div class="col-md-3 mb-3">
					{{-- <div class="card">
						<div class="card-header">
							<b>{{$name}}</b>
							<a data-toggle="collapse" href="#{{$name}}" aria-expanded="true" 
							aria-controls="test-block" class="float-right btn-primary btn btn-sm">+
                           </a>
						</div>
						<div class="card-body p-0 collapse" id="{{$name}}">
							<table class="table table-bordered">
								@foreach($columns as $column)
									<tr>
										<td><span class="ml-4"> - {{$column}}</span></td>
									</tr>
								@endforeach
							</table>
						</div>
					</div> --}}
					<table class="table table-bordered">
						<thead>
							<tr>
								<th class="text-bold p-1 text-center bg-primary text-white">{{$name}}</th>
							</tr>
						</thead>
						<tbody>
							@foreach($columns as $column)
								<tr>
									<td class="p-1"><span class="ml-2"> {{$column}}</span></td>
								</tr>
							@endforeach
						</tbody>
					</table>
					
				</div>
			@endforeach
		</div>
	</div>
</body>
</html>