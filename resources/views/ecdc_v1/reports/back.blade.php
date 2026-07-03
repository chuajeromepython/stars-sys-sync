<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>STARS - ECDC Print Back</title>

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
		table.bordered, table.bordered tr, table.bordered td, table.bordered th {
			border: solid 1px black;
			border-collapse: collapse;
		}
		.bordered{
			width: 100%;
		}
		.border-bottom{
			border-bottom: solid 1px;
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
				@foreach($data as $key =>  $domain)
					@if($key >= 7)
						<table class="bordered">
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
				<table class="bordered">
					<thead>
						<tr>
							<th colspan="3" class="bg-info">
								ECD SUMMARY REPORT
							</th>
						</tr>
						<tr>
							<td></td>
							<th colspan="2">BoSY</th>
						</tr>
						<tr>
							<th>Date Tested</th>
							<th colspan="2">{{$ecdc['date']}}</th>
						</tr>
						<tr>
							<th>Pupil's Age</th>
							<th colspan="2">{{$result['age']}}</th>
						</tr>
						<tr>
							<th>Domains</th>
							<th>Raw Score</th>
							<th>Scaled Score</th>
						</tr>
					</thead>
					<tbody>
						@foreach($result['domains'] as $key => $row)
							<tr>
								<td>{{$data[$key]['domain']}}</td>
								
								<td><center>{{$row['score']}}</center></td>
								<td><center>{{$row['scaled_score']}}</center></td>
							</tr>
						@endforeach
						<tr>
							<th>Sum of Scaled Score</th>
							<th colspan="2">{{$result['total_scaled_score']}}</th>
						</tr>
						<tr>
							<th>Standard Score</th>
							<th  colspan="2">{{$result['standard_score']}}</th>
						</tr>
					</tbody>
				</table>
				<center>
					<p><br><p><br>
					<b>INTERPRETATION</b>
					<p>{{$result['interpretation']}}
					<p><br><p><br>
					<br>_______________________________________
					<br>Parent/Guardian’s Signature
				</center>
			</div>
			<div class="col">
				<table style="width: 100%;">
					<tr>
						<td><img src="/images/deped_national_logo.png" height="70"></td>
						<th>
							<span class="text-old text-sm">Republic of the Philippines</span><br>
							<span class="text-old text-xl">Department of Education</span><br>
							<span class="text-md">Region IV-A CALABARZON</span><br>
							<span class="text-md">DIVISION OFFICE OF LAGUNA</span><br>
						</th>
					</tr>
				</table>

				<br>

				<center>
					<b><span class="text-xl">SCHOOL NAME</span></b><br>
					<b><span class="text-lg">SY 2021 - 2022</span></b>
				</center>
				<br>
				<b>Sociodemographic Profile
				<p>
				<table style="width: 100%;" class="text-md">
					<tr>
						<td style="width:30%;">Child's Name:</td>
						<td class="border-bottom"><center>{{$user->last_name}}</center></td>
						<td class="border-bottom"><center>{{$user->first_name}}</center></td>
						<td class="border-bottom"><center>{{$user->middle_name[1]}}.</center></td>
					</tr>
					<tr>
						<td></td>
						<td class="text-xs"><center><i>Last Name</i></center></td>
						<td class="text-xs"><center><i>First Name</i></center></td>
						<td class="text-xs"><center><i>M.I.</i></center></td>
					</tr>
				</table>
				<table class="text-md" style="width: 100%;">
					<tr>
						<td style="width:29%;">Date of Birth:</td>
						<td class="border-bottom" style="width:28%"><center>{{$user->birth_date}}</center></td>
						<td style="width: 10%;">Age:</td>
						<td class="border-bottom" style="width: 10%;"><center>{{$result['age']}}</center></td>
						<td style="width: 10%;">Sex:</td>
						<td class="border-bottom" style="width: 10%;"><center>{{$user->gender}}</center></td>
					</tr>
					<tr>
						<td></td>
						<td><center><i>month/day/year</i></center></td>
						<td></td>
						<td><center><i>y/m</i></center></td>
						<td></td>
						<td></td>
					</tr>
					<tr>
						<td>Address:</td>
						<td class="border-bottom" colspan="5"><center></center></td>
					</tr>
					<tr>
						<td></td>
						<td class="text-xs" colspan="5">
							<i>Barangay &nbsp; Municipality &nbsp; Province &nbsp; Region</i>
						</td>
					</tr>
				</table>
				<p>
				<table style="width:100%" class="text-md">
					<tr>
						<td>Child’s Handedness</td>
						<td>&#11036; Right</td>
						<td>&#11036; Left</td>
					</tr>
					<tr>
						<td></td>
						<td>&#11036; Both</td>
						<td>&#11036; Not Yet Established</td>
					</tr>
					<tr><td colspan="3"><p></td></tr>
					<tr>
						<td colspan="2">Is the child presently studying? </td>
						<td>&#11036; Yes &nbsp; &#11036; No</td>
					</tr>
				</table>
				<p>
				<table style="width:100%" class="text-md">
					<tr>
						<td style="width:38%">Father's Name:</td>
						<td class="border-bottom"><center></center></td>
						<td style="width:10%">Age:</td>
						<td class="border-bottom" style="width:8%"><center></center></td>
					</tr>
					<tr>
						<td style="width:38%">Father's Occupation:</td>
						<td class="border-bottom" colspan="3"><center></center></td>
					</tr>
					<tr>
						<td style="width:38%">Ed. Attainment:</td>
						<td class="border-bottom" colspan="3"><center></center></td>
					</tr>
					<tr>
						<td style="width:38%">Mother's Name:</td>
						<td class="border-bottom"><center></center></td>
						<td style="width:10%">Age:</td>
						<td class="border-bottom" style="width:8%"><center></center></td>
					</tr>
					<tr>
						<td style="width:38%">Mother's Occupation:</td>
						<td class="border-bottom" colspan="3"><center></center></td>
					</tr>

					<tr>
						<td style="width:38%">Ed. Attainment:</td>
						<td class="border-bottom" colspan="3"><center></center></td>
					</tr>
				</table>
				<table style="width:100%" class="text-md">
					<tr>
						<td style="width:45%">Child's Number of Siblings:</td>
						<td class="border-bottom"><center></center></td>
					</tr>
					<tr>
						<td style="width:45%">Child's Birth Order: </td>
						<td class="border-bottom"><center></center></td>
					</tr>
				</table>
				<table style="width:100%" class="text-md">
					<tr>
						<td style="width:10%">LRN:</td>
						<td class="border-bottom"><center></center></td>
					</tr>
				</table>
				<br><br>
				<div class="text-md" 
				style="text-align: justify; text-indent: 30px;
	  			text-justify: inter-word;">
	  				Ang Philippine Early Childhood and Development Checklist (Form 2) ay nagtataglay ng mga kakayahan, ugali at kaalaman ng mga batang 3 taon hanggang 5.11 taon. Ito ay maaaring gamiting gabay sa pagkilala ng inyong anak at sa kalaunan ay makagawa ng angkop na pag-aalaga, pagtuturo at paggabay sa kanilang pagpapalaki at pag-unlad.
	  			</div>
	  			<br><br><br><br>
	  			<table style="width:100%" class="text-md">
	  				<tr>
	  					<td class="border-bottom" style="width:45%;"><center></center></td>
	  					<td><center></center></td>
	  					<td class="border-bottom" style="width:45%;"><center></center></td>
	  				</tr>
	  				<tr>
	  					<td class="text-xs" style="width:45%;"><center>
	  						<i>Guro</i>
	  					</center></td>
	  					<td><center></center></td>
	  					<td class="text-xs" style="width:45%;"><center>
	  						<i>Punongguro</i>
	  					</center></td>
	  				</tr>
	  			</table>
			</div>
		</div>
	</div>
</body>
</html>