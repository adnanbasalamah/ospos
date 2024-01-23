<?php $this->load->view("partial/header"); ?>
<script type="text/javascript">
    $(document).ready(function() {
        <?php $this->load->view('partial/daterangepicker'); ?>

        $("#daterangepicker").on('apply.daterangepicker', function(ev, picker) {
            table_support.refresh();
        });

        <?php $this->load->view('partial/bootstrap_tables_locale'); ?>

        table_support.query_params = function()
        {
            return {
                start_date: start_date,
                end_date: end_date,
                filters: $("#filters").val() || ''
            }
        };
        table_support.init({
            resource: '<?php echo site_url($controller_name) . '/get_matrix_so/'; ?>',
            headers: <?php echo $table_headers; ?>,
            pageSize: <?php echo $this->config->item('lines_per_page'); ?>,
            onLoadSuccess: function (response) {
                if ($("#table tbody tr").length > 1) {
                    $("#table tbody tr:last td:first").html("");
                    $("#table tbody tr:last").css('font-weight', 'bold');
                }
            },
            queryParams: function() {
                return $.extend(arguments[0], table_support.query_params());
            },
        });

    });
</script>
<?php $this->load->view('partial/print_receipt', array('print_after_sale'=>false, 'selected_printer'=>'takings_printer')); ?>
<div id="title_bar" class="print_hide btn-toolbar">
    <button onclick="javascript:printdoc()" class='btn btn-info btn-sm pull-right'>
        <span class="glyphicon glyphicon-print">&nbsp</span><?php echo $this->lang->line('common_print'); ?>
    </button>
    <?php echo anchor("sales_order", '<span class="glyphicon glyphicon-shopping-cart">&nbsp</span>' . $this->lang->line('sales_order_list'), array('class'=>'btn btn-info btn-sm pull-right', 'id'=>'show_sales_button')); ?>
</div>
<div id="page_title"><?php echo $page_title; ?></div>
<div class="row">&nbsp;</div>
<div id="toolbar">
    <div class="pull-left form-inline" role="toolbar">
        <?php echo form_input(array('name'=>'daterangepicker', 'class'=>'form-control input-sm', 'id'=>'daterangepicker')); ?>
    </div>
</div>
<div id="table_holder">
    <table id="table"></table>
</div>
<?php $this->load->view("partial/footer"); ?>
