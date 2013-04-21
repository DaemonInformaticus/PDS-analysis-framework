package FTP_client;

import java.io.BufferedReader;
import java.io.FileWriter;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.net.SocketException;

import org.apache.commons.net.ftp.FTPClient;
import org.apache.commons.net.ftp.FTPFile;
import org.apache.commons.net.ftp.FTPReply;

public class PDS_FTP_Client
{
	// NOTE
	// For testing, we used: ftp://pds-geosciences.wustl.edu/mex/mex-m-aspera3-2-edr-ima-v1/mexasp_2100/data/ima_edr_l1b_2005_02/ima_az0020050591156c_accs01.csv
	// It's a nice 13 kB file, so it's small enough for testing purposes.

	/**
	 * The main method of this class is used as a simple testcase.
	 * @param args Ignored.
	 */
	public static void main(String[] args)
	{
		PDS_FTP_Client ftpClient = new PDS_FTP_Client();
		if ( ftpClient.connectToPDS( ) )
		{
	        // Just get a file... for now, getting a short file (13 kB).
	        String fileName = "ima_az0020050591156c_accs01.csv";
			String directory = "mex/mex-m-aspera3-2-edr-ima-v1/mexasp_2100/data/ima_edr_l1b_2005_02/";
			try
			{
				ftpClient.loadFile( directory, fileName );
			} catch (IOException exc)
			{
				// TODO Auto-generated catch block
				exc.printStackTrace();
			}
			finally
			{
				ftpClient.disconnectFromPDS( );
			}
		}
	}

	private FTPClient ftp = new FTPClient();

	public boolean connectToPDS()
	{
		String server = "pds-geosciences.wustl.edu";
	
		try
		{
			ftp.connect( server );
			System.out.println("Connected to " + server + ".");
			System.out.print(ftp.getReplyString());

			// After connection attempt, you should check the reply code to verify success.
			int reply = ftp.getReplyCode();
			if(!FTPReply.isPositiveCompletion(reply)) 
			{
				ftp.disconnect();
				System.err.println("FTP server refused connection.");
				return false;
			}
      
			ftp.enterLocalPassiveMode();
	        ftp.login("anonymous", "");
	        
	        return true;
		} catch (SocketException exc)
		{
			// TODO Auto-generated catch block
			exc.printStackTrace();
			return false;
		} catch (IOException exc)
		{
			// TODO Auto-generated catch block
			exc.printStackTrace();
			return false;
		}
	}

	public void disconnectFromPDS()
	{
		try
		{
			ftp.logout( );
			ftp.disconnect( );
		} catch (IOException exc)
		{
			// TODO Auto-generated catch block
			exc.printStackTrace();
		}
	}
	
	public FTPFile[] listFiles( ) throws IOException
	{
		return ftp.listFiles( );
	}
	
	// Function to collect all files from a defined path, ignoring folders
	String[] collectPathFiles( String path ) throws IOException
	{
		FTPFile [] fileList;
		
		// Get all the files from path (including directories)
		fileList = ftp.listFiles(path);
		
		// Initialise string array to filelist length
		String [] fileNamesList = new String[fileList.length];
		
		// Loop through all files and directories
		for (int i = 0; i<fileList.length; i++) {
			// Pick only files
			if (fileList[i].isFile()) {
				// Save the filenames to the fileNamesList
				fileNamesList [i] = fileList[i].getName();
		    }
		}			
	    return fileNamesList;	
	    
	} // end of collectPathFiles
	
	public void loadFile( String remoteDirectory, String fileName ) throws IOException
	{
		InputStream is = ftp.retrieveFileStream( remoteDirectory + fileName );
		// Using BufferedReader to speed stuff up. If that doesn't speed things up enough, we can use smarter algorithms later.
		BufferedReader reader = new BufferedReader(new InputStreamReader(is));
		
		FileWriter writer = new FileWriter("/tmp/" + fileName );
		
		int ch;
		while ((ch = reader.read( )) != -1 )
		{
			System.out.print((char) ch);
			writer.write( ch );
		}
		
		reader.close( );
		writer.close( );
	}
}
