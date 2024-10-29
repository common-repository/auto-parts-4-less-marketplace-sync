<?php
// check user capabilities
if (! current_user_can('manage_options')) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
    return;
}

include_once AP4L_DIR . 'views/common/header.php';

$ap4l_logs_status = get_option('ap4l_logs_status');
$ap4l_logs_status = (!empty(intval($ap4l_logs_status))) ? intval($ap4l_logs_status) : 1;

$ap4l_logs_days   = AP4L_LOGS_DAYS;
?>

<div class="wrap CategoryModule ap4lModule">
    <div class="titleWrap">
        <h1 class="wp-heading-inline">AP4L Log Settings</h1>
        <hr class="wp-header-end">
    </div>
    <div class="LogsForm">
        <form id="LogsForm" action="" method="post" class="generalFormDesign">
            <table class="form-table" role="presentation">
                <tbody>
                    <input type="hidden" id="ap4l_logs_status" name="ap4l_logs_status" value="<?php echo esc_attr($ap4l_logs_status); ?>" />

                    <tr>
                        <th scope="row">
                            <label for="ap4l_logs_days">Remove logs older than number of days</label>
                        </th>
                        <td>
                            <input class="regular-text" type="number" id="ap4l_logs_days" name="ap4l_logs_days" value="<?php echo esc_attr($ap4l_logs_days); ?>" autofocus>
                            <p class="description">Default = 60. i.e. last 60 days API log will be saved into database.</p>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p class="submit">
                <input type="submit" class="button button-primary" value="Save Changes">
                <img class="loaderImage" src="<?php echo esc_url(AP4L_URL); ?>assets/images/loader.gif"/>
            </p>
        </form>
    </div>
</div>
