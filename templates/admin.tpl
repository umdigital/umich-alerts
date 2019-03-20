<?php
$umAlertsOptions = array_replace_recursive(
    array(
        'mode' => 'test'
    ),
    get_option( 'umich_alerts_options' ) ?: array()
);
?>
<div class="wrap">
    <h2>University of Michigan: Alerts</h2>
    <p>Emergency Alerts are maintained by DPSS and are pulled from feeds they provide.</p>
    <form method="post" action="options.php">
        <?php settings_fields( 'umich-alerts' ); ?>

        <table class="form-table">
            <tr valign="top">
                <th scope="row">Alert Mode:</th>
                <td>
                    <input type="radio" id="umich_alerts_options--mode-1" name="umich_alerts_options[mode]" value="test"<?php echo ($umAlertsOptions['mode'] == 'test' ? ' checked="checked"' : null);?> />
                    <label for="umich_alerts_options--mode-1">Development</label>

                    <input type="radio" id="umich_alerts_options--mode-2" name="umich_alerts_options[mode]" value="prod"<?php echo ($umAlertsOptions['mode'] == 'prod' ? ' checked="checked"' : null);?> />
                    <label for="umich_alerts_options--mode-2">Production</label>

                    <br/>
                    <em>Development cycles a test message every minute or two.</em>
                </td>
            </tr>
        </table>

        <?php submit_button(); ?>
    </form>
</div>
