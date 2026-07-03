<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Print Front</title>

	<style type="text/css">
		@page {
			width: 11in;
			height: 8.5in;
			margin: 0;
		}
		@media print {
			
			
		}
		.page{
			width: 11in;
			height: 8.5in;
			/*border:  solid 1px black;*/
			padding: 10px;
			font-size: 11px;
		}
		.col {
			float: left;
			width: 31.5%;
			padding: 5px;
			margin-left: 7px;
			/*border:  solid 1px black;*/
		}

		.row:after {
			content: "";
			display: table;
			clear: both;
		}
		table, tr, td, th {
			border: solid 1px black;
			border-collapse: collapse;
		}

		.bg-red{background-color: #EA2027; color: white;}
		.bg-orange{background-color: #EE5A24; color: white;}
		.bg-yellow{background-color: #FFC312; color: white;}
		.bg-green{background-color: #009432; color: white;}
		.bg-primary{background-color: #0652DD; color: white;}
		.bg-info{background-color: #12CBC4; color: white;}
		.bg-purple{background-color: #833471; color: white;}

	</style>

</head>
<body onload="window.print()">
	<div class="page">
		<div class="row">
			<div class="col">
				<b>Nilalaman:</b><br>
				&nbsp; Ang bawat bata ay nagtataglay ng iba’t ibang antas ng pag-unlad
				gaya ng mga sumusunod kung saan binibilang ang iskor:
				<ul>
					<li>Gross Motor Domain na may 13 aytems</li>
					<li>Fine Motor Domain na may 11 aytems</li>
					<li>Self-Help Domain na may 27 aytems</li>
					<li>Receptive Language Domain na may 5 aytems</li>
					<li>Expressive Language Domain na may 8 aytems</li>
					<li>Cognitive Domain na may 21 aytems</li>
					<li>Social-Emotional Domain na may 24 aytems</li>
				</ul>
				Ang bawat aytem na naobserbahan ay itatala ng dalawang beses sa isang taon: 
				<br>1) sa simula ng taon (Beginning of School Year-BoSY).
				<br>2) sakatapusan ng taon (End of School Year-EoSY).
				<br>
				@foreach($data as $key =>  $domain)
					@if($key < 3)
						<br>
						<table>
							<thead>
								<tr class="bg-{{$domain['color']}}">
									<th  colspan="2">{{$domain['domain']}}</th>
									<th>P</th>
									<th>O</th>
									<th>R</th>
								</tr>
							</thead>
							<tbody>
								@php $count = 1  @endphp
								@foreach($domain['competencies'] as $competency)

									<tr>
										<td>{{$count}}</td>
										<td>{{$competency->competency}}</td>
										<td>{{$competency->p}}</td>
										<td>{{$competency->o}}</td>
										<td>{{$competency->r}}</td>
									</tr>

								@php $count++  @endphp
								@endforeach
							</tbody>
							<tfoot>
								<tr  class="bg-{{$domain['color']}}">
									<th colspan="2">KABUUANG ISKOR</th>
									<th colspan="3">{{$result['domains'][$key]['score']}}</th>
								</tr>
							</tfoot>
						</table>
					@endif
				@endforeach
			</div>
			<div class="col">
				@foreach($data as $key =>  $domain)
					@if($key >= 3 && $key <= 4)
						<table>
							<thead>
								<tr class="bg-{{$domain['color']}}">
									<th  colspan="2">{{$domain['domain']}}</th>
									<th>P</th>
									<th>O</th>
									<th>R</th>
								</tr>
							</thead>
							<tbody>
								@php $count = 1  @endphp
								@foreach($domain['competencies'] as $competency)

									<tr>
										<td>{{$count}}</td>
										<td>{{$competency->competency}}</td>
										<td>{{$competency->p}}</td>
										<td>{{$competency->o}}</td>
										<td>{{$competency->r}}</td>
									</tr>

								@php $count++  @endphp
								@endforeach
							</tbody>
							<tfoot>
								<tr  class="bg-{{$domain['color']}}">
									<th colspan="2">KABUUANG ISKOR</th>
									<th colspan="3">{{$result['domains'][$key]['score']}}</th>
								</tr>
							</tfoot>
						</table>
						<br>
					@endif
				@endforeach
			</div>
			<div class="col">
				@foreach($data as $key =>  $domain)
					@if($key >= 5 && $key <= 6)
						<table>
							<thead>
								<tr class="bg-{{$domain['color']}}">
									<th  colspan="2">{{$domain['domain']}}</th>
									<th>P</th>
									<th>O</th>
									<th>R</th>
								</tr>
							</thead>
							<tbody>
								@php $count = 1  @endphp
								@foreach($domain['competencies'] as $competency)

									<tr>
										<td>{{$count}}</td>
										<td>{{$competency->competency}}</td>
										<td>{{$competency->p}}</td>
										<td>{{$competency->o}}</td>
										<td>{{$competency->r}}</td>
									</tr>

								@php $count++  @endphp
								@endforeach
							</tbody>
							<tfoot>
								<tr  class="bg-{{$domain['color']}}">
									<th colspan="2">KABUUANG ISKOR</th>
									<th colspan="3">{{$result['domains'][$key]['score']}}</th>
								</tr>
							</tfoot>
						</table>
						<br>
					@endif
				@endforeach
			</div>
		</div>
	</div>
</body>
</html>