/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

function contains(src,find)
{
    if (src.indexOf(find) == -1 ) return false;
    return  true;
}


function array_contains(src_array,find_str)
{
    
    var result = false
    $.each(src_array, function(index, value)
    {
        if (contains(value,find_str)) result = true;     
    });
    
    return result;
    
}


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


/**
 *  From the list of elements via findSelector
 *  
 *  @param rootSelector    find starts here
 *  @param findSelector    find this selector
 *  @param findClass,      considerit selected if it has this class
 *  @param removeString    remove This string from the ID
 *  @param replaceString   replace removed string with this one (default = "")
 *  
 *  @return array          IDs of selected elements
 *  
 */
function selected(rootSelector,findSelector, findClass,removeString, replaceString)
{
    
    
    if (rootSelector == null) return null;
    if (findSelector == null) return null;

    rootSelector = jQuery.trim(rootSelector);
    findSelector = jQuery.trim(findSelector);
 
    if (rootSelector == "") return null;
    if (findSelector == "") return null;
    
    
    if (replaceString == null)  replaceString = '';
    
    var resultID = "";
    
    var ids = new Array();
    var count = 0;
    jQuery.each(
        $(rootSelector).find(findSelector),
        function() 
        {
            
            
            if (findClass == null)
            {
                resultID = this.id.toString();
                if (removeString != null )
                    resultID = resultID.replace(removeString,replaceString);

                ids[count] = resultID;
                count++;

            }
            else
            {
                if ($(this).hasClass(findClass))
                {
                    resultID = this.id.toString();
                    if (removeString != null )
                        resultID = resultID.replace(removeString,replaceString);

                    ids[count] = resultID;
                    count++;
                }
                
            }
            
            
            
        }
    );
    

    return ids;
}


function selectedAsString(rootSelector,findSelector, findClass,delim,removeString, replaceString)
{
    if (delim == null) return null;

    var result = selected(rootSelector,findSelector, findClass,removeString, replaceString);
    
    if (result == null) return null;
    
    return result.join(delim);
    
}


function object_replace(src,from,to)
{
    
    var result = new Object();
    
    $.each(src, function(index, value) 
    { 
        result[index] = value.replace(from,to);
    });
    
    return result;
    
}

function array_replace(src,from,to)
{
    
    var result = new Array();
    
    $.each(src, function(index, value) 
    { 
        result[index] = value.replace(from,to);
    });
    
    return result;
    
}

function datetime_now()
{
    var currentTime = new Date()
    var hours = currentTime.getHours()
    var minutes = currentTime.getMinutes()
    if (minutes < 10){minutes = "0" + minutes}
    

    var month = currentTime.getMonth() + 1
    if (month < 10){month = "0" + month}
    
    var day = currentTime.getDate()
    if (day < 10){day = "0" + day}
    
    var year = currentTime.getFullYear()
    
    return month + "/" + day + "/" + year + " " + hours + ":" + minutes;

}


function Value(obj, propertyName, null_value)
{
    
    if (typeof null_value == "undefined") null_value = null;
    
    if (typeof obj == "undefined") return null_value;    
    
    if (typeof obj[propertyName] == "undefined") return null_value;     

    return obj[propertyName];
    
}

function Value(property, null_value)
{
    
    if (typeof property == "undefined") return null_value;    
    return property;

}

function Value(property)
{
    if (typeof property == "undefined") return null;    
    return property;

}


function isNull(src)
{    
    if (typeof src == "undefined") return true;    
    if (src == null ) return true;    
    return false;
}

function isNotNull(src)
{    
    return !isNull(src);
}



