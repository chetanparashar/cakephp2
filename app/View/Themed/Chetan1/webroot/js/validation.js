var alphanum = /[a-zA-Z0-9]+$/;//alphanum
var alpha = /^[a-zA-Z]+$/;//Alpha
var digit = /[^0-9]/g;//Digit
//var float = /^\d+\.?\d*$/;//Float
var float = /^[0-9]*(\.[0-9]+)$/;//Float
var text = /^[A-Za-z0-9_\-!@#$%^&*<>?=+\{\}[\]`\~\,\.\:\;\"\'\|\(\)\[\]]+$/;//Text
var email = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;//Email
var mobileno = /^\d{10}$/;//Mobile No
//var dateFormat1 = /^\d{2}-\d{2}-\d{4}$/;
//var dateFormat = /^\d{4}-\d{2}-\d{2}$/;//Date Format
//var dateTimeFormat = /^\d{4}-\d{2}-\d{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$/;//Date Time Format
//var dateTimeFormat1 = /^\d{2}-\d{2}-\d{4} [0-9]{2}:[0-9]{2}:[0-9]{2}$/;

function checkLength(id, length)
{
    if ($("#" + id).val().length != length)
    {
        $("#msgDiv").html("Please Check The Length");
        $("#" + id).focus();
        return true;
    }
    else
	return false;
}   

function checkDigitValue(id) {
    if (digit.test($("#" + id).val())) {
        $("#" + id).focus();
        return false;
    }
    return true;
}

function checkAlphaValue(id)
{
    if (!alpha.test($("#" + id).val())) {
        $("#" + id).focus();
        return false;
    }
    return true;
}
function checkAlphaNumericValue(id) {
    if (!alphanum.test($("#" + id).val())) {
        $("#" + id).focus();
        return false;
    }
    return true;
}

function checkFloatValue(id)
{
    if (!float.test($("#" + id).val())) {
        $("#" + id).focus();
        return false;
    }
    return true;
}

function checkTextValue(id)
{
    if (!text.test($("#" + id).val())) {
        $("#" + id).focus();
        return false;
    }
    return true;
}

function checkMobileNoValue(id)
{
    if (!mobileno.test($("#" + id).val())) {
         $("#" + id).focus();
        return false;
    }
    return true;
}

function checkEmailValue(id)
{
    if (!email.test($("#" + id).val())) {
         $("#" + id).focus();
        return false;
    }
    return true;
}

function ValidatePram(InputArr) { 
    var haserror = false;
    $.each(InputArr, function(id, type) {
        if ($.trim($("#" + id).val()) == null || $.trim($("#" + id).val()) == "") {
            $('#msgDiv').html("Parameter Can't be NULL");
            $("#" + id).focus();
            haserror = true;
            return false;
        }
        else if (!RegValidate(type, id)) {
            $('#msgDiv').html("Parameter Not In Proper Format");
            $("#" + id).focus();
            haserror = true;
            return false;
        }
    });
    if (haserror) {
        return false;
    } else {
        return true;
    }
}


function RegValidate(type, id) {
    switch (type) {
        case 'D':
            return isDate(id); //date
        case 'DT':
            return IsValidDate(id); //date time
        case 'A':
            return checkAlphaValue(id); //alpha
        case 'AN':
            return checkAlphaNumericValue(id); //alpha numeric
        case 'N':
            return checkDigitValue(id); //numeric
        case 'F':
            return checkFloatValue(id); //Float
        case 'ANS':
            return checkTextValue(id); //text
        case 'PAN':
            return validatePANNO(id); //PAN NO.
        case 'MN':
            return checkMobileNoValue(id);//Mobile No.
        case 'EMAIL':
            return checkEmailValue(id);//Email
        default:
            return false;
    }
}

function isDate(id)
	{
	  var currVal = $('#'+id).val();
	  if(currVal == '')
	    return false;
	  //Declare Regex 
	  var rxDatePattern = /^(\d{1,2})(\/|-)(\d{1,2})(\/|-)(\d{4})$/;
	  var dtArray = currVal.match(rxDatePattern); // is format OK?
	  if (dtArray == null)
	     return false;
	  
	  //Checks for mm/dd/yyyy format.
	  dtDay = dtArray[1];
	    dtMonth= dtArray[3];
	    dtYear = dtArray[5];  
	  if (dtMonth < 1 || dtMonth > 12)
	      return false;
	  else if (dtDay < 1 || dtDay> 31)
	      return false;
	  else if ((dtMonth==4 || dtMonth==6 || dtMonth==9 || dtMonth==11) && dtDay ==31)
	      return false;
	  else if (dtMonth == 2)
	  {
	     var isleap = (dtYear % 4 == 0 && (dtYear % 100 != 0 || dtYear % 400 == 0));
	     if (dtDay> 29 || (dtDay ==29 && !isleap))
	          return false;
	  }
	  return true;
	}

function EnterNumericKeyOnly(e){
      if (/[^0-9]/g.test(e.value)) e.value = e.value.replace(/[^0-9 ]/g,'');
}

function EnterAlphaNumericOnly(e){
     if (/[^A-Za-z0-9]/g.test(e.value)) e.value = e.value.replace(/[^A-Za-z0-9 ]/g,'');
}

function EnterAlphaOnly(e){
    if (/[^A-Za-z]/g.test(e.value)) e.value = e.value.replace(/[^A-Za-z ]/g,'');
}
//function EnterNumericKeyOnly(ids) {
//    $(ids).bind("keyup", function(event) {
////        $(ids).bind('keyup',function(event){
////            
////        });
//        //  alert(event.keyCode);
//        if (event.keyCode == 46 || event.keyCode == 8 || event.keyCode == 9 || event.keyCode == 27 || event.keyCode == 13 ||
//                // Allow: Ctrl+A, Ctrl+V, Ctrl+C
//                        (event.ctrlKey === true && (event.keyCode == 65 || event.keyCode == 86 || event.keyCode == 67)) || (event.shiftKey == false && (event.keyCode >= 48 && event.keyCode <= 57)) ||
//                        // (event.keyCode >=65 && event.keyCode <=91) ||
//                                (event.keyCode >= 96 && event.keyCode <= 105))
//                        // Allow: home, end, left, right
//                                //  (event.keyCode >= 35 && event.keyCode <= 39))
//                                {
//                                    // let it happen, don't do anything
//                                    return;
//                                }
//                        else {
//                            // Ensure that it is a number and stop the keypress
//                            // if (event.shiftKey || (event.keyCode < 48 || event.keyCode > 57) && (event.keyCode < 96 || event.keyCode > 105 )) {
//                            event.preventDefault();
//                            //    }
//                        }
//                    });
//}
//function EnterAlphaNumericOnly(ids)
//{
//    $(ids).bind("keydown", function(event) {
//        //  alert(event.keyCode);
//        if (event.keyCode == 46 || event.keyCode == 8 || event.keyCode == 9 || event.keyCode == 27 || event.keyCode == 13 || (event.ctrlKey === true && (event.keyCode == 65 || event.keyCode == 86 || event.keyCode == 67)) || (event.shiftKey == false && (event.keyCode >= 48 && event.keyCode <= 57)) || (event.keyCode >= 65 && event.keyCode <= 91) || (event.keyCode >= 96 && event.keyCode <= 105) || (event.keyCode >= 35 && event.keyCode <= 39)) {
//            return;
//        }
//        else {
//            event.preventDefault();
//        }
//    });
//}
//
//function EnterAlphaOnly(ids)
//{
//    $(ids).bind("keydown", function(event) {
//        //alert(event.keyCode);
//        if (event.keyCode == 46 || event.keyCode == 8 || event.keyCode == 9 || event.keyCode == 27 || event.keyCode == 13 || (event.ctrlKey === true && (event.keyCode == 65 || event.keyCode == 86 || event.keyCode == 67)) || (event.keyCode >= 65 && event.keyCode <= 91) || (event.keyCode >= 35 && event.keyCode <= 39)) {
//            return;
//        }
//        else {
//            event.preventDefault();
//        }
//    });
//}


function IsValidDate(input) {
    input = $("#" + input).val();
    var validformat = /^\d{2}-\d{2}-\d{4}$/;//Basic check for format validity
    var validformat1 = /^\d{4}-\d{2}-\d{2}$/;
    var returnval = false;
    if (!validformat.test(input) && !validformat1.test(input))
    {
        alert("Invalid Date Format. Please correct and submit again.");
    }
    else { //Detailed check for valid date ranges
        var arr1 = input.split("-");
        if (validformat.test(input))
        {
            var monthfield = arr1[1];
            var dayfield = arr1[0];
            var yearfield = arr1[2];
            var dayobj = new Date(yearfield, monthfield - 1, dayfield);
        }

        if (validformat1.test(input))
        {
            var monthfield = arr1[1];
            var dayfield = arr1[2];
            var yearfield = arr1[0];
            var dayobj = new Date(yearfield, monthfield - 1, dayfield);
        }
        if (monthfield == 2) {
            var leapYear = false;
            if ((!(yearfield % 4) && yearfield % 100) || !(yearfield % 400)) {
                leapYear = true;
            }

            if ((leapYear == false) && (dayfield >= 29)) {
                alert('Invalid date format!');
                return false;
            }

            if ((leapYear == true) && (dayfield > 29)) {
                alert('Invalid date format!');
                return false;
            }

        }
        if ((dayobj.getMonth() + 1 != monthfield) || (dayobj.getDate() != dayfield) || (dayobj.getFullYear() != yearfield))
        {
            alert("Invalid Day, Month, or Year range detected. Please correct and submit again.");
        }
        else
            returnval = true
    }
    if (returnval == false)
        return returnval
}

function fetchAscii(obj)
{
    obj = $("#" + obj).val();

    var convertedObj = '';

    for (i = 0; i < obj.length; i++)
    {

        var asciiChar = obj.charCodeAt(i);

        convertedObj += '&#' + asciiChar + ';';

    }
    return convertedObj;

}

function validatePANNO(PANNO) {
    var val = '';
    var str = "ABCFGHJLPT";
    PANNO = $.trim($("#" + PANNO).val()).toUpperCase();
    if (PANNO.length != 10)
        return false;
    for (var key = 0; key < PANNO.length; key++) {
        var val = PANNO.charCodeAt(key);
        if ((key == 9 || key < 3 || key == 4) && (val < 65 || val > 90)) {
            return false;
        }
        else if (key > 4 && key <= 8 && (val < 48 || val > 57)) {
            return false;
        }
        else if (key == 3 && (str.indexOf(PANNO[key]) == -1)) {
            return false;
        }
    }
    return true;
}
function ajaxRequest(URL, ParamString) {
    var ServResp = "";
    $.ajax({
        url: URL,
        type: 'POST',
        data: ParamString,
        async: false,
        dataType: "text",
        contentType: "application/x-www-form-urlencoded",
        accepts: {text: "application/xml"},
        success: function(data) {
            ServResp = data;
        },
        error: function(data) {
            ServResp = "";
        }
    });
    return ServResp;
}

function downloadFile(URL, ParamString) {
    $("#loaderDiv").show();
    formElement = document.createElement("form");
    formElement.method = "POST";
    formElement.action = URL;
    
    var ParamArr = ParamString.split("&");
    for (var i = 0; i < ParamArr.length; i = i + 1) {
        var data = ParamArr[i].split("=");
        $(formElement).append('<input type="hidden" name="' + data[0] + '" value="' + data[1] + '" />');
    }

    bodyElement = document.getElementsByTagName("body")[0];
    bodyElement.appendChild(formElement);
    $("#loaderDiv").hide();
    formElement.submit();
    bodyElement.removeChild(formElement);
}