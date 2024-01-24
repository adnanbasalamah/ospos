<?php $this->load->view("partial/header"); ?>
<script type="text/javascript">
    $(document).ready(function()
    {
        <?php $this->load->view('partial/bootstrap_tables_locale'); ?>

        table_support.init({
            resource: '<?php echo site_url($controller_name).'/get_detail_po/'.$po_id;?>',
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
</script>
<div id="title_bar" class="print_hide btn-toolbar">
    <button onclick="javascript:printdoc()" class='btn btn-info btn-sm pull-right'>
        <span class="glyphicon glyphicon-print">&nbsp</span><?php echo $this->lang->line('common_print'); ?>
    </button>
    <?php echo anchor("purchase_order", '<span class="glyphicon glyphicon-shopping-cart">&nbsp</span>' . $this->lang->line('po_list'), array('class'=>'btn btn-info btn-sm pull-right', 'id'=>'show_sales_button')); ?>
</div>
<div id="page_title">PURCHASE ORDER</div>
<div class="so-number"><?php echo $po_number; ?></div>
<div class="row">&nbsp;</div>
<div class="row">&nbsp;</div>
<div class="row">
    <div class="col-md-2 so-info">Supplier</div><div class="col-md-4 so-info so-info-value"><?php echo $po_info_suplier; ?></div>
    <div class="col-md-2 so-info">Order Date</div><div class="col-md-4 so-info so-info-value"><?php echo $po_info_date; ?></div>
    <div class="col-md-2 so-info">Order Notes</div><div class="col-md-4 so-info so-info-value"><?php echo $po_info_comment;?></div><div class="col-md-2 so-info">&nbsp;</div><div class="col-md-4 so-info so-info-value">&nbsp;</div>
</div>
<div id="table_holder">
    <table id="table"></table>
</div>
<?php $this->load->view("partial/footer"); ?>
