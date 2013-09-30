<?php

//information needed to connect to the database

$dbConnect  = NULL;
$dbHost     = "webdb.uvm.edu";
$dbUsername = "aberger4";
$dbUserPass = "aideivim";

$query      = NULL;

/*
   Function to connect to the database
*/

function db_connect($dbName)
{
    global $dbConnect, $dbHost, $dbUsername, $dbUserPass;
   
    if(!$dbConnect)
		{
		 	 $dbConnect = mysql_connect($dbHost, $dbUsername, $dbUserPass);
		}
		
  	if(!$dbConnect)
		{
     	 return 0;
  	}
		
		else if(!mysql_select_db($dbName))
		{
       return 0;
  	}
		
		else
		{
       return $dbConnect;
  	}
}

$questionID = $_GET['id']; //retrieve the question ID from the URL
$choice = $_GET['choice']; //retrieve the researcher's approval choice from the URL

$question = ""; //text of the proposed the question

//connect to the database

$connectID = mysql_connect($dbHost, $dbUsername, $dbUserPass);
mysql_select_db("ABERGER4", $connectID);

//retrieve the text that corresponds with the current question ID

$updateSql = "SELECT fldQuestion FROM tblQuestion WHERE pkQuestionID = '$questionID'";
$questionData = mysql_query($updateSql, $connectID);

//store the query data in the $question variable

while ($row = mysql_fetch_row($questionData))
{
   $question = $row[0];
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

			<title>Question Approval Page</title>
			
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
 			<meta name="author" content="Alex Berger" />
 			<meta name="description" content="Question Approval Page" />
			
			<link rel="stylesheet" href="questionApproval.css" type="text/css" media="screen" />

</head>

<body>

			<?php
					 
					//if the researcher clicks the Submit Question button, store the updated question in the database as approved 
					
					if(isset($_POST["submitted"]))
					{
					   $question = $_POST["question"]; //retrieve the modified question from the textarea
						 
						 //connect to the database
						 
						 $connectID = mysql_connect($dbHost, $dbUsername, $dbUserPass);	
	  				 mysql_select_db("ABERGER4", $connectID);
						 
						 //update the question text and set the question as approved
						 
	  				 $updateSql = "UPDATE tblQuestion SET fldQuestion = '$question', fldApproved = 1 WHERE pkQuestionID = '$questionID'";
    				 mysql_query($updateSql, $connectID);
						 
						 //close the database
						 
	  				 mysql_close($connectID);
						 
						 //display message saying that the question has been successfully modified
						 //display the modified question
						 
					   echo "<h1>Question has been successfully modified!</h1>";
						 echo "<p class='message'><br /><br /><br />The following question has been added:<br /><br /><b>" . $question . "</b></p>";
					}
					
					//if the researcher chooses to accept the question, set the question as approved in the database
			
					else if($choice == 0)
					{
					 	 //connect to the database
					
					   $connectID = mysql_connect($dbHost, $dbUsername, $dbUserPass);	
	  				 mysql_select_db("ABERGER4", $connectID);
				
						 //set the question as approved
				
	  				 $updateSql = "UPDATE tblQuestion SET fldApproved = 1 WHERE pkQuestionID = '$questionID'";
    				 mysql_query($updateSql, $connectID);
						 
						 //close the connection
						 
	  				 mysql_close($connectID);
						 
						 //display message saying that the question has been approved and will now appear in the survey
						 //display the question that has been added
						 
						 echo "<h1>Question has been approved and will now appear in the survey!</h1>";
						 echo "<p class='message'><br /><br /><br />The following question has been added:<br /><br /><b>" . $question . "</b></p>";
					}
					
					//if the researcher chooses to reject the question, remove the question from the database
					
					else if($choice == 1)
					{
					   //connect to the database
					
					   $connectID = mysql_connect($dbHost, $dbUsername, $dbUserPass);	
	  				 mysql_select_db("ABERGER4", $connectID);
						 
						 //remove the question from the database
						 
	  				 $deleteSql = "DELETE FROM tblQuestion WHERE pkQuestionID = '$questionID'";
    				 mysql_query($deleteSql, $connectID);
						 
						 //close the connection
						 
	  				 mysql_close($connectID);
						 
						 //display message saying that the question has been rejected
						 //display the question that has been removed
						 
					   echo "<h1>Question has been rejected and will not appear in the survey!</h1>";
						 echo "<p class='message'><br /><br /><br />The following question has been removed:<br /><br /><b>" . $question . "</b></p>";
					}
					
					//if the researcher chooses to modify the question, display textarea to type the question and Submit Question button
					
					else
					{
					   echo "<h1>Please modify the following question:</h1>";
						 echo "<p><br /></p>";
						 echo "<form method='post' enctype='multipart/form-data'>" .
						    "<p class='message'><textarea style='font-size: 18px; font-weight: bold;' cols='35' rows='5' name='question'>$question</textarea></p>" .
								"<p><br /></p><div><input type='submit' name='submitted' value='Submit Question' style='font-size: 18px;'/></div></form>";
					}
			?>

</body>

</html>