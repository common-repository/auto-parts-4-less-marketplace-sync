<?php
$alert_req = (!empty($_REQUEST['alert'])) ? sanitize_text_field($_REQUEST['alert']) : '';
$msg_req = (!empty($_REQUEST['msg'])) ? sanitize_text_field($_REQUEST['msg']) : '';
$action_req = (!empty($_REQUEST['action'])) ? sanitize_text_field($_REQUEST['action']) : '';
$page_req = (!empty($_REQUEST['page'])) ? sanitize_text_field($_REQUEST['page']) : '';

$class   = ($alert_req == 'error') ? 'alert-danger' : 'd-none';
$message = $msg_req;

if ($alert_req == 'success') {
    $class = 'alert-success';
} elseif ($action_req == 'trash') {
    $class = 'alert-success';

    if ($page_req == 'ap4l-accounts') {
        $message = 'Account deleted successfully.';
    } elseif ($page_req == 'ap4l-policies') {
        $message = 'Policies deleted successfully.';
    } elseif ($page_req == 'ap4l-listings') {
        $message = 'Listing deleted successfully.';
    }
}
?>

<div class="ap4lError alert alert-info alert-dismissible fade show <?php echo esc_attr($class); ?>" role="alert" id="alert">
    <div><?php echo wp_kses($message, AP4L_ALLOWED_HTML); ?></div>
</div>
