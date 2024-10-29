<?php
$action_req = (!empty($_REQUEST['action'])) ? sanitize_text_field($_REQUEST['action']) : '';
$logid_req = (!empty($_REQUEST['logid'])) ? sanitize_text_field($_REQUEST['logid']) : 0;

if (!empty($logid_req) && ($action_req == 'view')) {
    $logId   = $logid_req;
    $logData = $this->getAllOrderLogs($logId);
    $logData = $logData[0];
}
?>

<div class="SyncPolicyForm">
    <form id="SyncPolicyForm" action="" method="post" class="generalFormDesign">
        <h2>Order Log Id : <?php echo wp_kses($logid_req, AP4L_ALLOWED_HTML); ?> </h2>
        <hr>
        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row">API Endpoint</th>
                    <td>
                        <p class="description"><?php echo wp_kses($logData->api_endpoint, AP4L_ALLOWED_HTML); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Seller</th>
                    <td>
                        <p class="description"><?php echo wp_kses($logData->seller_id, AP4L_ALLOWED_HTML); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">API Payload</th>
                    <td>
                        <textarea rows="5" cols="100"><?php echo wp_json_encode(json_decode($logData->api_payload), JSON_PRETTY_PRINT)?></textarea>
                    </td>
                </tr>
                <tr>
                    <th scope="row">API Request At</th>
                    <td>
                        <p class="description"><?php echo wp_kses($this->getTimeFormated($logData->request_at), AP4L_ALLOWED_HTML); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">API Response</th>
                    <td>
                        <p class="description">
                            <textarea rows="5" cols="100"><?php echo wp_json_encode(json_decode($logData->api_response), JSON_PRETTY_PRINT); ?></textarea>
                        </p>

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