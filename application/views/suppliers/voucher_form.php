<div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>

<ul id="error_message_box" class="error_message_box"></ul>

<?php echo form_open("suppliers/voucher_save", array('id'=>'voucher_form', 'class'=>'form-horizontal')); ?>
<fieldset id="payment_voucher">
    <div class="form-group form-group-sm">
        <?php echo form_label($this->lang->line('payment_date'), 'date', array('class'=>'control-label col-xs-3')); ?>
        <div class='col-xs-8'>
            <?php echo form_input(
                    array(
                        'name'=>'date',
                        'value'=> to_datetime(strtotime($payment_date)),
                        'class'=>'datetime form-control input-sm'
                    )
            );
            ?>
        </div>
    </div>
    <div class="form-group form-group-sm">
        <?php echo form_label($this->lang->line('suppliers_supplier'), 'supplier', array('class'=>'control-label col-xs-3')); ?>
        <div class='col-xs-8'>
            <?php
            echo form_input(array('name'=>'supplier_name', 'value'=> $selected_supplier_name, 'id'=>'supplier_name', 'class'=>'form-control input-sm', 'readonly' => 'readonly'));
            echo form_hidden('supplier_id', $selected_supplier_id);
            ?>
        </div>
    </div>

    <div class="form-group form-group-sm">
        <?php echo form_label($this->lang->line('suppliers_up_to'), 'voucher_up_to', array('class'=>'control-label col-xs-3')); ?>
        <div class='col-xs-8'>
            <?php
            echo form_input(array('name'=>'voucher_up_to', 'value'=> $supplier_contact, 'id'=>'supplier_contact', 'class'=>'form-control input-sm', 'readonly' => 'readonly'));
            ?>
        </div>
    </div>

    <div class="form-group form-group-sm">
        <?php echo form_label($this->lang->line('payment_voucher_number'), 'voucher_number', array('class'=>'control-label col-xs-3')); ?>
        <div class='col-xs-8'>
            <?php
            echo form_input(array('name'=>'voucher_number', 'value'=> $voucher_number, 'id'=>'voucher_number', 'class'=>'form-control input-sm'));
            ?>
        </div>
    </div>

    <div class="form-group form-group-sm">
        <?php echo form_label($this->lang->line('payment_voucher_notes'), 'voucher_notes', array('class'=>'control-label col-xs-3')); ?>
        <div class='col-xs-8'>
            <?php
            echo form_input(array('name'=>'voucher_notes', 'value'=> $voucher_notes, 'id'=>'voucher_notes', 'class'=>'form-control input-sm'));
            ?>
        </div>
    </div>

    <div class="form-group form-group-sm">
        <?php echo form_label($this->lang->line('payment_voucher_value'), 'voucher_value', array('class'=>'control-label col-xs-3')); ?>
        <div class='col-xs-8'>
            <?php
            echo form_input(array('name'=>'voucher_value', 'value'=> $voucher_value, 'id'=>'voucher_value', 'class'=>'form-control input-sm'));
            ?>
        </div>
    </div>

    <div class="form-group form-group-sm">
        <?php echo form_label($this->lang->line('payment_voucher_type'), 'payment_voucher_type', array('class'=>'control-label col-xs-3')); ?>
        <div class='col-xs-8'>
            <?php
            echo form_dropdown(
                array(
                    'name'=>'payment_voucher_type',
                    'id'=>'payment_voucher_type'
                ),
                $pv_type_option,
                0,
                array('class' => 'form-control input-sm')
            );
            ?>
        </div>
    </div>

    <div class="form-group form-group-sm">
        <?php echo form_label($this->lang->line('suppliers_account_number'), 'account_number', array('class'=>'control-label col-xs-3')); ?>
        <div class='col-xs-8'>
            <?php
            echo form_input(array('name'=>'account_number', 'value' => $account_number, 'id'=>'account_number', 'class'=>'form-control input-sm'));
            ?>
        </div>
    </div>
</fieldset>

<?php echo form_close(); ?>

<script type="text/javascript">
    $(document).ready(function() {
        <?php $this->load->view('partial/datepicker_locale'); ?>
        $('#voucher_form').validate($.extend( {
            submitHandler: function(form) {
                $(form).ajaxSubmit({
                    success: function(response)
                    {
                        dialog_support.hide();
                        table_support.handle_submit("<?php echo site_url($controller_name); ?>", response);

                        const params = $.param(table_support.query_params());
                        $.get("<?php echo site_url($controller_name); ?>/search_paid_items_supp?" + params, function(response) {
                            //$("#payment_summary").html(response.payment_summary);
                        }, 'json');
                    },
                    dataType: 'json'
                });
            },

            errorLabelContainer: '#error_message_box'
        }, form_support.error));
    });
</script>