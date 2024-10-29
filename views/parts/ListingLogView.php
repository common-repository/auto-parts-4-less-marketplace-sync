<?php
$action_req = (!empty($_REQUEST['action'])) ? sanitize_text_field($_REQUEST['action']) : '';
$logid_req = (!empty($_REQUEST['logid'])) ? sanitize_text_field($_REQUEST['logid']) : 0;

if (!empty($logid_req) && ($action_req == 'view')) {
    $logId   = $logid_req;
    $logData = $this->getAllListingLogs($logId);
    $logData = $logData[0];
}
?>

<div class="SyncPolicyForm">
    <form id="SyncPolicyForm" action="" method="post" class="generalFormDesign">
        <h2>Listing Log Id : <?php echo wp_kses($logid_req, AP4L_ALLOWED_HTML); ?> </h2>
        <hr>
        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row">Cron</th>
                    <td>
                        <p class="description"><?php echo wp_kses($logData->cron, AP4L_ALLOWED_HTML); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Listing ID</th>
                    <td>
                        <p class="description"><?php echo wp_kses($logData->listing_id, AP4L_ALLOWED_HTML); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Product ID</th>
                    <td>
                        <p class="description"><?php echo wp_kses($logData->product_id, AP4L_ALLOWED_HTML); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">API Endpoint</th>
                    <td>
                        <p class="description"><?php echo wp_kses($logData->api_endpoint, AP4L_ALLOWED_HTML); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">API Payload</th>
                    <td>
                        <textarea rows="5" cols="100"><?php echo wp_kses(json_encode(json_decode($logData->api_payload), JSON_PRETTY_PRINT), AP4L_ALLOWED_HTML); ?></textarea>
                    </td>
                </tr>
                <tr>
                    <th scope="row">API Response</th>
                    <td>
                        <p class="description">
                            <textarea rows="5" cols="100"><?php echo wp_kses(json_encode(json_decode($logData->api_response), JSON_PRETTY_PRINT), AP4L_ALLOWED_HTML); ?></textarea>
                        </p>

                    </td>
                </tr>
                <tr>
                    <th scope="row">API Request At</th>
                    <td>
                        <p class="description"><?php echo wp_kses($this->getTimeFormated($logData->request_at), AP4L_ALLOWED_HTML); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">API Response At</th>
                    <td>
                        <p class="description"><?php echo wp_kses($this->getTimeFormated($logData->response_at), AP4L_ALLOWED_HTML); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">API Response Code</th>
                    <td>
                        <p class="description"><?php echo wp_kses($logData->resposne_code, AP4L_ALLOWED_HTML); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">API Comment</th>
                    <td>
                        <p class="description"><?php echo wp_kses($logData->message, AP4L_ALLOWED_HTML); ?></p>
                    </td>
                </tr>
            </tbody>
        </table>
    </form>
</div>