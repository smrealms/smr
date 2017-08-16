This uses the last newsletter to be added to the DB!<br />
Please enter an eMail address where the newsletter should be sent (* for all):
<?php echo create_echo_form($ProcessingContainer); ?>
<input type="text" name="to_email" value="<?php echo htmlspecialchars($CurrentEmail); ?>" id="InputFields" size="25">&nbsp;
<?php echo create_submit('Send'); ?>
</form>
