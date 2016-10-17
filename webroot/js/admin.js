(function($) {
	$(document).ready(function() {
		// check/uncheck all check boxes when the #select-all is clicked
		var select_all = $('#select-all');
		if(select_all) {
			select_all.on('click', function(e) {
				$('.checkbox').each(function(i, el) {
					el = $(el);
					if(select_all.is(':checked')) {
						el.attr('checked', 'checked');
					}
					else {
						el.removeAttr('checked');
					}
				});
			});
		}
	});
})(jQuery);