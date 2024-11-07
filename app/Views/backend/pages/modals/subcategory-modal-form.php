<div class="modal fade" id="subcategory-modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <form action="<?= route_to('add-subcategory') ?>" method="POST" class="modal-content" id="add_subcategory_form">
            <div class="modal-header">
                <h4 class="modal-title" id="myLargeModalLabel">
                    Large modal
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    ×
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" class="ci_csrf_data">
                <div class="form-group">
                    <label for=""><b>Parent category</b></label>
                    <select name="parent_cat" id="" class="form-control">
                        <option value="">Uncategorized</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for=""><b>Subcategory name</b></label>
                    <input type="text" class="form-control" name="subcategory_name" placeholder="Enter subcategory name">
                    <span class="text-danger error-text subcategory_name_error"></span>
                </div>
                <div class="form-group">
                    <label for=""><b>Description</b></label>
                    <textarea name="description" class="form-control" cols="30" rows="10" placeholder="Type..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    Close
                </button>
                <button type="submit" class="btn btn-primary action">
                    Save changes
                </button>
            </div>
        </form>
    </div>
</div>
