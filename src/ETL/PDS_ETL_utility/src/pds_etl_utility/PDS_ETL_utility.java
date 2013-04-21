/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
package pds_etl_utility;

import java.sql.Connection;
import java.io.BufferedReader;
import java.io.DataInputStream;
import java.io.FileInputStream;
import java.io.IOException;
import java.io.InputStreamReader;
import java.sql.DriverManager;
import java.sql.SQLException;
import java.util.ArrayList;
import proj.spaceapps.pds_etl.data.CDataColumnDescriptor;
import proj.spaceapps.pds_etl.data.CDataDataset;
import proj.spaceapps.pds_etl.data.CDataDescription;
import proj.spaceapps.pds_etl.data.CDataValue;

/**
 *
 * @author martin
 */
public class PDS_ETL_utility 
{
    public static final String      m_dbName        = "dbPDSData";
    public static final String      m_dbUser        = "root";
    public static final String      m_dbPass        = "";
    private String                  m_path          = "";
    private String                  m_pathData      = "";
    private BufferedReader          m_reader;
    private BufferedReader          m_dataReader;
    private Connection              m_conn          = null;
    private long                    m_datasetID     = 0L;
    private ArrayList<CDataDescription> m_descriptions;
    private ArrayList<CDataColumnDescriptor> m_colDesc;
    private ArrayList<CDataValue>   m_dataLines;
    private int                     m_colIndex = 1;
    
    private boolean extractObject(String type)
    {
        try
        {
            String line     = m_reader.readLine();
            
            // if type is 'column'
            if(type.equals("COLUMN"))
            {
                // parse out everything until END_OBJECT

                String key      = "";
                String value    = "";

                while(key.equals("END_OBJECT") == false)
                {
                    // get new key and value
                    line                = cleanLine(line);
                    String arrLine[]    = line.split("=");
                                        
                    if(arrLine.length < 2)
                    {
                        System.out.println("extractObject: searching for END_OBJECT, I ignore this line: " + line);
                        
                        line = m_reader.readLine();
                        
                        continue;
                    }

                    // if the value starts with a ", but doesn't end with one:
                    if(arrLine[1].trim().startsWith("\"") == true && arrLine[1].trim().endsWith("\"") == false)
                    {
                        // it's a multiline value. 
                        // read lines and concat them to the value, until " is found at the end. 
                        boolean bDoConcat = true;
                        while(bDoConcat == true)
                        {
                            String subLine  = m_reader.readLine();
                            arrLine[1]     += subLine;
                            bDoConcat       = !subLine.trim().endsWith("\"");
                        }
                    }
                    
                    key                 = arrLine[0].trim();
                    value               = arrLine[1].trim();
                    
                    // System.out.println("extractObject: key: " + key);
                    
                    // create a new column descriptor object.
                    CDataColumnDescriptor desc = new CDataColumnDescriptor(m_conn, m_datasetID, m_colIndex, key, value);

                    // store column descriptor in list. 
                    m_colDesc.add(desc);
                    line = m_reader.readLine();
                }

                m_colIndex++;
            }   
        }
        catch(IOException ioE)
        {
            System.err.println("Read error: ");
            ioE.printStackTrace();
        }
        
        return true;
    }
    
    private String cleanLine(String line)
    {
        if(line == null)
            return "";
        
        // remove spaces.
        line = line.replace("  ", " ");
        
        // remove "'s
        // line = line.replace("\"", "");
        
        // return result. 
        return line;
    }
    
    private boolean extract()
    {        
        // for each line: 
        String line                 = "";
       boolean bParseDescription   = true;
        
        try
        {
            while ((line = m_reader.readLine()) != null)
            {
                // clean out all the cruft from the line. 
                line                = cleanLine(line);
                String arrLine[]    = line.split("=");

                if(arrLine.length < 2)
                {
                    System.err.println("Ignoring line: " + line);
                    continue;
                }
                
                // if arrLine.length > 2: 
                if(arrLine.length > 2)    
                {
                    // the value actually contains '='s. Knit everything after the first object back
                    // together and treat as one object. 
                    String reconstruct = "";
                    
                    for(int i = 1; i < arrLine.length; i++)
                        reconstruct += arrLine[i] + "=";
                    
                    arrLine[1] = reconstruct;
                }
                
                // if the value starts with a ", but doesn't end with one:
                if(arrLine[1].trim().startsWith("\"") == true && arrLine[1].trim().endsWith("\"") == false)
                {
                    // it's a multiline value. 
                    // read lines and concat them to the value, until " is found at the end. 
                    boolean bDoConcat = true;
                    while(bDoConcat == true)
                    {
                        String subLine  = m_reader.readLine();
                        arrLine[1]     += subLine;
                        bDoConcat       = !subLine.trim().endsWith("\"");
                    }
                }
                
                // if line is part of an object: 
                if(arrLine[0].trim().equals("OBJECT") == true)
                {
                    bParseDescription = false;
                    
                    // parse object. 
                    extractObject(arrLine[1].trim());
                }
                // else
                else if(bParseDescription == true)
                {
                    // parse description. 
                    CDataDescription desc = new CDataDescription(m_conn, arrLine[0].trim(), arrLine[1].trim(), m_datasetID);
                    
                    m_descriptions.add(desc);
                }
            }
        }
        catch(IOException ioE)
        {
            System.err.println("IOException on extract: ");
            ioE.printStackTrace();
            return false;
        }
        return true;
    }
    
    private boolean Translate()
    {
        // this is pretty much done inside the data objects... 
        
        return true;
    }
    
    private boolean load()
    {
        // for each element in the arraylists: insert lines in the database.
        for(CDataDescription d          : m_descriptions)   d.insertLine();
        for(CDataColumnDescriptor cD    : m_colDesc)        cD.insertLine();
        // for(CDataValue v                : m_dataLines)      v.insertLine();
        
        return true;
    }

    private void createLineObj(String line, int index)
    {
        // explode the line in spaces. 
        String arrLine[] = line.split(" ");
        
        // for each space that is not empty: 
        int colIndex = 1;
        int rowIndex = index - 1;
        
        for(String e : arrLine)
        {
            String elem = e.trim();

            if(elem.equals(""))
                continue;
            
            // create new Value object. 
            CDataValue val = new CDataValue(m_conn, m_datasetID, colIndex, rowIndex, elem);
            val.insertLine();
            // store value object in array list. 
            // m_dataLines.add(val);    
            
            colIndex++;
        }
    }
    
    private boolean readData()
    {
        int     index   = 0;
        String  line    = "";
        
        try
        {
            while ((line = m_dataReader.readLine()) != null)
            {
                // ignore the first 2 lines....
                if(index < 2)
                {
                    index++;
                    continue;
                }
                
                createLineObj(line, index);
                index++;
            }
            
            index--;
            System.out.println("There are " + index + " values.");
        }
        catch (IOException ioE)
        {
            System.err.println("Error reading data.");
        }
        
        return true;
    }
    
    private boolean createDBConnection()
    {
        m_conn = null;
        
        try
        {
            Class.forName("com.mysql.jdbc.Driver");
            
            m_conn = DriverManager.getConnection("jdbc:mysql://localhost/" + m_dbName + "?" + "user=" + m_dbUser + "&password=" + m_dbPass);
        }
        catch(ClassNotFoundException cnfE)
        {
            System.err.println("could not load class: ");
            cnfE.printStackTrace();
            return false;
        }
        catch(SQLException sE)
        {
            System.err.println("Could not create connection: ");
            sE.printStackTrace();
            return false;
        }
        
        return true;
    }
    
    private boolean initDataSet()
    {
        CDataDataset set = new CDataDataset(m_conn, "Group1", "identifier");

        set.insertLine();
        
        m_datasetID = set.getID();
        
        return true;
    }
    
    private boolean initReader()
    {
        try
        {
            // Open the file that is the first 
            // command line parameter
            System.out.println("Initializing lbl file reader...");
            FileInputStream fstream     = new FileInputStream(m_path);
            DataInputStream in          = new DataInputStream(fstream);
            m_reader                    = new BufferedReader(new InputStreamReader(in));
            System.out.println("Done. ");
            
            System.out.println("Initializing tbl file reader...");
            FileInputStream fDataStream = new FileInputStream(m_pathData);
            DataInputStream dataIn      = new DataInputStream(fDataStream);
            m_dataReader                = new BufferedReader(new InputStreamReader(dataIn));
            System.out.println("Done. ");
        }
        catch(IOException ioE)
        {
            return false; 
        }
        
        return true;
    }

    private void evaluateSets()
    {
        System.out.println("There are " + m_descriptions.size() + " description lines.");
        System.out.println("There are " + m_colDesc.size()      + " column description lines.");
    }
    
    public PDS_ETL_utility(String path, String pathData)
    {
        m_path          = path;
        m_pathData      = pathData;
        m_descriptions  = new ArrayList<CDataDescription>();
        m_colDesc       = new ArrayList<CDataColumnDescriptor>();
        m_dataLines     = new ArrayList<CDataValue>();
        boolean bParse  = initReader();
        bParse          = bParse == true ? createDBConnection() : false;
        bParse          = bParse == true ? initDataSet()        : false;
        bParse          = bParse == true ? extract()            : false;
        bParse          = bParse == true ? load()               : false;
        bParse          = bParse == true ? readData()           : false;
        
        if(bParse == true)
        {
            evaluateSets();
        }
        else
        {
            System.err.println("Something has gone south when parsing the file....");
        }
    }
    
    /**
     * @param args the command line arguments
     */
    public static void main(String[] args) 
    {
        // first argument in the call should be the absolute path of the file. 
        String path     = "c:\\file.txt";
        String pathData = "c:\\data.txt";
        
        if(args.length > 0) path        = args[0];
        if(args.length > 1) pathData    = args[1];
        
        PDS_ETL_utility app = new PDS_ETL_utility(path, pathData);
    }
}
