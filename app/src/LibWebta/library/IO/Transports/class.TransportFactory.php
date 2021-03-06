<?
    /**
     * This file is a part of LibWebta, PHP class library.
     *
     * LICENSE
     *
	 * This source file is subject to version 2 of the GPL license,
	 * that is bundled with this package in the file license.txt and is
	 * available through the world-wide-web at the following url:
	 * http://www.gnu.org/copyleft/gpl.html
     *
     * @category   LibWebta
     * @package    IO
     * @subpackage Transports
     * @copyright  Copyright (c) 2003-2007 Webta Inc, http://www.gnu.org/licenses/gpl.html
     * @license    http://www.gnu.org/licenses/gpl.html
     */

    Core::Load("Interface.Transport.php", dirname(__FILE__)."/Transports");
	
    /**
	 * Transport factory
	 * 
     * @name       TransportFactory
     * @category   LibWebta
     * @package    IO
     * @subpackage Transports
     * @version 1.0
     * @author Igor Savchenko <http://webta.net/company.html>
     */
	class TransportFactory extends Core
	{
	    /**
	     * Return Transport. example: TransportFactory::GetTransport('SSH', $ssh_host, $ssh_port, $ssh_user, $ssh_pass);
	     *
	     * @static 
	     * @param string $transportname
	     * @return Object
	     */
	    static public function GetTransport($transportname)
	    {
	        $path_to_transports = dirname(__FILE__)."/Transports";
        
	        if (file_exists("{$path_to_transports}/class.{$transportname}Transport.php"))
	        {
	            Core::Load("class.{$transportname}Transport.php", $path_to_transports);
	            
	            if (class_exists("{$transportname}Transport") && self::IsTransport("{$transportname}Transport"))
	            {              
	                // Get Constructor Reflection	
    				if (is_callable(array("{$transportname}Transport", "__construct")))
    					$reflect = new ReflectionMethod("{$transportname}Transport", "__construct");

    				// Delete $objectname from arguments
    				$num_args = func_num_args()-1;
    				$args = func_get_args();
    				array_shift($args);
    				
    				if ($reflect)
    				{
    					$required_params = $reflect->getNumberOfRequiredParameters();    					
    					if ($required_params > $num_args)
    					{
    					    $params = $reflect->getParameters();
    					    //TODO: Show what params are missing in error
    					    Core::RaiseError(sprintf(_("Missing some required arguments for %s Transport constructor. Passed: %s, expected: %s."),$transportname, $num_args, $required_params));							
    					}
				
				    }
				    $reflect = new ReflectionClass("{$transportname}Transport");
				     
				    if (count($args) > 0)
						return $reflect->newInstanceArgs($args);
					else 
						return $reflect->newInstance(true);
	            }
	            else 
	               Core::RaiseError(sprintf(_("Class '%s' not found or doesn't implements ITransport interface"), "{$transportname}Transport"));
	        }
	    }
	    
	    /**
	     * Return all avaiable transports
	     *
	     * @static 
	     * @return array
	     */
	    static public function GetAvaiableTransports()
	    {
	        $retval = array();
			
			$transports = glob(dirname(__FILE__)."/Transports/class.*Transport.php");
			
			foreach((array)$transports as $transport)
			{
				$pi = pathinfo($transport);
				Core::Load($pi["basename"], $pi["dirname"]);
				preg_match("/class\.([A-Za-z0-9_]+)\.php/si", $pi["basename"], $matches);
				
				if (class_exists($matches[1]) && self::IsTransport($matches[1]))
					$retval[] = substr($matches[1], 0, -9);
			}
			
			return $retval;
	    }
	    
	    /**
	     * Is Transport class with name $name
	     *
	     * @param string $name
	     * @access private
	     * @static 
	     * @return bool
	     */
	    static private function IsTransport($name)
	    {
	        $reflect = new ReflectionClass("{$name}");
	        return $reflect->implementsInterface("ITransport");
	    }
	}
?>