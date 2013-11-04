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

$userID = $_GET['user']; //retrieve the user ID from the URL

$newQuestion = ""; //new question added to the survey by the user
$errorMessage = NULL; //error message for invalid input
$thankYouMessage = NULL; //thank you message for when the user adds a valid question to the survey

//if the user clicks the Add Question button, retrieve the user input
//if the question is valid, store question in database, send email to researcher, and create thank you message
//if the question is not valid, create error message

if(isset($_POST["addQuestion"]))
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

			<title>Personal Savings Prediction</title>
			
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
 			<meta name="author" content="Alex Berger" />
 			<meta name="description" content="Personal Savings Prediction" />
			
			<link rel="stylesheet" href="prediction.css" type="text/css" media="screen" />

</head>

<body>

			<?php
					
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

			  <!--Display message saying that the computer will predict the user's personal savings-->
			
			<h1>Based on your responses to the previous questions, the computer will predict your personal savings:</h1>
			
			  <!--Skip a line before displaying the automated prediction-->
			
			<p>
				 <br />
			</p>
			
			<?php
			
					 //connect to the database
					
					 $connectID = mysql_connect($dbHost, $dbUsername, $dbUserPass);	
					 mysql_select_db("ABERGER4", $connectID);
					 
					 //retrieve the the user's savings from the database
					 
					 $savingsSql = "SELECT fldSavings FROM tblUser WHERE pkUserID = '$userID'";
					 $savingsData = mysql_query($savingsSql, $connectID);
					 
					 $savings = ""; //the user's savings
					 
					 //store query results in $savings variable
					 
					 while ($row = mysql_fetch_row($savingsData))
					 {
				      $savings = $row[0];
					 }
					 
					 //display the computer's automated prediction of the user's personal savings
					 //display the correct value of the user's personal savings
			
					 echo "<p class='message'>Automated Prediction: $" . number_format(10000) . "<br /></p>";
					 echo "<p class='message'>Correct Answer: $" . number_format($savings) . "</p>";
			?>
			
			  <!--Skip a line before displaying the Continue hyperlink-->
			
			<p>
				 <br />
			</p>
			
			  <!--Display the Continue hyperlink-->
			
			<div class="hyperlink">
			   <a href="completion.php">Continue</a>
		  </div>
			
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
			
			<div class="button">
					 <input type="submit" name="addQuestion" value="Add Question to Survey" style="font-size: 18px;"/>
			</div>
			
			</form>

</body>

</html>