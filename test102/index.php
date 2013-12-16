<html>
  <head>
    <title>HTML5 Create HTML5 Canvas JavaScript Drawing App Example</title>
    <script type="text/javascript">
    
    function loadJSON() {
	    var xmlhttp;
	
	    if (window.XMLHttpRequest) {
	        // code for IE7+, Firefox, Chrome, Opera, Safari
	        xmlhttp = new XMLHttpRequest();
	    } else {
	        // code for IE6, IE5
	        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
	    }
	
	    xmlhttp.onreadystatechange = function() {
	        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
	        	var json = JSON.parse(xmlhttp.responseText);
	        	if(typeof json[0].attributes != 'undefined'){
	            	json = JSON.stringify(eval("(" + json[0].attributes + ")"));
	            	console.log(JSON.parse(json));
	            }
	        }
	    }
	
	    xmlhttp.open("GET", "json.php", true);
	    xmlhttp.send();
	}
    
    window.onload = function(){
    	loadJSON();
    }
    
    </script>
  </head>
  <body>
  </body>
</html>