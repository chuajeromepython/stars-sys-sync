<table class="table table-bordered table-sm b-2">
    <tr>
        <th colspan="5" class="bg-light">
            <center>
                ECDC Summary Report
            </center>
        </th>
    </tr>
    <tr>
        <th style="width: 25%"></th>
        <th colspan="2" style="width: 37.5%">BOSY</th>
        <th colspan="2" style="width: 37.5%">EOSY</th>
    </tr>
    <tr>
        <th>Date Tested</th>
        @foreach ($periods as $period)
            @php($result = $results[$period])
            <td colspan="2">
                <center>
                    {{ ($result != "No Record") ? $result['date_tested'] : ""}}
                </center>
            </td>
        @endforeach    
    </tr>
    <tr>
        <th>Age</th>
        @foreach ($periods as $period)
            @php($result = $results[$period])
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
    </tr>
    @foreach ($domains as $domain)
        <tr>
            <td>{{$domain->domain}}</td>
            @foreach ($periods as $period)
                @php($result = $results[$period])
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
        @foreach ($periods as $period)
            @php($result = $results[$period])
            <td colspan="2">
                <center>
                    {{ ($result != "No Record") ? $result['total_scaled_score'] : ""}}
                </center>
            </td>
        @endforeach    
    </tr>
    <tr>
        <th>Standard Scores</th>
        @foreach ($periods as $period)
            @php($result = $results[$period])
            <td colspan="2">
                <center>
                    {{ ($result != "No Record") ? $result['standard_score'] : ""}}
                </center>
            </td>
        @endforeach    
    </tr>
    <tr>
        <th>Interpretation</th>
        @foreach ($periods as $period)
            @php($result = $results[$period])
            <td colspan="2">
                <center>
                    {{ ($result != "No Record") ? $result['interpretation'] : ""}}
                </center>
            </td>
        @endforeach    
    </tr>
</table>
