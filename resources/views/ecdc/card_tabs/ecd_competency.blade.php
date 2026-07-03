@foreach ($domain_competencies as $domain_id => $domain)
    <table class="table table-bordered table-sm b-2">
        <tr>
            <th rowspan="2" colspan="2" style="width: 55%; vertical-align: middle">
                
                {{$domain['domain']}}
            </th>
            <th colspan="2" style="width: 15%" class="bl-2">BOSY</th>
            <th colspan="2" style="width: 15%" class="bl-2">MOSY</th>
            <th colspan="2" style="width: 15%" class="bl-2">EOSY</th>
        </tr>
        <tr>
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
                                        "<i class='fa fa-check text-success'></i>" :
                                        ""
                                    !!}
                                </center>
                            </td>
                            <td  class="br-2">
                                <center>
                                    {!!
                                        ($row['score'] == 0) ?
                                        "<i class='fa fa-check text-danger'></i>" :
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
@endforeach


