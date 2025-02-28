<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>STARS | ECDC PRINT</title>
    <style>
        * {
            font-family: "Calibri";
            font-size: 11px;
        }
        @media print {
            body{
                margin: 0px;
                font-size: 11px;
            }
            .card{
                margin: 20px;
                height:7.5in;
                width: 11.7in;
                font-size: 12px;
                /* border: solid 1px black; */
            }
            .pagebreak {
                clear: both;
                page-break-before: always;
            }
		}
        .card{
            margin: 20px;
            height:8.3in;
            width: 11.7in;
            font-size: 12px;
            /* border: solid 1px black; */
        }

        .col {
			float: left;
			width: 48%;
			padding: 5px;
			margin-left: 7px;
			/*border:  solid 1px black;*/
            align-content: left;
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
        .bb{
            border-bottom: solid 1px black;
        }
        .b{
            border: solid 1px black;
        }

        .bg-main-1{background-color: #dc3545;color: white; }
        .bg-sub-1{background-color: #FDA4AF;}
        .bg-main-2{background-color: #fd7e14;color: white; }
        .bg-sub-2{background-color: #FDBA74;}
        .bg-main-3{background-color: #ffc107;color: white; }
        .bg-sub-3{background-color: #FEF08A;}
        .bg-main-4{background-color: #28a745;color: white; }
        .bg-sub-4{background-color: #86EFAC;}
        .bg-main-5{background-color: #332FD0;color: white; }
        .bg-sub-5{background-color: #93C5FD;}
        .bg-main-6{background-color: #17a2b8;color: white; }
        .bg-sub-6{background-color: #67E8F9;}
        .bg-main-7{background-color: #6f42c1;color: white; }
        .bg-sub-7{background-color: #D8B4FE;}
    </style>

	<link rel="stylesheet" href="/css/app.css">
</head>
<body>
    <center>
        {{-- PAGE 1 --}}
        <div class="card"> 
            <div class="row">
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
                    <center>
                        <b>
                            <img src="/images/deped_national_logo.png" alt="" width="70" style="float: left">
                            <img src="/images/deped_logo.png" alt="" width="70" style="float: right">
                            <br>Republic of the Philippines <br>
                            <br>Department of Education
                            <br>Region IV – A CALABARZON
                            <br>Schools Division Office of Laguna
                            <br>{{ $user['district'] }} Sub – Office
                            <br>{{ $user['school'] }}
                            <br>SY {{$academic_year->from}} – {{$academic_year->to}}
                            <br>PHILIPPINE EARLY CHILDHOOD
                            <br>DEVELOPMENT CHECKLIST
                            <br>CHILD’S RECORD FORM 2
                            <br>3.1 – 5.11 Years Old
                        </b>
                    </center>
                    <br>
                    <table>
                        <tr>
                            <td colspan="4"><i>Socio-demographic Profile</i></td>
                        </tr>
                        <tr>
                            <td style="width: 25%"><b>CHILD’S NAME:</b></td>
                            <td class="bb">{{$person->last_name}}</td>
                            <td class="bb">{{$person->first_name}}</td>
                            <td class="bb">{{$person->middle_name}}</td>
                        </tr>
                        <tr>
                            <td style="width: 25%"></td>
                            <th><i>Last Name</i></th>
                            <th><i>First Name</i></th>
                            <th><i>M.I.</i></th>
                        </tr>
                    </table>
                    <table>
                        <tr>
                            <td style="width: 25%"><B>LRN NO.</B></td>
                            @php $lrn = str_split($student->lrn) @endphp
                            @foreach ($lrn as $no)
                                <th class="b"> {{$no}}</th>
                            @endforeach
                        </tr>
                    </table>
                    <table> 
                        <tr>
                            <td style="width: 25%"><b>Date of Birth</b></td>
                            <td class="bb">{{$person->birth_date}}</td>
                            <td style="width: 8%"><b>Age</b></td>
                            <td class="bb">{{$age}}</td>
                            <td style="width: 10%"><b>Gender</b></td>
                            <td class="bb">{{$person->gender}}</td>
                        </tr>
                        <tr>
                            <td style="width: 25%"></td>
                            <th class=""><i>yyyy/dd/mm</i></th>
                            <td style="width: 8%"></td>
                            <th class=""><i>y/m</i></th>
                            <td style="width: 10%"></th>
                            <th class="">M/F</th>
                        </tr>
                    </table>
                    <table>
                        <tr>
                            <td><b>Kadalasang Gamit na Kamay:</b></td>
                            <td> (Lagyan ng tsek ang kahon)</td>
                        </tr>
                        <tr>
                            <td style="width: 40%"></td>
                            <td> &#9744; Kanan &nbsp; &#9744;Kaliwa &nbsp; &#9744;Hindi pa naitatag</td>
                        </tr>
                    </table>
                    <table>
                        <tr>
                            <td style="width: 25%"><b>Father's Name: </b></td>
                            <td class="bb"></td>
                        </tr>
                        <tr>
                            <td style="width: 25%"><b>Father's Occupation: </b></td>
                            <td class="bb"></td>
                        </tr>
                        <tr>
                            <td style="width: 25%"><b>Educational Attainment </b></td>
                            <td class="bb"></td>
                        </tr>
                        <tr>
                            <td style="width: 25%"><b>Mother's Name: </b></td>
                            <td class="bb"></td>
                        </tr>
                        <tr>
                            <td style="width: 25%"><b>Mother's Occupation: </b></td>
                            <td class="bb"></td>
                        </tr>
                        <tr>
                            <td style="width: 25%"><b>Educational Attainment </b></td>
                            <td class="bb"></td>
                        </tr>
                    </table>
                    <br>
                    <table>
                        <tr>
                            <td style="width: 40%;" class="bb"></td>
                            <td style="width: 20%;"></td>
                            <td style="width: 40%;" class="bb"></td>
                        </tr>
                        <tr>
                            <th style="width: 40%;">Guro</th>
                            <th style="width: 20%;"></th>
                            <th style="width: 40%;">Punongguro</th>
                        </tr>
                    </table>

                    <table>
                        <tr>
                            <td style="text-align: justify">
                                <b>Mga Magulang:</b>
                                <p>Ang Philippine Early Childhood Development (Phil. ECD) Checklist ay dinisenyo para magamit ng mga guro, mga
                                naglilingkod para sa pag-unlad ng mga mag-aaral sa mga daycare, mga tagapag-alaga, at mga magulang. Sa
                                pamamagitan nito, matutukoy kung sapat ba o nasa pagkaantala ang pag-unlad ng bata.
                                <p>Ang tseklis na ito ay HINDI inilaan upang magamit sa sumusunod: 1) paggawa ng isang medikal na pagsusuri; 2)
                                pagtukoy sa intelligence quotient (IQ) ng isang bata; o sa 3) pagsukat sa akademikong pagtamo. Ito ay una lamang
                                sa napakaraming hakbang para sa proseso ng komprehensibong pagsusuri sa bata. Sa gayon, ang mga batang
                                matutukoy na may pagkaantala sa kaniyang pag-unlad ay maagap na matutugunan sa kaniyang
                                pangangailangan.
                                <p>Ang tseklis na ito ay para sa mga batang may edad na tatlong taong gulang at isang buwan hanggang limang taon
                                at 11 na buwan. Ang mga aytem sa tseklis ay nahahati sa pitong mga domain: 1) gross motor, 2) fine motor, 3) selfhelp, 4) receptive language, 5) expressive language, 6) cognitive, at 7) socio-emotional.
                                Gagamitin ito para sa mga mag-aaral ng Kindergarten ng Kagawaran ng Edukasyon.
                                Ang mga aytem sa tseklis ay maaaring na oobserbahan na ninyo sa pang-araw-araw na gawain ng inyong mga
                                anak. Kung hindi pa ninyo nakikita o naoobserbahan, maaari ninyong subukang ipagawa sa kanila.
                                Lagyan ng tsek (/) ang angkop na hanay kung ito ay kayang gawin o hindi pa kayang gawin.
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="pagebreak"> </div>
        </div>
        {{-- PAGE 2 --}}
        <div class="card">
            <div class="row">
                @foreach ($domain_competencies as $domain_id => $domain)
                    @if ($domain_id == 1 || $domain_id == 7)
                        <div class="col">
                            <table class="table table-bordered table-sm b-2" style="height:7.5in;">
                                <tr class="bg-main-{{$domain_id}}">
                                    <th rowspan="2" colspan="2" style="width: 55%; vertical-align: middle">
                                        
                                        {{$domain['domain']}}
                                    </th>
                                    <th colspan="2" style="width: 15%" class="bl-2">BOSY</th>
                                    <th colspan="2" style="width: 15%" class="bl-2">MOSY</th>
                                    <th colspan="2" style="width: 15%" class="bl-2">EOSY</th>
                                </tr>
                                <tr class="bg-sub-{{$domain_id}}">
                                    <th class="bl-2 bb-2">Kayang gawin</th>
                                    <th class="br-2 bb-2">Di pa kayang gawin</th> 
                                    <th class="bl-2 bb-2">Kayang gawin</th>
                                    <th class="br-2 bb-2">Di pa kayang gawin</th> 
                                    <th class="bl-2 bb-2">Kayang gawin</th>
                                    <th class="br-2 bb-2">Di pa kayang gawin</th> 
                                </tr>
                                @php
                                    $blank = "<td class='bl-2'></td><td></td>";
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
                                                                ($row['score'] == 1) ?
                                                                "&#10003;" :
                                                                ""
                                                            !!}
                                                        </center>
                                                    </td>
                                                    <td  class="br-2">
                                                        <center>
                                                            {!!
                                                                ($row['score'] == 0) ?
                                                                "&#10003;" :
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
                                <tr class="b-2 bg-sub-{{$domain_id}}">
                                    <th class="b-2" colspan="2">KABUUANG ISKOR</th>
                                    @foreach ($periods as $period)
                                        <th class="b-2" colspan="2">
                                            @if ($results[$period] != "No Record")
                                                @if (isset($results[$period]['domains'][$domain_id]["score"]))
                                                    {{$results[$period]['domains'][$domain_id]["score"]}}
                                                @endif
                                            @endif
                                        </th>
                                    @endforeach
                                </tr>
                            </table>
                        </div>
                    @endif
                @endforeach
            </div>
            <div class="pagebreak"> </div>
        </div>
        {{-- PAGE 3 --}}
        <div class="card">
            <div class="row">
                @foreach ($domain_competencies as $domain_id => $domain)
                    @if ($domain_id == 6 || $domain_id == 2)
                        <div class="col">
                            <table class="table table-bordered table-sm b-2" style="height:7.5in;">
                                <tr class="bg-main-{{$domain_id}}">
                                    <th rowspan="2" colspan="2" style="width: 55%; vertical-align: middle">
                                        
                                        {{$domain['domain']}}
                                    </th>
                                    <th colspan="2" style="width: 15%" class="bl-2">BOSY</th>
                                    <th colspan="2" style="width: 15%" class="bl-2">MOSY</th>
                                    <th colspan="2" style="width: 15%" class="bl-2">EOSY</th>
                                </tr>
                                <tr class="bg-sub-{{$domain_id}}">
                                    <th class="bl-2 bb-2">Kayang gawin</th>
                                    <th class="br-2 bb-2">Di pa kayang gawin</th> 
                                    <th class="bl-2 bb-2">Kayang gawin</th>
                                    <th class="br-2 bb-2">Di pa kayang gawin</th> 
                                    <th class="bl-2 bb-2">Kayang gawin</th>
                                    <th class="br-2 bb-2">Di pa kayang gawin</th> 
                                </tr>
                                @php
                                    $blank = "<td class='bl-2'></td><td></td>";
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
                                                                ($row['score'] == 1) ?
                                                                "&#10003;" :
                                                                ""
                                                            !!}
                                                        </center>
                                                    </td>
                                                    <td  class="br-2">
                                                        <center>
                                                            {!!
                                                                ($row['score'] == 0) ?
                                                                "&#10003;" :
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
                                <tr class="b-2  bg-sub-{{$domain_id}}">
                                    <th class="b-2" colspan="2">KABUUANG ISKOR</th>
                                    @foreach ($periods as $period)
                                        <th class="b-2" colspan="2">
                                            @if ($results[$period] != "No Record")
                                                @if (isset($results[$period]['domains'][$domain_id]["score"]))
                                                    {{$results[$period]['domains'][$domain_id]["score"]}}
                                                @endif
                                            @endif
                                        </th>
                                    @endforeach
                                </tr>
                            </table>
                        </div>
                    @endif
                @endforeach
            </div>
            <div class="pagebreak"> </div>
        </div>
        {{-- PAGE 3 --}}
        <div class="card">
            <div class="row">
                @foreach ($domain_competencies as $domain_id => $domain)
                    @if ($domain_id == 3 || $domain_id == 4 || $domain_id == 5)
                        @if ($domain_id == 3 || $domain_id == 4 )
                            <div class="col">
                        @endif
                            <table class="table table-bordered table-sm b-2">
                                <tr class="bg-main-{{$domain_id}}">
                                    <th rowspan="2" colspan="2" style="width: 55%; vertical-align: middle">
                                        
                                        {{$domain['domain']}}
                                    </th>
                                    <th colspan="2" style="width: 15%" class="bl-2">BOSY</th>
                                    <th colspan="2" style="width: 15%" class="bl-2">MOSY</th>
                                    <th colspan="2" style="width: 15%" class="bl-2">EOSY</th>
                                </tr>
                                <tr class="bg-sub-{{$domain_id}}">
                                    <th class="bl-2 bb-2">Kayang gawin</th>
                                    <th class="br-2 bb-2">Di pa kayang gawin</th> 
                                    <th class="bl-2 bb-2">Kayang gawin</th>
                                    <th class="br-2 bb-2">Di pa kayang gawin</th> 
                                    <th class="bl-2 bb-2">Kayang gawin</th>
                                    <th class="br-2 bb-2">Di pa kayang gawin</th> 
                                </tr>
                                @php
                                    $blank = "<td class='bl-2'></td><td></td>";
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
                                                                ($row['score'] == 1) ?
                                                                "&#10003;" :
                                                                ""
                                                            !!}
                                                        </center>
                                                    </td>
                                                    <td  class="br-2">
                                                        <center>
                                                            {!!
                                                                ($row['score'] == 0) ?
                                                                "&#10003;" :
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
                                <tr class="b-2 bg-sub-{{$domain_id}}">
                                    <th class="b-2" colspan="2">KABUUANG ISKOR</th>
                                    @foreach ($periods as $period)
                                        <th class="b-2" colspan="2">
                                            @if ($results[$period] != "No Record")
                                                @if (isset($results[$period]['domains'][$domain_id]["score"]))
                                                    {{$results[$period]['domains'][$domain_id]["score"]}}
                                                @endif
                                            @endif
                                        </th>
                                    @endforeach
                                </tr>
                            </table>
                        
                        @if ($domain_id == 3 || $domain_id == 5 )
                            </div>
                        @endif
                    @endif
                @endforeach
            </div>
            <div class="pagebreak"> </div>
        </div>
       

    </center>
</body>
</html>