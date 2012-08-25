<?php
	$rVal = 'ERROR';
	if(isset($_GET['code']))
		$rVal=$_GET['code'];
	else if(isset($_GET['openkey']))
		$rVal = $_GET['openkey'];
?>
<script type="text/javascript">
	location.href='about:blank#code=<?php echo $rVal;?>';
</script>