/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
package proj.spaceapps.pds_etl.data;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Statement;
import java.sql.Date;
import proj.spaceapps.pds_etl.enumerations.EnumDataType;

/**
 *
 * @author martin
 */
public class CDataColumnDescriptor extends CData
{
    private long    m_datasetID;
    private int     m_colIndex;
    private String  m_key;
    private String  m_value;
    
    public CDataColumnDescriptor(Connection conn, long dsID, int index, String key, String value)
    {
        super(conn, "tblColumnDescriptor");
        m_datasetID = dsID;
        m_colIndex  = index;
        m_key       = key;
        m_value     = value;
    }

    @Override
    public EnumDataType getDataType() { return EnumDataType.DataTypeDescription; }

    @Override
    public boolean insertLine() 
    {
        /*
        - id          INT PRIMARY KEY AUTO_INCREMENT
        - datasetID   INT
        - colIndex    INT
        - stringKey   TEXT
        - stringValue TEXT
        - created     DATETIME
        - updated     DATETIME
        - createdBy   INT
        - updatedBy   INT         
         */
        try
        {
            PreparedStatement statement = m_conn.prepareStatement( 
                    "INSERT INTO " + m_tableName + " VALUES(0, ?, ?, ?, ?, ? ,'0000-00-00', 0, 0);", Statement.RETURN_GENERATED_KEYS);
            
            statement.setLong(1, m_datasetID);
            statement.setInt(2, m_colIndex);
            statement.setString(3, m_key);
            statement.setString(4, m_value);
            statement.setDate(5, new Date(System.currentTimeMillis()));
            statement.executeUpdate();     
            
            ResultSet generatedKeys = statement.getGeneratedKeys();
            
            if(generatedKeys.next())
            {
                m_id = generatedKeys.getLong(1);
            }
        }
        catch(SQLException sE)
        {
            System.err.println("Error inserting description line: ");
            sE.printStackTrace();
            
            return false;
        }

        return true;
    }
    
    
    
}
