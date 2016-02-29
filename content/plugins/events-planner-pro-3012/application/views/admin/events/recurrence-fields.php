
<div id="recurrence_section">

    <table class="epl_form_data_table" cellspacing ="0" id ="epl_recurrence_fields_table">
        <tr class ="not_for_class">
            <td><?php epl_e('First Event Start Date'); ?></td>
            <td> <?php echo $r_f['_epl_rec_first_start_date']['field']; ?> <?php epl_e('Until'); ?> <?php echo $r_f['_epl_rec_first_end_date']['field']; ?></td>
        </tr>
        <tr class ="not_for_class">
            <td><?php epl_e('Last Event Date'); ?></td>
            <td><?php echo $r_f['_epl_recurrence_end']['field']; ?></td>
        </tr>
        <tr class ="not_for_class">
            <td><?php epl_e('Registrations Start'); ?></td>
            <td> <?php echo $r_f['_epl_rec_regis_start_date']['field']; ?> <?php epl_e('Or'); ?>
                <?php echo $r_f['_epl_rec_regis_start_days_before_start_date']['field']; ?> <?php epl_e('days before the start date'); ?>.
            </td>
        </tr>
        <tr class ="not_for_class">
            <td><?php epl_e('Registrations End'); ?></td>
            <td>  <?php echo $r_f['_epl_rec_regis_end_date']['field']; ?> <?php epl_e('Or'); ?>
                <?php echo $r_f['_epl_rec_regis_end_days_before_start_date']['field']; ?> <?php epl_e('days before the start date'); ?>.
            </td>
        </tr>

        <tr>
            <td><?php epl_e('Repeats'); ?></td>
            <td> <?php echo $r_f['_epl_recurrence_frequency']['field']; ?></td>
        </tr>
        <tr class ="">
            <td><?php epl_e('Frequency'); ?></td>
            <td><?php echo $r_f['_epl_recurrence_interval']['field']; ?></td>
        </tr>

        <tr class ="">
            <td><?php epl_e('Weekdays'); ?></td>
            <td><?php echo $r_f['_epl_recurrence_weekdays']['field']; ?>
                (<a href="#" class="check_all"><?php epl_e('All'); ?></a> | <a href="#" class="uncheck_all"><?php epl_e('None'); ?></a>)
            </td>
        </tr>
        <tr class ="">
            <td><?php epl_e('Monthly Repeat By'); ?></td>
            <td>
                <?php echo $r_f['_epl_recurrence_repeat_by']['field']; ?>
                <?php echo $r_f['_epl_recurrence_repeat_by']['description']; ?>
            </td>
        </tr>
        <tr class ="not_for_class">
            <td><?php epl_e('Capacity'); ?></td>
            <td><?php echo $r_f['_epl_recurrence_capacity']['field']; ?></td>

        </tr>
    </table>

    <p>
        <a href="#" id="recurrence_preview" class="button-primary"><?php epl_e('Preview Calendar'); ?></a>
        <a href="#" id="recurrence_process" class="button-primary not_for_class"><?php epl_e('Get Date Fields'); ?></a>
    </p>
</div>