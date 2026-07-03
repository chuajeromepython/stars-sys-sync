<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>STARS | ASSESSMENT RESULT</title>
	<link rel="stylesheet" type="text/css" href="/css/page_print.css">
</head>
<body>
	<center>
		<a href="#" class="btn bg-blue" onclick="window.print();">Print</a>
		<a href="/class_assessments/{{$class_assessment->id}}/results/download" class="btn bg-green">Download in Spreadsheet Format</a>

		<div class="page">
			<table class="fixed">
				<tr>
					<th class="w-25">Title</th>
					<td colspan="5">{{$assessment->assessment}}</td>
				</tr>
				<tr>
					<th class="">Teacher</th>
					<td colspan="3">
						{{$class->first_name}}
						{{$class->middle_name}}
						{{$class->last_name}}
						{{$class->suffix}}
					</td>
					<th class="w-20">Date</th>
					<td class="w-30">{{$assessment->date}}</td>
				</tr>
				<tr>
					<th>Type</th>
					<td>Periodical</td>
					<th>Period</th>
					<td>{{$assessment->period}}</td>
					<th>No. of Items</th>
					<td>{{$assessment->number_of_items}}</td>
				</tr>
				<tr>
					<th>Grade Level</th>
					<td class="">{{$assessment->level}}</td>
					<th class="">Section</th>
					<td class="">{{$class->section}}</td>
					<th>Subject</th>
					<td>{{$class->subject}}</td>
				</tr>
			</table>

			<h2>ASSESSMENT RESULT</h2>

			@php $page = 1; $count =1; @endphp

			@foreach($results as $result)
				@if($count == 1)
					@if($page > 1)
					<div class="page">
					@endif
					<table class="fixed">
					<tr>
						<th class="w-30">LRN</th>
						<th>Student</th>
						<th class="w-25">Sex</th>
						<th class="w-25">Score</th>
						<th class="w-25">%</th>
						<th class="w-25">Prof. level</th>
						<th class="w-25">Achi. level</th>
					</tr>
				@endif
				<tr>
					<td>{{$result['lrn']}}</td>
					<td>{{$result['name']}}</td>
					<td>{{$result['gender']}}</td>
					<td>{{$result['score']}}</td>
					<td>{{$result['percentage']}}</td>
					<td>{{$result['proficiency']}}</td>
					<td>{{$result['achievement']}}</td>
					{{-- @foreach($result['answers'] as $answer)
						<td>{{$answer}}</td>
					@endforeach --}}
				</tr>
				@php
					if($page == 1){
						if($count == 20){
							$count = 1;
							$page++;
						}else{
							$count++;
						}
					}else{
						if($count == 25){
							$count = 1;
							$page++;
						}else{
							$count++;
						}
					}
				@endphp
				@if($count == 1)
					</table>
				</div>
				@endif
			@endforeach
	</center>
</body>
</html>