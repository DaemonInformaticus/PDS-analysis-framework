/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
package proj.spaceapps.pds_etl.data;

import java.sql.SQLException;
import proj.spaceapps.pds_etl.enumerations.EnumDataType;

/**
 *
 * @author martin
 */
public interface IData 
{
    public boolean      insertLine();
    public EnumDataType getDataType();
    public long         getID();
}
