<?php
// Base Script to be copied then modified.
//											COPY THEN MODIFY

$call = "List";
$method = "Get";


$call_type = $call.$method;

require_once(dirname(dirname(__DIR__))."/Classes/Api/Account_Credentials/KeysAndSecrets.php"); 	
$account_credentials = new KeysAndSecrets();
$account_credentials->setAccount();



//START CALL SPECIFIC 
require_once(dirname(dirname(__DIR__))."/Classes/Api/List/ListGet.php");				//Call Specifc//Incomplete//
$api_object = new ListGet();												//Call Specifc//Incomplete//
$api_object->setAccount($account_credentials);
include_once(dirname(dirname(__DIR__))."/Use_Case_Vars/ListGetVars.php");			//Call Specifc//Incomplete//		
//END CALL SPECIFIC 



require_once(dirname(dirname(__DIR__))."/Classes/CliScriptAbstract.php");	
$script = new CliScriptAbstract();	

require_once(dirname(dirname(__DIR__))."/Classes/Client_Library/Sailthru_Implementation_Client.php");

include_once(dirname(dirname(__DIR__))."/Setup_Files/ScriptSettings.php");			
new ScriptSettings();

////////////////////   VARS 
/*
There are three ways to input vars for a script: 
1) Thorugh the Command Line Interface, CLI, when you run the script.
2) The vars array in this file. 
3) An include file specific to this script. The Use_Vase_Vars include above. 

While they are essentially equivalent, The Use Case include is the prefered method. The other two are
provided to give ease of use in a given situation. They can also be used in conjunction -with each 
overridden by the next in the order given above. That means if you add two vars, a & b, to the use case 
vars file but provide b again in the CLI, 'a' will be the value from the use case file, but 'b' will be 
the command line value. This allows for a lot of flexibility but can create confusing results if you 
forget what is active (not commented out) in the Use case file. When in doubt run 
files with the '-i' option, aka the interactive option, to print out and confirm the default values 
before submitting the call to Sailthru. Alternatively, use the '-v' option to just see what is being 
sent in.  

While the option exists to hardcode vars into this file, unless you are creating a completely modified file, 
I recommend you use the Use Case Var file instead. There are two advantages to putting use cases into a seperate 
file. First, you keep this file reliable both for functionality and as a template for modifications. The better 
reason however is you can easily preserve old use cases for future use by grouping them in the use case file and 
commenting them out when you are done. Then you can easily go back to old work by uncommenting your use case block 
in the use case file. :)

The command line is likely the easiest for any one off tests. When these files are run they will print out a help 
menu to let you know the proper inputs. 

Open a terminal and type "php " then drag this file to the terminal. Hit enter and the help printout 
will show you the options. 

For ease of use on the terminal, a shorthand was set up to make any API call by simply typing the type of call (User/Send/Etc) 
then the method (Get/Post/Delete) followed by '.sh'. You should press 'tab' twice in quick succession to see your options 
and autocomplete the name. This format will reference the use case vars file, but bypasses this file. 
Eg: 
UserPost.sh

When entering input, the parameter names should match up to the help menu's provided options (cli) or the Sailthru Docs page 
for that call.

http://getstarted.sailthru.com/new-for-developers-overview
*/
							
////Defaults or One Time Vars - In a conflict this over writes the use case file, but loses to cli input
// $api_object->setVar("id","jlewis@sailthru.com");

//Add new parameters to print out in the help screen that are exclusive to this custom Script. Can use the simpler format here or the format api classes use. 
$cli_params = ["example" => "What example should be used to do"];
$api_object->createCliParameters($cli_params);

////Read in CLI Vars - In conflict takes highest priority over other inputs
$input_vars = $script->readCliArguments($argv, $api_object);
$api_object->ingestInput($input_vars["config_vars"] + $input_vars["wildcard_vars"], CliScriptAbstract::$flags["isOverride"]);  //Validates and Assigns Vars


//////////   END VARS

////////////////////   START MAIN PROGRAM
////Create Client
if (CliScriptAbstract::$flags["isDefaults"]) {
	$account_credentials->setAccount("defaults");
}
$client = new Sailthru_Implementation_Client($account_credentials->getKey(), $account_credentials->getSecret(), $account_credentials->getEnvironment);
////Designate Call Parameters
$call_data = $api_object->getCallData();
$endpoint = $api_object->getEndpoint();
$method = $api_object->getMethod();

////Status Output
CliScriptAbstract::$flags["isSilent"]?:print "Starting\n";
 
if ((CliScriptAbstract::$flags["isVerbose"] || CliScriptAbstract::$flags["isInteractive"]) && (!CliScriptAbstract::$flags["isQuiet"] && !CliScriptAbstract::$flags["isSilent"])) {
	if ($account_credentials->getNumber() && $account_credentials->getKey() == $account_credentials->getKey($account_credentials->getNumber())) {
		print "Account ".$account_credentials->getNumber().": ".$account_credentials->getName()."\n";
	}
	print "Key: ".$account_credentials->getKey()."\n";
	print "Secret: ".$account_credentials->getSecret()."\nValues:";
	print json_encode($call_data, JSON_PRETTY_PRINT)."\n";

	if (CliScriptAbstract::$flags["isInteractive"]){
		//Confirm + screen output if user decides to kills the script.
		$script->confirm("Proceed?", "Add the '-h' option for more details on valid inputs.");
	}
	//Seperate input from output
	print "\n\nCall Response\n";
}

////Api Call
$response = $client->$method($endpoint, $call_data); 
		
////Status Output						
if (!CliScriptAbstract::$flags["isQuiet"] && !CliScriptAbstract::$flags["isSilent"]) {
	print json_encode($response, JSON_PRETTY_PRINT)."\n";
}
CliScriptAbstract::$flags["isSilent"]?:print"\nFinished\n";

////Successful Output
exit(0);
////////   END MAIN PROGRAM