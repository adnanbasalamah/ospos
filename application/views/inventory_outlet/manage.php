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
                    $('#customer-name').html(ui.item.label);
                    table_support.refresh();
                }
                return ui.item.label;
            }
        });

        <?php $this->load->view('partial/bootstrap_tables_locale'); ?>

        table_support.query_params = function()
        {
            return {
                customer_id: $('input[name="customer_id"]').val() || 0,
                filters: $("#filters").val() || ''
            }
        };

        table_support.init({
            resource: '<?php echo site_url($controller_name);?>',
            headers: <?php echo $table_headers; ?>,
            pageSize: <?php echo $this->config->item('lines_per_page'); ?>,
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
<div id="title_bar" class="print_hide btn-toolbar">
    <button onclick="javascript:printdoc()" class='btn btn-info btn-sm pull-right'>
        <span class="glyphicon glyphicon-print">&nbsp</span><?php echo $this->lang->line('common_print'); ?>
    </button>
</div>

<div id="toolbar">
    <div class="pull-left form-inline" role="toolbar">
        <?php echo form_input(array('name'=>'filters', 'class'=>'form-control input-sm', 'id'=>'filters')); ?>
        <?php echo form_hidden('customer_id', ''); ?>
    </div>
</div>
<div class="form-inline" role="toolbar">
    <div id="customer-name">&nbsp;</div>
</div>
<div id="table_holder">
    <table id="table"></table>
</div>

<?php $this->load->view("partial/footer"); ?>
