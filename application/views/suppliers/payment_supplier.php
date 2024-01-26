<?php $this->load->view("partial/header"); ?>
<script type="text/javascript">
    $(document).ready(function()
    {
        <?php $this->load->view('partial/daterangepicker'); ?>

        $("#daterangepicker").on('apply.daterangepicker', function(ev, picker) {
            table_support.refresh();
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
                    $("#table tbody tr td").each(function(){
                      $(this).css('line-height', '2px');
                    })
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
