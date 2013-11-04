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

/*
   Function returns array of questions that user has not seen yet
	 $questions is the query results for all approved questions
	 $responses is the query results for all questions already seen by the user
*/

function getQuestionChoices($questions, $responses)
{			
		$questionIDs = array(); //array to hold the IDs of all approved questions in the database
		
		//store question query results in $questionIDs array
		
		while ($row = mysql_fetch_row($questions))
		{
		   $questionIDs[] = $row[0];
		}
				
		$reponseIDs = array(); //array to hold the IDs of all questions already seen by the user
		
		//store response query results in $responseIDs array
		
		while ($row = mysql_fetch_row($responses))
		{
		   $responseIDs[] = $row[0];
		}
				
		$questionChoices = array(); //array to hold the IDs of all approved questions that user has not seen yet
		$questionUsed = false; //boolean variable to indicate whether the current question has already been used
		
		//if question ID exists in $questionID but does not exist in $responseID, then add the ID to $questionChoices
		
		for($i = 0 ; $i < count($questionIDs) ; $i++)
		{
		   $questionUsed = false;
					 
		   for($j = 0 ; $j < count($responseIDs) ; $j++)
		   {
		      if($questionIDs[$i] == $responseIDs[$j])
					{
					   $questionUsed = true;
					}
			}
					 
			if($questionUsed == false)
			{
			   $questionChoices[] = $questionIDs[$i];
			}
   }
	 
	 //return array of question IDs that the user has not seen yet
	 
	 return $questionChoices;
}

$userID = $_GET['user']; //retrieve the user ID from the URL
$questionID = $_GET['id']; //retrieve the question ID from the URL

$response = ""; //user's response to the current question
$newQuestion = ""; //new question added to the survey by the user
$errorMessage = NULL; //error message for invalid input
$thankYouMessage = NULL; //thank you message for when the user adds a valid question to the survey

//if the user clicks the Next button, retrieve user input
//if the user input is valid, store response in the database and take user to the next page
//if the user input is not valid, display error message

if(isset($_POST["nextQuestion"]))
{
 		$response = $_POST["response"]; //user input from textbox
		
		//if there is no value in the textbox, create an error message asking user to enter a value
		
		if($response == "")
		{
		 		$errorMessage = "Please enter a value in the textbox below!";
		}
		
		//if the user input is not entirely numeric, create error message asking user to enter a numeric value
		
		else if(!(is_numeric($response)))
		{
		 		$errorMessage = "Input must be completely numeric! Please try again!";
		}
		
		//if valid user input, store value in the database and take user to the next page
		
		else
		{
		    //connect to the database
		
		 		$connectID = mysql_connect($dbHost, $dbUsername, $dbUserPass);	
				mysql_select_db("ABERGER4", $connectID);
				
				//insert the user input into the database
				
				$insertSql = "INSERT INTO tblResponse SET fkUserID = '$userID', fkQuestionID = '$questionID', fldResponse = '$response'";
        mysql_query($insertSql, $connectID);
				
				//retrieve the question IDs of all questions that have been approved by the researcher
				
				$questionSql = "SELECT pkQuestionID FROM tblQuestion WHERE fldApproved = 1";
				$questions = mysql_query($questionSql, $connectID);
				
				//retrieve the question IDs of all questions that have already been seen by the user
				
				$responseSql = "SELECT fkQuestionID FROM tblResponse WHERE fkUserID = '$userID'";
				$responses = mysql_query($responseSql, $connectID);
				
				//close the connection
				
				mysql_close($connectID);
				
				$questionChoices = getQuestionChoices($questions, $responses); //IDs of all approved questions that have not been seen by the user
				
				//if user has already seen all approved questions, take user to the prediction page
				//if user has not already seen all approved questions, randomly choose a question to display next
				
				if(count($questionChoices) == 0)
				{
				 		header("Location: https://www.uvm.edu/~aberger4/CrowdsourcingProject/prediction.php?user=" . $userID);
				}
				
				else
				{
				   $nextQuestionID = $questionChoices[array_rand($questionChoices, 1)];				 
				   header("Location: https://www.uvm.edu/~aberger4/CrowdsourcingProject/question.php?user=" . $userID . "&id=" . $nextQuestionID);
				}
		}
}

//if user clicks the Skip button, store NULL in the database as the answer to the current question
//take the user to the next page (either be the next question or the prediction page)

else if(isset($_POST["skipQuestion"]))
{
    //connect to the database

 		$connectID = mysql_connect($dbHost, $dbUsername, $dbUserPass);	
		mysql_select_db("ABERGER4", $connectID);
		
		//insert NULL in the database as the answer to the current question
		
		$insertSql = "INSERT INTO tblResponse SET fkUserID = '$userID', fkQuestionID = '$questionID'";
    mysql_query($insertSql, $connectID);
		
		//retrieve the question IDs of all questions that have been approved by the researcher
		
		$questionSql = "SELECT pkQuestionID FROM tblQuestion WHERE fldApproved = 1";
		$questions = mysql_query($questionSql, $connectID);
		
		//retrieve the question IDs of all questions that have already been seen by the user
		
		$responseSql = "SELECT fkQuestionID FROM tblResponse WHERE fkUserID = '$userID'";
		$responses = mysql_query($responseSql, $connectID);
	  
		//close the connection
		
		mysql_close($connectID);
				
		$questionChoices = getQuestionChoices($questions, $responses); //IDs of all approved questions that have not been seen by the user
		
		//if user has already seen all approved questions, take user to the prediction page
		//if user has not already seen all approved questions, randomly choose a question to display next
		
		if(count($questionChoices) == 0)
		{
		   header("Location: https://www.uvm.edu/~aberger4/CrowdsourcingProject/prediction.php?user=" . $userID);
		}
				
		else
		{
		   $nextQuestionID = $questionChoices[array_rand($questionChoices, 1)];				 
	     header("Location: https://www.uvm.edu/~aberger4/CrowdsourcingProject/question.php?user=" . $userID . "&id=" . $nextQuestionID);
		}
}

//if the user clicks the Add Question button, retrieve the user input
//if the question is valid, store question in database, send email to researcher, and create thank you message
//if the question is not valid, create error message

else if(isset($_POST["addQuestion"]))
{
   $newQuestion = $_POST["newQuestion"]; //user input from textarea
	 
	 //prepare text of question before storing in the database
	 
	 $newQuestion = htmlentities($newQuestion, ENT_QUOTES);
	 
	 //if user did not type anything into textarea, create error message asking user to type a question
	 
	 if($newQuestion == "")
	 {
	    $errorMessage = "To add a question to this survey, please type the question in the big textbox at the bottom!";
	 }
	 
	 //if the question is valid, store the question in the database (as unapproved), send email to researcher, and create thank you message
	 
	 else
	 {
	    //connect to the database
	 
	    $connectID = mysql_connect($dbHost, $dbUsername, $dbUserPass);	
	    mysql_select_db("ABERGER4", $connectID);
			
			//insert new question into the database
			
			$insertSql = "INSERT INTO tblQuestion SET fkUserID = '$userID', fldQuestion = '$newQuestion', fldApproved = 0";
      mysql_query($insertSql, $connectID);
			
			//retrieve the question ID of the question that was just inserted in the database
			
			$newQuestionID = mysql_insert_id();
			
			//close the connection
			
			mysql_close($connectID);
			
			//define email parameters (to, subject, message, and headers)
			
			$to = "aberger4@uvm.edu";
			$subject = "Question Added to Survey!";
			
			$message = "<html><head><title>Question Added to Survey</title></head><body><p>The following question was added to the survey:<br /><br /><b>" . $newQuestion . "</b>";
			$message .= "<br /><br /><br />Please choose to either approve, reject, or modify this question:<br /><br />";
			$message .= "<a href='https://www.uvm.edu/~aberger4/CrowdsourcingProject/questionApproval.php?id=" . $newQuestionID . "&choice=0'>Approve Question</a><br /><br />";
		  $message .= "<a href='https://www.uvm.edu/~aberger4/CrowdsourcingProject/questionApproval.php?id=" . $newQuestionID . "&choice=1'>Reject Question</a><br /><br />";
		  $message .= "<a href='https://www.uvm.edu/~aberger4/CrowdsourcingProject/questionApproval.php?id=" . $newQuestionID . "&choice=2'>Modify Question</a><br /><br /></p>";
				 
			$headers  = "MIME-Version: 1.0\r\n";
      $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
      $headers .= "From: Crowdsourcing Project <aberger4@uvm.edu>\r\n";
			
			//send the email to the researcher
			
			$mail = mail($to, $subject, $message, $headers);
			
			//create thank you message to display to the user
			
			$thankYouMessage = "Thank you for adding a question to this survey!";
	 }
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

			<title>Predictor Question</title>
			
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
 			<meta name="author" content="Alex Berger" />
 			<meta name="description" content="Predictor Question" />
			
			<link rel="stylesheet" href="question.css" type="text/css" media="screen" />

</head>

<body>

			<?php
			    
					//if the user clicks the Next button, display the error message if one was created
					
					if(isset($_POST["nextQuestion"]))
					{
					 		if(!(is_null($errorMessage)))
							{
							 		$message = "<p class='message'>" . $errorMessage . "</p>";
									echo $message;
							}
					}
					
					//if user clicks the Add Question button, display the error message if one was created
					//if no error message was created, display the thank you message
					
					if(isset($_POST["addQuestion"]))
					{
					   if(!(is_null($errorMessage)))
						 {
						    $message = "<p class='message'>" . $errorMessage . "</p>";
						    echo $message;
						 }
							
						 else
						 {
						    $message = "<p class='message'>" . $thankYouMessage . "</p>";
								echo $message;
						 }
					}
			?>

			  <!--Display message asking user to answer the following question-->
			
			<h1>Please answer the following question:</h1>
			
			  <!--Skip a line before displaying the question-->
			
			<p>
				 <br />
			</p>
			
			  <!--Display the randomly chosen question-->
			
			<?php
			
					 //connect to the database
			
					 $connectID = mysql_connect($dbHost, $dbUsername, $dbUserPass);	
					 mysql_select_db("ABERGER4", $connectID);
					 
					 //retrieve the question text of the randomly chosen question ID
					 
					 $questionSql = "SELECT fldQuestion FROM tblQuestion WHERE pkQuestionID = '$questionID'";
					 $questionData = mysql_query($questionSql, $connectID);
					 
					 $question = ""; //text of the question to display
					 
					 //store query results in $question variable
					 
					 while ($row = mysql_fetch_row($questionData))
					 {
				      $question = $row[0];
					 }
					 
					 //display randomly chosen question
					 
					 echo "<p class='question'>" . $question . "</p>";	
			?>
			
			  <!--Create a form to retrieve the user's response to the displayed question-->
			
			<form method="post" action="<?php $_SERVER['PHP_SELF'] ?>" enctype="multipart/form-data">
			
			  <!--Create a textbox so that the user can type his/her response to the question-->
			
			<p class="response"><input type="text" maxlength="15" size="15" style="font-size: 18px;" value="<?php echo $response ?>" name="response" /></p>
			
			  <!--Skip a line before displaying the buttons-->
			
			<p>
				 <br />
			</p>
			
			  <!--Display the Next and Skip buttons-->
			
			<div>
					 <input type="submit" name="skipQuestion" value="Skip" style="font-size: 18px;" />
					 <input type="submit" name="nextQuestion" value="Next" style="font-size: 18px;" />
			</div>
			
			</form>
			
			  <!--Skip a few lines before displaying the Add Question message-->
			
			<p>
			   <br /><br />
			</p>
			
			  <!--Display a message asking the user to add a question to the survey-->
			
			<h2>To help improve the accuracy of this calculator, please add a question to this survey:</h2>
			
			  <!--Skip a line before displaying the textarea-->
			
			<p>
				 <br />
			</p>
			
			  <!--Create a form to retrieve the question that the user wants to add to the survey-->
			
			<form method="post" action="<?php $_SERVER['PHP_SELF'] ?>" enctype="multipart/form-data">
			
			  <!--Create a textarea so that the user can type his/her added question-->
			
			<p class="response"><textarea style="font-size: 18px; font-weight: bold;" cols="35" rows="5" name="newQuestion"></textarea></p>
			
			  <!--Skip a line before displaying the Add Question to Survey button-->
			
			<p>
				 <br />
			</p>
			
			  <!--Display the Add Question to Survey button-->
			
			<div>
					 <input type="submit" name="addQuestion" value="Add Question to Survey" style="font-size: 18px;"/>
			</div>
			
			</form>

</body>

</html>