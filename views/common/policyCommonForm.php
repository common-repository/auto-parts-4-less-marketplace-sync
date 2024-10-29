<?php
$action_req = (!empty($_REQUEST['action'])) ? sanitize_text_field($_REQUEST['action']) : '';
?>

<tr>
    <th scope="row">
        <label for="policyName">Policy Name<span>*</span></label>
    </th>
    <td>
        <input class="regular-text" type="text" id="policyName" name="policyName" required="" value="<?php echo esc_attr($policy_name); ?>" autofocus>
        <p class="description">You can name your policy anything, this is only for your own reference.</p>
    </td>
</tr>

<?php if ($action_req == 'edit' && 1 == 0) : ?>
    <tr>
        <th scope="row">
            <label for="policyStatus">Policy Status</label>
        </th>
        <td>
            <input class="regular-text" type="text" id="policyStatus" name="policyStatus" type="checkbox" name="policyStatus" value="1" <?php checked(1, $policyStatus); ?>>
            <p class="description">(Enabled/Disabled)</p>
        </td>
    </tr>
<?php endif; ?>
