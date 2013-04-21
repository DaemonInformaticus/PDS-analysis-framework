/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
package proj.spaceapps.pds_etl.data;

import java.sql.Connection;
import java.sql.Date;
import java.sql.PreparedStatement;
import java.sql.SQLException;
import proj.spaceapps.pds_etl.enumerations.EnumDataType;

/**
 *
 * @author martin
 */
public class CDataDescription extends CData
{
    private long    m_datasetID;
    private String  m_key;
    private String  m_value;
    private String  m_columns[] = {"", ""};
    
    public CDataDescription(Connection c, String key, String value, long datasetID)
    {
        super(c, "tblDataSetDescription");

        m_key       = key;
        m_value     = value;
        m_datasetID = datasetID;
    }

    @Override
    public EnumDataType getDataType()  { return EnumDataType.DataTypeDescription; }

    @Override
    public boolean insertLine()
    {
        /*
         tblDataSetDescription
            - id          INT PRIMARY KEY AUTO_INCREMENT
            - datasetID   INT
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
                        "INSERT INTO " + m_tableName + " VALUES(0, ?, ?, ?, ?, '0000-00-00', 0, 0);");
            statement.setLong(1, m_datasetID);
            statement.setString(2, m_key);
            statement.setString(3, m_value);
            statement.setDate(4, new Date(System.currentTimeMillis()));
            statement.executeUpdate();
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
