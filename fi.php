<?php
/*	Jan Beneke    -    http://www.janbeneke.de    -   mail@janbeneke.de
 *	Title: lgFileindex fi.php - 0.2
 *  
 */

error_reporting(E_ALL);
ini_set('display_errors', 1); 

$startzeit = explode(" ", microtime());
$startzeit = $startzeit[0]+$startzeit[1];

session_name ('lgFileindex_Session');
session_start();

$user_names = array( 1=>"admin", 2=>"user");

$user_passes = array( 1=>md5("upload"), 2=>md5("download"));

//$user_privilege = array( 1=>true, 2=> false);
//$is_admin = false;

/**
 * print_index
 * return an array containing all folders and files of the $directory_base_path
 * @author	Jan Beneke -- lightgraffiti.de
 *
 * @param    $directory_base_path   string    either absolute or relative path
 * @return   $result_list    		array     Nested array or false
 * @access 	public
 * @license  GPL v3
 */
function print_index($directory_base_path = './', $exclude = ".|..|./|../|.DS_Store|fi.php|fi.css|index.php|index.html|index.htm|.svn|.htaccess|.htpasswd|doc.png|dir.png"){
	$directory_base_path = rtrim($directory_base_path, "/") . "/";

    if (!is_dir($directory_base_path)){
        error_log(__FUNCTION__ . "File at: $directory_base_path is not a directory.");
		echo '<pre class="error_msg">ERROR: File at: '.$directory_base_path.' is not a directory.</pre>';
        return '';
    }
	
	$url = 'http://'.$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	$url = substr($url,0, -strlen(strrchr($url,'/'))+1);
	
	$exclude_array = explode("|", $exclude);
	
	$toggle_id = md5( $directory_base_path.$directory_base_path.strval(filectime($directory_base_path)) );
	
	$result = '';
	$result .= '<div class="dir">';
	$result .= '<a href="javascript:toggle(\''. $toggle_id.'_content\')" class="name">';
	if($directory_base_path == './') 
		$result .= 'Hauptverzeichnis'; 
	else
		$result .= substr($directory_base_path,2,-1);
	$result .= '</a>';
	$result .= '<a href="javascript:toggle(\''.$toggle_id.'_details\')" class="more"> Details</a>';
	$result .= '<div id="'. $toggle_id.'_details" class="details">';
	$result .= '<span></span><span></span>';
	if($directory_base_path != './') 
		$result .= '<span style="float:right;"><a href="javascript:dialog_toggle2(\'dialog_delete_dir\',\'overlay\',\'delete_dir_form\',\'delete_dirname\',\''.$directory_base_path.'\')" class="blank">löschen</a></span><br />';
	else
		$result .= '<span style="float:right;"></span><br />';
    $result .= '<span>Erstellungsdatum:</span>';
 	$result .= '<span>'.date("d.m.Y - H:i",filectime($directory_base_path)).'</span>';
    if($directory_base_path != './') 
		$result .= '<span style="float:right;"><a href="javascript:dialog_toggle3(\'dialog_rename_dir\',\'overlay\',\'rename_dir_form\',\'new_dirname\',\''.substr($directory_base_path,1).'\',\'old_dirname\',\''.$directory_base_path.'\')" class="blank">umbenennen</a></span><br />';
	else
		$result .= '<span style="float:right;"></span><br />';
    $result .= '<span>Änderungsdatum:</span>';
    $result .= '<span>'.date("d.m.Y - H:i",filemtime($directory_base_path)).'</span>';
    if($directory_base_path != './') 
		$result .= '<span style="float:right;"><a href="javascript:dialog_toggle2(\'dialog_move_dir\',\'overlay\',\'move_dir_form\',\'move_dirname\',\''.$directory_base_path.'\')" class="blank">bewegen</a></span>';
	else
		$result .= '<span style="float:right;"></span><br />';
	$result .= '</div>';
    $result .= '<div id="'.$toggle_id.'_content" class="content"';
	if($directory_base_path == './') 
		$result .= 'style ="display:block;"';
	$result .= '>';
	
    if (!$folder_handle = opendir($directory_base_path)) {
        error_log(__FUNCTION__ . "Could not open directory at: $directory_base_path");
		echo '<pre class="error_msg">ERROR: Could not open directory at: '.$directory_base_path.'</pre>';
        return '';
    }else{
        while( $filename = readdir($folder_handle) ) {
			if( !in_array($filename, $exclude_array) && is_dir( substr($directory_base_path,2) . $filename) && strcmp($filename, ".")!=0 && strcmp($filename, "..")!=0) {				
				$result .= print_index($directory_base_path.$filename, $exclude);	
			}
		}
		closedir($folder_handle);
		$folder_handle = opendir($directory_base_path);
		$i = 0;
		while($filename = readdir($folder_handle) ) {
			/*}else*/ if( !in_array($filename, $exclude_array) && is_file( substr($directory_base_path,2) . $filename)) {					
					$toggle_id = md5( $directory_base_path.$filename.strval(filectime($directory_base_path.$filename)) );	
					$path = $directory_base_path . $filename;		
					$i++;
					$result .= '<div class="file">';
					$result .= '<a href="fi.php?dl='.$url.substr($directory_base_path, 2).$filename.'" class="name">'.$filename.'</a>';
					$result .= '<a href="javascript:toggle(\''.$toggle_id.'\')" class="more"> Details</a>';
					$result .= '<div id="'.$toggle_id.'" class="details">';
					$result .= '<span>Dateigröße:</span>';
					$result .= '<span>'.file_size($directory_base_path . $filename).'</span>';
					$result .= '<span style="float:right;"><a href="javascript:dialog_toggle2(\'dialog_delete_file\',\'overlay\',\'delete_file_form\',\'delete_filename\',\''.$path.'\')" class="blank">löschen</a></span><br />';
					$result .= '<span>Erstellungsdatum:</span>';
					$result .= '<span>'.date("d.m.Y - H:i",filectime($directory_base_path . $filename)).'</span>';
					$result .= '<span style="float:right;"><a href="javascript:dialog_toggle3(\'dialog_rename_file\',\'overlay\',\'rename_file_form\',\'new_filename\',\''.substr(strrchr($path,'/'),1).'\',\'old_filename\',\''.$path.'\')" class="blank">umbenennen</a></span><br />';
					$result .= '<span>Änderungsdatum:</span>';
					$result .= '<span>'.date("d.m.Y - H:i",filemtime($directory_base_path . $filename)).'</span>';
					$result .= '<span style="float:right;"><a href="javascript:dialog_toggle2(\'dialog_move_file\',\'overlay\',\'move_file_form\',\'move_filename\',\''.$path.'\')" class="blank">bewegen</a></span>';
					$result .= '</div></div>';
			}
        }
		if( $i == 0)
		{
			$result .= '<div class="no_files">';
			$result .= 'Dieser Ordner enthält keine Dateien.';
			$result .= '</div>';
		}
		closedir($folder_handle);
		$result .= '</div></div>';
		return $result;
    }		
}

/**
 * print_dir_options
 * return an array containing all folders of the $directory_base_path with <option> tags to use with <select>
 * @author	Jan Beneke -- lightgraffiti.de
 *
 * @param    $directory_base_path   string    either absolute or relative path
 * @return   $result_list    		array     Nested array or false
 * @access 	public
 * @license  GPL v3
 */
function print_dir_options($directory_base_path = './', $exclude = ".|..|./|../|.DS_Store|fi.php|fi.css|index.php|index.html|index.htm|.svn|.htaccess|.htpasswd|doc.png|dir.png"){
	$directory_base_path = rtrim($directory_base_path, "/") . "/";

    if (!is_dir($directory_base_path)){
        error_log(__FUNCTION__ . "File at: $directory_base_path is not a directory.");
		echo '<pre class="error_msg">ERROR: File at: '.$directory_base_path.' is not a directory.</pre>';
        return '';
    }
	
	$exclude_array = explode("|", $exclude);

    $result = '<option>'.$directory_base_path.'</option>';

    if (!$folder_handle = opendir($directory_base_path)) {
        error_log(__FUNCTION__ . "Could not open directory at: $directory_base_path");
		echo '<pre class="error_msg">ERROR: Could not open directory at: '.$directory_base_path.'</pre>';
        return false;
    }else{
        while($filename = readdir($folder_handle) ) {
			if( !in_array($filename, $exclude_array) && is_dir( substr($directory_base_path,2) . $filename) && strcmp($filename, ".")!=0 && strcmp($filename, "..")!=0) {				
            	$result .= print_dir_options( $directory_base_path.$filename.'/' , $exclude);
			}
        }
        closedir($folder_handle);
        return $result;
    }
}

/**
 * is_user
 * checks login and sets user privileges
 * requires $user_names, $user_passes, $user_rights
 * @author	Jan Beneke -- lightgraffiti.de
 *
 * @param    $username 		string    username
 * @param    $password 		string    password
 * @return   true/false	  	boolean	  true if login is okay
 * @access 	 public
 * @license  GPL v3
 */
function is_user( $username, $password){
	extract($GLOBALS);
	$user_key = array_search( $username, $user_names);
	if( $user_key && strcmp($user_passes[$user_key], md5($password)) == 0)
		return true;	
	else
		return false;	
}

function is_user_md5( $username_md5, $password_md5){
	extract($GLOBALS);
	$user_names_md5 = array();
	for($i = 1; $i < count($user_names); $i++)
		$user_names_md5[$i] = md5($user_names[$i]);
		
	$user_key = array_search( $username_md5, $user_names_md5);
	if( $user_key && md5($user_passes[$user_key]) == $password_md5)
		return true;	
	else
		return false;	
}

function rrmdir($dir) {
   if (is_dir($dir)) {
     $objects = scandir($dir);
     foreach ($objects as $object) {
       if ($object != "." && $object != "..") {
         if (filetype($dir."/".$object) == "dir") rrmdir($dir."/".$object); else unlink($dir."/".$object);
       }
     }
     reset($objects);
     rmdir($dir);
   }
 }

/**
 * file_size
 * return the filesize in a nice string
 * @author	Jan Beneke -- lightgraffiti.de
 *
 * @param    $URL    		string    URL to the file
 * @return   $result_list   string	  Nicely formated file size
 * @access 	 public
 * @license  GPL v3
 */
function file_size($URL)		
{
	$Groesse = filesize($URL);

	if($Groesse < 1000)
	{
		return number_format($Groesse, 0, ",", ".")." Bytes";
	}
	elseif($Groesse < 1000000)
	{
		return number_format($Groesse/1024, 0, ",", ".")." kB";
	}
	else
	{
		return number_format($Groesse/1048576, 1, ",", ".")." MB";
	}
} 
?>

<!DOCTYPE HTML>
<html>
<head>
<meta charset="UTF-8">
<link rel="stylesheet" type="text/css" href="fi.css" />
<link rel="icon" href="http://www.lightgraffiti.de/favicon.ico" type="image/x-icon" />
<title>lgFileindex - easy server-sided file exchange</title>
<?
$fileurl = "";
if(isset($_GET['dl'])){
	if(/*!file_exists($_GET['dl']) || */$_GET['dl'] != "index.php" || $_GET['dl'] != "fi.php"){
		$fileurl = $_GET['dl'];
	}
}
?>
<script type="text/javascript">
function toggle(control){
    var con = document.getElementById(control);
    if(con.style.display == "block"){
        con.style.display = "none";
    }else{
        con.style.display = "block";
    }
}

function dialog_toggle(dialog,overlay){
    var dlg = document.getElementById(dialog);
	var ovl = document.getElementById(overlay);
    if(dlg.style.display == "block" || ovl.style.display == "block"){
        dlg.style.display = "none";
		ovl.style.display = "none";
    }else{
        dlg.style.display = "block";
		ovl.style.display = "block";
    }
}

function dialog_toggle2(dialog,overlay,form_id,element_name,element_value){
	formObject = document.forms[form_id];
	formObject.elements[element_name].value = element_value;
    var dlg = document.getElementById(dialog);
	var ovl = document.getElementById(overlay);
    if(dlg.style.display == "block" || ovl.style.display == "block"){
        dlg.style.display = "none";
		ovl.style.display = "none";
    }else{
        dlg.style.display = "block";
		ovl.style.display = "block";
    }
}

function dialog_toggle3(dialog,overlay,form_id,element1_name,element1_value,element2_name,element2_value){
	formObject = document.forms[form_id];
	formObject.elements[element1_name].value = element1_value;
	formObject.elements[element2_name].value = element2_value;
    var dlg = document.getElementById(dialog);
	var ovl = document.getElementById(overlay);
    if(dlg.style.display == "block" || ovl.style.display == "block"){
        dlg.style.display = "none";
		ovl.style.display = "none";
    }else{
        dlg.style.display = "block";
		ovl.style.display = "block";
    }
}

var timeout = 10;
var int = window.setInterval ("countdown()", timeout*100);

function countdown () {
   if (timeout <= 1) {
      window.clearInterval (int);
      document.getElementById ("download_time").innerHTML = "0"
      window.location = "<? echo $fileurl; ?>"
   }
   else
      document.getElementById ("download_time").innerHTML = --timeout;
}

function submitenter(myfield,e)
{
var keycode;
if (window.event) keycode = window.event.keyCode;
else if (e) keycode = e.which;
else return true;

if (keycode == 13)
   {
   myfield.form.submit();
   return false;
   }
else
   return true;
}
</script>

</head>
<body <? if($fileurl != "") echo ' onLoad="setTimeout(\'countdown()\',0)"' ?>>

<div id="content">

<div id="header">
	<span>lgFileindex</span>
    
    <form action="<?php print $_SERVER['PHP_SELF']; ?>" method="post" name="log_out_form" class="logout" >
    	<? if((isset($_POST['u']) && isset($_POST['p']))||(isset($_SESSION['u']) && isset($_SESSION['p']))) echo '<a href="javascript:document.log_out_form.submit();" class="logout">abmelden</a>'; ?>
    	<input name="log_out" value="log out please" style="display:none;">
    </form>
   
   <p>easy server-sided file exchange</p> 
    
	<hr>
</div>

<?php
if(isset($_GET['dl'])){
	if( $fileurl == ""){
	?>
    	<p class="error_msg" align="center">Ein Fehler ist aufgetreten.<br />Die angeforderte Datei kann nicht gefunden werden.</p>
    <?php	
	} else {
?>
		  <div id="download">
          <p align="center">Der Download von <a align="center" href="<?php echo $fileurl; ?>"><?php echo $fileurl; ?></a> beginnt in <span id="download_time"></span> Sekunden.</p>
          <p align="center">Clicke <a align="center" href="<?php echo $fileurl; ?>">hier</a> um den Download jetzt zu starten.</p>
          </div>
<?php 
	}
?>
	<p align="center" style="margin: 20px;"><a href="javascript:history.go(-1);return true;">Zurück zur vorherigen Seite</p></a>
<?php	
} else if( ( isset($_POST['u']) && isset($_POST['p']) && is_user($_POST['u'], $_POST['p']) ) || ( isset($_SESSION['u']) && isset($_SESSION['p']) && is_user_md5($_SESSION['u'],$_SESSION['p']) ) ) {	

	if(isset($_POST['u']) && isset($_POST['p'])){
			$_SESSION['u'] = md5($_POST['u']);
			$_SESSION['p'] = md5($_POST['p']);
	}
	
	// logout: cancle session
	if(isset($_POST['log_out'])){
		session_unset();
		session_destroy();
		echo '<p class="msg">Erfolgreich abgemeldet.</p>';
		goto end;
		
	// rename file
	}else if( isset($_POST['old_filename']) && isset($_POST['new_filename']) ){
		$err = false;
		if( file_exists($_POST['old_filename']) && $_POST['new_filename'] != ''){
			$new_filename = substr( $_POST['old_filename'], 0, -strlen( strrchr($_POST['old_filename'],"/")) + 1 ) . $_POST['new_filename'];
			rename( $_POST['old_filename'], $new_filename);
		}else
			$err =true;
		
		if( !$err)
			echo '<p class="msg">Die Datei '.$_POST['old_filename'].' wurde in '.$new_filename.' umbenannt.</p>';
		else
			echo '<p class="error_msg">Ein Fehler ist bei der umbenennung von '.$_POST['old_filename'].' in '.$_POST['new_filename'].' aufgetreten!</p>';
			
	// delete file
	}else if( isset($_POST['delete_filename']) ){
		$err = false;
		if( file_exists($_POST['delete_filename']) ){
			unlink($_POST['delete_filename']);
		}else
			$err =true;
		
		if( !$err)
			echo '<p class="msg">Die Datei '.$_POST['delete_filename'].' wurde gelöscht.</p>';
		else
			echo '<p class="error_msg">Die Datei '.$_POST['delete_filename'].' konnte nicht gelöscht werden!</p>';
			
	// move file
	}else if( isset($_POST['move_filename']) && isset($_POST['move_file_to'])){
		$err = false;
		if( file_exists($_POST['move_filename']) ){
			$new_filename = $_POST['move_file_to'] . substr( strrchr($_POST['move_filename'],"/"), 0);
			rename( $_POST['move_filename'], $new_filename);
		}else
			$err =true;
		
		if( !$err)
			echo '<p class="msg">Die Datei '.$_POST['move_filename'].' wurde nach '.$new_filename.' verschoben.</p>';
		else
			echo '<p class="error_msg">Die Datei '.$_POST['move_filename'].' konnte nicht verschoben werden!</p>';
	// rename dir
	}else if( isset($_POST['old_dirname']) && isset($_POST['new_dirname']) ){
		$err = false;
		if( file_exists($_POST['old_dirname']) && $_POST['new_dirname'] != ''){
			$new_dirname = substr( $_POST['old_dirname'], 0, -strlen( strrchr($_POST['old_dirname'],"/")) + 1 ) . $_POST['new_dirname'];
			rename( $_POST['old_dirname'], '.'.$new_dirname);
		}else
			$err =true;
		
		if( !$err)
			echo '<p class="msg">Der Ordner '.$_POST['old_dirname'].' wurde in '.$new_dirname.' umbenannt.</p>';
		else
			echo '<p class="error_msg">Ein Fehler ist bei der umbenennung von '.$_POST['old_dirname'].' in '.$_POST['new_dirname'].' aufgetreten!</p>';
			
	// delete dir
	}else if( isset($_POST['delete_dirname']) ){
		$err = false;
		if( file_exists($_POST['delete_dirname']) ){
			rrmdir($_POST['delete_dirname']);
		}else
			$err =true;
		
		if( !$err)
			echo '<p class="msg">Der Ordner '.$_POST['delete_dirname'].' wurde gelöscht.</p>';
		else
			echo '<p class="error_msg">Der Ordner '.$_POST['delete_dirname'].' konnte nicht gelöscht werden!</p>';
			
	// move dir
	}else if( isset($_POST['move_dirname']) && isset($_POST['move_dir_to'])){
		$err = false;
		if( file_exists($_POST['move_dirname']) && is_dir($_POST['move_dirname'] .'/' ) && $_POST['move_dirname'] != $_POST['move_dir_to']){
			$new_dirname = $_POST['move_dir_to'] . substr( strrchr($_POST['move_dirname'],"/"), 0);
			rename( $_POST['move_dirname'], $new_dirname);
		}else
			$err =true;
		
		if( !$err)
			echo '<p class="msg">Der Ordner '.$_POST['move_dirname'].' wurde nach '.$new_dirname.' verschoben.</p>';
		else
			echo '<p class="error_msg">Der Ordner '.$_POST['move_dirname'].' konnte nicht verschoben werden!</p>';
			
	// upload file
	}else if(isset($_FILES['upload_filename']['tmp_name']) && isset($_FILES['upload_filename']['name']) && $_FILES['upload_filename']['size'] > 0 && $_FILES['upload_filename']['error'] == 0 && is_dir($_POST['upload_to'].'/')){
		$target = $_POST['upload_to'].'/'.$_FILES['upload_filename']['name'];
		if( move_uploaded_file($_FILES['upload_filename']['tmp_name'],$target))
			echo '<p class="msg">Die Datei '.$target.' ('.file_size($target).') wurde hochgeladen.</p>';
		else
			echo '<p class="error_msg">Die Datei '.$target.' konnte nicht hochgeladen werden!</p>';
	// new dir
	}else if( isset($_POST['newdir_name']) && isset($_POST['newdir_parent']) && is_dir($_POST['newdir_parent'].'/') && $_POST['newdir_name'] != ""){
		$target = $_POST['newdir_parent'].'/'.$_POST['newdir_name'];
		if( mkdir($target,0777,true))
			echo '<p class="msg">Der Ordner '.$target.' wurde erstellt.</p>';
		else
			echo '<p class="error_msg">Der Ordner '.$target.' konnte nicht erstellt werden!</p>';
	}
	
	// get folders as <option> tags to use with <select>
	$dir_options = print_dir_options();

?>  
	<div id="function">
        <a href="javascript:toggle('files_pop')" class="section">index</a>
        <div id="files_pop" class="lst">
		<? echo print_index('./'); ?>
        </div>
    </div>
    
    <div id="function">
        <a href="javascript:toggle('up_pop')" class="section">upload</a>
        
        <div id="up_pop" class="lst" style="display:none">
           <form enctype="multipart/form-data" action="<?php echo htmlspecialchars ($_SERVER['PHP_SELF']); ?>" method="post" name="upload_form" >
            <input type="hidden" name="max_file_size" value="1000000">
            <p class="label">Datei auswählen:</p>
            <p class="input"><input name="upload_filename" type="file" draggable="true" dropzone="link"></p>
            <p class="label">Zielordner auswählen:</p>
            <p class="input"><select name="upload_to" size="1">
            <?
            	echo $dir_options;
			?>
    		</select></p>          
            <p class="submit" style="float:none;"><a href="javascript:document.upload_form.submit();">hochladen</a></p>
            </form>
        </div>
    </div>
    
    <div id="function">
        <a href="javascript:toggle('nd_pop')" class="section">neuer ordner</a>
        
        <div id="nd_pop" class="lst" style="display:none">
           <form enctype="multipart/form-data" action="<?php echo htmlspecialchars ($_SERVER['PHP_SELF']); ?>" method="post" name="newdir_form" >
            <p class="label">Ordnername:</p>
            <p class="input"><input name="newdir_name" type="text" size="30"></p>
            <p class="label">Stammordner:</p>
            <p class="input"><select name="newdir_parent" size="1">
            <?
            	echo $dir_options;
			?>
    		</select></p>          
            <p class="submit" style="float:none;"><a href="javascript:document.newdir_form.submit();" class="submit" style="float:none;">erstellen</a></p>
            </form>
        </div>
    </div>
<?	
} else {
	if( isset($_POST['u']) || isset($_POST['p']))
	{
	?>
    	<p class="error_msg">Ein Fehler ist bei der Anmeldung aufgetreten.<br />Bitte versuche es erneut.</p>
    <?php	
	}
?>
        <form action="<?php print $_SERVER['PHP_SELF']; ?>" method="post" name="login_form" style="width:260px; height:160px;" id="login">
        <p class="label">Benutzer:</p>
        <p class="input"><input type="text" name="u" size="30" onKeyPress="return submitenter(this,event)"></p>
        
        <p class="label">Passwort:</p>
        <p class="input"><input type="password" name="p" size="30" onKeyPress="return submitenter(this,event)"></p>
        
        <p class="submit"><input type="submit" name="link" value="versenden" style="display:none;"><a href="javascript:document.login_form.submit();">anmelden</a></p>
        </form>
<?php
}

end:

$year = date("Y");
$endzeit=explode(" ", microtime());
$endzeit=$endzeit[0]+$endzeit[1];
?>
    <div id="footnote">
    	<hr>
            <a href="javascript:toggle('copyright')">Copyright <?php echo $year ?> by Jan Beneke (click for more)</a>
            <div id="copyright" style="display: none">
                <p>Kontakt über <a href="http://www.lightgraffiti.de" target="_self">www.lightgraffiti.de</a></p>
                <p>Alle Dateien und Links auf dieser Seite verweisen auf den Eigentümer.</p>
                <p>Die Seite wurde in <?php echo round($endzeit - $startzeit,4) ?> Sekunden geladen.</p>
            </div>
    </div>
</div>

<div id="overlay"></div>
  
<div id="dialog_rename_file" class="dialog" style="width:260px; margin-left:-130px; height:120px; margin-top:-60px;">
    <p class="header">datei umbenennen<p>
    <form action="<?php print $_SERVER['PHP_SELF']; ?>" method="post" name="rename_file_form" >
        <p class="input">
        	<input type="text" name="new_filename" size="30" value="">
            <input type="text" name="old_filename" value="" style="display:none;">
        </p>
        <span style="float:left;">
            <a href="javascript:document.rename_file_form.submit();" class="button" style="width:100px;">umbenennen</a>
        </span>
        <span style="float:right;">
            <a href="javascript:dialog_toggle('dialog_rename_file','overlay');" class="button" style="width:100px;">abbrechen</a>
        </span>
    </form>
</div>

<div id="dialog_delete_file" class="dialog" style="width:260px; margin-left:-130px; height:80px; margin-top:-40px;">
    <p class="header">datei löschen<p>
    <form action="<?php print $_SERVER['PHP_SELF']; ?>" method="post" name="delete_file_form" >
        <p class="input">
        	<input type="text" name="delete_filename" value="" style="display:none;">
        </p>
        <span style="float:left;">
            <a href="javascript:document.delete_file_form.submit();" class="button" style="width:100px;">löschen</a>
        </span>
        <span style="float:right;">
            <a href="javascript:dialog_toggle('dialog_delete_file','overlay');" class="button" style="width:100px;">abbrechen</a>
        </span>
    </form>
</div>

<div id="dialog_move_file" class="dialog" style="width:260px; margin-left:-130px; height:120px; margin-top:-60px;">
    <p class="header">datei bewegen<p>
    <form action="<?php print $_SERVER['PHP_SELF']; ?>" method="post" name="move_file_form" >
        <p class="input">
        	<select name="move_file_to" size="1">
            <? echo print_dir_options(); ?>
    		</select>
        	<input type="text" name="move_filename" value="Demo/Url/Date.txt" style="display:none;">
        </p>
        <span style="float:left;">
            <a href="javascript:document.move_file_form.submit();" class="button" style="width:100px;">bewegen</a>
        </span>
        <span style="float:right;">
            <a href="javascript:dialog_toggle('dialog_move_file','overlay');" class="button" style="width:100px;">abbrechen</a>
        </span>
    </form>
</div>

<div id="dialog_rename_dir" class="dialog" style="width:260px; margin-left:-130px; height:120px; margin-top:-60px;">
    <p class="header">ordner umbenennen<p>
    <form action="<?php print $_SERVER['PHP_SELF']; ?>" method="post" name="rename_dir_form" >
        <p class="input">
        	<input type="text" name="new_dirname" size="30" value="">
            <input type="text" name="old_dirname" value="" style="display:none;">
        </p>
        <span style="float:left;">
            <a href="javascript:document.rename_dir_form.submit();" class="button" style="width:100px;">umbenennen</a>
        </span>
        <span style="float:right;">
            <a href="javascript:dialog_toggle('dialog_rename_dir','overlay');" class="button" style="width:100px;">abbrechen</a>
        </span>
    </form>
</div>

<div id="dialog_delete_dir" class="dialog" style="width:260px; margin-left:-130px; height:80px; margin-top:-40px;">
    <p class="header">ordner löschen<p>
    <form action="<?php print $_SERVER['PHP_SELF']; ?>" method="post" name="delete_dir_form" >
        <p class="input">
        	<input type="text" name="delete_dirname" value="" style="display:none;">
        </p>
        <span style="float:left;">
            <a href="javascript:document.delete_dir_form.submit();" class="button" style="width:100px;">löschen</a>
        </span>
        <span style="float:right;">
            <a href="javascript:dialog_toggle('dialog_delete_dir','overlay');" class="button" style="width:100px;">abbrechen</a>
        </span>
    </form>
</div>

<div id="dialog_move_dir" class="dialog" style="width:260px; margin-left:-130px; height:120px; margin-top:-60px;">
    <p class="header">ordner bewegen<p>
    <form action="<?php print $_SERVER['PHP_SELF']; ?>" method="post" name="move_dir_form" >
        <p class="input">
        	<select name="move_dir_to" size="1">
            <? echo print_dir_options(); ?>
    		</select>
        	<input type="text" name="move_dirname" value="Demo/Url/Date.txt" style="display:none;">
        </p>
        <span style="float:left;">
            <a href="javascript:document.move_dir_form.submit();" class="button" style="width:100px;">bewegen</a>
        </span>
        <span style="float:right;">
            <a href="javascript:dialog_toggle('dialog_move_dir','overlay');" class="button" style="width:100px;">abbrechen</a>
        </span>
    </form>
</div>

</body>
</html>