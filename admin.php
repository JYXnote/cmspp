<?php
include ('./config/config.inc.php');
include ('./include/Cmspp.php');
if(!empty($_POST))
{
    Cmspp::init(false);
 	if($_POST['line'])
 		echo CommandLine::command($_POST['line']);
 	exit();
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Admin</title>
<script type="text/javascript" src="lib/jQuery/jquery-1.7.2.js"></script>
<style type="text/css">
body
{
	background:#EEE;
}
#monitor
{
	background:#000;
	color:#FFF;
	font:"MS Serif", "New York", serif;
	width:667px;
	overflow:hidden;
	resize: none;
	outline:none;
}
#command
{
	background:#000;
	color:#FFF;
	font:"MS Serif", "New York", serif;
	width:667px;
}
</style>
<script type="text/javascript">
var historyCmds = new Array();
var lastHistoryLine = 0;
jQuery(function(){ 
	jQuery("#command").keydown(
		function(e)
		{ 
			var curKey = e.which;
			if(curKey == 13)
			{
				lastHistoryLine = 0;
				var commond = jQuery("#command").val().replace(/(^\s*)|(\s*$)/g, "");
				historyCmds.push(commond);
				switch(commond)
				{
					case 'cls':
						jQuery('#monitor').val('');
						break;
					default:
						jQuery('#monitor').val(jQuery('#monitor').val() + "#" + jQuery('#command').val() + '\n');
						jQuery('#monitor').scrollTop(jQuery('#monitor')[0].scrollHeight);
						jQuery.post('admin.php',{line:commond},function(data,statu,request){
							if(data == '')
								return false;
							jQuery('#monitor').val(jQuery('#monitor').val() + data + '\n');
							jQuery('#monitor').scrollTop(jQuery('#monitor')[0].scrollHeight);
						});
				}
				jQuery('#command').val('');
				return false; 
			}
			else if(curKey == 38)
			{
				if(historyCmds.length == 0)return;
				if(historyCmds.length < lastHistoryLine+1)return;
				lastHistoryLine++;
				jQuery('#command').val(historyCmds[historyCmds.length-lastHistoryLine]);
			}
			else if(curKey == 40)
			{
				if(historyCmds.length == 0)return;
				if(historyCmds.length < lastHistoryLine-1)return;
				lastHistoryLine--;
				jQuery('#command').val(historyCmds[historyCmds.length-lastHistoryLine]);
			}
		});
	    window.onbeforeunload = function() {
            jQuery.post('admin.php',{line:'exit'});
		    return '';
        };
		jQuery.post('admin.php',{line:"hello"},function(data,statu,request){
							if(data == '')
								return false;
							jQuery('#monitor').val(jQuery('#monitor').val() + data + '\n');
		});
});
</script>
</head>
<body>
	<div id="consle">
<textarea cols="81" rows="20" id="monitor" readonly="readonly">
</textarea>
</div>
    <div id="commandBox">
    	<input type="text" id="command" size="81" />
    </div>
</body>
</html>