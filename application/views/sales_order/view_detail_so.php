<?php $this->load->view("partial/header"); ?>
<script type="text/javascript">
    $(document).ready(function()
    {
        <?php $this->load->view('partial/bootstrap_tables_locale'); ?>

        table_support.init({
            resource: '<?php echo site_url($controller_name).'/get_detail_so/'.$so_id;?>',
            headers: <?php echo $table_headers; ?>,
            pageSize: <?php echo $this->config->item('lines_per_page'); ?>,
            uniqueId: 'sale_id',
            onLoadSuccess: function(response) {
                if($("#table tbody tr").length > 1) {
                    $("#payment_summary").html(response.payment_summary);
                    $("#table tbody tr:last td:first").html("");
                    $("#table tbody tr:last").css('font-weight', 'bold');
                }
            },
            columns: {
                'invoice': {
                    align: 'center'
                }
            }
        });
    });
    $('#add-item').removeAttr('disabled');
</script>
<?php $this->load->view('partial/print_receipt', array('print_after_sale'=>false, 'selected_printer'=>'takings_printer')); ?>
<div id="title_bar" class="print_hide btn-toolbar">
    <button onclick="javascript:printdoc()" class='btn btn-info btn-sm pull-right'>
        <span class="glyphicon glyphicon-print">&nbsp</span><?php echo $this->lang->line('common_print'); ?>
    </button>
    <?php echo anchor("sales_order", '<span class="glyphicon glyphicon-shopping-cart">&nbsp</span>' . $this->lang->line('sales_order_list'), array('class'=>'btn btn-info btn-sm pull-right', 'id'=>'show_sales_button')); ?>
</div>
<div id="page_title"><?php echo $page_title; ?></div>
<div class="so-number"><?php echo $so_number; ?></div>
<div class="row">&nbsp;</div>
<div class="row">&nbsp;</div>
<div class="row">
    <div class="col-md-2 so-info">Customer</div><div class="col-md-4 so-info so-info-value"><?php echo $so_info_customer; ?></div>
    <div class="col-md-2 so-info">Order Date</div><div class="col-md-4 so-info so-info-value"><?php echo $so_info_date; ?></div>
    <div class="col-md-2 so-info">Order Notes</div><div class="col-md-4 so-info so-info-value"><?php echo $so_info_comment;?></div>
    <div class="col-md-2 so-info">Order Status</div><div class="col-md-4 so-info so-info-value"><?php echo $so_info_status;?></div>
</div>
<?php if ($so_info_status_int != CANCELED && $so_info_status_int != COMPLETED){ ?>
<div id="title_bar" class="print_hide btn-toolbar">
    <?php echo anchor("sales_order/add-item", '<span class="glyphicon glyphicon-plus-sign">&nbsp</span>' . $this->lang->line('sales_new_item'), array('class'=>'btn btn-info btn-sm pull-right', 'id'=>'show_sales_button')); ?>
</div>
<div id="toolbar">
    <div class="pull-left form-inline" role="toolbar">
        <button id="delete" class="btn btn-default btn-sm print_hide">
            <span class="glyphicon glyphicon-trash">&nbsp</span><?php echo $this->lang->line("common_delete");?>
        </button>
    </div>
</div>
<?php } ?>
<div id="table_holder">
    <table id="table"></table>
</div>
<?php $this->load->view("partial/footer"); ?>
