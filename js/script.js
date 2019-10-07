(function($){

	// prepare the custom functions first
	function cpt_submenu_load_post_types(id) {
		var pt_selector = $('#cpt_submenu_post_type_' + id);
		var pt_container = $('#cpt_submenu_container_' + id);
		var pt_field = $("[name='cpt_submenu_post_type[" + id + "]']");

		pt_selector.find('option').remove()
		pt_selector.append('<option></option>');
		$.each(cpts, function(cpt, data) {
			var selected = pt_field.val()==cpt?'selected':'';
			pt_selector.append('<option value="'+cpt+'" ' + selected + '>'+data.name+'</option>');
		});
		pt_container.slideDown();
		cpt_submenu_load_taxonomies(id);
	}

	function cpt_submenu_load_taxonomies(id) {
		var tax_selector = $('#cpt_submenu_taxonomy_' + id);
		var tax_container = tax_selector.parents('.cpt_submenu_taxonomy');
		var tax_field = $("[name='cpt_submenu_taxonomy[" + id + "]']");

		tax_selector.find('option').remove()
		var pt = $('#cpt_submenu_post_type_' + id).val();
		if (pt) {
			if (cpts[pt].taxes) {
				tax_selector.append('<option></option>');
				$.each(cpts[pt].taxes, function(tax, data) {
					var selected = tax_field.val()==tax?'selected':'';
					tax_selector.append('<option value="'+tax+'" ' + selected + '>'+data.name+'</option>');
				});
				cpt_submenu_show_message(id);
				tax_container.slideDown();
			}else {
				cpt_submenu_show_message(id, 'No taxonomies found for the selected post type.');
				tax_container.slideUp();
			}
		}else {
			cpt_submenu_show_message(id, 'Please choose a post type above!');
			tax_container.slideUp();
		}
		cpt_submenu_load_terms(id);
	}

	function cpt_submenu_load_terms(id) {
		var term_selector = $('#cpt_submenu_term_' + id);
		var term_container = term_selector.parents('.cpt_submenu_term');
		var term_field = $("[name='cpt_submenu_term[" + id + "]']");

		term_selector.find('option').remove()
		var pt = $('#cpt_submenu_post_type_' + id).val();
		var tax = $('#cpt_submenu_taxonomy_' + id).val();
		if (pt && tax) {
			if (cpts[pt].taxes[tax].terms) {
				$.each(cpts[pt].taxes[tax].terms, function(term, data) {
					// because terms can not be left blank, if the term_field is empty, we need to set it to the first option
					if (!term_field.val()) term_field.val(term);
					var selected = term_field.val()==term?'selected':'';
					term_selector.append('<option value="'+term+'" ' + selected + '>'+data.name+'</option>');
				});
				cpt_submenu_show_message(id);
				term_container.slideDown();
			}else {
				cpt_submenu_show_message(id, "This taxonomy doesn't have any terms.");
				term_container.slideUp();
			}
		}else {
			term_container.slideUp();
		}
		cpt_submenu_load_orders(id);
	}

	function cpt_submenu_load_orders(id) {

		// build orderby selector
		var orderby_selector = $('#cpt_submenu_orderby_' + id);
		var orderby_field = $("[name='cpt_submenu_orderby[" + id + "]']");
		var orderbys = ['menu_order', 'title', 'name','date', 'modified', 'ID', 'parent', 'comment_count', 'rand', 'none'];
		$.each(orderbys, function(id, orderby) {
			if (!orderby_field.val()) orderby_field.val(orderby);
			var selected = orderby_field.val()==orderby?'selected':'';
			orderby_selector.append('<option ' + selected + '>'+orderby+'</option>');
		});

		// build order selector
		var order_selector = $('#cpt_submenu_order_' + id);
		var order_field = $("[name='cpt_submenu_order[" + id + "]']");
		var orders = ['DESC', 'ASC'];
		$.each(orders, function(id, order) {
			if (!order_field.val()) order_field.val(order);
			var selected = order_field.val()==order?'selected':'';
			order_selector.append('<option ' + selected + '>'+order+'</option>');
		});

	}

	function cpt_submenu_clear(id) {
		$('#cpt_submenu_post_type_' + id).find('option').remove()
		$('#cpt_submenu_taxonomy_' + id).find('option').remove()
		$('#cpt_submenu_term_' + id).find('option').remove()
		$('#cpt_submenu_container_' + id).slideUp();
	}

	function cpt_submenu_show_message(id, message) {
		$('#cpt_submenu_message_' + id).html(message);
		if (message) {
			$('#cpt_submenu_message_' + id).slideDown();
		}else {
			$('#cpt_submenu_message_' + id).slideUp();
		}
	}

	// now add the watchers
	$("#menu-to-edit").on('change', "input[name^=cpt_submenu_enable]", function(){
		var id = $(this).parents('.cpt_submenu_options').data('id');
		if ($(this).is(':checked')) {
			cpt_submenu_load_post_types(id);
		}else {
			cpt_submenu_clear(id);
		}
	});

	$("#menu-to-edit").on('change', "select[id^=cpt_submenu_post_type]", function(){
		var id = $(this).parents('.cpt_submenu_options').data('id');

		// update the hidden post_type field
		$("[name='cpt_submenu_post_type[" + id + "]']").val($(this).val());
		// blank the hidden taxonomy field
		$("[name='cpt_submenu_taxonomy[" + id + "]']").val('');
		// blank the hidden term field
		$("[name='cpt_submenu_term[" + id + "]']").val('');

		cpt_submenu_load_taxonomies(id);
	});
	
	$("#menu-to-edit").on('change', "select[id^=cpt_submenu_taxonomy]", function(){
		var id = $(this).parents('.cpt_submenu_options').data('id');

		// update the hidden taxonomy field
		$("[name='cpt_submenu_taxonomy[" + id + "]']").val($(this).val());
		// blank the hidden term field
		$("[name='cpt_submenu_term[" + id + "]']").val('');

		cpt_submenu_load_terms(id);
	});

	$("#menu-to-edit").on('change', "select[id^=cpt_submenu_term]", function(){
		var id = $(this).parents('.cpt_submenu_options').data('id');
		// update the hidden term field
		$("[name='cpt_submenu_term[" + id + "]']").val($(this).val());
	});

	$("#menu-to-edit").on('change', "select[id^=cpt_submenu_orderby]", function(){
		var id = $(this).parents('.cpt_submenu_options').data('id');
		// update the hidden term field
		$("[name='cpt_submenu_orderby[" + id + "]']").val($(this).val());
	});

	$("#menu-to-edit").on('change', "select[id^=cpt_submenu_order]", function(){
		var id = $(this).parents('.cpt_submenu_options').data('id');
		// update the hidden term field
		$("[name='cpt_submenu_order[" + id + "]']").val($(this).val());
	});

	// on load, the form is already populated with selected value, we just need to show them!
	$(document).ready(function(){
		$('input[name^=cpt_submenu_enable]:checked').each(function(){
			var id = $(this).parents('.cpt_submenu_options').data('id');
			$('#cpt_submenu_container_' + id).slideDown();
			cpt_submenu_load_post_types(id);
		});
	});


})(jQuery);


