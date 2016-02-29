<?php //echo "<pre class='prettyprint'>" . __LINE__ . "> " . print_r( $_REQUEST, true ) . "</pre>";  ?>
<style>

    #epl_lookup_table_result td {
        white-space: nowrap;
        border-bottom: 1px solid #eee;
        padding:0 5px;
    }




</style>


<table id="epl_lookup_table_result">
    <thead><tr>

            <td>First Name</td>
            <td>Last Name</td>
            <td>Email</td>

            <td></td>


        </tr>



    </thead>

    <tbody>

        <?php echo $r; ?>
    </tbody>

</table>


<script>
    
    jQuery(document).ready(function($){
        
       
        
    });
    
</script>