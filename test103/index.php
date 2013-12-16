<html>
  <head>
    <title>HTML5 Create HTML5 Canvas JavaScript Drawing App Example</title>
    <script src="./js/canvasTools.js"></script>
    <script type="text/javascript">    
    
    var loadJSON = function(callback) {
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
	        		
	        		// convert attributes string to json and update json parent node
    		
		   			for(i in json){
			      		if(typeof json[i].attributes != 'undefined'){
			      			// create a new immediate function object using the attributes string
			      			json[i].attributes = new Function ('return '+json[i].attributes)();
	           			}
			        }
	        		
	            	callback(json);
	        }
	    }
	
	    xmlhttp.open("GET", "json.php", true);
	    xmlhttp.send();
	}
    
    window.onload = function(){
    	
    	var ajaxCallback = function(json){
	            	
	        // create new canvas
	    	var myCanvas = new CanvasTool();
	    	var canvas = myCanvas.createCanvas({
	    		id:'canvas1',
	    		width:800,
	    		height:800
	    	});
	    	
	    	var acceptedTypes = ['Square', 'Triangle', 'Image'];
	    	/*
	    	*/
	    	// now iterate trhough json and create shapes according to type
	    	for(i in json){
	    		// cache the iteration
	    		var item = json[i];
	    		// if json object type matches acceptedTypes proceed
	    		var itemType = window.capitalise(item.name);
	    			item.attributes.type = itemType;
	    		if(window.inArray(itemType, acceptedTypes)){
	    			myCanvas["draw"+itemType](canvas.ctx, item.attributes);
	    		}
	    	}
	    	
	    	var canvas2 = myCanvas.createCanvas({
	    		id:'canvas2',
	    		width:800,
	    		height:800
	    	});
	    	myCanvas.drawSquare(canvas2.ctx, {
				fillStyle : '#FF0000',
				coord:{
					x:500, 
					y:300
				},
				width:50,
				height:50,
				animate : true,
				directionFactorX : 1,
				animationFunction :function(delta){
					var base = this;
					return function(){
						if(base.coord.x <= 100){
							base.directionFactorX = Math.abs(base.directionFactorX) * 1;	
						}else if(base.coord.x >= 500){
							base.directionFactorX = Math.abs(base.directionFactorX) * -1
						}		
						//console.log(base.coord.x);
						//console.log(base.directionFactorX);
						
						dx = Math.abs(delta) * base.directionFactorX;
						
						base.coord.x+=dx;	
					}();
				}
	    	});
	    	myCanvas.drawTriangle(canvas2.ctx, {
				fillStyle : '#FF0000',
				coord:{
					x:100, 
					y:100
				},
				width:50,
				height:50,
				animate : true,
				directionFactorY : 1,
				animationFunction :function(delta){
					var base = this;
					return function(){
						//console.log(base.coord.y);
						if(base.coord.y < 100){
							base.directionFactorY = Math.abs(base.directionFactorY) * 1;	
						}else if(base.coord.y > 500){
							base.directionFactorY = Math.abs(base.directionFactorY) * -1
						}		
						// console.log(base.directionFactorY);
						
						dy = Math.abs(delta) * base.directionFactorY;
						
						console.log(base.coord.y);
						console.log(dy);
						
						base.coord.y+=dy;
						console.log(base.coord.y);
					}();
				}
	    	});
	    	/*
	    	myCanvas.drawTriangle(canvas.ctx, {
				fillStyle : '#FF0000',
				coord:{
					x:200, 
					y:200
				},
				angle :45,
				width:100,
				height:100,
				animate : true
	    	});
	    	
	    	myCanvas.drawTriangle(canvas.ctx, {
				fillStyle : '#FFFF00',
				coord:{
					x:410, 
					y:90
				},
				angle :-45,
				width:50,
				height:60,
				animate : true
	    	});
	    	
	    	myCanvas.drawImage(canvas.ctx, {
				strokeStyle : '#FF0000',
				coord:{
					x:10, 
					y:20
				},
				width:200,
				height:200,
				src : './img/testimage.jpg',
				animate : true
	    	});
	    	*/
	    	
	    	myCanvas.loadGame(canvas2.ctx);
    		
    	}
    	
    	// load json and pass in callback function
    	loadJSON(ajaxCallback);
    }
    
    </script>
  </head>
  <body>
  </body>
</html>