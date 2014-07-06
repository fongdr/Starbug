<form <?php html_attributes($display->attributes); ?>>
<? if ($display->method == "post") { ?>
	<input class="postback" name="postback" type="hidden" value="<?= $display->postback; ?>" />
<? } ?>
<? if (!empty($display->action)) { ?>
	<input class="action" name="action[<?= $display->model; ?>]" type="hidden" value="<?= $display->action; ?>" />
<? } ?>
<?php foreach ($display->items as $item) { ?>
	<? if (!empty($item['id'])) { ?>
		<input id="id" name="<?= $display->model; ?>[id]" type="hidden" value="<?= $item['id']; ?>" />
	<? } ?>	
<?php } ?>
<?php
	if (!$display->layout->is_empty()) {
		foreach ($display->fields as $name => $field) $display->layout->append($field['pane'], $display->form_control($field['input_type'], array_merge(array($name), $field)));
		$display->layout->render();
	} else {
		foreach ($display->fields as $name => $field) echo $display->form_control($field['input_type'], array_merge(array($name), $field));
	}
?>
	<div class="row form-actions">
		<div class="col-sm-12">
			<div class="btn-group">
				<?php button("Save", "class:btn-success"); ?>
				<?php if (!empty($display->options['cancel_url'])) { ?>
					<button type="button" class="cancel btn btn-danger" onclick="window.location='<?= uri($display->options['cancel_url']); ?>'">Cancel</button>
				<?php } ?>
			</div>
		</div>
	</div>
</form>
