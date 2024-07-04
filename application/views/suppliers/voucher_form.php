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
    <?php
    if (!$payment_type_selected){
    ?>
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
    <?php
    }else{
    ?>
        <div class="form-group form-group-sm">
            <?php echo form_label($this->lang->line('supplier_name'), 'custom_supplier', array('class'=>'control-label col-xs-3')); ?>
            <div class='col-xs-8'>
                <?php
                echo form_input(array('name'=>'custom_supplier', 'value'=> '', 'id'=>'custom_supplier', 'class'=>'form-control input-sm'));
                ?>
            </div>
            <?php echo form_label($this->lang->line('suppliers_up_to'), 'voucher_up_to', array('class'=>'control-label col-xs-3')); ?>
            <div class='col-xs-8'>
                <?php
                echo form_input(array('name'=>'voucher_up_to', 'value'=> $employee_contact, 'id'=>'employee_contact', 'class'=>'form-control input-sm'));
                ?>
            </div>
        </div>
    <?php
    }
    ?>
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
                $payment_type_selected,
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
    <?php
    if (!empty($expense_data)){
        $detail_table = '<table id="detail-pv" class="table-striped table-bordered table table-hover">';
        $detail_table .= '<thead><tr>';
        $detail_table .= '<th>'.$this->lang->line('common_no').'</th>';
        $detail_table .= '<th>'.$this->lang->line('items_item').'</th>';
        $detail_table .= '<th id="label-current">'.$this->lang->line('sales_invoice').'</th>';
        $detail_table .= '<th id="label-next">'.$this->lang->line('sales_sub_total').'</th>';
        $detail_table .= '</tr></thead>';
        $detail_table .= '<tbody>';
        $Counter = 1;
        for ($i = 0;$i < count($expense_data);$i++){
            $detail_table .= '<tr>';
            $detail_table .= '<td class="fix-align">'.$Counter.'</td>';
            $PvItem = '['.substr($expense_data[$i]->date,0,10).'] : '.$expense_data[$i]->description;
            $input_pv_item = form_input(
                array(
                    'name'=>'pv_item[]',
                    'id' => 'pv_item-'.$expense_data[$i]->expense_id,
                    'value'=> $PvItem,
                    'class'=>'form-control input-sm large-input'
                )
            );
            $detail_table .= '<td class="fix-align">'.$input_pv_item.'</td>';
            $detail_table .= '<td class="fix-align"><div class="align-right">'.$voucher_number.'</div></td>';
            $hidden_item_value = form_hidden('pv_value[]', $expense_data[$i]->amount);
            $detail_table .= '<td>'.$expense_data[$i]->amount.$hidden_item_value.'</td>';
            $detail_table .= '</tr>';
            $Counter++;
        }
        $detail_table .= '</tbody></table>';
        ?>
        <div id="table_wrapper" class="wrapper">
            <?php echo $detail_table; ?>
        </div>
    <?php
    }
    ?>
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