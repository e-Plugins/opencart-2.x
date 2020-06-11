<?php

/**
 *
 *	iDEALplugins.nl
 *  TargetPay plugin for Opencart 2.0+
 *
 *  (C) Copyright Yellow Melon 2014
 *
 *	@file 		TargetPay Catalog Template
 *	@author		Yellow Melon B.V. / www.idealplugins.nl
 *
 */

?>

<div class="row">
	<div class="col-xs-12">
		<p>
    		<input type="hidden" name="custom" value="<?php echo $custom; ?>" />   
    		<input type="button" value="<?php echo $button_confirm; ?>" id="button-confirm" class="button" />	
    	</p>
	</div>
</div>

<script type="text/javascript">
$('#button-confirm').bind('click', function() {
	$.ajax({
		url: 'index.php?route=payment/creditcard/send',
		type: 'post',
		data: $('#payment :input'),
		dataType: 'json',		
		beforeSend: function() {
			$('#button-confirm').attr('disabled', true);
			$('#payment').before('<div class="attention"><img src="catalog/view/theme/default/image/loading.gif" alt="" /> <?php echo $text_wait; ?></div>');
		},
		complete: function() {
			$('#button-confirm').attr('disabled', false);
			$('.attention').remove();
		},				
		success: function(json) {
			if (json['error']) {
				alert(json['error']);
			}
			
			if (json['success']) {
				location = json['success'];
			}
		}
	});
});
</script> 