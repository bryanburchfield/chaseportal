<div class="options options-text form-horizontal">
    <div class="form-group">
        <label class="control-label col-sm-3">Name</label>
        <div class="controls col-sm-9">
            <input type="text" class="options_text_name form-control" placeholder="Name">
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-sm-3">Color</label>
        <div class="controls col-sm-9">
            {{-- <input type="text" class="options_text_label form-control" placeholder="Label"> --}}
            <select class="options_submit_method form-control">
                <option value="btn-info">Blue</option>
                <option value="btn-success">Green</option>
                <option value="btn-warning">Orange</option>
                <option value="btn-danger">Red</option>
            </select>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-sm-3">Size</label>
        <div class="controls col-sm-9">
            <select class="options_submit_method form-control">
                <option value="btn-sm">Small</option>
                <option value="btn-md">Medium</option>
                <option value="btn-lg">Large</option>
            </select>
        </div>
    </div>
</div>