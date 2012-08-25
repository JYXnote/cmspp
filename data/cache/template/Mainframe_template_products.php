<?php if(!defined('INCMSPP')) exit('Forbidden!'); ?>
<?php include('D:\freehost\zohm\web/data/cache/template/Mainframe_template_header.php');?>
<script type="text/javascript">
	jQuery(function(){
		jQuery('#introduction dd').hide();
		jQuery('#introduction dd').first().show();
		jQuery('#introduction dt a').click(function(){
			var thisDt = jQuery(this).parent();
			if(thisDt.next().is(':hidden'))
			{
				thisDt.parent().find('dd').hide(100,function(){
					thisDt.next().show(100);
				});
			}
		});
	});
</script>
        <div id="introduction">
            <h1>Our <span>Products</span></h1>
            <dl>
            	<dt><a href="#">CMSPP</a></dt>
            	<dd>
            		<div>
            			<p>Open source information management.</p>
            			<a href="#">Download</a>
            		</div>
            	</dd>
            	<dt><a href="#">Customer Center</a></dt>
            	<dd>
            		<div>
            			<p>Customer and user managenment.</p>
            			<a href="#">Download</a>
            		</div>
            	</dd>
            </dl>
        </div>
<?php include('D:\freehost\zohm\web/data/cache/template/Mainframe_template_footer.php');?>