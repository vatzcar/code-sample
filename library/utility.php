<?php
/**
 * Class for utility methods. Most of this code has been carried out from older code base.
 * 
 * @package JMTFW
 * @subpackage Utility
 * @author Bhaskar Banerjee
 * @version 1.1
 * @copyright vatzcar.com
 * @license GNU/GPL 2
 * 
 * @todo evaluate the necessity of methods and deprecate all obsolate/unnecessary methods.
 *
 */
class Util {
	/***************************************************************************************
	 * this function takes 2 parameters,
	 * 1. $varValue - for integer value
	 * 2. $varSignificantDigit - number of significant digits
	 *    abstruct - it calculates the length of the number and inserts leading zeros
	 *               to match no. of significant digits.
	 **************************************************************************************/
	/**
	 * *
	 *
	 * @param int $varValue
	 * @param int $varSignificantDigit
	 * @return string, returns value with leading zero with no of significant digit
	 */
	public function fnFormatData($varValue, $varSignificantDigit)
	{
		$varText = ""; //*** the formatted number will be stored here
		
		for($varI = 0; $varI < $varSignificantDigit - strlen((string)($varValue)); $varI++)
		{
			$varText .= "0"; //*** inserting leading zeros
		}
		
		return $varText.$varValue; //*** returning formatted value
	}
	
	/***************************************************************************************
	 * function to get current date
	 **************************************************************************************/
	/**
	 * *
	 * 
	 * @return string, returns Date in dd-MM-yyyy format
	 */
	public function fnGetToday()
	{
		$varDate = getdate();
		
		$varToday = fnFormatData($varDate['mday'],'2')."/"; //*** dd
		$varToday .= fnFormatData($varDate['mon'],'2')."/"; //*** mm
		$varToday .= fnFormatData($varDate['year'],'4');    //*** yyyy
		
		return $varToday;
	}
	
	/***************************************************************************************
	 * function to change dd/mm/yyyy => yyyy/mm/dddd and vice versa
	 * takes 2 parameters
	 * 1. $varNewDate - date
	 * 2. $varFormat  - format
	 **************************************************************************************/
	/**
	 * *
	 *
	 * @param string $varNewDate, must be either yyyy-MM-dd format or dd-MM-yyyy format
	 * @param string $varFormat, must be opposit of given date formay. param should be either YMD or DMY
	 * @return string, returns the date in dd-MM-yyyy or yyyy-MM-dd format
	 * @todo Have some limitation. To convert date the param must be in opposit format. It would be automated.
	 */
	public function fnChangeDateFormat($varNewDate, $varFormat)
	{
		if (strpos($varNewDate,"-"))
			$fragDate = explode("-",$varNewDate);
		else 
			$fragDate = explode("/",$varNewDate);
	
			if (strlen($fragDate[0]) == 4)
				$varNewYear = $fragDate[0];
			else 
				$varNewDay = $fragDate[0];
			
			$varNewMonth = $fragDate[1];
			
			if (strlen($fragDate[2]) == 4)
				$varNewYear = $fragDate[2];
			else 
				$varNewDay = $fragDate[2];
			
			//$varNewMonth = substr($varNewDate,3,2);
		if($varFormat == 'YMD')
		{
			return $varNewYear."/".$varNewMonth."/".$varNewDay;
		}
		else
		{
			/*$varNewDay = substr($varNewDate,8,2);
			$varNewMonth = substr($varNewDate,5,2);
			$varNewYear = substr($varNewDate,0,4);*/
			return $varNewDay."/".$varNewMonth."/".$varNewYear;
		}
	}
	
	/***************************************************************************************
	 * function to populate time list
	 * takes 1 parameter
	 * 1. $varInterval - time interval in minute
	 *    abstruct - generates HTML script for time list depending upon time interval
	 **************************************************************************************/
	/**
	 * *
	 *
	 * @param int $varInterval
	 * @return string, returns HTML code of time range in <select><option>;
	 */
	public function fnPopulateTime($varInterval)
	{
		$varText = "";
		for($varHr=0;$varHr<24;$varHr++)
		{
			for($varMn=0;$varMn<60;$varMn+=$varInterval)
			{
				$vatNewTime = fnFormatData(($varHr % 12 == '0') ? '12' : ($varHr % 12), '2').":";
				$vatNewTime .= fnFormatData($varMn, '2');
				
				if($varHr > 11)
				{
					$vatNewTime .= " PM";
				}
				else
				{
					$vatNewTime .= " AM";
				}
				
				if($vatNewTime == "10:00 AM")
				{
					$varText .= "<option selected value='".$vatNewTime."'>";
				}
				else
				{
					$varText .= "<option value='".$vatNewTime."'>";
				}
				
				$varText .= $vatNewTime."</option>\n";
			}
		}
		
		return $varText;
	}
	
	/**
	 * *
	 *
	 * @param string $varTime, time must be in hh:mm AMPM format
	 * @return string, returns 12 hr.s time format to 24 hrs. format in hh:mm:ss
	 */
	public function fnGetDBTimeFormat($varTime)
	{
		if(substr($varTime,6,2) == "PM" && substr($varTime,0,2) < "12")
			return (((int)(substr($varTime,0,2))+12).substr($varTime,2,3));
		elseif(substr($varTime,6,2) == "AM" && substr($varTime,0,2) == "12")
			return ("00".substr($varTime,2,3));
		else
			return substr($varTime,0,5);
	}
	
	/**
	 * function to return UNIX timestamp from datetime
	 *
	 * @param datetime $time format must be yyyy-mm-dd hh:mm AMPM format
	 * @return unix timestamp
	 */
	public function getTimeStamp($time) {
		return strtotime($time);
	}
	
	/**
	 * *
	 *
	 * @param string $varDate, date must be in yyyy-MM-dd format
	 * @return string, next date of given date yyyy-MM-dd
	 */
	public function fnNextDate($varDate)
	{
		$ts = strtotime($varDate);
		$varTimeStamp = mktime(0, 0, 0, date("m",$ts)  , date("d",$ts)+1, date("Y",$ts));
		$varNewDate = Date("Y/m/d",$varTimeStamp);
		return $varNewDate;
	}
	
	/**
	 * *
	 *
	 * @param string $varDate, date must be in yyyy-MM-dd format
	 * @return string, previous date of given date in yyyy-MM-dd
	 */
	public function fnPrevDate($varDate)
	{
		$varTimeStamp = strtotime($varDate)-86400;
		$varNewDate = Date("Y/m/d",$varTimeStamp);
		
		return $varNewDate;
	}
	
	/**
	 * Enter description here...
	 *
	 * @param string $varDate, date must be yyyy-MM-dd format
	 * @param int $days, no. of days to be added
	 * @return string, returns the date after adding days
	 */
	public function fnAddDays($varDate,$days)
	{
		$varTimeStamp = strtotime($varDate)+(86400*$days);
		$varNewDate = Date("Y/m/d",$varTimeStamp);
		
		return $varNewDate;
	}
	
	/**
	 * Enter description here...
	 *
	 * @param string $varDate, date must be yyyy-MM-dd format
	 * @param int $days, no. of days to be deducted
	 * @return string, returns the date after deducting days
	 */
	public function fnLessDays($varDate,$days)
	{
		$varTimeStamp = strtotime($varDate)-(86400*$days);
		$varNewDate = Date("Y/m/d",$varTimeStamp);
		
		return $varNewDate;
	}
	
	/**
	 * *
	 *
	 * @param string $varSDt, 1st date, must be in yyyy-MM-dd format
	 * @param unknown_type $varEDt, 2nd date, must be in yyyy-MM-dd format
	 * @return int, returns 0 if equal, 1 if 1st date is bigger than 2nd one and -1 for opposit
	 */
	public function fnCompDate($varSDt,$varEDt)
	{
		$varTimeStamp1 = strtotime($varSDt);
		$varTimeStamp2 = strtotime($varEDt);
		
		if($varTimeStamp1 < $varTimeStamp2)
			return "-1";
		else if($varTimeStamp1 > $varTimeStamp2)
			return "1";
		else
			return "0";
	}
	
	/**
	 * *
	 *
	 * @param string $varSDt, 1st date to be compared, must be yyyy-MM-dd format
	 * @param string $varEDt, 2nd date to be compared, must be yyyy-MM-dd format
	 * @param int $varOp, flag to identify which date to return. positive for greater one and nagetive for opposit
	 * @return unknown
	 */
	public function fnMaxMinDate($varSDt,$varEDt,$varOp)
	{
		$varTimeStamp1 = strtotime($varSDt);
		$varTimeStamp2 = strtotime($varEDt);
		
		if($varOp > '0')
		{
			if($varTimeStamp1 > $varTimeStamp2)
				return $varSDt;
			else
				return $varEDt;
		}
		else
		{
			if($varTimeStamp1 > $varTimeStamp2)
				return $varEDt;
			else
				return $varSDt;
		}
	}
	
	/**
	 * *
	 *
	 * @param string $varSDt, 1st date, must be in yyyy-MM-dd format
	 * @param string $varEDt, 2nd date, must be in yyyy-MM-dd format
	 * @return int, no. of dayes between 1st date to 2nd date
	 */
	public function fnNoOfDays($varSDt,$varEDt)
	{
		$varTimeStamp1 = strtotime($varSDt);
		$varTimeStamp2 = strtotime($varEDt);
		$varDiff = ($varTimeStamp2 - $varTimeStamp1);
		$varAbsDiff = ($varDiff < 0)? $varDiff * (-1): $varDiff;
		return (($varAbsDiff) / 86400 + 1);
	}
	
	/**
	 * *Function for adding trailing zero for currency type
	 *
	 * @param string $amount, takes the amount figure
	 * @return string, amount with trailing zeros.
	 */
	public function fnFormatCurrency($amount){
		// If the string is blank then rturn immediately
		if ($amount == "") {
			return $amount;
		}
		
		// Else format the string and return
		if (strlen(strrchr($amount,".")) > 2) {
			return $amount;
		}elseif (strlen(strrchr($amount,".")) == 2) {
			return $amount."0";
		}
		else {
			return $amount.".00";
		}
	}
	
	/**
	 * *
	 *
	 * @param array to be sorted $array
	 * @param index (column) on which to sort $column
	 * @param SORT_ASC for ascending or SORT_DESC for descending $order
	 * @param start index (row) for partial array sort $first
	 * @param stop  index (row) for partial array sort $last
	 * @return sorted $array
	 */
	
	public function array_qsort (&$array, $column, $order='SORT_ASC', $first=0, $last= -2)
	{
	  // $array  - the array to be sorted
	  // $column - index (column) on which to sort
	  //          can be a string if using an associative array
	  // $order  - SORT_ASC (default) for ascending or SORT_DESC for descending
	  // $first  - start index (row) for partial array sort
	  // $last  - stop  index (row) for partial array sort
	  // $keys  - array of key values for hash array sort
	  if (is_array($array)) {
	  	$keys = array_keys($array);
	 
	  if($last == -2) $last = count($array) - 1;
	  
	  if($last > $first) {
		$alpha = $first;
	   	$omega = $last;
	   	$key_alpha = $keys[$alpha];
	   	$key_omega = $keys[$omega];
	   	$guess = $array[$key_alpha][$column];
	   	while($omega >= $alpha) {
	     	if($order == 'SORT_ASC') {
	       		while($array[$key_alpha][$column] < $guess) {
	       			$alpha++; $key_alpha = $keys[$alpha]; 
	       		}
	       		while($array[$key_omega][$column] > $guess) {
	       			$omega--; $key_omega = $keys[$omega]; 
	       		}
	     	} else {
	       		while($array[$key_alpha][$column] > $guess) {
	       			$alpha++; $key_alpha = $keys[$alpha]; 
	       		}
	       		while($array[$key_omega][$column] < $guess) {
	       			$omega--; $key_omega = $keys[$omega]; 
	       		}
	     	}
	    	if($alpha > $omega) break;
	    	
	     	$temporary = $array[$key_alpha];
	     	$array[$key_alpha] = $array[$key_omega]; $alpha++;
	     	$key_alpha = $keys[$alpha];
	     	$array[$key_omega] = $temporary; $omega--;
	     	$key_omega = $keys[$omega];
	   	}
	   	array_qsort ($array, $column, $order, $first, $omega);
	   	array_qsort ($array, $column, $order, $alpha, $last);
	  	}
	 }
	  return $array;
	}
}
?>