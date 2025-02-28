<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <style>
        * {
            font-family: "Calibri"
        }
        @media print {
            body{
                margin: 0px;
            }
            .card{
                margin: 20px;
                height:8in;
                width: 13in;
                font-size: 8px;
                /* border: solid 1px red; */
            }
		}
        .card{
            font-size: 9.5px;
            margin: 20pxl
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
        table.table-bordered{
            border-collapse: collapse;
        }
        table.table-bordered td{
            border: solid 0.5px #cccccc;
            
        }

        table.table-bordered th {
            border: solid 0.5px #cccccc;
            
        }
        table.table-bordered tr {
            border: solid 0.5px #cccccc;
            
        }
        table{
            margin-bottom: 10px;
            width:  100%;
        }
        
    </style>

	<link rel="stylesheet" href="/css/app.css">
</head>
<body                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           >
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <b>Nilalaman:</b><br>
                    Ang bawat bata ay nagtataglay ng iba’t ibang antas ng pag-unlad
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
                    Ang bawat aytem na naobserbahan ay itatala ng tatlong beses sa isang taon: 
                    <br>1) sa simula ng taon (Beginning of School Year-BoSY).
                    <br>2) sa kalagitnaan ng taon (Mid of School Year-MoSY).
                    <br>3) sa katapusan ng taon (End of School Year-EoSY).
                    <p>
                    {{-- TABLE --}}
                    @foreach ($domain_competencies as $domain_id => $domain)

                        {{-- RESET ROW --}}
                        @if ($domain_id <= 6)
                            @if ($domain_id == 3 || $domain_id == 5)
                                <div class="col">
                            @endif
                            <table class="table table-bordered table-sm b-2">
                                <tr>
                                    <th rowspan="2" colspan="2" style="width: 75%; vertical-align: middle">
                                        {{$domain['domain']}}
                                    </th>
                                    <th colspan="3" style="width: 8%" class="bl-2">BOSY</th>
                                    <th colspan="3" style="width: 8%" class="bl-2">MOSY</th>
                                    <th colspan="3" style="width: 8%" class="bl-2">EOSY</th>
                                </tr>
                                <tr>
                                    <th class="bl-2 bb-2">P</th>
                                    <th class="bb-2">O</th>
                                    <th class="bb-2">R</th>
                                    <th class="bl-2 bb-2"">P</th>
                                    <th class="bb-2">O</th>
                                    <th class="bb-2">R</th>
                                    <th class="bl-2 bb-2"">P</th>
                                    <th class="bb-2">O</th>
                                    <th class="bb-2">R</th>
                                </tr>
                                @php
                                    $blank = "<td class='bl-2'></td><td></td><td></td>";
                                    $count = 1;
                                @endphp
                                @foreach ($domain['competencies'] as $competency)
                                    <tr>
                                        <td>{{$count++}}</td>
                                        <td>{{$competency->competency}}</td>
                                        @foreach ($periods as $period)
                                            @if ($results[$period] != "No Record")
                                                @if (isset($results[$period]['domains'][$domain_id]["competencies"][$competency->id]))
                                                    @php $row = $results[$period]['domains'][$domain_id]["competencies"][$competency->id] @endphp
                                                    <td class="bl-2">
                                                        <center>
                                                            {!!
                                                                ($row['p'] == 1) ?
                                                                "<span>&#10003;</span>" :
                                                                ""
                                                            !!}
                                                        </center>
                                                    </td>
                                                    <td>
                                                        <center>
                                                            {!!
                                                                ($row['o'] == 1) ?
                                                                "<span>&#10003;</span>" :
                                                                ""
                                                            !!}
                                                        </center>
                                                    </td>
                                                    <td  class="br-2">
                                                        <center>
                                                            {!!
                                                                ($row['r'] == 1) ?
                                                                "<span>&#10003;</span>" :
                                                                ""
                                                            !!}
                                                        </center>
                                                    </td>
                                                @else
                                                    {!! $blank !!}
                                                @endif
                                            @else
                                                {!! $blank !!}
                                            @endif
                                            
                                        @endforeach
                                    </tr>
                                @endforeach
                                <tr class="b-2">
                                    <th class="b-2" colspan="2">KABUUANG ISKOR</th>
                                    @foreach ($periods as $period)
                                        <th class="b-2" colspan="3">
                                            @if ($results[$period] != "No Record")
                                                @if (isset($results[$period]['domains'][$domain_id]["score"]))
                                                    {{$results[$period]['domains'][$domain_id]["score"]}}
                                                @endif
                                            @endif
                                        </th>
                                    @endforeach
                                </tr>
                            </table>      
                            {{-- END ROW --}}
                            @if ($domain_id == 2 || $domain_id == 4 || $domain_id == 6 )
                                </div>
                            @endif
                        @endif
                    @endforeach
                    {{-- ENDTABLE --}}
                </div>
            </div>
        </div>
    </div>
    <p style="page-break-before: always">&nbsp;</p>
    <div class="card">
        <div class="row">
            <div class="col">
                @foreach ($domain_competencies as $domain_id => $domain)

                    {{-- RESET ROW --}}
                    @if ($domain_id == 7)
                        <table class="table table-bordered table-sm b-2">
                            <tr>
                                <th rowspan="2" colspan="2" style="width: 55%; vertical-align: middle">
                                    
                                    {{$domain['domain']}}
                                </th>
                                <th colspan="3" style="width: 15%" class="bl-2">BOSY</th>
                                <th colspan="3" style="width: 15%" class="bl-2">MOSY</th>
                                <th colspan="3" style="width: 15%" class="bl-2">EOSY</th>
                            </tr>
                            <tr>
                                <th class="bl-2 bb-2">P</th>
                                <th class="bb-2">O</th>
                                <th class="bb-2">R</th>
                                <th class="bl-2 bb-2"">P</th>
                                <th class="bb-2">O</th>
                                <th class="bb-2">R</th>
                                <th class="bl-2 bb-2"">P</th>
                                <th class="bb-2">O</th>
                                <th class="bb-2">R</th>
                            </tr>
                            @php
                                $blank = "<td class='bl-2'></td><td></td><td></td>";
                                $count = 1;
                            @endphp
                            @foreach ($domain['competencies'] as $competency)
                                <tr>
                                    <td>{{$count++}}</td>
                                    <td>{{$competency->competency}}</td>
                                    @foreach ($periods as $period)
                                        @if ($results[$period] != "No Record")
                                            @if (isset($results[$period]['domains'][$domain_id]["competencies"][$competency->id]))
                                                @php $row = $results[$period]['domains'][$domain_id]["competencies"][$competency->id] @endphp
                                                <td class="bl-2">
                                                    <center>
                                                        {!!
                                                            ($row['p'] == 1) ?
                                                            "<span>&#10003;</span>" :
                                                            ""
                                                        !!}
                                                    </center>
                                                </td>
                                                <td>
                                                    <center>
                                                        {!!
                                                            ($row['o'] == 1) ?
                                                            "<span>&#10003;</span>" :
                                                            ""
                                                        !!}
                                                    </center>
                                                </td>
                                                <td  class="br-2">
                                                    <center>
                                                        {!!
                                                            ($row['r'] == 1) ?
                                                            "<span>&#10003;</span>" :
                                                            ""
                                                        !!}
                                                    </center>
                                                </td>
                                            @else
                                                {!! $blank !!}
                                            @endif
                                        @else
                                            {!! $blank !!}
                                        @endif
                                        
                                    @endforeach
                                </tr>
                            @endforeach
                            <tr class="b-2">
                                <th class="b-2" colspan="2">KABUUANG ISKOR</th>
                                @foreach ($periods as $period)
                                    <th class="b-2" colspan="3">
                                        @if ($results[$period] != "No Record")
                                            @if (isset($results[$period]['domains'][$domain_id]["score"]))
                                                {{$results[$period]['domains'][$domain_id]["score"]}}
                                            @endif
                                        @endif
                                    </th>
                                @endforeach
                            </tr>
                        </table>     
                    @endif
                @endforeach
            </div>
            <div class="col">
                <table class="table table-bordered table-sm b-2">
                    <tr>
                        <th colspan="7" class="bg-light">
                            <center>
                                ECDC Summary Report
                            </center>
                        </th>
                    </tr>
                    <tr>
                        <th style="width: 25%"></th>
                        <th colspan="2" style="width: 25%">BOSY</th>
                        <th colspan="2" style="width: 25%">MOSY</th>
                        <th colspan="2" style="width: 25%">EOSY</th>
                    </tr>
                    <tr>
                        <th>Date Tested</th>
                        @foreach ($results as $period => $result)
                            <td colspan="2">
                                <center>
                                    {{ ($result != "No Record") ? $result['date_tested'] : ""}}
                                </center>
                            </td>
                        @endforeach    
                    </tr>
                    <tr>
                        <th>Age</th>
                        @foreach ($results as $period => $result)
                            <td colspan="2">
                                <center>
                                    {{ ($result != "No Record") ? $result['age'] : ""}}
                                </center>
                            </td>
                        @endforeach    
                    </tr>
                    <tr>
                        <th>Domains</th>
                        <th>Raw Score</th>
                        <th>Scale Score</th>
                        <th>Raw Score</th>
                        <th>Scale Score</th>
                        <th>Raw Score</th>
                        <th>Scale Score</th>
                    </tr>
                    @foreach ($domains as $domain)
                        <tr>
                            <td>{{$domain->domain}}</td>
                            @foreach ($results as $period => $result)
                                @if  ($result != "No Record")
                                    <td>
                                        <center>
                                            {{ (isset($result["domains"][$domain->id]['score'])) ?
                                            $result["domains"][$domain->id]['score'] : ""}}
                                        </center>
                                    </td>
                                    <td>
                                        <center>
                                            {{ (isset($result["domains"][$domain->id]['scaled_score'])) ?
                                            $result["domains"][$domain->id]['scaled_score'] : ""}}
                                        </center>
                                    </td>
                                    
                                @else
                                    <td></td> 
                                    <td></td>     
                                @endif
                            @endforeach    
                        </tr>
                    @endforeach
                    <tr>
                        <th>Sum of Scaled Scores</th>
                        @foreach ($results as $period => $result)
                            <td colspan="2">
                                <center>
                                    {{ ($result != "No Record") ? $result['total_scaled_score'] : ""}}
                                </center>
                            </td>
                        @endforeach    
                    </tr>
                    <tr>
                        <th>Standard Scores</th>
                        @foreach ($results as $period => $result)
                            <td colspan="2">
                                <center>
                                    {{ ($result != "No Record") ? $result['standard_score'] : ""}}
                                </center>
                            </td>
                        @endforeach    
                    </tr>
                </table>


                <center><h3>INTERPRETATION</h3></center>
                @foreach ($results as $period => $result)
                <h3>{{ strtoupper($period) }}   :
                    <center>
                        @if ($result != "No Record")
                        {{$result['interpretation']}}
                    @endif
                    </center>
                </h3><br>
                <center>
                    __________________________________________
                    <br>Parent/Guardian’s Signature
                </center>
                @endforeach    

            </div>
            <div class="col">
                <table class="" style="width: 100%">
                    <tr>
                        <td>
                            <img src="/images/deped_national_logo.png" width="70" alt="">
                        </td>
                        <td colspan="6">
                            <span>Republic of the Philippines</span><br>
                            <span>Department of Education</span><br>
                            <span>Region IVA- CALABARZON</span><br>
                            <span>SCHOOLS DIVISION OFFICE OF LAGUNA</span>
                        </td>
                    </tr>
                    <tr><td colspan="8" class="text-center"><br></td></tr>
                    <tr>
                        <td colspan="8" class="text-center">
                            <b>SCHOOL NAME</b><br>
                            2019-2022
                        </td>
                    </tr>
                    <tr><td colspan="8" class="text-center"></td></tr>
                    <tr>
                        <td colspan="8" class="text-center">
                            <b>
                                PHILIPPINE EARLY CHILDHOOD <br>
                                DEVELOPMENT CHECKLIST <br>
                                CHILD’S RECORD FORM 2 <br>
                                3.1 – 5.11 Years Old
                            </b>
                        </td>
                    </tr>
                    <tr><td colspan="8" class="text-center"><br></td></tr>
                    <tr>
                        <td colspan="8"><b><i>Sociodemographic Profile</i></b></td>
                    </tr>
                    <tr>
                        <td style="width: 80px;">Child's Name: </td>
                        <td class="bb-1"></td>
                        <td></td>
                        <td colspan="2" class="bb-1"></td>
                        <td></td>
                        <td class="bb-1"><i></i></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan=""><i>Last Name</i></td>
                        <td></td>
                        <td colspan="2"><i>First Name</i></td>
                        <td></td>
                        <td> <i>M.I.</i></td>
                    </tr>
                </table>
                <table class="text-md" style="width: 100%;">
					<tr>
						<td style="width: 80px;">Date of Birth:</td>
						<td class="bb-1" style="width:28%"><center></center></td>
						<td style="width: 40px;">Age:</td>
						<td class="bb-1" style=""><center></center></td>
						<td style="width: 40px;">Sex:</td>
						<td class="bb-1" style=""><center></center></td>
					</tr>
					<tr>
						<td></td>
						<td><i>month/day/year</i></td>
						<td></td>
						<td><i>y/m</i></td>
						<td></td>
						<td><i>F/M</i></td>
					</tr>
					<tr>
						<td>Address:</td>
						<td class="bb-1" colspan="5"><center></center></td>
					</tr>
					<tr>
						<td></td>
						<td class="text-xs" colspan="5">
							<i>Barangay &nbsp; &nbsp; Municipality &nbsp; &nbsp; Province &nbsp; &nbsp; Region</i>
						</td>
					</tr>
				</table>
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
					<tr>
						<td colspan="2">Is the child presently studying? </td>
						<td>&#11036; Yes &nbsp; &#11036; No</td>
					</tr>
				</table>
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
				<table style="width:100%" class="text-md">
					<tr>
						<td style="width:38%">Father's Name:</td>
						<td class="bb-1"><center></center></td>
						<td style="width:10%">Age:</td>
						<td class="bb-1" style="width:8%"><center></center></td>
					</tr>
					<tr>
						<td style="width:38%">Father's Occupation:</td>
						<td class="bb-1" colspan="3"><center></center></td>
					</tr>
					<tr>
						<td style="width:38%">Ed. Attainment:</td>
						<td class="bb-1" colspan="3"><center></center></td>
					</tr>
					<tr>
						<td style="width:38%">Mother's Name:</td>
						<td class="bb-1"><center></center></td>
						<td style="width:10%">Age:</td>
						<td class="bb-1" style="width:8%"><center></center></td>
					</tr>
					<tr>
						<td style="width:38%">Mother's Occupation:</td>
						<td class="bb-1" colspan="3"><center></center></td>
					</tr>

					<tr>
						<td style="width:38%">Ed. Attainment:</td>
						<td class="bb-1" colspan="3"><center></center></td>
					</tr>
				</table>
				<table style="width:100%" class="text-md">
					<tr>
						<td style="width:45%">Child's Number of Siblings:</td>
						<td class="bb-1"><center></center></td>
					</tr>
					<tr>
						<td style="width:45%">Child's Birth Order: </td>
						<td class="bb-1"><center></center></td>
					</tr>
				</table>
				<table style="width:100%" class="text-md">
					<tr>
						<td style="width:10%">LRN:</td>
						<td class="bb-1"><center></center></td>
					</tr>
				</table>
				<div class="text-md" 
				style="text-align: justify; text-indent: 30px;
	  			text-justify: inter-word;">
	  				Ang Philippine Early Childhood and Development Checklist (Form 2) ay nagtataglay ng mga kakayahan, ugali at kaalaman ng mga batang 3 taon hanggang 5.11 taon. Ito ay maaaring gamiting gabay sa pagkilala ng inyong anak at sa kalaunan ay makagawa ng angkop na pag-aalaga, pagtuturo at paggabay sa kanilang pagpapalaki at pag-unlad.
	  			</div>
	  			<br><br>
	  			<table style="width:100%" class="text-md">
	  				<tr>
	  					<td class="bb-1" style="width:45%;"><center></center></td>
	  					<td><center></center></td>
	  					<td class="bb-1" style="width:45%;"><center></center></td>
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
