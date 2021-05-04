<div class="options options-button form-horizontal">
    <div class="form-group">
        <label class="control-label col-sm-3">Label</label>
        <div class="controls col-sm-9">
            <input type="text" class="options_button_label form-control" placeholder="Label">
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-sm-3">Color</label>
        <div class="controls col-sm-9">
            <select class="options_button_color form-control">
                <option value="btn-info">Blue</option>
                <option value="btn-success">Green</option>
                <option value="btn-warning" selected>Orange</option>
                <option value="btn-danger">Red</option>
            </select>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-sm-3">Size</label>
        <div class="controls col-sm-9">
            <select class="options_button_size form-control">
                <option value="btn-sm">Small</option>
                <option value="btn-md" selected>Medium</option>
                <option value="btn-lg">Large</option>
            </select>
        </div>
    </div>

    <button type="button" class="btn btn-primary mb0 mt10 ml10" id="save_options">Save Changes</button>
    <button type="button" class="btn btn-default mt10" id="cancel_options" tabindex="-1">Cancel</button>
    
</div>