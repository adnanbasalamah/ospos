<?php $this->load->view("partial/header"); ?>
<script type="text/javascript">
    $(document).ready(function()
    {
        // when any filter is clicked and the dropdown window is closed
        $('#filters').on('hidden.bs.select', function(e) {
            table_support.refresh();
        });

        // load the preset datarange picker
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
            }
        };

        table_support.init({
            resource: '<?php echo site_url($controller_name);?>',
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
<div id="title_bar" class="print_hide btn-toolbar">
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
    </div>
</div>

<div id="table_holder">
    <table id="table"></table>
</div>

<?php $this->load->view("partial/footer"); ?>
