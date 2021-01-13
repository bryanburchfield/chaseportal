    <table class="table table-hover reports_table table-striped scrollTable report_pinned_datatable">
        <thead>
            <tr>
                @foreach($params['columns'] as $key => $val)
                    <th><span>{{$key}}</span></th>
                @endforeach
        </thead>
        <tbody>

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
