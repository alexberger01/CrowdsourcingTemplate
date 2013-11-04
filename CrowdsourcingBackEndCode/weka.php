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

$noData = false;

//connect to the database

$connectID = mysql_connect($dbHost, $dbUsername, $dbUserPass);	
mysql_select_db("ABERGER4", $connectID);

//retrieve savings values from user table and sort in numerical order by the corresponding user ID

$userSql = "SELECT User.fldSavings FROM tblUser User WHERE (SELECT COUNT(*) FROM tblResponse Response WHERE " . 
				 "User.pkUserID = Response.fkUserID) > 0 AND User.fldSavings IS NOT NULL ORDER BY User.pkUserID";
$userData = mysql_query($userSql, $connectID);

//retrieve every question Id from the question table and sort the IDs in numerical order

$questionSql = "SELECT pkQuestionID FROM tblQuestion ORDER BY pkQuestionID";
$questionData = mysql_query($questionSql, $connectID);

//retrieve every user ID, question ID, and response from the response table
//first sort numerically by user ID, then sort numerically by question ID

$responseSql = "SELECT fkUserID, fkQuestionID, fldResponse FROM tblResponse ORDER BY fkUserID, fkQuestionID";
$responseData = mysql_query($responseSql, $connectID);

//close the connection

mysql_close($connectID);

//create array to hold response variable from user table
//fill the array with the query results
//array will be sorted in numerical order by user ID

$userSavings = array();

while ($row = mysql_fetch_row($userData))
{
 		$userSavings[] = $row[0];
}

//create array to hold every question ID from the question table
//fill the array with the query results
//array will be sorted in numerical order

$surveyQuestions = array();

while ($row = mysql_fetch_row($questionData))
{
 		$surveyQuestions[] = $row[0];
}

//create arrays to hold every user ID, question ID, and response from the response table
//fill the arrays with the query results

//userID array will be in numerical order, though there will be duplicate values
//questionID array will be in numerical order for every subset of userID
//responses represents user ID's response to question ID

$userIDs = array();
$questionIDs = array();
$responses = array();

while ($row = mysql_fetch_row($responseData))
{
 		$userIDs[] = $row[0];
		$questionIDs[] = $row[1];
		$responses[] = $row[2];
}

//only run weka if at least one user has answered a question

if(count($userSavings) > 0)
{

//array will hold the average (mean) response for each question
//index of questionAverages array corresponds to index of surveyQuestions array

//if there are no responses to a question, remove question ID from surveyQuestions

$questionAverages = array();

$sum = 0.0;
$num = 0;

for($i = 0 ; $i < count($surveyQuestions) ; $i++)
{
 		$sum = 0.0;
		$num = 0;

 		for($j = 0 ; $j < count($questionIDs) ; $j++)
		{
		 		if($surveyQuestions[$i] == $questionIDs[$j] && !is_null($responses[$j]))
				{
				 		$sum = $sum + $responses[$j];
						$num++;
				}
		}
		
		if($num == 0)
		{
		 		unset($surveyQuestions[$i]);
				$surveyQuestions = array_values($surveyQuestions);
				$i--;
		}
		
		else
		{
		 		$questionAverages[] = $sum/$num;
		}	
}

if(count($surveyQuestions) > 0)
{

//remove null responses from questions without averages

$total = 0;

for($i = 0 ; $i < count($responses) ; $i++)
{
 		for($j = 0 ; $j < count($surveyQuestions) ; $j++)
		{
		 		if($questionIDs[$i] == $surveyQuestions[$j])
				{
				 		$total++;
				}
		}
		
		if($total == 0)
		{
		 		unset($userIDs[$i]);
				$userIDs = array_values($userIDs);
				
				unset($questionIDs[$i]);
				$questionIDs = array_values($questionIDs);
				
				unset($responses[$i]);
				$responses = array_values($responses);
				
				$i--;
		}
		
		else
		{
		 		$total = 0;
		}
}

//create finalResponses array
//finalResponses represents user's response to each question
//finalResponses replaces missing/null values with the mean value for that question

$finalResponses = array();

$totalQuestions = count($surveyQuestions);
$numSkipped = 0;

for($i = 0 ; $i < count($responses) ; $i++)
{
		if($questionIDs[$i] == $surveyQuestions[($i + $numSkipped) % $totalQuestions] && !is_null($responses[$i]))
		{
				$finalResponses[] = $responses[$i];
		}
		
		else if(is_null($responses[$i]))
		{
		 		$finalResponses[] = $questionAverages[$i % $totalQuestions];
		}
				
		else
		{
				$finalResponses[] = $questionAverages[$i % $totalQuestions];
				$numSkipped++;
				$i--;
		}
}

//open data file
//print question ID headers on first line (separated by commas)

$file = fopen("data.csv", "w");
fwrite($file, "Savings, ");

for($i = 0 ; $i < count($surveyQuestions) ; $i++)
{
 		if($i + 1 < count($surveyQuestions))
		{
		 		fwrite($file, $surveyQuestions[$i] . ", ");
		}
		
		else
		{
		 		fwrite($file, $surveyQuestions[$i] . "\n");
		}
}

//print final response data on following lines (separated by commas)

$questionIndex = 0;
$savingsIndex = 0;

fwrite($file, $userSavings[$savingsIndex] . ", ");
$savingsIndex++;

for($i = 0 ; $i < count($finalResponses) ; $i++)
{
 		if($questionIndex + 1 < count($surveyQuestions))
		{
		 		fwrite($file, $finalResponses[$i] . ", ");
		 		$questionIndex++;
		}
		
		else
		{
		 		fwrite($file, $finalResponses[$i] . "\n");
				$questionIndex = 0;
				
				if($savingsIndex < count($userSavings))
				{
				 		fwrite($file, $userSavings[$savingsIndex] . ", ");
						$savingsIndex++;
				}
		}
}

//close data file

fclose($file);

//execute java program and retrieve output
//output will consist of coefficients separated by spaces

$javaOutput = shell_exec('java -jar project.jar');

//store coefficients in an array

$coefficients = explode(" ", $javaOutput);

//connect to the database

$connectID = mysql_connect($dbHost, $dbUsername, $dbUserPass);	
mysql_select_db("ABERGER4", $connectID);

//update coefficients in the question table

for($i = 0 ; $i < count($surveyQuestions) ; $i++)
{
 		$coefficientSql = "UPDATE tblQuestion SET fldCoefficient = " . 
					$coefficients[$i + 1] . " WHERE pkQuestionID = " . $surveyQuestions[$i];
		mysql_query($coefficientSql, $connectID);
}

//update constant (y-intercept) in constant table

$constantSql = "UPDATE tblConstant SET fldConstant = " . $coefficients[count($coefficients) - 1];
mysql_query($constantSql, $connectID);

//close the connection

mysql_close($connectID);

}

else
{
 		$noData = true;
}

}

else
{
 		$noData = true;
}

//if no questions have been answered, store 10000 for constant

if($noData == true)
{
 		//connect to the database

		$connectID = mysql_connect($dbHost, $dbUsername, $dbUserPass);	
		mysql_select_db("ABERGER4", $connectID);
		
		//reset question coefficients to 0
		
		$resetSql = "UPDATE tblQuestion SET fldCoefficient = 0";
		mysql_query($resetSql, $connectID);
		
		//store 10000 as value for constant
		
		$constantSql = "UPDATE tblConstant SET fldConstant = 10000";
		mysql_query($constantSql, $connectID);
 		
		//close the connection

		mysql_close($connectID);
}

?> 