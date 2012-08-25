<?php

/** 
 * @author OHM
 * 
 */
class String
{
    public static function strlen($string, $getWidth = false, $charset = CHARSET)
    {
        switch(strtoupper(str_replace('_', '', trim($charset))))
        {
            case 'UTF8':return self::strlen_utf8($string,$getWidth);
            case 'BIG5':return self::strlen_big5($string,$getWidth);
            case 'GBK':case 'GB2312':case 'GB18030':return self::strlen_gb($string,$getWidth); 
            case 'UNICODE':case 'HZ':case'CJK':return strlen($string)/2*$getWidth?1:2;
            case 'ASCII':default:return strlen($string);
        }
    }
    private static function strlen_utf8($string, $getWidth)
    {
        if(empty($string))return 0;
        $length = 0;
        $intArray = str_split($string);
        $intArrayLength = count($intArray);
        for($i=0;$i<$intArrayLength;)
        {
            $length++;
            $char = ord($intArray[$i]);
            if($char<0x80)
            {
                $i+=1;
            }
            else
            {
                $_char = ~$char;
                $charLength = 2;
                while(!(($_char<<$charLength)&0x80))$charLength++;
                $i+=$charLength;
                if($getWidth)$length++;
            }
        }
        return $length;
    }
    private static function strlen_big5($string, $getWidth)
    {
        if(empty($string))return 0;
        $length = 0;
        $intArray = str_split($string);
        $intArrayLength = count($intArray);
        for($i=0;$i<$intArrayLength;)
        {
        	$length++;
        	if(ord($intArray[$i])<0x80)
        	{
        	    $i+=1;
        	}
        	else
        	{
        	    $i+=2;
        	    if($getWidth)$length++;
        	}
        }
        return $length;
    }
    private static function strlen_gb($string, $getWidth)
    {
        if(empty($string))return 0;
        $length = 0;
        $intArray = str_split($string);
        $intArrayLength = count($intArray);
        for($i=0;$i<$intArrayLength;)
        {
            $length++;
            $char = ord($intArray[$i]);
            if($char<0x80)
            {
                $i+=1;
            }
            else
            {
                $i+=((($char==0x81 || $char==0x82)&&ord($intArray[$i+1])<0x40)?4:2);
                if($getWidth)$length++;
            }
        }
        return $length;
    }

    public static function substr($string, $start, $length = null, $charset = CHARSET)
    {
        switch(strtoupper(str_replace('_', '', trim($charset))))
        {
        	case 'UTF8':return self::substr_utf8($string, $start, $length);
        	case 'BIG5':return self::substr_big5($string, $start, $length);
        	case 'GBK':case 'GB2312':case 'GB18030':return self::substr_gb($string, $start, $length);
        	case 'UNICODE':case 'HZ':case'CJK':return substr($string, $start*2,$length==null?null:$length*2);
        	case 'ASCII':default:return substr($string, $start,$length);
        }
    }
    private static function substr_utf8($string, $start, $length)
    {
        $strLength = self::strlen_utf8($string, false);
        if($strLength<=$start)return "";
        $end = ($length==null || $length>$strLength-$start)?$strLength:$start+$length;
        $length = 0;
        $intArray = str_split($string);
        $intArrayLength = count($intArray);
        
        $startIndex=-1;
        $i=0;
        for(;$i<$intArrayLength&&$length<$end;)
        {
            $length++;
            $char = ord($intArray[$i]);
            $charLength = 1;
            if($char<0x80)
            {
                $i+=1;
            }
            else
            {
                $_char = ~$char;
                $charLength = 2;
                while(!(($_char<<$charLength)&0x80))$charLength++;
                $i+=$charLength;
            }
            if($startIndex<0 && $start == $length)$startIndex = $i;
        }
        return substr($string, $startIndex,$i-$startIndex);
    }
    private static function substr_big5($string, $start, $length)
    {
        $strLength = self::strlen_utf8($string, false);
        if($strLength<=$start)return "";
        $end = ($length==null || $length>$strLength-$start)?$strLength:$start+$length;
        
        $length = 0;
        $intArray = str_split($string);
        $intArrayLength = count($intArray);
        
        $startIndex=-1;
        $i=0;
        for(;$i<$intArrayLength&&$length<$end;)
        {
        	$length++;
        	$charLength = 1;
        	if(ord($intArray[$i])<0x80)
        	{
        	    $i+=1;
        	}
        	else
        	{
        	    $i+=2;
        	    $charLength = 2;
        	}
            if($startIndex<0 && $start == $length)$startIndex = $i;
        }
        return substr($string, $startIndex,$i-$startIndex);
    }
    private static function substr_gb($string, $start, $length)
    {
        $strLength = self::strlen_utf8($string, false);
        if($strLength<=$start)return "";
        $end = ($length==null || $length>$strLength-$start)?$strLength:$start+$length;
        
        $length = 0;
        $intArray = str_split($string);
        $intArrayLength = count($intArray);
        
        $startIndex=-1;
        $i=0;
        for(;$i<$intArrayLength&&$length<$end;)
        {
            $length++;
            $char = ord($intArray[$i]);
            $charLength = 1;
            if($char<0x80)
            {
                $i+=1;
            }
            else
            {
                $charLength = ((($char==0x81 || $char==0x82)&&ord($intArray[$i+1])<0x40)?4:2);
                $i+=$charLength;
            }
            if($startIndex<0 && $start == $length)$startIndex = $i;
        }
        return substr($string, $startIndex,$i-$startIndex);
    }

    
}