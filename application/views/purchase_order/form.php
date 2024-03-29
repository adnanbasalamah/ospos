<div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>

<ul id="error_message_box" class="error_message_box"></ul>

<?php echo form_open("purchase_order/save/".$po_info['po_id'], array('id'=>'po_edit_form', 'class'=>'form-horizontal')); ?>
	<fieldset id="receiving_basic_info">
		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('po_receipt_number'), 'supplier', array('class'=>'control-label col-xs-3')); ?>
			<?php echo anchor('purchase_oder/receipt/'.$po_info['po_id'], 'PO ' .str_pad($po_info['po_id'],5,'0',STR_PAD_LEFT) , array('target'=>'_blank', 'class'=>'control-label col-xs-8', "style"=>"text-align:left"));?>
		</div>
		
		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('po_date'), 'date', array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-8'>
				<?php echo form_input(array(
						'name'	=> 'date',
						'value'	=> to_datetime(strtotime($po_info['po_time'])),
						'id'	=> 'datetime',
						'class'	=> 'datetime form-control input-sm',
        					'readonly' => 'readonly'));
				?>
			</div>
		</div>
		
		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('receivings_supplier'), 'supplier', array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-8'>
				<?php echo form_input(array('name' => 'supplier_name', 'value' => $selected_supplier_name, 'id' => 'supplier_name', 'class'=>'form-control input-sm'));?>
				<?php echo form_hidden('supplier_id', $selected_supplier_id);?>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('receivings_reference'), 'reference', array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-8'>
				<?php echo form_input(array('name' => 'reference', 'value' => $po_info['reference'], 'id' => 'reference', 'class'=>'form-control input-sm'));?>
			</div>
		</div>
		
		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('receivings_employee'), 'employee', array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-8'>
				<?php echo form_dropdown('employee_id', $employees, $po_info['employee_id'], 'id="employee_id" class="form-control"');?>
			</div>
		</div>
		
		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('po_status'), 'purchaseorder', array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-8'>
				<?php echo form_dropdown('po_status', $po_status, $po_info['po_id'], 'id="po_id" class="form-control"');?>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('receivings_comments'), 'comment', array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-8'>
				<?php echo form_textarea(array('name'=>'comment','value'=>$po_info['comment'], 'id'=>'comment', 'class'=>'form-control input-sm'));?>
			</div>
		</div>
	</fieldset>
<?php echo form_close(); ?>
		
<script type="text/javascript">
$(document).ready(function()
{
	<?php $this->load->view('partial/datepicker_locale'); ?>

    $('#datetime').datetimepicker(pickerconfig);

	var fill_value = function(event, ui) {
		event.preventDefault();
		$("input[name='supplier_id']").val(ui.item.value);
		$("input[name='supplier_name']").val(ui.item.label);
	};

	$('#supplier_name').autocomplete({
		source: "<?php echo site_url('suppliers/suggest'); ?>",
		minChars: 0,
		delay: 15, 
		cacheLength: 1,
		appendTo: '.modal-content',
		select: fill_value,
		focus: fill_value
	});

	$('button#delete').click(function()
	{
		dialog_support.hide();
		table_support.do_delete("<?php echo site_url($controller_name); ?>", <?php echo $po_info['po_id']; ?>);
	});

	$('#receivings_edit_form').validate($.extend({
		submitHandler: function(form) {
			$(form).ajaxSubmit({
				success: function(response)
				{
					dialog_support.hide();
					table_support.handle_submit("<?php echo site_url($controller_name); ?>", response);
				},
				dataType: 'json'
			});
		}
	}, form_support.error));
});
</script>
