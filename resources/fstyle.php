<?php 
	#	By Kaeru~
	header('Content-type: text/css');
	
	$color = isset($_GET['c'])?$_GET['c']:'#58f';
	$inversec = isset($_GET['inversec']);
	
	function hex2rgb($c)
	{
		if ($c{0} == '#')
			$c = substr($c, 1);

		if(strlen($c) == 6)
			list($r, $g, $b) = array($c{0}.$c{1},$c{2}.$c{3},$c{4}.$c{5});
		elseif(strlen($c) == 3)
			list($r, $g, $b) = array($c{0}.$c{0},$c{1}.$c{1},$c{2}.$c{2});
		else
			return false;

		return array(hexdec($r), hexdec($g), hexdec($b));
	}
	function rgb2hex($r, $g=-1, $b=-1)
	{
		if(is_array($r) && sizeof($r) == 3)
			list($r, $g, $b) = $r;
		
		$r = intval($r);
		$g = intval($g);
		$b = intval($b);
		$r = dechex($r<0?0:($r>255?255:$r));
		$g = dechex($g<0?0:($g>255?255:$g));
		$b = dechex($b<0?0:($b>255?255:$b));

		$c = (strlen($r)<2?0:'').$r;
		$c .= (strlen($g)<2?0:'').$g;
		$c .= (strlen($b)<2?0:'').$b;
		return '#'.$c;
	}	
	function invert($palette)
	{
		$result = array();
		
		foreach($palette as $n => $color)
		{
			$newc = array();
			
			foreach(hex2rgb($color) as $c)
				$newc[] = 255-$c;
			
			$result[$n] = rgb2hex($newc);
		}
		return $result;
	}	
	function brightness($color, $how, $recursive)
	{
		$result = array();
		$min = 0;
		$max = 255;
		
		if(($color[0]+$how>=$min && $color[0]+$how<=$max) || ($color[1]+$how>=$min && $color[1]+$how<=$max) || ($color[2]+$how>=$min && $color[2]+$how<=$max))
		{
			foreach($color as $c)
				$result[] = $c+$how;
			
			if($recursive)
				$result = brightness($result, $how, true);
		}
		else
			$result = $color;			
		
		return $result;		
	}
	function makePalette($c)
	{
		$c = hex2rgb($c);
		$palette = array();
		
		$c = brightness($c, -10, true);
		
		for($i=0;$i<9;++$i)
		{
			$c = brightness($c, 40, false);
			$palette[$i] = rgb2hex($c);
		}
		
		return $palette;
	}
	
	$c1 = makePalette($color);
	
	if($inversec)
		$c1 = invert($c1);
?>
* {
	margin: 0;
}
html, body {
	height: 100%;
}
#wrapper {
	min-height: 100%;
	height: auto !important;
	height: 100%;
	margin: 0 auto -20px;
}
#foot, #push {
	height: 20px;
}
body{
	background: <?php echo $c1[8]; ?> url('bg.png');
	font-family: "DejaVu Sans", sans-serif;
	font-size: 12px;
}
/*	Links	*/
a{
	text-decoration: underline;
}
a:link{
	color: <?php echo $c1[0]; ?>;
}
a:visited{
	color: <?php echo $c1[0]; ?>;
}
a:hover{
	color: <?php echo $c1[1]; ?>;
}
a:active{
	color: <?php echo $c1[1]; ?>;
}
a#more, a#less{
	font-weight: bold;
	font-size: 0.9em;
	text-decoration: none;
}
.opts a{
	text-decoration: none;
	font-size: 0.9em;
}
/*	Table, rows, cells	*/
table#list{
	width: 100%;
	border-collapse: collapse;
}
table#list tr{
	border: <?php echo $c1[3]; ?> 1px solid;
}
table#list th{
	padding: 4px;
}
table#list td{
	padding: 4px;
	color: <?php echo $c1[3]; ?>;
}
table#list tr.d2{
	background: <?php echo $c1[3]; ?> url('thead.png') repeat-x;
	color: <?php echo $c1[8]; ?>;
	font-weight: bold;
	font-size: 1.1em;
	padding: 4px;
}
table#list tr.d1{
	background: <?php echo $c1[6]; ?> url('tr1.png') repeat-x;
}
table#list tr.d0{
	background: <?php echo $c1[7]; ?> url('tr1.png') repeat-x;
}
tr.d0, tr.d1:hover{
	background: <?php echo $c1[5]; ?> url('tr2.png') repeat-x;
}
table#list th{
	cursor: pointer;
}
/*	Divs	*/
.msg{
	background: <?php echo $c1[7]; ?>;
	font-weight: bold;
	font-size: 1.1em;
	padding: 20px;
	margin: 2px 8px;
	color: <?php echo $c1[2]; ?>;
	border: <?php echo $c1[3]; ?> 1px solid;
}
div#main, div.box{
	padding: 4px;
}
a#showlog{
	z-index: 999;
	text-decoration: none;
}
#log{
	border-bottom: <?php echo $c1[3]; ?> 1px solid;
	color: <?php echo $c1[2]; ?>;
	padding: 4px;
	background: url('menu.png');
	position: fixed;
	top: 0px;
	left: 0px;
	text-align: center;
	vertical-align: middle;
	display: none;
	width: 100%;
}
#log a{
	color: <?php echo $c1[8]; ?> !important;
}
#foot{
	font-size: 0.9em;
	color: <?php echo $c1[1]; ?>;
}
/*	Misc	*/
div#main input[type="file"] {
	display: block;
	margin: 2px;
}
.imp{
	font-weight: bold;
	color: <?php echo $c1[7]; ?>;
	font-size: 1.2em;
}
.left{
	float: left;
	text-align: center;
	vertical-align: middle;
}
.right{
	float: right;
	text-align: center;
	vertical-align: middle;
}
.pl{
	padding-left: 30px;
}
.pr{
	padding-right: 30px;
}
abbr{
	border-bottom: <?php echo $c1[2]; ?> 1px dotted;
}
