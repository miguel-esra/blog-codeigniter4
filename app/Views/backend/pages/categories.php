<?= $this->extend('backend/layout/pages-layout') ?>
<?= $this->section('content') ?>


<div class="page-header">
    <div class="row">
        <div class="col-md-12 col-sm-12">
            <div class="title">
                <h4>Categories</h4>
            </div>
            <nav aria-label="breadcrumb" role="navigation">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="<?= route_to('admin.home') ?>">Home</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        Categories
                    </li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card card-box">
            <div class="card-header">
                <div class="clearfix">
                    <div class="pull-left">
                        Categories
                    </div>
                    <div class="pull-right">
                        <a href="" class="btn btn-default btn-sm p-0" role="button" id="add_category_btn">
                            <i class="fa fa-plus-circle"></i> Add category
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless table-hover table-striped" id="categories-table">
                    <thead>
                        <tr>
                            <th scope="col" width="4%">#</th>
                            <th scope="col">Category name</th>
                            <th scope="col">N° of subcategories</th>
                            <th scope="col" width="12%">Action</th>
                            <th scope="col">Ordering</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-12 mb-4">
        <div class="card card-box">
            <div class="card-header">
                <div class="clearfix">
                    <div class="pull-left">
                        Subcategories
                    </div>
                    <div class="pull-right">
                        <a href="" class="btn btn-default btn-sm p-0" role="button" id="add_subcategory_btn">
                            <i class="fa fa-plus-circle"></i> Add subcategory
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless table-hover table-striped" id="subcategories-table">
                    <thead>
                        <tr>
                            <th scope="col" width="4%">#</th>
                            <th scope="col">Subcategory name</th>
                            <th scope="col">Parent category</th>
                            <th scope="col">N° of posts</th>
                            <th scope="col" width="12%">Action</th>
                            <th scope="col">Ordering</th>
                        </tr>
                    </thead>
                    <tbody>
                        
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


<?php include('modals/category-modal-form.php') ?>
<?php include('modals/edit-category-modal-form.php') ?>
<?php include('modals/subcategory-modal-form.php') ?>

<?= $this->endSection() ?>


<?= $this->section('stylesheets') ?>

<link rel="stylesheet" href="/backend/src/plugins/datatables/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="/backend/src/plugins/datatables/css/responsive.bootstrap4.default.min.css">
<link rel="stylesheet" href="/extra-assets/jquery-ui-1.14.1/jquery-ui.min.css">
<link rel="stylesheet" href="/extra-assets/jquery-ui-1.14.1/jquery-ui.structure.min.css">
<link rel="stylesheet" href="/extra-assets/jquery-ui-1.14.1/jquery-ui.theme.min.css">

<?= $this->endSection() ?>


<?= $this->section('scripts') ?>

<script src="/backend/src/plugins/datatables/js/dataTables.default.min.js"></script>
<script src="/backend/src/plugins/datatables/js/dataTables.bootstrap4.default.min.js"></script>
<script src="/backend/src/plugins/datatables/js/dataTables.responsive.default.min.js"></script>
<script src="/backend/src/plugins/datatables/js/responsive.bootstrap4.default.min.js"></script>
<script src="/extra-assets/jquery-ui-1.14.1/jquery-ui.min.js"></script>

<script>
    $(document).on('click', '#add_category_btn', function (e) {
        e.preventDefault();
        var modal = $('body').find('div#category-modal');
        var modal_title = 'Add category';
        var modal_btn_text = 'Add';
        modal.find('.modal-title').html(modal_title);
        modal.find('.modal-footer > button.action').html(modal_btn_text);
        modal.find('span.error-text').html('');
        modal.find('input[type="text"]').val('');
        modal.modal('show');
    });


    $('#add_category_form').on('submit', function (e) {
        e.preventDefault();
        var csrfName = $('.ci_csrf_data').attr('name');      // CSRF token name
        var csrfHash = $('.ci_csrf_data').val();     // CSRF hash
        var form = this;
        var modal = $('body').find('div#category-modal')
        var formdata = new FormData(form);
            formdata.append(csrfName, csrfHash);

        $.ajax({
            url: $(form).attr('action'),
            method: $(form).attr('method'),
            data: formdata,
            processData: false,
            dataType: 'json',
            contentType: false,
            cache: false,
            beforeSend: function () {
                toastr.remove();
                $(form).find('span.error-text').text('');
            },
            success: function (response) {
                // Update CSRF hash
                $('.ci_csrf_data').val(response.token);

                if ( $.isEmptyObject(response.error) ) {
                    if ( response.status == 1 ) {
                        $(form)[0].reset();
                        modal.modal('hide');
                        toastr.success(response.msg);
                        categories_DT.ajax.reload(null, false);     // Update datatable
                        subcategories_DT.ajax.reload(null, false);
                    } else {
                        toastr.error(response.msg);
                    }
                } else {
                    $.each(response.error, function (prefix, val) {
                        $(form).find('span.' + prefix + '_error').text(val);
                    });
                }
            }
        }); 
    });


    // Retrieve categories
    var categories_DT = $('#categories-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: "<?= route_to('get-categories') ?>",
        info: true,
        layout: {
            topStart: null,
            topEnd: null,
            bottomStart: 'info',
            bottomEnd: 'paging'
        },
        fnCreatedRow: function (row, data, index) {
            var api = this.api();
            var page = api.page();
            $('td', row).eq(0).html(page * 10 + index + 1);
            $('td', row).parent().attr('data-index', data[0]).attr('data-ordering', data[4]);
            // console.log(data);
        },
        columnDefs: [
            { orderable: false, targets: [0,1,2,3] },
            { visible: false, targets: 4 }
        ],
        order: [[4, 'asc']]
    });


    $(document).on('click', '.editCategoryBtn', function (e) {
        e.preventDefault();
        var category_id = $(this).data('id');
        var url = "<?= route_to('get-category') ?>";
        $.get(url, { category_id: category_id }, function (response) {
            var modal_title = 'Edit category';
            var modal_btn_text = 'Save changes';
            var modal = $('body').find('div#edit-category-modal');
            modal.find('form').find('input[type="hidden"][name="category_id"]').val(category_id);
            modal.find('.modal-title').html(modal_title);
            modal.find('.modal-footer > button.action').html(modal_btn_text);
            modal.find('input[type="text"]').val(response.data.name);
            modal.find('span.error-text').html('');
            modal.modal('show');
        }, 'json');
    });


    $('#update_category_form').on('submit', function (e) {
        e.preventDefault();
        var csrfName = $('.ci_csrf_data').attr('name');      // CSRF token name
        var csrfHash = $('.ci_csrf_data').val();     // CSRF hash
        var form = this;
        var modal = $('body').find('div#edit-category-modal')
        var formdata = new FormData(form);
            formdata.append(csrfName, csrfHash);

        $.ajax({
            url: $(form).attr('action'),
            method: $(form).attr('method'),
            data: formdata,
            processData: false,
            dataType: 'json',
            contentType: false,
            cache: false,
            beforeSend: function () {
                toastr.remove();
                $(form).find('span.error-text').text('');
            },
            success: function (response) {
                // Update CSRF hash
                $('.ci_csrf_data').val(response.token);

                if ( $.isEmptyObject(response.error) ) {
                    if ( response.status == 1 ) {
                        modal.modal('hide');
                        toastr.success(response.msg);
                        categories_DT.ajax.reload(null, false);     // Update datatable
                        subcategories_DT.ajax.reload(null, false);
                    } else {
                        toastr.error(response.msg);
                    }
                } else {
                    $.each(response.error, function (prefix, val) {
                        $(form).find('span.' + prefix + '_error').text(val);
                    });
                }
            }
        }); 
    });


    $(document).on('click', '.deleteCategoryBtn', function (e) {
        e.preventDefault();
        var category_id = $(this).data('id');
        var url = "<?= route_to('delete-category') ?>";
        swal.fire({
            title: 'Are you sure?',
            html: 'Do you want to delete this category?',
            showCloseButton: true,
            showCancelButton: true,
            cancelButtonText: 'Cancel',
            confirmButtonText: 'Yes, delete',
            cancelButtonColor: '#D33',
            confirmButtonColor: '#3085D6',
            width: 360,
            allowOutsideClick: false
        }).then(function (result) {
            if (result.value) {
                $.get(url, { category_id: category_id }, function (response) {
                    if ( response.status == 1) {
                        categories_DT.ajax.reload(null, false);
                        subcategories_DT.ajax.reload(null, false);
                        toastr.success(response.msg);
                    } else {
                        toastr.error(response.msg);
                    }
                }, 'json');
            }
        });
    });


    // Ordering categories
    $('table#categories-table').find('tbody').sortable({
        update: function (event, ui) {
            $(this).children().each( function (index) {
                var api = categories_DT;
                var page = api.page();
                if ( $(this).attr('data-ordering') != (page * 10 + index + 1) ) {
                    $(this).attr('data-ordering', (page * 10 + index + 1)).addClass('updated');
                }
            });
            var positions = [];

            $('.updated').each(function () {
                positions.push([$(this).attr('data-index'), $(this).attr('data-ordering')]);
                $(this).removeClass('updated');
            });

            // console.log(positions);

            var url = "<?= route_to('reorder-categories') ?>";
            $.get(url, { positions: positions }, function (response) {
                if ( response.status == 1) {
                    categories_DT.ajax.reload(null, false);
                    toastr.success(response.msg);
                }
            }, 'json');
        }
    });


    $(document).on('click', '#add_subcategory_btn', function (e) {
        e.preventDefault();
        var modal_title = 'Add Subcategory';
        var modal_btn_text = 'Add';
        var modal = $('body').find('div#subcategory-modal');
        var select = modal.find('select[name="parent_cat"]');
        var url = "<?= route_to('get-parent-categories') ?>";
        $.getJSON(url, { parent_category_id:null }, function (response) {
            select.find('option').remove();
            select.html(response.data);
        });
        modal.find('.modal-title').html(modal_title);
        modal.find('.modal-footer > button.action').html(modal_btn_text);
        modal.find('input[type="text"]').val('');
        modal.find('textarea').html('');
        modal.find('span.error-text').html('');
        modal.modal('show');
    });


    $(document).on('submit', '#add_subcategory_form', function (e) {
        e.preventDefault();
        // CSRF
        var csrfName = $('.ci_csrf_data').attr('name');      // CSRF token name
        var csrfHash = $('.ci_csrf_data').val();     // CSRF hash
        var form = this;
        var modal = $('body').find('div#subcategory-modal')
        var formdata = new FormData(form);
            formdata.append(csrfName, csrfHash);

        $.ajax({
            url: $(form).attr('action'),
            method: $(form).attr('method'),
            data: formdata,
            processData: false,
            dataType: 'json',
            contentType: false,
            cache: false,
            beforeSend: function () {
                toastr.remove();
                $(form).find('span.error-text').text('');
            },
            success: function (response) {
                // Update CSRF hash
                $('.ci_csrf_data').val(response.token);

                if ( $.isEmptyObject(response.error) ) {
                    if ( response.status == 1 ) {
                        $(form)[0].reset();
                        modal.modal('hide');
                        toastr.success(response.msg);
                        subcategories_DT.ajax.reload(null, false);
                    } else {
                        toastr.error(response.msg);
                    }
                } else {
                    $.each(response.error, function (prefix, val) {
                        $(form).find('span.' + prefix + '_error').text(val);
                    });
                }
            }
        }); 
    });


    // Retrieve subcategories
    var subcategories_DT = $('#subcategories-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: "<?= route_to('get-subcategories') ?>",
        info: true,
        layout: {
            topStart: null,
            topEnd: null,
            bottomStart: 'info',
            bottomEnd: 'paging'
        },
        fnCreatedRow: function (row, data, index) {
            var api = this.api();
            var page = api.page();
            $('td', row).eq(0).html(page * 10 + index + 1);
            $('td', row).parent().attr('data-index', data[0]).attr('data-ordering', data[5]);
            // console.log(data);
        },
        columnDefs: [
            { orderable: false, targets: [0,1,2,3,4] },
            { visible: false, targets: 5 }
        ],
        order: [[5, 'asc']]
    });

</script>

<?= $this->endSection() ?>
