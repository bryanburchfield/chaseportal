@if(count($results) > 0)
    <table class="table table-hover reports_table">
        <thead>
            <tr>
                @foreach($params['columns'] as $field => $col)
                <th>
                    <span>{{ $col }}</span>
                    <a href="#" class="sort-by"> <span class="asc"></span><span class="desc"></span></a>
                </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
        @foreach($results as $rec)
            <tr 
            @if($params['hasTotals'] && $loop->last)
                class="report_totals"
            @endif
            style='word-break:break-all;'>

            @foreach($rec as $k => $v)
                <td>{{ $v }}</td>
            @endforeach
            </tr>
        @endforeach
        </tbody>
    </table>
@endif
