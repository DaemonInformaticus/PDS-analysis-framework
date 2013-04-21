/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
package proj.spaceapps.pds_etl.data;

import java.sql.Connection;

/**
 *
 * @author martin
 */
public abstract class CData implements IData
{
    protected   Connection  m_conn;
    protected   String      m_tableName;
    protected   long        m_id;
    
    public CData(Connection c, String tableName)
    {
        m_conn          = c;
        m_tableName     = tableName;
        m_id            = 0;
    }

    @Override
    public long getID() { return m_id; }
}
