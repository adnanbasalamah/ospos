<?php $this->load->view("partial/header"); ?>
<script type="text/javascript">
    $(document).ready(function()
    {
        <?php $this->load->view('partial/bootstrap_tables_locale'); ?>

        table_support.init({
            resource: '<?php echo site_url($controller_name).'/get_detail_pv/'.$voucher_id;?>',
            headers: <?php echo $table_headers; ?>,
            pageSize: <?php echo $this->config->item('lines_per_page'); ?>,
            uniqueId: 'voucher_id',
            onLoadSuccess: function(response) {
                if($("#table tbody tr").length > 1) {
                    $("#table tbody tr:last td:first").html("");
                    $("#table tbody tr:last").css('font-weight', 'bold');
                }
                $('.fixed-table-toolbar,.fixed-table-pagination').attr('class','print_hide');
            },
            onLoadError: function(response){
                $('.fixed-table-toolbar,.fixed-table-pagination').attr('class','print_hide');
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
    <button onclick="javascript:printdoc()" class='btn btn-info btn-sm pull-right'>
        <span class="glyphicon glyphicon-print">&nbsp</span><?php echo $this->lang->line('common_print'); ?>
    </button>
    <?php
    echo anchor("suppliers/payment", '<span class="glyphicon glyphicon-th">&nbsp</span>' . $this->lang->line('supplier_payment'), array('class'=>'btn btn-warning btn-sm pull-right', 'id'=>'supplier_payment'));
    echo anchor("suppliers/payment_voucher_table", '<span class="glyphicon glyphicon-credit-card">&nbsp</span>' . $this->lang->line('payment_voucher_table'), array('class'=>'btn btn-danger btn-sm pull-right', 'id'=>'supplier_payment'));
    ?>
</div>
<div id="page_title"><?php echo $page_title; ?></div>
<div class="so-number"><?php echo $voucher_number; ?></div>
<div class="row">&nbsp;</div>
<div class="row">&nbsp;</div>
<div class="row">
    <?php
    if (!empty($pv_info_supplier)){
    ?>
    <div class="col-md-2 so-info">Supplier</div><div class="col-md-4 so-info so-info-value"><?php echo $pv_info_supplier; ?></div>
    <?php
    }
    if (!empty($pv_custom_supplier)){
        ?>
        <div class="col-md-2 so-info">Supplier</div><div class="col-md-4 so-info so-info-value"><?php echo $pv_custom_supplier; ?></div>
        <?php
    }
    ?>
    <div class="col-md-12 so-info width-small">U/P : <?php echo $pv_contact; ?></div>
    <div class="row">&nbsp;</div>
    <div class="col-md-2 so-info">Payment Date</div><div class="col-md-4 so-info so-info-value"><?php echo $pv_info_date; ?></div>
    <div class="row">&nbsp;</div>
    <div class="col-md-2 so-info">PV Notes</div><div class="col-md-4 so-info so-info-value"><?php echo $pv_info_notes;?></div>
</div>
<div class="row">&nbsp;</div>
<div id="table_holder">
    <table id="table"></table>
</div>
<div class="row">&nbsp;</div>
<div class="row">&nbsp;</div>
<div class="row so-info">
    <div class="col-md-12">AKAUN : <?php echo $pv_info_supplier; ?></div>
    <div class="col-md-12"><?php echo $pv_info_account_number; ?></div>
</div>
<div class="row">&nbsp;</div>
<div class="row">&nbsp;</div>
<div class="row">&nbsp;</div>
<div class="row">&nbsp;</div>
<div class="row">&nbsp;</div>
<div class="row">&nbsp;</div>
<div class="row so-info">
    <span>Diluluskan Oleh,</span><?php echo str_repeat('&nbsp;', 80); ?><span>Penerima,</span>
    <br /><br /><br /><br />
    <br /><br /><br /><br />
    <span><?php echo str_repeat('.', 50); ?></span><?php echo str_repeat('&nbsp;', 60); ?><span><?php echo str_repeat('.', 50); ?></span>
</div>
<?php $this->load->view("partial/footer"); ?>
