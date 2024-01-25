<?php $this->load->view("partial/header"); ?>
<script type="text/javascript">
    $(document).ready(function()
    {
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
                supplier_id: $('input[name="supplier_id"]').val() || '',
                filters: $("#filters").val() || ''
            }
        };
        table_support.init({
            resource: '<?php echo site_url($controller_name).'/search_paid_items_supp';?>',
            headers: <?php echo $table_headers; ?>,
            pageSize: <?php echo $this->config->item('lines_per_page'); ?>,
            uniqueId: 'items.item_id',
            onLoadSuccess: function(response) {
                if($("#table tbody tr").length > 1) {
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
<div id="toolbar">
    <div class="pull-left form-inline" role="toolbar">
        <?php echo form_input(array('name'=>'daterangepicker', 'class'=>'form-control input-sm', 'id'=>'daterangepicker')); ?>
        <?php echo form_input(array('name'=>'filters', 'class'=>'form-control input-sm', 'id'=>'filters')); ?>
        <?php echo form_hidden('supplier_id', ''); ?>
    </div>
</div>
<div id="table_holder">
    <table id="table"></table>
</div>

<?php $this->load->view("partial/footer"); ?>
