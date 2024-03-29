<?php $this->load->view("partial/header"); ?>
<script type="text/javascript">
    $(document).ready(function()
    {
        // when any filter is clicked and the dropdown window is closed
        $('#filters').on('keyup', function(e) {
            if ($(this).val() == '') {
                $('input[name="customer_id"]').val('');
                table_support.refresh();
            }
        });

        // load the preset datarange picker
        <?php $this->load->view('partial/daterangepicker'); ?>

        $("#daterangepicker").on('apply.daterangepicker', function(ev, picker) {
            table_support.refresh();
        });

        $('#filters').autocomplete( {
            source: "<?php echo site_url('customers/suggest'); ?>",
            minChars: 0,
            delay: 15,
            cacheLength: 1,
            appendTo: '.modal-content',
            select: function( event, ui ) {
                event.preventDefault();
                $('#filters').val(ui.item.label);
                if (parseInt(ui.item.value) > 0){
                    $('input[name="customer_id"]').val(ui.item.value);
                    table_support.refresh();
                }
                return ui.item.label;
            }
        });

        <?php $this->load->view('partial/bootstrap_tables_locale'); ?>

        table_support.query_params = function()
        {
            return {
                start_date: start_date,
                end_date: end_date,
                customer_id: $('input[name="customer_id"]').val() || '',
                filters: $("#filters").val() || ''
            }
        };

        table_support.init({
            resource: '<?php echo site_url($controller_name);?>',
            headers: <?php echo $table_headers; ?>,
            pageSize: <?php echo $this->config->item('lines_per_page'); ?>,
            uniqueId: 'sale_order_id',
            onLoadSuccess: function(response) {
                if($("#table tbody tr").length > 1) {
                    $("#payment_summary").html(response.payment_summary);
                    $("#table tbody tr:last td:first").html("");
                    $("#table tbody tr:last").css('font-weight', 'bold');
                }
            },
            queryParams: function() {
                return $.extend(arguments[0], table_support.query_params());
            },
            columns: {
                'invoice': {
                    align: 'center'
                }
            }
        });
    });
</script>
<?php $this->load->view('partial/print_receipt', array('print_after_sale'=>false, 'selected_printer'=>'takings_printer')); ?>
<div id="title_bar" class="print_hide btn-toolbar">
    <?php echo anchor("sales_order/matrix", '<span class="glyphicon glyphicon-th">&nbsp</span>' . $this->lang->line('sales_order_matrix'), array('class'=>'btn btn-danger btn-sm pull-right', 'id'=>'show_sales_matrix')); ?>
    <?php echo anchor("sales_order/summary", '<span class="glyphicon glyphicon-th">&nbsp</span>' . $this->lang->line('sales_order_summary'), array('class'=>'btn btn-warning btn-sm pull-right', 'id'=>'show_sales_summary')); ?>
    <button onclick="javascript:printdoc()" class='btn btn-info btn-sm pull-right'>
        <span class="glyphicon glyphicon-print">&nbsp</span><?php echo $this->lang->line('common_print'); ?>
    </button>
</div>

<div id="toolbar">
    <div class="pull-left form-inline" role="toolbar">
        <button id="delete" class="btn btn-default btn-sm print_hide">
            <span class="glyphicon glyphicon-trash">&nbsp</span><?php echo $this->lang->line("common_delete");?>
        </button>

        <?php echo form_input(array('name'=>'daterangepicker', 'class'=>'form-control input-sm', 'id'=>'daterangepicker')); ?>
        <?php echo form_input(array('name'=>'filters', 'class'=>'form-control input-sm', 'id'=>'filters')); ?>
        <?php echo form_hidden('customer_id', ''); ?>
    </div>
</div>

<div id="table_holder">
    <table id="table"></table>
</div>

<?php $this->load->view("partial/footer"); ?>
