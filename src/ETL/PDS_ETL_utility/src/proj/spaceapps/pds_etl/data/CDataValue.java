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
public class CDataValue extends CData
{
    /*
        tblColumnValue
        - id          INT PRIMARY KEY AUTO_INCREMENT
        - datasetID   INT
        - columnindex INT
        - stringValue VARCHAR(24)
        - created     DATETIME
        - updated     DATETIME
        - createdBy   INT
        - updatedBy   INT
     
     */
    
    private long    m_datasetID;
    private int     m_columnIndex;
    private int     m_rowIndex;
    private String  m_value;
    
    public CDataValue(Connection conn, long setID, int colIndex, int rowIndex, String value)
    {
        super(conn, "tblColumnValue");
        
        m_datasetID     = setID;
        m_columnIndex   = colIndex;
        m_rowIndex      = rowIndex;
        m_value         = value;
    }

    @Override
    public EnumDataType getDataType() { return EnumDataType.DataTypeColumnValue; }

    @Override
    public boolean insertLine() 
    {
        /*
         tblColumnValue
            - id          INT PRIMARY KEY AUTO_INCREMENT
            - datasetID   INT
            - columnindex INT
            - rowIndex    INT
            - stringValue VARCHAR(24)
            - created     DATETIME
            - updated     DATETIME
            - createdBy   INT
            - updatedBy   INT

         */
        try
        {
            PreparedStatement statement = m_conn.prepareStatement(
                        "INSERT INTO " + m_tableName + " VALUES(0, ?, ?, ?, ?, ?, '0000-00-00', 0, 0);");
            statement.setLong(1, m_datasetID);
            statement.setInt(2, m_columnIndex);
            statement.setInt(3, m_rowIndex);
            statement.setString(4, m_value);
            statement.setDate(5, new Date(System.currentTimeMillis()));
            statement.executeUpdate();
            statement.close();
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
