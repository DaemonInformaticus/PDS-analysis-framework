/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
package proj.spaceapps.pds_etl.data;

import java.sql.ResultSet;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.SQLException;
import java.sql.Date;
import java.sql.Statement;
import proj.spaceapps.pds_etl.enumerations.EnumDataType;

/**
 *
 * @author martin
 */
public class CDataDataset extends CData
{
    private String m_group;
    private String m_identifier;
    
    
    public CDataDataset(Connection conn, String group, String identifier)
    {
        super(conn, "tblDataSet");
        m_group         = group;
        m_identifier    = identifier;
    }

    @Override
    public EnumDataType getDataType() { return EnumDataType.DataTypeDataSet; }

    @Override
    public boolean insertLine() 
    {
        /*
         tblDataSet
            - id INT PRIMARY KEY AUTO_INCREMENT
            - group TEXT
            - identifier TEXT
            - created DATETIME
            - updated DATETIME
            - createdBy INT
            - updatedBy INT

         */
        try
        {
            PreparedStatement statement = m_conn.prepareStatement( 
                    "INSERT INTO " + m_tableName + " VALUES(0, ?, ?, ? ,'0000-00-00', 0, 0);", Statement.RETURN_GENERATED_KEYS);
            
            statement.setString(1, m_group);
            statement.setString(2, m_identifier);
            statement.setDate(3, new Date(System.currentTimeMillis()));
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
