{{-- @if(count($results) > 0) --}}
    <table class="table table-hover reports_table table-striped scrollTable report_pinned_datatable">
        <thead>
            <tr role="row">
                <th></th>
               {{--  @foreach($params['columns'] as $field => $col)
                <th>
                    <span>{{ $col }}</span>
                    <a href="#" class="sort-by"> <span class="asc"></span><span class="desc"></span></a>
                </th>
                @endforeach --}}
            </tr>
        </thead>
        <tbody>
        {{-- @foreach($results as $rec)
            <tr 
            @if(isset($rec['isSubtotal']))
                @unset($rec['isSubtotal'])
                class="report_subtotals"
            @endif
            @if($params['hasTotals'] && $loop->last)
                class="report_totals"
            @endif
            style='word-break:break-all;'>

            @foreach($rec as $k => $v)
                @if (strpos($v, '<audio controls') !== false)
                    <td>{!! $v !!}</td>
                @else
                    <td>{{ $v }}</td>
                @endif
            @endforeach
            </tr>
        @endforeach --}}
        </tbody>
    </table>

    <div class="col-sm-3 pl0">
        <div class="form-group">
              <label>Select # of Columns to Freeze</label>
              <select name="numb_pinned_cols" id="numb_pinned_cols" class="form-control numb_pinned_cols">
                    <option value="">Select One</option>
                    <option value="0">0</option>
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                    <option value="5">5</option>
              </select>
        </div>

        <label class="radio-inline"><input type="radio" class="pin_direction" name="pin_direction" value="left" checked>Freeze Left</label>
        <label class="radio-inline"><input type="radio" class="pin_direction" name="pin_direction" value="right">Freeze Right</label>
    </div>
{{-- @endif --}}
