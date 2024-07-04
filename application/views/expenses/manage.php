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
    var selected_rows = function () {
        return $("#table td input:checkbox:checked").parents("tr");
    };

	table_support.init({
		resource: '<?php echo site_url($controller_name);?>',
		headers: <?php echo $table_headers; ?>,
		pageSize: <?php echo $this->config->item('lines_per_page'); ?>,
		uniqueId: 'expense_id',
		onLoadSuccess: function(response) {
            if($("#table tbody tr").length > 1) {
				$("#payment_summary").html(response.payment_summary);
				$("#table tbody tr:last td:first").html("");
				$("#table tbody tr:last").css('font-weight', 'bold');
			}
            $('#payment-voucher').click(function(e){
                var selected_row = selected_rows();
                var Ids = [];
                var added_query = '';
                var total_payment = 0;
                for (var i = 0; i < selected_row.length;i++){
                    var selected_cells = selected_row[i]['cells'][1];
                    var payment_row = selected_row[i]['cells'][5];
                    var payment_str = payment_row.innerHTML;
                    var payment_arr = payment_str.split('&nbsp;');
                    total_payment += parseFloat(payment_arr[1]);
                    var row_id = selected_cells.innerHTML;
                    Ids.push(row_id);
                    added_query += '&expense_id[]='+ row_id
                }
                console.log(added_query);
                added_query += '&payment='+ total_payment +'&payment_type=1';
                var hrefVal = 'suppliers/payment_voucher?start_date='+ start_date +'&end_date='+ end_date + added_query;
                $('#show_pv_button').attr('href',hrefVal);
                $('#show_pv_button').click();
                console.log(Ids);
            })
		},
		queryParams: function() {
			return $.extend(arguments[0], {
				start_date: start_date,
				end_date: end_date,
				filters: $("#filters").val() || [""]
			});
		}
	});
});
</script>

<?php $this->load->view('partial/print_receipt', array('print_after_sale'=>false, 'selected_printer'=>'takings_printer')); ?>

<div id="title_bar" class="print_hide btn-toolbar">
	<button onclick="javascript:printdoc()" class='btn btn-info btn-sm pull-right'>
		<span class="glyphicon glyphicon-print">&nbsp;</span><?php echo $this->lang->line('common_print'); ?>
	</button>
	<button class='btn btn-info btn-sm pull-right modal-dlg' data-btn-submit='<?php echo $this->lang->line('common_submit') ?>' data-href='<?php echo site_url($controller_name."/view"); ?>'
			title='<?php echo $this->lang->line($controller_name.'_new'); ?>'>
		<span class="glyphicon glyphicon-tags">&nbsp</span><?php echo $this->lang->line($controller_name . '_new'); ?>
	</button>
</div>

<div id="toolbar">
	<div class="pull-left form-inline" role="toolbar">
		<button id="delete" class="btn btn-default btn-sm print_hide">
			<span class="glyphicon glyphicon-trash">&nbsp</span><?php echo $this->lang->line("common_delete");?>
		</button>

		<?php echo form_input(array('name'=>'daterangepicker', 'class'=>'form-control input-sm', 'id'=>'daterangepicker')); ?>
		<?php echo form_multiselect('filters[]', $filters, '', array('id'=>'filters', 'data-none-selected-text'=>$this->lang->line('common_none_selected_text'), 'class'=>'selectpicker show-menu-arrow', 'data-selected-text-format'=>'count > 1', 'data-style'=>'btn-default btn-sm', 'data-width'=>'fit')); ?>
        <button id="payment-voucher" class="btn btn-success btn-sm print_hide">
            <span class="glyphicon glyphicon-credit-card">&nbsp</span><?php echo $this->lang->line("payment_voucher");?>
        </button>
        <?php echo '&nbsp;&nbsp;'.anchor("#", '<span class="glyphicon glyphicon-credit-card">&nbsp</span>' . $this->lang->line('payment_voucher_online'), array('class'=>'btn btn-success btn-sm pull-right modal-dlg print_hide', 'id'=>'show_pv_button', 'style' => 'display: none;', 'data-btn-submit' => 'Submit')); ?>
	</div>
</div>

<div id="table_holder">
	<table id="table"></table>
</div>

<div id="payment_summary">
</div>

<?php $this->load->view("partial/footer"); ?>
