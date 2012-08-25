<?php if(!defined('INCMSPP')) exit('Forbidden!'); ?>
<?php include('D:\freehost\zohm\web/data/cache/template/Mainframe_template_header.php');?>
<script type="text/javascript">
	jQuery(function(){
		jQuery('#manualIndex a').click(function(){
			jQuery.get('http://dev.cmspp.net/plugins/Mainframe/static//manual/'+jQuery(this).html().toLowerCase()+'.html',function(html,state){
				jQuery('#manualText').html(html);
			});
		});
		jQuery('#manualIndex a').first().click();
	});
</script>
        <div id="manualIndex">
            <h1>CMSPP <span>Manual</span></h1>
            <dl>
            	<dt>Copyright</dt>
        		<dd>
        			<ul>
        				<li><a href="#manualText">Copyright</a></li>
        			</ul>
        		</dd>
            	<dt>Getting Started</dt>
        		<dd>
        			<ul>
        				<li><a href="#manualText">Introduction</a></li>
        			</ul>
        		</dd>
            	<dt>Installation and Configuration</dt>
        		<dd>
        			<ul>
        				<li><a href="#manualText">Installation</a></li>
        				<li><a href="#manualText">Configuration</a></li>
        			</ul>
        		</dd>
        		<dt>Reference</dt>
        		<dd>
        			<ul>
        				<li><a href="#manualText">Plugins</a></li>
        				<li><a href="#manualText">Router</a></li>
        				<li><a href="#manualText">Template</a></li>
        			</ul>
        		</dd>
            </dl>
        </div>
        <div id="manualText">
        	
        </div>
<?php include('D:\freehost\zohm\web/data/cache/template/Mainframe_template_footer.php');?>