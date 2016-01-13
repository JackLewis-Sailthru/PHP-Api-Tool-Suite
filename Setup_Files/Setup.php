<?php

$path = $argv[1];
$cred_file = $path."/../Classes/Api/Account_Credentials/DefaultKeysAndSecrets.php";
$client_file = $path."/../Classes/Client_Library/Sailthru_Implementation_Client.php";
$last_error;
$exit = 0;

include($client_file);

$file_access = "w";

if (file_exists($cred_file)) {
	//Bit of a hack to make sure the credential file was completely created. If it wasn't 
	if (filesize($cred_file) < 900) {
		echo "Your Key and Secret file wasn't completed or has been currupted. Lets create it again.\n\n";
	} else {
		$exit = 1;
		print "A Key and Secret file already exists. Do you want to change your default account, add a new account, or skip this step and refresh bash scripts?\n(edit/add/skip)\n";
		$answer = readline();
		if ($answer == "add") {
			$edit_mode = "add";
		} else if ($answer == "edit") {
			$edit_mode = "edit";
		} else if ($answer == "skip") {
			exit(2);
		} else {
			print "That isn't a valid option, please run SetupApiAccounts.sh again.\n";
			exit(3);
		}
		if (($creds = fopen($cred_file, "r")) === FALSE) {
	    	throw new Exception("Unable to open ".$cred_file);   
		}
	}
} else {
	print "Lets set up your default API Key and Secret.\n\n";
	if (($creds = fopen($cred_file, "w")) === FALSE) {
    	throw new Exception("Unable to open ".$cred_file);   
	}
}


if ($edit_mode == "add") {
	$account_ref = "the new";
} else {
	$account_ref = "your default";
}


print "Open my.sailthru.com and go to $account_ref Account's Config/Setup page to retrieve your API Key and Secret.\n";
$is_repeat = true;

$user_retries_entry = 3;
do 
{
	$retry = 0;
	do
	{
		print "Enter ".$account_ref." Account's API Key:\n";
		if ($key) {
			print "Hit enter to reuse ".$key."\n";
			$old_key = $key;
		}
		$key = trim(readline());
		if ($key == "" && $old_key) {
			$key = $old_key;
		}
		$retry += 1;
	} while ($key == "" && $retry < $user_retries_entry); 

	$retry = 0;
	do
	{
		print "Enter ".$account_ref." Account's API Secret:\n";
		if ($secret) {
			print "Hit enter to reuse ".$secret."\n";
			$old_secret = $secret;
		}
		$secret = trim(readline());
		if ($secret == "" && $old_secret) {
			$secret = $old_secret;
		}
		$retry += 1;
	} while ($secret == "" && $retry < $user_retries_entry); 

	$retry = 0;
	do
	{
		print "Enter the Numerical ID of ".$account_ref." Account:\n";
		if ($id) {
			print "Hit enter to reuse ".$id."\n";
			$old_id = $id;
		}
		$id = intval(trim(readline()));
		if ($id == "" && $old_id) {
			$id = $old_id;
		}
		$retry += 1;
	} while ($id == "" && $retry < $user_retries_entry); 

	$retry = 0;
	do
	{
		print "Enter ".$account_ref." Account's Name:\n";
		if ($name) {
			print "Hit enter to reuse ".$name."\n";
			$old_name = $name;
		}
		$name = trim(readline());
		if ($name == "" && $old_name) {
			$name = $old_name;
		}
		$retry += 1;
	} while ($name == "" && $retry < $user_retries_entry); 

	$client = new Sailthru_Implementation_Client($key, $secret);
	
	try 
	{
		print "Confirming Acct #\n";
		$return_id = $client->getAccountId();
	} catch (Exception $e) {
	}

	if (is_numeric($return_id)) 
	{
		if ($return_id != $id) 
		{
			print "\nThe Id doesn't match up: Supplied $id vs Retrieved $return_id.\n";
			print "Would you like to enter the information again, proceed anyway or abort?\n (retry/proceed/abort)\n";
			$answer = readline();
			if ($answer == "proceed")  
			{
				$is_repeat = false;
			} 
			else if ($answer == "abort") 
			{
				exit(3);
			}
		} 
		else 
		{
			$is_repeat = false;
		}
	} 
	else
	{
		print "\nThere was an error with the call. Probably an invalid Key or Secret.\n"; 
		print "Would you like to try again? (y/n)\n";
		$answer = readline();
		if ($answer == "error") {
			var_dump($return_id);
			exit(3);
		} else if ($answer != "y" && $answer != "yes") {
			print "Don't give up, go ask for help!\n";
			exit(3);
		}	
	}
} while ($is_repeat);


//Edit File Markers
$startDefaultsLine = "//Start Default DO NOT EDIT";
$endDefaultsLine = "//End Default DO NOT EDIT";
$defaultDelimiterLine = "//Setup.sh Delimiter";

//Start Writing to file
if ($edit_mode == null) {
	fwrite($creds, "<?php\n\n");

	fwrite($creds, "/**\n");
	fwrite($creds, "*  Holder of Keys and Secrets\n");
	fwrite($creds, "*/\n");
	fwrite($creds, "class DefaultKeysAndSecrets {\n\n");

	fwrite($creds, "\t$startDefaultsLine\n");
	fwrite($creds, "\tprotected static \$acctNameDefault = \"".$name."\";\n");
	fwrite($creds, "\tprotected static \$acctNumberDefault = ".$id.";\n");
	fwrite($creds, "\tprotected static \$apiKeyDefault = \"".$key."\";\n");
	fwrite($creds, "\tprotected static \$apiSecretDefault = \"".$secret."\";\n");
	fwrite($creds, "\t$endDefaultsLine\n");

	fwrite($creds, "\n\t//Add other clients to this array:\n");
	fwrite($creds, "\tprotected static \$clients = array(\n");
	fwrite($creds, "\t//Manually add new clients here:\n");
	fwrite($creds, "\t\t-1 => [\n");
	fwrite($creds, "\t\t\t\"key\" => \"abc\",\n");
	fwrite($creds, "\t\t\t\"secret\" => \"123\",\n");	
	fwrite($creds, "\t\t\t\"name\" => \"Example Account\",\n");
	fwrite($creds, "\t\t],\n");
	fwrite($creds, "\t\t".$id." => [\n");
	fwrite($creds, "\t\t\t\"key\" => \"".$key."\",\n");
	fwrite($creds, "\t\t\t\"secret\" => \"".$secret."\",\n");
	fwrite($creds, "\t\t\t\"name\" => \"".$name."\",\n");
	fwrite($creds, "\t\t],\n");
	fwrite($creds, "\t//Setup.sh Delimiter\n");	
	fwrite($creds, "\t//To those editing this file: Don't muck with the line above or any that follow it.\n");	 
	fwrite($creds, "\t//The Setup.sh file will edit as well as create this file, so I'm using that line as a hacky way to identify where edits should begin again.\n");	 

	fwrite($creds, "\t\t".$id." => [\n");
	fwrite($creds, "\t\t\t\"key\" => \"".$key."\",\n");
	fwrite($creds, "\t\t\t\"secret\" => \"".$secret."\",\n");
	fwrite($creds, "\t\t\t\"name\" => \"".$name."\",\n");
	fwrite($creds, "\t\t]\n");
	fwrite($creds, "\t);\n}\n");
} else {
	$file_contents = "";
	$is_edit_defaults = ($edit_mode == "edit");
	$is_add_new = ($edit_mode == "add");
	while (($line = fgets($creds)) !== FALSE) {
		if (trim($line) == $endDefaultsLine) {
			$is_editing_defaults = false;
		} else if ($is_editing_defaults) {
			continue;
		}
		if (trim($line) == $startDefaultsLine && $is_edit_defaults) {
			$is_editing_defaults = true;
			$file_contents .= "\t$startDefaultsLine\n";
			$file_contents .= "\tprotected static \$acctNameDefault = \"".$name."\";\n";
			$file_contents .= "\tprotected static \$acctNumberDefault = ".$id.";\n";
			$file_contents .= "\tprotected static \$apiKeyDefault = \"".$key."\";\n";
			$file_contents .= "\tprotected static \$apiSecretDefault = \"".$secret."\";\n";
			continue;
		}
		if (trim($line) == $defaultDelimiterLine) {
			if ($is_add_new) {
				$file_contents .= "\t\t".$id." => [\n";
				$file_contents .= "\t\t\t\"key\" => \"".$key."\",\n";
				$file_contents .= "\t\t\t\"secret\" => \"".$secret."\",\n";	
				$file_contents .= "\t\t\t\"name\" => \"".$name."\",\n";
				$file_contents .= "\t\t],\n";
			}
			if ($is_edit_defaults) {
				$file_contents .= "\t\t".$id." => [\n";
				$file_contents .= "\t\t\t\"key\" => \"".$key."\",\n";
				$file_contents .= "\t\t\t\"secret\" => \"".$secret."\",\n";	
				$file_contents .= "\t\t\t\"name\" => \"".$name."\",\n";
				$file_contents .= "\t\t],\n";

				$file_contents .= "\t//Setup.sh Delimiter\n";	
				$file_contents .= "\t//To those editing this file: Don't muck with the line above or any that follow it.\n";	 
				$file_contents .= "\t//The Setup.sh file will edit as well as create this file, so I'm using that line as a hacky way to identify where edits should begin again.\n";	 

				$file_contents .= "\t\t".$id." => [\n";
				$file_contents .= "\t\t\t\"key\" => \"".$key."\",\n";
				$file_contents .= "\t\t\t\"secret\" => \"".$secret."\",\n";
				$file_contents .= "\t\t\t\"name\" => \"".$name."\",\n";
				$file_contents .= "\t\t]\n";
				$file_contents .= "\t);\n}\n";
				break;
			}
		} 
		$file_contents .= $line;
	}
	file_put_contents($cred_file, $file_contents);
}
print "\n".ucfirst($account_ref)." Key and Secret have been added. \nTo add more accounts, run this file again. It will be available with the other scripts at \"SetupApiAccounts.sh\".\n";
fclose($creds);

exit($exit);

//I should probably just overload this function wherever it exists (rather than have a local version.)
function confirm($question, $failText, $kill = false, $exit_status = 0) {
	$response = [true, "y"];
	print $question."\n(y/n)\n";
	$answer = readline();
	if ($answer != "y" && $answer != "yes") 
	{
		if ($kill) {
			print $failText."\n";
			exit($exit_status);
		} else {
			$response = [false, $answer];
			print $failText."\n";
		}
	}
	return $response;
}