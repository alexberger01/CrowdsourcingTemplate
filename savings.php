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

$savings = ""; //personal savings entered by the user
$errorMessage = NULL; //error message for invalid input

//if the user clicks the Next Question button, retrieve user input
//if valid, save in database and take user to first predictor question
//if not valid, display error message

if(isset($_POST["submitted"]))
{
 		//retrieve user input from textbox

 		$savings = $_POST["savings"];
		
		//if no value was entered, create error message asking the user to enter a value in the textbox
		//if value is not entirely numeric, create error message asking user to enter a numeric value in textbox
		//if value is less than 0, create error message asking user to enter a value that is not negative
		//if value if valid, save it in database and take user to the next page
		
		if($savings == "")
		{
		 		$errorMessage = "Please enter a value in the textbox below!";
		}
		
		else if(!(is_numeric($savings)))
		{
		 		$errorMessage = "Input must be completely numeric! Please try again!";
		}
				
		else if($savings < 0)
		{
				$errorMessage = "Your total savings cannot be less than $0! Please enter an appropriate value!";
		}
		
		else
		{
		 		//connect to the database
						
		 		$connectID = mysql_connect($dbHost, $dbUsername, $dbUserPass);	
				mysql_select_db("ABERGER4", $connectID);
				
				//insert value for savings in the database
				
				$insertSql = "INSERT INTO tblUser SET fldSavings = '$savings'";
        mysql_query($insertSql, $connectID);
				
				//retrieve the user ID of the row that was just inserted
				
				$userID = mysql_insert_id();
				
				//retrieve the question IDs of all questions that have been approved by the researcher
				
				$questionSql = "SELECT pkQuestionID FROM tblQuestion WHERE fldApproved = 1";
				$questions = mysql_query($questionSql, $connectID);
			  
				//close the connection
				
				mysql_close($connectID);
				
				$questionIDs = array(); //approved question IDs
				
				//store query results in an array
				
				while ($row = mysql_fetch_row($questions))
				{
				   $questionIDs[] = $row[0];
				}
				
				//randomly choose one of the question IDs to determine the next question
				
				$nextQuestionID = $questionIDs[array_rand($questionIDs, 1)];
				
				//take user to the next page (first predictor question)
				
				header("Location: https://www.uvm.edu/~aberger4/CrowdsourcingProject/question.php?user=" . $userID . "&id=" . $nextQuestionID);
		}
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

			<title>Personal Savings Question</title>
			
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
 			<meta name="author" content="Alex Berger" />
 			<meta name="description" content="Personal Savings Question" />
			
			<link rel="stylesheet" href="savings.css" type="text/css" media="screen" />

</head>

<body>

			<?php
			
					 //if the user clicks the Next Question button, check to see if an error message was created
					 //if an error message was created, display the error message
			
					if(isset($_POST["submitted"]))
					{
					 		if(!(is_null($errorMessage)))
							{
							 		$message = "<p class='error'>" . $errorMessage . "</p>";
									echo $message;
							}
					}
			?>
			
			  <!--Display message asking user to answer the following question-->

			<h1>In order to help us improve this calculator, please answer the following question:</h1>
			
			  <!--Skip a few lines before displaying the question-->
			
			<p>
				 <br /><br /><br />
			</p>
			
			  <!--Display question asking user to enter his/her personal savings-->
			
			<p class="question">
				 How much money do you have in your bank account?
			</p>
			
			  <!--Create a form to retrieve the user's personal savings-->
			
			<form method="post" action="<?php $_SERVER['PHP_SELF'] ?>" enctype="multipart/form-data">
			
			  <!--Create a textbox so that the user can type his/her personal savings-->
			
			<p class="answer">$<input type="text" maxlength="10" size="10" style="font-size: 18px;" value="<?php echo $savings ?>" name="savings" /></p>
			
			  <!--Skip a few lines before displaying the Next Question button-->
			
			<p>
				 <br /><br />
			</p>
			
			  <!--Display the Next Question button-->
			
			<div>
					 <input type="submit" name="submitted" value="Next Question" style="font-size: 18px;"/>
			</div>
			
			</form>

</body>

</html>