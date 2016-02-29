<?php

echo $content;
?>


<script type="text/javascript">

    jQuery(document).ready(function($) {
        var oTable = $('#<?php echo $table_id; ?>').dataTable( { 
            "bJQueryUI": true,
            "sPaginationType": "full_numbers",
            "aaSorting": [[ 2, "asc" ]],
            "iDisplayLength": 10,
            "sDom": 'T<"clear">lfrtip',
            "sScrollX": "100%",
            //"sScrollXInner": "110%",
            "bScrollCollapse": true,
                
            "oTableTools": {
                "sSwfPath": "<?php echo EPL_FULL_URL; ?>swf/copy_csv_xls_pdf.swf"
            }
           
                                
        });
                        				
    });

</script>