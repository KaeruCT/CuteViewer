<?php 

	//	By Kaeru~
	
	//	Configurable stuff
	$pass = sha1('changeme');// Change the password.
	$hiddenDirs = array('.', '..', '.htaccess'); // Add your own hidden directories
	$fileDir = 'resources/'; // If you the additional resources to another directory, change this variable
	
	session_start();
	ini_set('max_execution_time', 0);
	ini_set('upload_max_filesize', '8M');
	$tformat = "Y-m-d H:i:s";
	$copyd = '2009'.(date("Y", time())>2009?' - '.date("Y", time()):'');
	$out = $title = $ptitle = $js = '';
	$backUrl = '';
	
	$colors = array(
		'afa' => 'Green',
		'faf' => 'Purple',
		'aff' => 'Cyan',
		'aaf' => 'Blue',
		'faa' => 'Red',
		'ffa' => 'Gold',
		'fff' => 'Silver'
	);

	$select = '
			<select id="change-color">';
	
	$mdir = end(explode('/', dirname(__file__)));
	
	$dir = isset($_GET['d'])?stripslashes(trim($_GET['d'])):'.';
	
	$dir = $opendir = preg_replace('%\.+|/{1,}%', '', $dir);
	
	$dir = $opendir = $dir?$dir:'.';
	
	if(in_array(substr(str_replace('/', '', $dir), 0, strlen($mdir)), $hiddenDirs))
		$dir = $opendir = '.';
	
	$mode = isset($_GET['m'])?stripslashes($_GET['m']):'view';
	$l = $_SESSION['pass']==$pass;
	
	$c = isset($_GET['c'])?$_GET['c']:'5ff';
	$color = isset($_COOKIE['cl'])?$_COOKIE['cl']:$c;
	
	if(isset($_GET['c'])){
		$color = $c;
	}
	
	foreach($colors as $n => $c)
	{
		$select .=	'
				<option value="'.$n.'"'.($color==$n?'selected="selected"':'').'>'.$c.'</option>';
	}
	
	$select .= '			
	'.	'		</select>';
	
	$log =	'<div class="left pr pl">
	'.		'		'.($l?'You are logged in!':'You are not logged in!
	'.		'		<form action="?m=login&d='.$dir.'" method="post">
	'.		'			Password: <input type="password" name="pass" />&nbsp;
	'.		'			<input type="submit" value="Log in!" />
	'.		'		</form>').'
	'.		'	</div>
	'.		'	<div class="right pl pr">
	'.		'		Color: '.$select.'
	'.		'	</div>';
	
	function dirsort($a, $b)
	{
		global $opendir;
		
		if(is_dir($opendir.'/'.$a))
			return -1;
		elseif(is_dir($opendir.'/'.$b))
			return 1;
		else
			return filemtime($opendir.'/'.$a)>filemtime($opendir.'/'.$b)?-1:1;
	}
	
	function mkname($m)
	{
		global $dir;
		return '<a href="'.$_SERVER['PHP_SELF'].'?d='.substr($dir, 0, strpos($dir, $m[1])+strlen($m[1])).'">'.$m[1].'</a>/';
	}
	
	function mkdirname($link)
	{
		global $mdir, $dir;
		
		$name = '/'.($link?'<a href='.$_SERVER['PHP_SELF'].'>'.$mdir.'</a>':$mdir).'/'.($dir=='.'?'':($link?preg_replace_callback('%(([0-9]|[A-Za-z]|[_\./-])+?)\/%', 'mkname', $dir.'/'):($dir.'/')));
		
		return $name;
	}
	
	function show_thumbnail($filename){
		$imgfiles = array('.gif', 'jpeg', '.jpg', '.png', 'apng', '.svg', '.bmp');
		
		return in_array(strtolower(substr($filename, -4)), $imgfiles)
			&& filesize($filename) < 1024000;
	}
	
	function display_filesize($bytes)
	{
		$units =	array(
						'<abbr title="bytes">bytes',
						'<abbr title="kibibytes">KiB',
						'<abbr title="mebibytes">MiB',
						'<abbr title="gibibytes">GiB',
						'<abbr title="tebibytes">TiB',
						'<abbr title="pebibyte">PiB'
					);
		
		$bytes = max($bytes, 0);
		$pow = floor(($bytes?log($bytes):0)/log(1024));
		$pow = min($pow, count($units)-1);

		$bytes /= pow(1024, $pow);

		return round($bytes, 2).' '.$units[$pow].'</abbr>';
	}
	
	function remove_dir($current_dir)
	{
		$current_dir = $current_dir.'/';
		
		if($dir = @opendir($current_dir))
		{
			while (($f = readdir($dir)) !== false)
			{
				if($f > '0' && filetype($current_dir.$f) == 'file')
					unlink($current_dir.$f);
				elseif($f > '0' && filetype($current_dir.$f) == 'dir')
					remove_dir($current_dir.$f.'\\');
			}
		}
		closedir($dir);
		rmdir($current_dir);
		return true;
	}
	
	function safe_filename($str)
	{
		$str = str_replace(' ', '_', $str);

		$result = '';
		for ($i=0; $i<strlen($str); ++$i)
			if(preg_match('([0-9]|[A-Za-z]|[_\./-])', $str{$i}))
				$result .= $str{$i};

		return $result;
	}
	
	if($mode == 'login')
	{
		$passhash = sha1($_POST['pass']);
		if($passhash==$pass)
		{
			$_SESSION['pass'] = $passhash;
			$log = 'You are logged in!';
			$l = true;
		}else{
			$js = '$("#showlog").click();';
		}
		
		$mode = 'view';
	}
	
	if($mode == 'color')
	{
		$ap = '(color changed)';
		$c = isset($_GET['c'])?$_GET['c']:'Blue';
		setcookie('cl', $c, time()+31556926);
		$color = $c;
		
		$mode = 'view';
	}
	
	switch($mode)
	{
		case 'view':
		default:
		
			$fcount = $dcount = $i = 0;
			$out = '';
			
			if(file_exists($opendir))
			{
				$noback = true;
				$files = array_diff(scandir($opendir), $hiddenDirs);
				usort($files, 'dirsort');
				
				foreach($files as $file)
				{
					$furl = str_replace('./', '', $dir.'/'.$file);
					$fname = !is_dir($furl)?$file:'/'.$file.'/';
					$out .=	'<tr class="d'.($i++%2).'">
			'.					'	<td>
			'.					'		<div class="left">
			'.					'			'.(show_thumbnail($furl)?'
			'.					'			<img src="'.$furl.'" alt="'.$fname.'" width="16px" height="16px" /> ':'').'
			'.					'			<a href="'.(is_dir($furl)?'?d='.$furl:$furl).'">'.$fname.'</a>
			'.					'		</div>'.($l?'<div class="right opts">
			'.					'			<a href="#" class="rename" id="'.$file.'" title="Rename '.$fname.'...">rename</a>
			'.					'			- <a class="delete" title="Delete '.$fname.'..." href="?m=del&amp;f='.($furl).'">delete</a>
			'.					'		</div>':'').'
			'.					'	</td><td style="text-align: center;">
			'.					'		'.date($tformat, filemtime($furl)).'
			'.					'	</td><td>
			'.					'		'.(!is_dir($furl)?display_filesize(filesize($furl)):'&nbsp;').'
			'.					'	</td>
			'.					'</tr>';
					if(is_dir($furl)) ++$dcount; else ++$fcount;
				}
			
				$title = ' ('.$fcount.' file'.($fcount==1?'':'s').', '.$dcount.' director'.($dcount==1?'y':'ies').')';
				$ptitle =  mkdirname(false).$title;
				$title = mkdirname(true).$title;
				$opt = $l?'<a href="?m=upload&amp;d='.$dir.'">Upload files to '.mkdirname(false).'</a><br /><a class="create-dir" href="#">Create directory</a>':'';
				$out =	'<table id="list" cellpadding="0">
	'.					'	'.($out?('<thead>
	'.					'		<tr class="d2">
	'.					'			<th class="h" title="Sort by file/directory name...">
	'.					'				File/directory name
	'.					'			</th><th class="h" width="200px" title="Sort by date...">
	'.					'				Last modified/created
	'.					'			</th><th width="70px" class="h" title="Sort by file size...">
	'.					'				File size</th>
	'.					'		</tr>
	'.					'	</thead>
	'.					'	<tbody>
	'.					'		'.$out.'
	'.					'	</tbody>'):'
	'.					'	<tr class="d2">
	'.					'		<td>
	'.					'			There are no files in this directory.
	'.					'		</td>
	'.					'	</tr>').'
	'.					'</table>';
				$js .=	'$("a.delete").click(function(){
	'.					'	return confirm("Are you sure?\nOnce you delete this, it will be gone forever!");
	'.					'});
	'.					'$("td").filter(":not(td.h)").hover(function(){
	'.					'	$(this).addClass("highlight").siblings().addClass("highlight2");
	'.					'}, function(){
	'.					'	$(this).removeClass("highlight").siblings().removeClass("highlight2");
	'.					'});
	'.					'$("a.rename").click(function(){
	'.					'	oldname = $(this).attr("id");
	'.					'	newname = prompt("New file name:", oldname);
	'.					'	if(newname && newname != oldname)
	'.					'		top.location="?m=rename&new='.$opendir.'/"+newname+"&old='.$opendir.'/"+oldname;
	'.					'	else
	'.					'		return false;
	'.					'});
	'.					'$("a.create-dir").click(function(){
	'.					'	dirname = prompt("The new directory will be created inside this directory.\nDirectory name:", "");
	'.					'	if(dirname)
	'.					'		top.location="?m=createdir&new='.$opendir.'/"+dirname;
	'.					'	else
	'.					'		return false;
	'.					'});
	'.					'$("table#list").tablesorter(); ';
			}
			else
			{
				$title = $ptitle = 'Error: not found';
				$out = 'Directory not found.';
			}
			
			break;
			
		case 'upload':
		
			$title = $ptitle = 'Upload files to '.mkdirname(false);
			
			if(!$l)
				$out = 'You need to be logged in to upload files.';
			elseif(!is_dir($dir))
			{
				$title = $ptitle = 'Error: not found';
				$out = 'Directory not found.';
			}
			else
			{
				$js .=	'$("a#more").click(function(){
	'.					'	if(files<10){
	'.					'		$("input#submit").before("<input style=\'display: none;\' type=\'file\' id=\'file_"+(++files)+"\' name=\'file_"+files+"\' />");
	'.					'		$("input#file_"+files).fadeIn(100);
	'.					'		$("input#file_num").attr("value", files);
	'.					'		return false;
	'.					'	}
	'.					'});
	'.					'$("a#less").click(function(){
	'.					'	if(files>1){
	'.					'		$("input#file_"+(files--)).stop().
	'.					'		fadeOut(100, function(){
	'.					'			$(this).remove();
	'.					'		});
	'.					'		$("input#file_num").attr("value", files);
	'.					'		return false;
	'.					'	}
	'.					'});
	'.					'$("input#submit").click(function(){
	'.					'	$("span#info").hide().
	'.					'	html("Please wait while your "+(files==1?"file is":"files are")+" uploaded<span id=\'dotdotdot\'></span>").stop().
	'.					'	fadeIn(200);
	'.					'	ell(0);
	'.					'});';
				$out =	'<form id="upload_form" enctype="multipart/form-data" action="?m=uploading&amp;d='.$dir.'" method="post">
	'.					'	<div class="imp">
	'.					'		<a href="#" id="more" title="More files...">+</a>&nbsp;<a href="#" id="less" title="Less files...">-</a>
	'.					'	</div>
	'.					'	<input type="hidden" name="file_num" id="file_num" value="1" />
	'.					'	<input type="file" name="file_1" id="file_1" />
	'.					'	<input id="submit" type="submit" value="Upload file(s)" />
	'.					'</form>
	'.					'<span id="info">&nbsp;</span>';
			}
			
			$backUrl = '?d='.$dir;

			break;
			
		case 'uploading':
		
			$title = $ptitle = 'Upload files to '.mkdirname(false);
			
			if(!$l)
				$out = 'You need to be logged in to upload files.';
			else
			{
				$filenum = $filenum?intval($_POST['file_num']):1;
				
				if(!$filenum)
					$out = 'An error occured.';
				else
				{
					$out = '';
					foreach($_FILES as $file)
					{
						$dir = $dir!=$mdir?$dir:'.';
						$path = $dir.'/'.basename(stripslashes(safe_filename($file['name']))); 
						if(!file_exists($path))
						{
							if(move_uploaded_file(stripslashes($file['tmp_name']), $path))
							    $out .= 'File "'.$path.'" has been uploaded succesfully!<br />';
							else
							    $out .= 'An error occured when attempting to upload file "'.$path.'"!<br />';
						}
						else
							$out .= 'File "'.$path.'" already exists!<br />';
					}
				}		
			}
			
			$backUrl = '?d='.$dir;
			
			break;
		
		case 'del':
			
			$title = $ptitle = 'Delete file/directory';
			
			if(!$l)
				$out = 'You need to be logged in to upload files.';
			else
			{
				$file = stripslashes($_GET['f']);
			
				if(!$file || strstr($file, 'index.php'))
					$out = 'You provided an invalid file name!';
				else
				{
					if(is_dir($file))
					{
						if(remove_dir($file))
							$out = 'Directory "'.$file.'" was deleted succesfully!!';
						else
							$out = 'An error occured while attempting to delete directory "'.$file.'"!';
					}
					else
					{
						if(file_exists($file))
						{
							if(unlink($file))
								$out = 'File "'.$file.' was deleted succesfully!';
							else
								$out = 'An error occured while attempting to delete file "'.$file.'"!';
						}
					}
				}
			}
			
			$backUrl = 'javascript:history.back();';

			break;
		
		case 'rename':
			
			$oldname = stripslashes($_GET['old']);
			$newname = safe_filename(stripslashes($_GET['new']));
			$title = $ptitle = 'Rename file/directory';
			
			if(!$l)
				$out = 'You need to be logged in to rename files/directories.';
			elseif(!$newname)
				$out = 'You provided an invalid file name!';
			elseif(is_dir($oldname))
			{
				if(!is_dir($newname))
				{
					if(rename($oldname, $newname))
						$out = 'Directory "'.$oldname.'" renamed to "'.$newname.'" succesfully!';
				}
				else
				{
					$title = $ptitle = 'Error: directory exists';
					$out = 'A directory named "'.$newname.'" exists already.';
				}
			}
			elseif(file_exists($oldname))
			{
				if(!file_exists($newname))
				{
					if(rename($oldname, $newname))
						$out = 'File "'.$oldname.'" renamed to "'.$newname.'" succesfully!';
					else
						$out = 'An error occured while attempting to rename file "'.$oldname.'" to "'.$newname.'"!';
				}
				else
				{
					$title = $ptitle = 'Error: file exists';
					$out = 'A file named "'.$newname.'" exists already.';
				}
			}
			else
				$out = 'File not found.';
			
			$backUrl = '?d='.$dir;
			
			break;
		
		case 'createdir':
			
			$name = safe_filename(stripslashes($_GET['new']));
			$title = $ptitle = 'Create directory';
			
			if(!$l)
				$out = 'You need to be logged in to create directories.';
			elseif(!$name)
				$out = 'You provided an invalid file name!';
			elseif(is_dir($name))
			{
				$title = $ptitle = 'Error: directory exists';
				$out = 'A directory named "'.$name.'" exists already.';
			}
			else
			{
				if(mkdir($name))
					$out = 'Directory "'.$name.'" created succesfully!';
				else
					$out = 'An error occured while attempting to create directory "'.$name.'"!';
			}
			
			$backUrl = '?d='.$name;
			
			break;
	}
	
	$out =	'
	'.$out.(isset($noback)?'':'<br /><a href="'.($_SERVER['PHP_SELF']).$backUrl.'">Go back.</a>').'
';
	$log =	'
	'.($opt?('<div class="imp left pl">
	'.		'	'.$opt.'
	'.		'</div>
	'.		'<div class="imp right pr">
	'.		'	'.$log.'
	'.		'</div>'):('<div class="imp">
	'.		'	'.$log.'
	'.		'</div>')).'
';
	$js =	'<script type="text/javascript"><!--
	'.		'var files = 1, fadeOut = 0;
	'.		'function ell(n){
	'.		'	switch(n){
	'.		'		case 0: case 1: n++; break;
	'.		'		case 2: n = 0;
	'.		'	}
	'.		'	dots = "";
	'.		'	for(i=0;i-1<n;i++)
	'.		'		dots=dots+".";
	'.		'	$("#dotdotdot").text(dots);
	'.		'	setTimeout("ell("+n+")", 500);
	'.		'}
	'.		'$(function(){
	'.		'	$(document).mouseup(function(e){
	'.		'	if($(e.target).parents("#log").length==0&&$(e.target).filter("#log").length==0)
	'.		'		$("#log").slideUp("fast");
	'.		'	});
	'.		'	$("#showlog").click(function(){
	'.		'		$("#log").slideDown("fast");
	'.		'		return false;
	'.		'	});
	'.		'	$("#change-color").change(function(){
	'.		'		top.location="?d='.$dir.'&m=color&c="+$("#change-color option:selected").val();
	'.		'	});
	'.		'	'.$js.'
	'.		'}); //-->
	'.		'</script>
';

; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml"> 
<head> 
	<title><?php echo $ptitle; ?></title> 
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" /> 
	<meta name="Author" content="Kaeru" />
	<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $fileDir; ?>fstyle.php?c=<?php echo $color; ?>" />
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3/jquery.min.js"></script>
	<script type="text/javascript" src="<?php echo $fileDir; ?>tablesorter.js"></script>
	<?php echo $js; ?>
</head>
<body>
<div id="wrapper">
	<div class="box">
		<h1><a id="showlog" href="#" title="Show/hide options...">**</a> <?php echo $title; ?></h1>
		<div id="log"><?php echo $log; ?></div>
		<div id="main"><?php echo $out; ?></div>
	</div>
	<div id="push"></div>
</div>
<div id="foot">
	<p><a href="https://github.com/KaeruCT/CuteViewer">CuteViewer 2.0</a> &copy; <?php echo $copyd; ?> Ernesto Villarreal~</p>
</div>
</body>
</html>
