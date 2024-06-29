<div id="required_fields_message"><?php echo $this->lang->line('common_fields_required_message'); ?></div>

<ul id="error_message_box" class="error_message_box"></ul>

<?php echo form_open("sales_order/save/".$sale_info['sale_order_id'], array('id'=>'sales_order_edit_form', 'class'=>'form-horizontal')); ?>
<fieldset id="sale_basic_info">
    <div class="form-group form-group-sm">
        <?php echo form_label($this->lang->line('sales_date'), 'date', array('class'=>'control-label col-xs-3')); ?>
        <div class='col-xs-8'>
            <?php echo form_input(array('name'=>'date','value'=>to_datetime(strtotime($sale_info['sale_time'])), 'class'=>'datetime form-control input-sm'));?>
        </div>
    </div>
    <div class="form-group form-group-sm">
        <?php echo form_label($this->lang->line('sales_customer'), 'customer', array('class'=>'control-label col-xs-3')); ?>
        <div class='col-xs-8'>
            <?php
                //echo form_input(array('name'=>'customer_name', 'value'=>$selected_customer_name, 'id'=>'customer_name', 'class'=>'form-control input-sm'));
                echo form_dropdown(
                    array(
                        'name'=>'customer_id',
                        'id'=>'customer_id'
                    ),
                    $customer_option,
                    $selected_customer_id,
                    array('class' => 'form-control input-sm')
                );
            ?>
            <?php //echo form_hidden('customer_id', $selected_customer_id);?>
        </div>
    </div>

    <div class="form-group form-group-sm">
        <?php echo form_label($this->lang->line('sales_employee'), 'employee', array('class'=>'control-label col-xs-3')); ?>
        <div class='col-xs-8'>
            <?php
                //echo form_input(array('name'=>'employee_name', 'value'=>$selected_employee_name, 'id'=>'employee_name', 'class'=>'form-control input-sm'));
                echo form_dropdown(
                    array(
                        'name'=>'employee_id',
                        'id'=>'employee_id'
                    ),
                    $employee_option,
                    $selected_employee_id,
                    array('class' => 'form-control input-sm')
                );
            ?>
            <?php //echo form_hidden('employee_id', $selected_employee_id); ?>
        </div>
    </div>

    <div class="form-group form-group-sm">
        <?php echo form_label($this->lang->line('sales_order_status'), 'sales_order_status', array('class'=>'control-label col-xs-3')); ?>
        <div class='col-xs-8'>
            <?php
            echo form_dropdown(
                array(
                    'name'=>'sale_status',
                    'id'=>'sale_status'
                ),
                $status_option,
                $sale_info['sale_status'],
                array('class' => 'form-control input-sm'),
                $disabled_status
            );
            ?>
            <?php //echo form_hidden('employee_id', $selected_employee_id); ?>
        </div>
    </div>

    <div class="form-group form-group-sm">
        <?php echo form_label($this->lang->line('sales_comment'), 'comment', array('class'=>'control-label col-xs-3')); ?>
        <div class='col-xs-8'>
            <?php echo form_textarea(array('name'=>'comment', 'value'=>$sale_info['comment'], 'id'=>'comment', 'class'=>'form-control input-sm', 'rows' => 4));?>
        </div>
    </div>
    <?php
    $detail_table = '';
    //print_r($details_order);
    if (count($details_order)){
        $detail_table = '<table id="detail-table-so" class="table-striped table-bordered table table-hover">';
        $detail_table .= '<thead><tr>';
        $detail_table .= '<th>'.$this->lang->line('common_id').'</th>';
        $detail_table .= '<th>'.$this->lang->line('items_item').'</th>';
        $detail_table .= '<th id="label-current">'.$this->lang->line('items_ordered').'</th>';
        $detail_table .= '<th id="label-next">'.$this->lang->line('items_shipped').'</th>';
        $detail_table .= '</tr></thead>';
        $detail_table .= '<tbody>';
        foreach ($details_order as $row_detail){
            $detail_table .= '<tr>';
            $detail_table .= '<td class="fix-align">'.$row_detail[0].'</td>';
            $detail_table .= '<td class="fix-align">'.$row_detail[1].'</td>';
            $detail_table .= '<td class="fix-align"><div class="align-right">'.($row_detail[2]*1).'</div></td>';
            $input_qty_shipped = form_input(
                array(
                    'name'=>'qty_shipped[]',
                    'id' => 'qty_shipped-'.$row_detail[0],
                    'value'=> ($row_detail[3]*1),
                    'class'=>'form-control input-sm small-input'
                )
            );
            $hidden_item_id = form_hidden('item_id[]', $row_detail[0]);
            $detail_table .= '<td>'.$input_qty_shipped.$hidden_item_id.'</td>';
            $detail_table .= '</tr>';

        }
        $detail_table .= '</tbody></table>';
    }
    ?>
    <div id="table_wrapper" class="wrapper" style="display: none;">
        <?php echo $detail_table; ?>
    </div>
</fieldset>
<?php echo form_close(); ?>

<script type="text/javascript">
    $(document).ready(function() {
        <?php $this->load->view('partial/datepicker_locale'); ?>
        var detail_table_so = '';
        $('#sale_status').on('change', function(e){
           if ($(this).val() == 2 || $(this).val() == 3 || $(this).val() == 4){
               $('#table_wrapper').css('display','inline');
               if ($(this).val() == 2){
                   $('#label-current').html('<?php echo $this->lang->line('items_ordered'); ?>');
                   $('#label-next').html('<?php echo $this->lang->line('items_shipped'); ?>');
               }else{
                   $('#label-current').html('<?php echo $this->lang->line('items_shipped'); ?>');
                   $('#label-next').html('<?php echo $this->lang->line('items_delivered'); ?>');
               }
           }else{
               $('#table_wrapper').css('display','none');
           }
        });
        $('#sales_order_edit_form').validate($.extend( {
            submitHandler: function(form) {
                $(form).ajaxSubmit({
                    success: function(response)
                    {
                        dialog_support.hide();
                        table_support.handle_submit("<?php echo site_url($controller_name); ?>", response);

                        const params = $.param(table_support.query_params());
                        $.get("<?php echo site_url($controller_name); ?>/search?" + params, function(response) {
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
