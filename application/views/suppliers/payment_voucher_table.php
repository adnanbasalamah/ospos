<?php $this->load->view("partial/header"); ?>
<script type="text/javascript">
    $(document).ready(function()
    {
        <?php $this->load->view('partial/daterangepicker'); ?>
        $("#daterangepicker").on('apply.daterangepicker', function(ev, picker) {
            table_support.refresh();
            var DateVal = $("#daterangepicker").val();
            var SplitDate = DateVal.split('-');
            if (SplitDate.length > 0 && SplitDate[0].trim() == SplitDate[1].trim()){
                $('#report-date').html(SplitDate[0].trim());
            }else{
                $('#report-date').html($("#daterangepicker").val());
            }
        });
        // when any filter is clicked and the dropdown window is closed
        $('#filters').on('keyup', function(e) {
            if ($(this).val() == '') {
                $('input[name="supplier_id"]').val('');
                table_support.refresh();
            }
        });
        $('#filters').autocomplete( {
            source: "<?php echo site_url('suppliers/suggest'); ?>",
            minChars: 0,
            delay: 15,
            cacheLength: 1,
            appendTo: '.modal-content',
            select: function( event, ui ) {
                event.preventDefault();
                $('#filters').val(ui.item.label);
                if (parseInt(ui.item.value) > 0){
                    $('input[name="supplier_id"]').val(ui.item.value);
                    table_support.refresh();
                }
                return ui.item.label;
            }
        });
        <?php $this->load->view('partial/bootstrap_tables_locale'); ?>

        var selected_rows = function () {
            return $("#table td input:checkbox:checked").parents("tr");
        };

        table_support.query_params = function()
        {
            return {
                start_date: start_date,
                end_date: end_date,
                supplier_id: $('input[name="supplier_id"]').val() || '',
                filters: $("#filters").val() || ''
            }
        };
        table_support.init({
            resource: '<?php echo site_url($controller_name).'/search_voucher'; ?>',
            headers: <?php echo $table_headers; ?>,
            pageSize: <?php echo $this->config->item('lines_per_page'); ?>,
            uniqueId: 'items.item_id',
            onLoadSuccess: function(response) {
                if($("#table tbody tr").length > 1) {
                    $("#table tbody tr:last td:first").html("");
                    $("#table tbody tr:last").css('font-weight', 'bold');
                    /*$("#table tbody tr td").each(function(){
                        $(this).css('line-height', '2px');
                    })*/
                }
                $('.btn-status').off('click');
                $('.btn-status').on('click', function(e){
                    var IdButton = $(this).attr('id');
                    var SplitId = IdButton.split('-');
                    if (SplitId[1] == 1){
                        var konfirmasi = confirm('Yakin untuk mengubah status menjadi paid...??!');
                        if (konfirmasi){
                            var AjaxAddress = 'suppliers/update_status_paid/'+ parseInt(SplitId[0]);
                            $.ajax({
                                url: AjaxAddress,
                                type: 'GET',
                                dataType: 'json',
                                success: function (data) {
                                    var return_data = eval(data);
                                    alert(data.status);
                                    table_support.refresh();
                                }
                            });
                        }
                    }
                });
                $('#delete-button').off('click');
                $('#delete-button').on('click', function(){
                    var selected_row = selected_rows();
                    var Ids = [];
                    for (var i = 0; i < selected_row.length;i++){
                        var selected_cells = selected_row[i]['cells'][1];
                        var row_id = selected_cells.innerHTML;
                        Ids.push(row_id);
                    }
                    if (Ids.length > 0){
                        var konfirmasi = confirm('Yakin untuk menghapus payment voucher yang di pilih...??!');
                        if (konfirmasi){
                            var request = new Object;
                            request.pv_id = Ids;
                            var AjaxAddress = 'suppliers/delete_voucher';
                            $.ajax({
                                url: AjaxAddress,
                                type: 'POST',
                                data: request,
                                dataType: 'json',
                                success: function (data) {
                                    var return_data = eval(data);
                                    alert(data.status);
                                    table_support.refresh();
                                }
                            });
                        }
                    }
                });
            },
            queryParams: function() {
                return $.extend(arguments[0], table_support.query_params());
            },
        });
    });
</script>
<div id="page_title"><?php echo $page_title; ?> <span id="report-date" class="report-date-title"><?php print date('d/m/Y'); ?></span></div>
<div id="title_bar" class="btn-toolbar">
    <button class='btn btn-info btn-sm pull-right modal-dlg' data-btn-submit='<?php echo $this->lang->line('common_submit') ?>' data-href='<?php echo site_url($controller_name."/view"); ?>'
            title='<?php echo $this->lang->line($controller_name . '_new'); ?>'>
        <span class="glyphicon glyphicon-user">&nbsp</span><?php echo $this->lang->line($controller_name . '_new'); ?>
    </button>
    <?php echo anchor("suppliers/payment", '<span class="glyphicon glyphicon-th">&nbsp</span>' . $this->lang->line('supplier_payment'), array('class'=>'btn btn-warning btn-sm pull-right', 'id'=>'supplier_payment')); ?>
</div>
<div id="toolbar">
    <div class="pull-left form-inline" role="toolbar">
        <button id="delete-button" class="btn btn-default btn-sm print_hide">
            <span class="glyphicon glyphicon-trash">&nbsp</span><?php echo $this->lang->line("common_delete");?>
        </button>
        <?php echo form_input(array('name'=>'daterangepicker', 'class'=>'form-control input-sm print_hide', 'id'=>'daterangepicker')); ?>
        <?php echo form_input(array('name'=>'filters', 'class'=>'form-control input-sm print_hide', 'id'=>'filters')); ?>
        <?php echo form_hidden('supplier_id', ''); ?>
    </div>
</div>
<div id="table_holder">
    <table id="table" class="payment-voucher-table"></table>
</div>

<?php $this->load->view("partial/footer"); ?>
