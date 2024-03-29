<?php $this->load->view("partial/header"); ?>

<?php
if(isset($error_message))
{
    echo "<div class='alert alert-dismissible alert-danger'>".$error_message."</div>";
    exit;
}
?>

<?php if(!empty($customer_email)): ?>
    <script type="text/javascript">
        $(document).ready(function()
        {
            var send_email = function()
            {
                $.get('<?php echo site_url() . "purchase_order/send_pdf/" . $purchase_order_id_num; ?>',
                    function(response)
                    {
                        $.notify({ message: response.message }, { type: response.success ? 'success' : 'danger'});
                    }, 'json'
                );
            };

            $("#show_email_button").click(send_email);

            <?php if(!empty($email_receipt)): ?>
            send_email();
            <?php endif; ?>
        });
    </script>
<?php endif; ?>

<?php $this->load->view('partial/print_receipt', array('print_after_sale'=>$print_after_sale, 'selected_printer'=>'invoice_printer')); ?>

<div class="print_hide" id="control_buttons" style="text-align:right">
    <a href="javascript:printdoc();"><div class="btn btn-info btn-sm", id="show_print_button"><?php echo '<span class="glyphicon glyphicon-print">&nbsp</span>' . $this->lang->line('common_print'); ?></div></a>
    <?php /* this line will allow to print and go back to sales automatically.... echo anchor("sales", '<span class="glyphicon glyphicon-print">&nbsp</span>' . $this->lang->line('common_print'), array('class'=>'btn btn-info btn-sm', 'id'=>'show_print_button', 'onclick'=>'window.print();')); */ ?>
    <?php if(isset($supplier_email) && !empty($supplier_email)): ?>
        <a href="javascript:void(0);"><div class="btn btn-info btn-sm", id="show_email_button"><?php echo '<span class="glyphicon glyphicon-envelope">&nbsp</span>' . $this->lang->line('po_send_invoice'); ?></div></a>
    <?php endif; ?>
    <?php echo anchor("sales_order/manage", '<span class="glyphicon glyphicon-list-alt">&nbsp</span>' . $this->lang->line('purchase_order_list'), array('class'=>'btn btn-info btn-sm', 'id'=>'show_takings_button')); ?>
</div>
<div id="page-wrap">
    <?php
    if($this->Appconfig->get('company_logo') != '')
    {
        ?>
        <div class="company-logo-po">
        <img width="100" style="vertical-align: middle;"  id="image" src="<?php echo base_url('uploads/' . $this->Appconfig->get('company_logo')); ?>" alt="company_logo" />
        </div>
        <?php
    }
    ?>
    <?php
    if(isset($company_info))
    {
    ?>
        <div class="company-title-po"><?php echo nl2br($company_info) ?></div>
    <?php
    }
    ?>
</div>
<div id="page-wrap">
    <div id="header"><?php echo $page_title; ?></div>
    <div id="block1">

    </div>

    <div id="block2">
        <div id="company-title"><?php echo nl2br($supplier_info); ?></div>
        <table id="meta">
            <tr>
                <td class="meta-head"><?php echo $this->lang->line('purchase_order_number');?> </td>
                <td><?php echo $po_number; ?></td>
            </tr>
            <tr>
                <td class="meta-head"><?php echo 'Purchase Order '. $this->lang->line('common_date'); ?></td>
                <td><?php echo $transaction_date; ?></td>
            </tr>
            <tr>
                <td class="meta-head"><?php echo $this->lang->line('purchase_order_total'); ?></td>
                <td><?php echo $total; ?></td>
            </tr>
        </table>
    </div>

    <table id="items">
        <tr>
            <th><?php echo $this->lang->line('sales_item_number'); ?></th>
            <?php
            $invoice_columns = 3;
            ?>
            <th><?php echo $this->lang->line('sales_item_name'); ?></th>
            <th><?php echo $this->lang->line('sales_quantity'); ?></th>
            <th><?php echo $this->lang->line('sales_price'); ?></th>
            <th><?php echo $this->lang->line('sales_total'); ?></th>
        </tr>

        <?php
        //var_dump($cart);
        foreach($cart as $line=>$item)
        {
            ?>
            <tr class="item-row">
                <td><?php echo $item->item_number; ?></td>
                <td><?php echo $item->name; ?></td>
                <td style='text-align:center;'><?php echo to_quantity_decimals($item->quantity_purchased); ?></td>
                <td><?php echo to_currency($item->item_unit_price); ?></td>
                <td><?php echo to_currency($item->quantity_purchased*$item->item_unit_price); ?></td>
            </tr>
            <?php
        }
        ?>
    </table>

    <div id="terms">
        <div id="sale_return_policy">
            <h5>
                <div style='padding:4%;'><?php echo empty($comments) ? '' : $this->lang->line('sales_comments') . ': ' . $comments; ?></div>
                <div style='padding:4%;'><?php echo $this->config->item('invoice_default_comments'); ?></div>
            </h5>
            <div style='padding:2%;'><?php echo nl2br($this->config->item('return_policy')); ?></div>
        </div>
        <div id='barcode'>
            <img style='padding-top:4%;' src='data:image/png;base64,<?php echo $barcode; ?>' /><br>
            <?php echo $po_number; ?>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(window).on("load", function()
    {
        // install firefox addon in order to use this plugin
        if(window.jsPrintSetup)
        {
            <?php if(!$this->Appconfig->get('print_header'))
            {
            ?>
            // set page header
            jsPrintSetup.setOption('headerStrLeft', '');
            jsPrintSetup.setOption('headerStrCenter', '');
            jsPrintSetup.setOption('headerStrRight', '');
            <?php
            }

            if(!$this->Appconfig->get('print_footer'))
            {
            ?>
            // set empty page footer
            jsPrintSetup.setOption('footerStrLeft', '');
            jsPrintSetup.setOption('footerStrCenter', '');
            jsPrintSetup.setOption('footerStrRight', '');
            <?php
            }
            ?>
        }
    });
</script>

<?php $this->load->view("partial/footer"); ?>
