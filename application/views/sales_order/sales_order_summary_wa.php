<?php

//print_r($data_summary_so);
print 'Mohon izin pada Ydh DTC ALP DAN seluruh tim kepemimpinan <br />';
print 'menyampaikan laporan penghantaran <br />';
print 'produk ikhwan untuk mesra petronas : ';
print '<br /><br />';
print 'Tarikh '.date('d M Y');
print '<br /><br />';
print 'Total Outlet Mesra : '.$data_summary_so['total'].'<br />';
print '*LIST OUTLET MESRA*<br />';
print '```';
print '<br />';
foreach ($data_summary_so['rows'] as $Idx => $RowData){
    $ViewedText = trim(substr($RowData['outlet'],0,20));
    $TotalStr = strlen($ViewedText);
    $AddedSpace = str_repeat('&nbsp;', (20 - $TotalStr));
    print $ViewedText.$AddedSpace.'&nbsp;:&nbsp;';
    print $RowData['sum_total_order'];
    print '<br />';
}
print '```';
print '<br /><br />';
print 'Total Produk Ikhwan : '.$detail_product_so['total'].'<br />';
print '*LIST PRODUK IKHWAN*<br />';
print '```';
print '<br />';
foreach ($detail_product_so['rows'] as $Idx => $RowData){
    if (isset($RowData['name'])) {
        $ViewedText = trim(substr($RowData['name'], 0, 25));
        $TotalStr = strlen($ViewedText);
        $AddedSpace = str_repeat('&nbsp;', (25 - $TotalStr));
        print $ViewedText . $AddedSpace . '&nbsp;:&nbsp;';
        print $RowData['total_qty'];
        print '<br />';
    }
}
print '```';