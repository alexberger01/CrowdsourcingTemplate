import java.io.FileWriter;
import java.io.PrintWriter;
import java.text.DateFormat;
import java.text.SimpleDateFormat;
import java.util.Date;
import weka.classifiers.functions.LinearRegression;
import weka.core.Instances;
import weka.core.converters.ConverterUtils.DataSource;

public class WekaTest
{
    public static void main(String[] args) throws Exception
    {
        Instances data = DataSource.read("/users/a/b/aberger4/weka/data.csv");
        data.setClassIndex(0);
        
        LinearRegression predictor = new LinearRegression();
        predictor.buildClassifier(data);
        
        DateFormat dateFormat = new SimpleDateFormat("MM/dd/yyyy HH:mm:ss");
        Date date = new Date();
        
        PrintWriter outputFile = new PrintWriter(new FileWriter("/users/a/b/aberger4/weka/LinearRegression.txt", true));
        outputFile.println("Time Generated: " + dateFormat.format(date) + "\n" + predictor.toString() + "\n");
        outputFile.println("-------------------------------------\n");
        outputFile.close();
		  
		  double[] coefficients = predictor.coefficients();
		  
		  String output = "";
		  
		  for(int i = 0 ; i < coefficients.length ; i++)
		  {
		  		output = output + coefficients[i] + " ";
		  }
		  
		  System.out.println(output.trim());
    }
}