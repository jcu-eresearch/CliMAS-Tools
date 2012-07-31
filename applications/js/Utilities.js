/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

function string2Array(str,delim)
{
    var result = null;
    if (str.indexOf(delim) == -1 )
    {
        result = new Array();
        result[0] = str;
    }
    else
        result = str.split(delim);
    
    return result;
}

function exists(selector)
{
    if ( $(selector).length ) return true;
    return false;
}


