<div class="cpt_submenu_options" data-id="<?php echo $id; ?>">
	<p class="description">
		<label for="cpt_submenu_enable_<?php echo $id; ?>">
			<input type="checkbox" id="cpt_submenu_enable_<?php echo $id; ?>" value="_blank" name="cpt_submenu_enable[<?php echo $id; ?>]" <?php echo $data['enabled']?'checked':''; ?>>
			Enable CPT Submenu
		</label>
	</p>
	<div id="cpt_submenu_container_<?php echo $id; ?>" class="cpt_submenu_container">
		<div class="cpt_submenu_field cpt_submenu_post_type">
			<label for="cpt_submenu_post_type_<?php echo $id; ?>">Post Type</label>
			<input type="hidden" name="cpt_submenu_post_type[<?php echo $id; ?>]" value="<?php echo $data['post_type']; ?>" />
			<select id="cpt_submenu_post_type_<?php echo $id; ?>"></select>
		</div>
		<div class="cpt_submenu_field cpt_submenu_taxonomy">
			<label for="cpt_submenu_taxonomy_<?php echo $id; ?>">Taxonomy (optional)</label>
			<input type="hidden" name="cpt_submenu_taxonomy[<?php echo $id; ?>]" value="<?php echo $data['taxonomy']; ?>" />
			<select id="cpt_submenu_taxonomy_<?php echo $id; ?>"></select>
		</div>
		<div class="cpt_submenu_field cpt_submenu_term">
			<label for="cpt_submenu_term_<?php echo $id; ?>">Term</label>
			<input type="hidden" name="cpt_submenu_term[<?php echo $id; ?>]" value="<?php echo $data['term']; ?>" />
			<select id="cpt_submenu_term_<?php echo $id; ?>"></select>
		</div>
		<div id="cpt_submenu_message_<?php echo $id; ?>" class="cpt_submenu_field cpt_submenu_message"></div>
		<div class="cpt_submenu_field cpt_submenu_orderby">
			<label for="cpt_submenu_orderby_<?php echo $id; ?>">Order By</label>
			<input type="hidden" name="cpt_submenu_orderby[<?php echo $id; ?>]" value="<?php echo $data['orderby']; ?>" />
			<select id="cpt_submenu_orderby_<?php echo $id; ?>"></select>
		</div>
		<div class="cpt_submenu_field cpt_submenu_order">
			<label for="cpt_submenu_order_<?php echo $id; ?>">Order</label>
			<input type="hidden" name="cpt_submenu_order[<?php echo $id; ?>]" value="<?php echo $data['order']; ?>" />
			<select id="cpt_submenu_order_<?php echo $id; ?>"></select>
		</div>
	</div>
</div>
