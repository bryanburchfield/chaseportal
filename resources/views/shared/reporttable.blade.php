@if(count($results) > 0)

    <table class="table table-hover reports_table table-striped scrollTable">
        <thead class="fixedHeader">
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
            @if(isset($rec['isSubtotal']))
                @unset($rec['isSubtotal'])
                class="report_subtotals"
            @endif
            @if($params['hasTotals'] && $loop->last)
                class="report_totals"
            @endif
            style='word-break:normal;'>

            @foreach($rec as $k => $v)
                @if (strpos($v, '<audio controls') !== false)
                    <td>{!! $v !!}</td>
                @else
                    <td>{{ $v }}</td>
                @endif
            @endforeach
            </tr>
        @endforeach
        </tbody>
    </table>
@endif
