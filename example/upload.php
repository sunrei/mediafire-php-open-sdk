<?php
/*
 * Required parameters
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
$appId = "43117";
$apiKey = "wydv7s8f7mun7n0691eqb9ldumxfnt9drdqq0n24";
$email = "mediafire email";
$password = "mediafire password";

if ($appId == "" || $apiKey == "" || $email == "" || $password == "")
{
    exit("<pre>One or more required parameters are missing.<br /><br />" .
        "Please open in this file in any text editor to fill all the required " .
        "parameters and try again.</pre>");
}

/*
 * Show the upload form
 */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
        <title>MediaFire API PHP Library - File Uploading</title>
        <script>
        function _add_more() {
  			var txt = "<br><input type=\"file\" id=\"file\" name=\"file[]\">";
  			document.getElementById("dvFile").innerHTML += txt;
		}
		</script>
		<style>
			#file{
				display:block;
				clear:left;
				margin:0px;
				padding:0px;
			}
			p{
				padding:0px;
				margin:10px 0 0 0;
				display:block;
			}
		</style>
    </head>
    <body>
        <form method="post" enctype="multipart/form-data" action="">
            <p>Upload a file to a MediaFire account:</p>
            <p>
            	<label title="Choose Action On Duplicates Files" for="file">Choose Action On Duplicates Files:</label>
                <input type="radio" name="dupe" value="skip">Skip File -
				<input type="radio" name="dupe" value="Replace">Replace File -
				<input type="radio" name="dupe" value="Keep">Duplicate
                <label title="Choose a Local File to a MediaFire account" for="file">File:</label>
                <input type="file" id="file" name="file[]" size="30" />
                <div style="clear:left;display:block;" id="dvFile"></div>
                <a href="javascript:_add_more();">Add Another File</a> - 
                <input type="submit" id="upload" name="upload" value="Upload" />
            </p>
            <?php
 			if(isset($_POST['upload']))
 			{
 				include("../mflib.php");
 				$mflib = new mflib($appId, $apiKey);
				$mflib->dupe = $_POST['dupe'];	
            	$mflib->email = $email;
            	$mflib->password = $password;	
            	//echo $mflib->dupe;
				if(count($_FILES["file"]['name'])>0) { //check if any file uploaded
  					$GLOBALS['msg'] = ""; //initiate the global message
  					for($j=0; $j < count($_FILES["file"]['name']); $j++) { 
  						if($_FILES["file"]['name'] != ""){
  							//print_r($_FILES["file"]['name']);
  							//loop the uploaded file array
   							$filen = $_FILES["file"]['name']["$j"]; //file name
   							$path = 'tmp/'.$filen; //generate the destination path
   							$name = explode('@',$email);
   								if(move_uploaded_file($_FILES["file"]['tmp_name']["$j"],$path)) {
   									$new_name = rename($path,'tmp/'.$email.'~*'.$filen);
    								$GLOBALS['msg'] = "($filen) cached to server<br>";
    							//Success message
    								echo $GLOBALS['msg'];
    								$sessionToken = $mflib->userGetSessionToken();
    								//echo '<br><br>';
   									$unit = $mflib->check($sessionToken,'tmp/'.$email.'~*'.$filen,$filen);
   									if($unit == 'yes'){
   										$mflib->instant($sessionToken,'tmp/'.$email.'~*'.$filen,$filen);
   										$GLOBALS['msg'] = "($filen) Uploaded To MediaFire<br>";
    									//Success message
    									echo $GLOBALS['msg'];
   									}else{
   										if($unit > 3){
											$place = 0; 
											$mflib->fsplit('tmp/'.$email.'~*'.$filen,'split',$unit); // Splits file into chunks
										while($place < $unit){	// loop to upload each chunk
											$newpath = "split/".$place.$email.'~*'.$filen; // File path to split files
											$resume = $mflib->uploadResume($place,$sessionToken,$newpath,'tmp/'.$email.'~*'.$filen);
											if($resume == FALSE){
												$mflib->uploadResume($place,$sessionToken,$newpath,'tmp/'.$email.'~*'.$filen);
											}
											//echo $items['name'].' - Moved to MediaFire Completed<br>';
											sleep(1); // sleeps for 1 seconds to insure the upload command is called.
											$place++; // increase the current unit number
										}
										}else{
											$mflib->uploadResume('0',$sessionToken,'tmp/'.$email.'~*'.$filen,null);
										}
										$GLOBALS['msg'] = "($filen) Uploaded To MediaFire<br>";
    									//Success message
    									echo $GLOBALS['msg'];
									}
   									//echo '<br><br>';
   									//$sessionToken = $mflib->userRenewSessionToken($sessionToken);
             
                					//echo "File ($filen) Uploaded to MediaFire<br>"; 
   								$filens = 'tmp/'.$email.'~*'.$filen;
   								@unlink($filens);
   								}else{
									echo 'Upload Failed';
								}
						}
  					}
 				}else{
  					$GLOBALS['msg'] = "No files found to upload"; //No file upload message 
				}	
			}//end of "if(isset($_POST['upload']))"        
            
            /*
            if (isset($_POST["upload"]))
            {
                /*
                 * Move uploaded file to current script folder and start uploading
                 *
                $uploadedFile = "./" . basename($_FILES["file"]["name"]);

                if (move_uploaded_file($_FILES["file"]["tmp_name"], $uploadedFile))
                {
                    /*
                     * Initilize a new instance of the class
                     *
                    include("../mflib.php");

                    $mflib = new mflib($appId, $apiKey);
                    $mflib->email = $email;
                    $mflib->password = $password;

                    /*
                     * Select a file to be uploaded. The third argurment of method fileUpload() 
                     * is the quickkey of the destination folder. In this case it's omitted, which 
                     * means the file will be stored in the root folder - 'My files'
                     *
                    $sessionToken = $mflib->userGetSessionToken();
                    $uploadKey = $mflib->fileUpload($sessionToken, $uploadedFile);
                }

                /*
                 * Print the upload result
                 *
                if ($uploadKey)
                {
                    var_dump($mflib->filePollUpload($sessionToken, $uploadKey));
                }
            }

			*/
            ?>
        </form>
    </body>
</html>
