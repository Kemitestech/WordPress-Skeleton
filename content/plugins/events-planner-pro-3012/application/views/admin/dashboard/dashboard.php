<?php

global $wpdb;

$rptm = EPL_report_model::get_instance();
?>

<div id="wpbody-content" style="min-height: 100%;">

    <div class="wrap">

        <h2><?php epl_e( 'Events Planner Dashboard' ); ?></h2>


        <div id="poststuff" style="min-height: 100%;position: relative;">
            <form id="report_form" ction="<?php echo epl_get_url(); ?>" method="post">

                <table class="table">
                    <tr>
                        <td>
                            <label>Date</label> <input type="text" name="daterange" class ="daterange" size="30" /> 

                        </td>
                        <td>
                            <?php

                            $field = array(
                                'input_name' => 'location',
                                'input_type' => 'select',
                                'options' => array( 'all' => 'All' ) + get_list_of_available_locations()
                            );

                            $field = $this->epl_util->create_element( $field );
                            ?>
                            Location <?php echo $field['field']; ?>

                        </td>
                        <td>
                            <?php

                            $field = array(
                                'input_name' => 'accounting',
                                'input_type' => 'select',
                                'options' => array( 'acc' => 'Accrual', 'cash' => 'Cash' )
                            );

                            $field = $this->epl_util->create_element( $field );
                            ?>
                            Accounting <?php echo $field['field']; ?>

                        </td>
                        <td>
                            <?php

                            $field = array(
                                'input_name' => 'event_category',
                                'input_type' => 'select',
                                'options' => array( 'all' => 'All' ) + epl_term_list()
                            );
                            $field = $this->epl_util->create_element( $field );
                            ?>
                            Categories <?php echo $field['field']; ?>

                        </td>

                    </tr>               
                    <tr>
                        <td colspan="5">
                            <input class="btn btn-primary btn-lg" type="submit" value="Submit" />
                        </td>

                    </tr>

                </table>

            </form>
            <div class="dashboard-results">



                Results will appear here.

                <?php

                $date_filter = $rptm->daterange_filter();

                $r = $wpdb->get_row( "SELECT SUM(rp.payment_amount) revenue
                        FROM {$wpdb->epl_regis_payment} rp 
                            INNER JOIN {$wpdb->epl_registration} r
                                ON r.regis_id = rp.regis_id
                             WHERE 1=1 
                             $date_filter
                        " );
                ?>

                <div class="row">
                    <div class="col-md-5">
                        <div class="panel panel-success">
                            <div class="panel-heading">
                                Revenue
                            </div>
                            <div class="panel-body">
                                <?php echo $r->revenue; ?>
                            </div>
                        </div>    
                    </div>
                    <div class="col-md-5">

                        <div class="panel panel-success">
                            <div class="panel-heading">
                                # of Events
                            </div>
                            <div class="panel-body">
                                <?php echo $r->revenue; ?>
                            </div>
                        </div>


                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">

                        <div class="panel panel-success">
                            <div class="panel-heading">
                                Top Performers
                            </div>
                            <div class="panel-body">
                                <table>
                                    <?php

                                    $terms = epl_term_list( true );
                                    echo "<pre class='prettyprint'>" . __LINE__ . "> " . basename( __FILE__ ) . " > " . print_r( $terms, true ) . "</pre>";
                                    foreach ( $terms as $term ) :



                                        $post_ids = $wpdb->get_col( "SELECT SQL_CALC_FOUND_ROWS  {$wpdb->posts}.ID FROM {$wpdb->posts}  
                                        INNER JOIN {$wpdb->term_relationships} 
                                        ON ({$wpdb->posts}.ID = {$wpdb->term_relationships}.object_id) 
                                        WHERE 1=1  
                                        AND ( {$wpdb->term_relationships}.term_taxonomy_id IN ({$term['term_id']}) ) 
                                        AND {$wpdb->posts}.post_type = 'epl_event' 
                                        AND ({$wpdb->posts}.post_status = 'publish' 
                                        OR {$wpdb->posts}.post_status = 'future' 
                                        OR {$wpdb->posts}.post_status = 'draft' 
                                        OR {$wpdb->posts}.post_status = 'pending' 
                                        OR {$wpdb->posts}.post_status = 'private') 
                                        GROUP BY {$wpdb->posts}.ID 
                                        ORDER BY {$wpdb->posts}.post_date DESC" );

                                        $post_ids = implode( ',', $post_ids );

                                        $r = $wpdb->get_row( "SELECT SUM(rp.payment_amount) revenue
                                        FROM {$wpdb->epl_regis_payment} rp 
                                            INNER JOIN {$wpdb->epl_registration} r
                                                ON r.regis_id = rp.regis_id
                                            INNER JOIN {$wpdb->epl_regis_events} re
                                                ON r.regis_id = re.regis_id
                                             WHERE 1=1 
                                             $date_filter
                                             AND re.event_id in ($post_ids)
                                                 GROUP BY rp.id
                                        " );
                                        echo "<pre class='prettyprint'>" . __LINE__ . "> " . basename(__FILE__) . " > " . print_r($wpdb->last_query, true) . "</pre>";
                                        ?>
                                        <tr>
                                            <td>
                                                <?php echo $term['name']; ?>
                                            </td>
                                            <td>
                                                <?php echo $r->revenue; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>

                                </table>
                            </div>
                        </div>

                    </div>
                    <div class="col-md-3">

                        <div class="panel panel-success">
                            <div class="panel-heading">
                                Revenue By Category
                            </div>
                            <div class="panel-body">
                                <div class="demo-container" >
                                    <div id="placeholder" style="width:90%;height:200px;" class="demo-placeholder"></div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>

        </div>

    </div>

</div>

<script>
    
    jQuery(document).ready(function($){
        
        var data = [],
        series = Math.floor(Math.random() * 6) + 3;

        for (var i = 0; i < series; i++) {
            data[i] = {
                label: "Series" + (i + 1),
                data: Math.floor(Math.random() * 100) + 1
            }
        }

        var placeholder = $("#placeholder");
        
        $.plot('#placeholder', data, {
            series: {
                pie: {
                    show: true,
                    combine: {
                        color: '#999',
                        threshold: 0.1
                    }
                }
            },
            legend: {
                show: false
            }
        });
        
        $('#report_form').on('submit', function(){
           
            //epl_block($('.dashboard-results .panel-body'));
            //return false;
           
        });
        
       
        $('.daterange').daterangepicker({
            posX: null,
            posY: null
            // onOpen:function(){ if(inframe){ $(window.parent.document).find('iframe:eq(0)').width(700).height('35em');} }, 
            //onClose: function(){ if(inframe){ $(window.parent.document).find('iframe:eq(0)').width('100%').height('5em');} }
        }); 
        
    });
    
</script>