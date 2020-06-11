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

require_once ("system/helper/targetpay.class.php");
$targetPay = new TargetPayCore ("IDE", 93929, "bc4ea48b2540494ec38cbfa99aef6617", "nl", false);
$bankList = $targetPay->getBankList();
?>

<h2><?php echo $text_title; ?></h2>

<div class="row" id="payment">
	<div class="col-xs-12 col-sm-3">
		<p><?php echo $entry_bank_id; ?></p>
	</div>
	<div class="col-xs-12 col-sm-9">
		<p>
		<select name="bank_id">
		<?php 
		foreach ($bankList as $id => $name) 
			echo "<option value=\"".$id."\">".$name."</option>\r\n";
		?>
		</select>
		</p>
	</div>
</div>

<div class="row">
	<div class="col-xs-12 col-sm-offset-3 col-sm-9">
		<p>
    		<input type="hidden" name="custom" value="<?php echo $custom; ?>" />   
    		<input type="button" value="<?php echo $button_confirm; ?>" id="button-confirm" class="button" />	
    	</p>
	</div>
</div>

<script type="text/javascript">
$('#button-confirm').bind('click', function() {
	$.ajax({
		url: 'index.php?route=payment/ideal/send',
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