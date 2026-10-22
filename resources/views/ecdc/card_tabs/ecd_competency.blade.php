@foreach ($domain_competencies as $domain_id => $domain)
    <table class="table table-bordered table-sm b-2">
        <tr class="bg-{{$domain['color']}} text-white">
            <th rowspan="2" colspan="2" style="width: 64%; vertical-align: middle">
                
                {{$domain['domain']}}
            </th>
            @foreach ($assessment_periods as $assessment_label)
                <th colspan="3" style="width: 18%" class="bl-2">{{ $assessment_label }}</th>
            @endforeach
        </tr>
        <tr>
            @foreach ($assessment_periods as $assessment_label)
                <th class="bl-2 bb-2">P</th>
                <th class="bb-2">O</th>
                <th class="br-2 bb-2">R</th>
            @endforeach
        </tr>
        @php
            $count = 1;
            $present_types = ['P', 'O', 'R'];
        @endphp
        @foreach ($domain['competencies'] as $competency)
            <tr>
                <td>{{$count++}}</td>
                <td>{{$competency->competency}}</td>
                @foreach ($assessment_periods as $period => $assessment_label)
                    @if ($results[$period] != "No Record")
                        @php
                            $row = collect($results[$period]['domains'][$domain_id]['competencies'] ?? [])
                                ->firstWhere('ecdc_competency_id', $competency->id);
                        @endphp
                        @if ($row)
                            @foreach ($present_types as $present_type)
                                <td @class(['bl-2' => $loop->first, 'br-2' => $loop->last])>
                                    <center>
                                        @if (($row['present_type'] ?? null) === $present_type)
                                            @if (($row['status'] ?? $row['score']) === '1')
                                                <i class="fa fa-check text-success" aria-label="Present"></i>
                                            @else
                                                {{ $row['status'] ?? $row['score'] }}
                                            @endif
                                        @endif
                                    </center>
                                </td>
                            @endforeach
                        @else
                            <td class="bl-2"></td><td></td><td class="br-2"></td>
                        @endif
                    @else
                        <td class="bl-2"></td><td></td><td class="br-2"></td>
                    @endif
                    
                @endforeach
            </tr>
        @endforeach
        <tr class="b-2">
            <th class="b-2" colspan="2">KABUUANG ISKOR</th>
            @foreach ($assessment_periods as $period => $assessment_label)
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
@endforeach


