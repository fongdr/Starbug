<h2>Checkout</h2>
<div data-dojo-type="payment/Cart" data-dojo-props="mode:'checkout', target:'<?php echo $this->url->build('checkout/payment'); ?>', order:<?php echo str_replace('"', "'", json_encode($cart->getOrder())); ?>"></div>
<div style="clear:both"></div>
<?php $this->displays->render("CheckoutForm", array("id" => $cart->get("id"))); ?>
<?php if ($this->url->getScheme() == "https") { ?>
<div style="clear:both">
	<table width=130 border=0 cellspacing=0 cellpadding=0 title="CLICK TO VERIFY: This site uses a GlobalSign SSL Certificate to secure your personal information." ><tr><td><span id="ss_img_wrapper_130-65_flash_en"><a href="http://www.globalsign.com/" target=_blank title="SSL"><img alt="SSL" border=0 id="ss_img" src="//seal.globalsign.com/SiteSeal/images/gs_noscript_130-65_en.gif"></a></span><script type="text/javascript" src="//seal.globalsign.com/SiteSeal/gs_flash_130-65_en.js" ></script><br /><a href="http://www.globalsign.com/" target=_blank  style="color:#000000; text-decoration:none; font:bold 8px arial; margin:0px; padding:0px;">SSL</a></td></tr></table>
</div>
<?php } ?>
