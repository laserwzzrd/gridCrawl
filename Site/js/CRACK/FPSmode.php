<?php
	$debug = true;
?>

var users = false;
var localUser;
var linkList = [];
var siteSceneObjects = [];
var clickables = [];

// Dat.GUI - awesome but temp-------------------------
var DatUI = function() {
  this.user = 'user01';
  this.URL = 'http://www.gridcrack.com';
  this.playerColor = "#99FF99";
  this.walkSpeed = 1.8;
  this.positionX = 0;
  this.positionY = 0;
  this.positionZ = 0;
  this.userAgent = navigator.userAgent;
  this.message = "";
}; 

var datUI = new DatUI();
var gui = new dat.GUI({width: 400});
gui.add(datUI, 'user').listen();
gui.add(datUI, 'URL').listen();
gui.add(datUI, 'userAgent');
gui.add(datUI, 'message').listen();
gui.addColor(datUI, 'playerColor').listen();
gui.add(datUI, 'positionX', -10000, 10000).listen();
gui.add(datUI, 'positionY', -10000, 10000).listen();
gui.add(datUI, 'positionZ', -10000, 10000).listen();
gui.add(datUI, 'walkSpeed', 1, 5);		




function UserExists(p_user)
{
	for(var i = 0; i < users.length; i++)
	{
		if(users[i]['userid'] == userid)
		{
			return true;
		}
	}
	return false;
}

function CreateUser(p_user)
{
	var user = p_user;
	// create cube per user with text overlay
	geometry = new THREE.BoxGeometry( 20, 20, 20 );
	for ( var j = 0, l = geometry.faces.length; j < l; j ++ ) 
	{
		var face = geometry.faces[ j ];
		face.vertexColors[ 0 ] = new THREE.Color().setHSL( Math.random() * 0.3 + 0.5, 0.75, Math.random() * 0.25 + 0.75 );
		face.vertexColors[ 1 ] = new THREE.Color().setHSL( Math.random() * 0.3 + 0.5, 0.75, Math.random() * 0.25 + 0.75 );
		face.vertexColors[ 2 ] = new THREE.Color().setHSL( Math.random() * 0.3 + 0.5, 0.75, Math.random() * 0.25 + 0.75 );
	}
	
	material = new THREE.MeshPhongMaterial( { specular: 0xffffff, shading: THREE.FlatShading, vertexColors: THREE.VertexColors } );
	var mesh = new THREE.Mesh( geometry, material );		
	mesh.scale.x = 0.3;										
	mesh.scale.z = 0.3;		
	
	var material = new THREE.MeshPhongMaterial({color: 0xddffdd});
	var textGeom = new THREE.TextGeometry( "NAME: "+user['name'], {font: 'upheaval tt (brk)'});	
	var textMesh1 = new THREE.Mesh( textGeom, material );			
	textMesh1.scale.x = 0.04;									
	textMesh1.scale.y = 0.04;									
	textMesh1.scale.z = 0.04;

	var material = new THREE.MeshPhongMaterial({color: 0xddddff});
	var textGeom = new THREE.TextGeometry( "MESSAGE: "+user['message']+ "    URL: "+user['URL'], {font: 'upheaval tt (brk)'});	
	var textMesh2 = new THREE.Mesh( textGeom, material );			
	textMesh2.scale.x = 0.02;									
	textMesh2.scale.y = 0.02;									
	textMesh2.scale.z = 0.02;		

	var material = new THREE.MeshPhongMaterial({color: 0xdddddd});
	var textGeom = new THREE.TextGeometry( user['userAgentMD5'], {font: 'upheaval tt (brk)'});	
	var textMesh3 = new THREE.Mesh( textGeom, material );			
	textMesh3.scale.x = 0.01;									
	textMesh3.scale.y = 0.01;									
	textMesh3.scale.z = 0.01;							
	
	if(user['positionX'])
	{
		mesh.position.x = user['positionX'];
		mesh.position.y = user['positionY'];
		mesh.position.z = user['positionZ'];
		
		textMesh1.position.x = user['positionX'];
		textMesh1.position.y = 28;
		textMesh1.position.z = user['positionZ'];
		
		textMesh2.position.x = user['positionX'];
		textMesh2.position.y = 26;
		textMesh2.position.z = user['positionZ'];
		
		textMesh3.position.x = user['positionX'];
		textMesh3.position.y = 24;
		textMesh3.position.z = user['positionZ'];
	}
	else
	{
		mesh.position.x = Math.floor( Math.random() * 20 - 10 ) * 20;
		mesh.position.y = Math.floor( Math.random() * 20 ) * 20 + 10;
		mesh.position.z = Math.floor( Math.random() * 20 - 10 ) * 20;
	}
	
	sceneGL.add( mesh );
	material.color.setHSL( Math.random() * 0.2 + 0.5, 0.75, Math.random() * 0.25 + 0.75 );
	objects.push( mesh );
	
	console.log(users);
	user['model'] = mesh;
	
	sceneGL.add( textMesh1 );	
	sceneGL.add( textMesh2 );	
	sceneGL.add( textMesh3 );	
	user['textMesh1'] = textMesh1;
	user['textMesh2'] = textMesh2;
	user['textMesh3'] = textMesh3;		
	
	user['tweenX'] = user['positionX'];
	user['tweenY'] = user['positionY'];
	user['tweenZ'] = user['positionZ'];
	
	return user
}


ws.onmessage = function (evt) 
{ 
	var received_msg = evt.data;
	<?php if($debug == true) { ?> 
	//console.log("WSMSG: "+received_msg);
	<?php } ?>

	// login response
	if(received_msg[0] == 'N')
	{
		//console.log("stringified:" +JSON.stringify(received_msg.substr(1)));
		users = JSON.parse(received_msg.substr(1));
		console.log("USERS");
		console.log(users);
		
		for(var i = 0; i < users.length; i++)
		{
			if(users[i]['userid'] != userid)
			{
				CreateUser(users[i]);
			}
			else
			{
				localUser = users[i];
			}
		}
		
		console.log("LOCALUSER: "+JSON.stringify(localUser));
		
		// set initial data
		datUI.user = localUser['name'];
		datUI.URL = localUser['URL'];
		datUI.playerColor = localUser['playerColor'];
		datUI.message = localUser['message'];
		controls.getObject().position.x += Math.random() * 20;
		controls.getObject().position.z += Math.random() * 20;
	}
	
	// user position updates
	if(received_msg[0] == 'P')
	{
		if(users == false) { return; }
		usersResponse = JSON.parse(received_msg.substr(1));
		//console.log("usersResponse");
		//console.log(usersResponse);
		for(var i = 0; i < usersResponse.length; i++)
		{
			if(usersResponse[i]['userid'] != userid)
			{
				if(usersResponse[i]['positionX'])
				{
					users[i]['tweenX'] = usersResponse[i]['positionX'];
					users[i]['tweenY'] = usersResponse[i]['positionY'];
					users[i]['tweenZ'] = usersResponse[i]['positionZ'];
				}
			}
		}
	}
	
	// user status updates
	if(received_msg[0] == 'S')
	{
		if(users == false) { return; }
		usersResponse = JSON.parse(received_msg.substr(1));
		
		for(var i = 0; i < usersResponse.length; i++)
		{
			if(usersResponse[i]['userid'] != userid)
			{
				if(UserExists(users[i]) == false)
				{
					CreateUser(users[i]);
				}
				else
				{
					users[i]['message'] = usersResponse[i]['message'];
					users[i]['URL'] = usersResponse[i]['URL'];
					users[i]['name'] = usersResponse[i]['name'];
					users[i]['playerColor'] = usersResponse[i]['playerColor'];
					//users[i]['textMesh1'].
					
				}
			}
		}
	}
	
	// site cache found
	if(received_msg[0] == 'W')
	{
		for(var i = 0; i < siteSceneObjects.length; i++)
		{
			sceneGL.remove(siteSceneObjects[i]);
		}
		linkList = [];
		siteSceneObjects = [];
		clickables = [];
	
		var parser = document.createElement('a');
		parser.href = datUI.URL;
	
		// load site!
		var tex = THREE.ImageUtils.loadTexture("siteCache/"+parser.hostname+"/siteRender.jpg" );
		var siteMaterial = new THREE.MeshLambertMaterial({ color: 0xffffff, map : tex });		
		
		geometry = new THREE.PlaneGeometry( 100, 100, 100, 100 );
		//geometry.rotateX( Math.PI / 2 );
		mesh = new THREE.Mesh( geometry, siteMaterial );
		mesh.position.y = 50;
		sceneGL.add( mesh );	
		siteSceneObjects.push(mesh);

		// load link list
		$.ajax({
			url: "siteCache/"+parser.hostname+"/links.txt",
			dataType: 'text',
			type: 'GET',
			async: true,
			statusCode: {
				404: function (response) {
					console.log("NO LINKS FOUND IN SITECACHE FOR:");
				},
				200: function (response) {
					//console.log("LINKS FOUND IN SITECACHE: "+response);
					var linkURLs = response.split(/\r?\n/);
					linkURLs.push("http://www.ninjarobot.com");
					linkURLs.push("http://www.planetshipgame.com");
					linkURLs.push("http://www.laserwzzrd.com");
					linkURLs.push("http://www.forcesabertraining.com");
					linkURLs.push("http://www.gridcrack.com");
					
					console.log(linkURLs.length+" LINKS FOUND");
					
					for(var i = 0; i < linkURLs.length; i++)
					{
						var positionX = (Math.random()-0.5) * 600; 
						var positionY = ((Math.random()) * 20) + 10; 
						var positionZ = (Math.random()-0.5) * 600; 
						
						//console.log("LINK POS: X:"+positionX+" Z:"+positionZ);
						
						/*var geometry = new THREE.BoxGeometry( 20, 20, 20 );	
						material = new THREE.MeshPhongMaterial( { specular: 0x5555ff, shading: THREE.FlatShading } ); //, vertexColors: THREE.VertexColors } );
						var mesh = new THREE.Mesh( geometry, material );		
						mesh.scale.x = 0.3;										
						mesh.scale.z = 0.3;		
						mesh.position.x = positionX;
						mesh.position.y = 10;
						mesh.position.z = positionZ;
						sceneGL.add(mesh);
						siteSceneObjects.push(mesh);*/
						
						var material = new THREE.MeshPhongMaterial({color: getRandomBlueColor("aaaa", 6, 10)});//0xaaaaff});
						var textGeom = new THREE.TextGeometry( "LINK: "+linkURLs[i], {font: 'upheaval tt (brk)'});	
						THREE.GeometryUtils.center( textGeom );
						var textMesh1 = new THREE.Mesh( textGeom, material );			
						textMesh1.scale.x = 0.03;									
						textMesh1.scale.y = 0.06;									
						textMesh1.scale.z = 0.02;	
						textMesh1.position.x = positionX;
						textMesh1.position.y = positionY;
						textMesh1.position.z = positionZ;	
						textMesh1.URL = linkURLs[i];
						textMesh1.callback = function() 
						{ 
							console.log("CLICKED LINK: "+this.name);
							datUI.URL = this.URL;
							ws.send("W"+userid+" "+datUI.URL);
						}
						sceneGL.add(textMesh1);	
						siteSceneObjects.push(textMesh1);	
					}
				}
			},
			error: function (jqXHR, status, errorThrown) {
					console.log("NO LINKS FOUND IN SITECACHE");
					
					var linkURLs = [];
					linkURLs.push("http://www.ninjarobot.com");
					linkURLs.push("http://www.planetshipgame.com");
					linkURLs.push("http://www.laserwzzrd.com");
					linkURLs.push("http://www.forcesabertraining.com");
					
					for(var i = 0; i < linkURLs.length; i++)
					{
						var positionX = (Math.random()-0.5) * 600; 
						var positionY = ((Math.random()) * 20) + 10; 
						var positionZ = (Math.random()-0.5) * 600; 
						
						//console.log("LINK POS: X:"+positionX+" Z:"+positionZ);
						
						/*var geometry = new THREE.BoxGeometry( 20, 20, 20 );	
						material = new THREE.MeshPhongMaterial( { specular: 0x5555ff, shading: THREE.FlatShading } ); //, vertexColors: THREE.VertexColors } );
						var mesh = new THREE.Mesh( geometry, material );		
						mesh.scale.x = 0.3;										
						mesh.scale.z = 0.3;		
						mesh.position.x = positionX;
						mesh.position.y = 10;
						mesh.position.z = positionZ;
						sceneGL.add(mesh);
						siteSceneObjects.push(mesh);*/
						
						var material = new THREE.MeshPhongMaterial({color: 0xaaaaff});
						var textGeom = new THREE.TextGeometry( "LINK: "+linkURLs[i], {font: 'upheaval tt (brk)'});	
						THREE.GeometryUtils.center( textGeom );
						var textMesh1 = new THREE.Mesh( textGeom, material );			
						textMesh1.scale.x = 0.04;									
						textMesh1.scale.y = 0.08;									
						textMesh1.scale.z = 0.04;	
						textMesh1.position.x = positionX;
						textMesh1.position.y = positionY;
						textMesh1.position.z = positionZ;	
						textMesh1.URL = linkURLs[i];
						textMesh1.callback = function() 
						{ 
							console.log("CLICKED LINK: "+this.name);
							datUI.URL = this.URL;
							ws.send("W"+userid+" "+datUI.URL);
						}
						sceneGL.add(textMesh1);	
						siteSceneObjects.push(textMesh1);	
					}					
			}
		});		
		
		
		/*try
		{
			var xmlHttp = new XMLHttpRequest();
			xmlHttp.open( "GET", "siteCache/"+parser.hostname+"/links.txt", false );
			xmlHttp.send( null );
			

		} 
		catch(err) 
		{
			console.log("NO LINKS FOUND IN SITECACHE");
		}*/
	}
};


var raycaster = new THREE.Raycaster();
var mouse = new THREE.Vector2();
var camera, sceneGL, sceneCSS, rendererGL, rendererCSS;
var geometry, material, mesh, controls, raycaster2;
var objects = [];
var blocker = document.getElementById( 'blocker' );
var instructions = document.getElementById( 'instructions' );
var raycastCenterVec2 = new THREE.Vector2();


function onDocumentMouseDown( event ) 
{
    //event.preventDefault();
    mouse.x = ( event.clientX / rendererGL.domElement.clientWidth ) * 2 - 1;
    mouse.y = - ( event.clientY / rendererGL.domElement.clientHeight ) * 2 + 1;
    //raycaster.setFromCamera( mouse, camera );
	raycaster.setFromCamera(raycastCenterVec2, camera );  
    var intersects = raycaster.intersectObjects( siteSceneObjects ); //sceneGL.children, false ); 
	//console.log(sceneGL.children)
	console.log("CLICK: X="+mouse.x+" Y="+mouse.y+" INTERSECTS="+intersects.length+" SCENECHILDCOUNT="+sceneGL.children.length);
	
    if ( intersects.length > 0 ) 
	{
		for(var i = 0; i < intersects.length; i++)
		{
			console.log("CLICKED "+intersects[0].name);
		}
		if(typeof(intersects[0].object.callback) == "function")
		{
			intersects[0].object.callback();
		}
	}
}	


function onMouseMove( event ) 
{
    /*event.preventDefault();
    mouse.x = ( event.clientX / rendererGL.domElement.clientWidth ) * 2 - 1;
    mouse.y = - ( event.clientY / rendererGL.domElement.clientHeight ) * 2 + 1;
    raycaster.setFromCamera( mouse, camera );
    var intersects = raycaster.intersectObjects( sceneGL.children, false ); 
	//console.log(sceneGL.children)
	//console.log("CLICK: X="+mouse.x+" Y="+mouse.y+" INTERSECTS="+intersects.length+" SCENECHILDCOUNT="+sceneGL.children.length);
	
    if ( intersects.length > 0 ) 
	{
		for(var i = 0; i < intersects.length; i++)
		{
			console.log("MOUSEOVER "+intersects[0].name);
		}
        intersects[0].object.callback();
    }*/
}	


var havePointerLock = 'pointerLockElement' in document || 'mozPointerLockElement' in document || 'webkitPointerLockElement' in document;
if (havePointerLock ) 
{
	var element = document.body;

	var pointerlockchange = function ( event ) 
	{
		
	
		if ( document.pointerLockElement === element || document.mozPointerLockElement === element || document.webkitPointerLockElement === element ) {
			controlsEnabled = true;
			controls.enabled = true;
			blocker.style.display = 'none';
			ws.send("W"+userid+" "+datUI.URL);
		} else {
			controls.enabled = false;
			blocker.style.display = '-webkit-box';
			blocker.style.display = '-moz-box';
			blocker.style.display = 'box';
			instructions.style.display = '';
		}
	};

	var pointerlockerror = function ( event ) 
	{
		console.log(event);
		instructions.style.display = '';
	};

	// Hook pointer lock state change events
	document.addEventListener( 'pointerlockchange', pointerlockchange, false );
	document.addEventListener( 'mozpointerlockchange', pointerlockchange, false );
	document.addEventListener( 'webkitpointerlockchange', pointerlockchange, false );
	document.addEventListener( 'pointerlockerror', pointerlockerror, false );
	document.addEventListener( 'mozpointerlockerror', pointerlockerror, false );
	document.addEventListener( 'webkitpointerlockerror', pointerlockerror, false );

	instructions.addEventListener( 'click', function ( event ) {
		instructions.style.display = 'none';
		// Ask the browser to lock the pointer
		element.requestPointerLock = element.requestPointerLock || element.mozRequestPointerLock || element.webkitRequestPointerLock;
		if ( /Firefox/i.test( navigator.userAgent ) ) {
			var fullscreenchange = function ( event ) {
				if ( document.fullscreenElement === element || document.mozFullscreenElement === element || document.mozFullScreenElement === element ) {
					document.removeEventListener( 'fullscreenchange', fullscreenchange );
					document.removeEventListener( 'mozfullscreenchange', fullscreenchange );
					element.requestPointerLock();
				}
			};
			document.addEventListener( 'fullscreenchange', fullscreenchange, false );
			document.addEventListener( 'mozfullscreenchange', fullscreenchange, false );
			element.requestFullscreen = element.requestFullscreen || element.mozRequestFullscreen || element.mozRequestFullScreen || element.webkitRequestFullscreen;
			element.requestFullscreen();
		} else {
			element.requestPointerLock();
		}
	}, false );
} else {
	instructions.innerHTML = 'Your browser doesn\'t seem to support Pointer Lock API';
}

init();
animate();

var controlsEnabled = false;

var moveForward = false;
var moveBackward = false;
var moveLeft = false;
var moveRight = false;
var canJump = false;

var prevTime = performance.now();
var velocity = new THREE.Vector3();

function init() 
{



	camera = new THREE.PerspectiveCamera( 60, window.innerWidth / window.innerHeight, 1, 1000 );

	sceneGL = new THREE.Scene();
	var light = new THREE.HemisphereLight( 0xeeeeff, 0x777788, 0.75 );
	light.position.set( 0.5, 1, 0.75 );
	sceneGL.add( light );

	var directionalLight = new THREE.DirectionalLight( 0xddddff, 0.2 );
	directionalLight.position.set( 0, 1, 0 );
	directionalLight.rotateY( Math.PI / 2 );
	sceneGL.add( directionalLight );

	var directionalLight = new THREE.DirectionalLight( 0xffffdd, 0.2 );
	directionalLight.position.set( 0, 1, 0 );
	directionalLight.rotateZ( Math.PI / 3 );
	sceneGL.add( directionalLight );
	
	controls = new THREE.PointerLockControls( camera );
	sceneGL.add( controls.getObject() );

	var onKeyDown = function ( event ) 
	{
		switch ( event.keyCode ) 
		{
			case 38: // up
			case 87: // w
				moveForward = true;
				break;

			case 37: // left
			case 65: // a
				moveLeft = true; break;

			case 40: // down
			case 83: // s
				moveBackward = true;
				break;

			case 39: // right
			case 68: // d
				moveRight = true;
				break;

			case 32: // space
				if ( canJump === true ) velocity.y += 350;
				canJump = false;
				break;

			/*case 13: // enter */					
		}
	};

	var onKeyUp = function ( event ) 
	{
		switch( event.keyCode ) 
		{
			case 38: // up
			case 87: // w
				moveForward = false;
				break;

			case 37: // left
			case 65: // a
				moveLeft = false;
				break;

			case 40: // down
			case 83: // s
				moveBackward = false;
				break;

			case 39: // right
			case 68: // d
				moveRight = false;
				break;
		}
	};

	document.addEventListener( 'keydown', onKeyDown, false );
	document.addEventListener( 'keyup', onKeyUp, false );

	raycaster2 = new THREE.Raycaster( new THREE.Vector3(), new THREE.Vector3( 0, - 1, 0 ), 0, 10 );

	// floor
	var wallBoundsTex = THREE.ImageUtils.loadTexture( "objects/textures/wallBoundsOpt.jpg" );
	wallBoundsTex.wrapS = THREE.RepeatWrapping; 
	wallBoundsTex.wrapT = THREE.RepeatWrapping;
	wallBoundsTex.repeat.set(42, 42); 

	wallBoundsmaterial = new THREE.MeshLambertMaterial({ map : wallBoundsTex });
	wallBoundsmaterial.transparent = true;
	wallBoundsmaterial.blending = THREE["AdditiveBlending"];
	
	var tronTex = THREE.ImageUtils.loadTexture( "objects/textures/tronFloorOpt.jpg" );
	tronTex.wrapS = THREE.RepeatWrapping; 
	tronTex.wrapT = THREE.RepeatWrapping;
	tronTex.repeat.set(64, 64); 
	material = new THREE.MeshLambertMaterial({ map : tronTex });
	
	var ceilingTex = THREE.ImageUtils.loadTexture( "objects/textures/homeCeilingOpt.jpg" );
	ceilingTex.wrapS = THREE.RepeatWrapping; 
	ceilingTex.wrapT = THREE.RepeatWrapping;
	ceilingTex.repeat.set(16, 16); 
	ceilingMaterial = new THREE.MeshLambertMaterial({ map : ceilingTex });

	geometry = new THREE.PlaneGeometry( 2000, 2000, 100, 100 );
	geometry.rotateX( - Math.PI / 2 );

	mesh = new THREE.Mesh( geometry, material );
	sceneGL.add( mesh );
	
	geometry = new THREE.PlaneGeometry( 2000, 2000, 100, 100 );
	geometry.rotateX( Math.PI / 2 );
	mesh = new THREE.Mesh( geometry, ceilingMaterial );
	mesh.position.y = 200;
	sceneGL.add( mesh );

	var manager = new THREE.LoadingManager();
	manager.onProgress = function ( item, loaded, total ) {

		console.log( item, loaded, total );

	};
	
	var texture2 = new THREE.Texture();
	var loader = new THREE.ImageLoader( manager );
	loader.load( 'objects/textures/techWallsOpt.jpg', function ( image ) {
		texture2.image = image;
		texture2.needsUpdate = true;
		texture2.wrapS = THREE.RepeatWrapping; 
		texture2.wrapT = THREE.RepeatWrapping;
		texture2.repeat.set(16, 16); 
	} );
	
	var onProgress = function ( xhr ) {
		if ( xhr.lengthComputable ) {
			var percentComplete = xhr.loaded / xhr.total * 100;
			console.log( Math.round(percentComplete, 2) + '% downloaded' );
		}
	};

	var onError = function ( xhr ) {
		console.log("OBJ ERROR: "+xhr);
	};				
	
	// room obj load
	var basicMaterial = new THREE.MeshLambertMaterial({ map : texture2 });

	var loader = new THREE.OBJLoader( manager );
	loader.load( 'objects/homeWalls.obj', function ( object ) {
		object.traverse( function ( child ) {
			if ( child instanceof THREE.Mesh ) {
				child.material = basicMaterial;
				child.scale.x = 40;
				child.scale.y = 40;
				child.scale.z = 40;
				sceneGL.add( child );
			}
		} );
	}, onProgress, onError );
	
	loader.load( 'objects/homeWalls.obj', function ( object ) {
		object.traverse( function ( child ) {
			if ( child instanceof THREE.Mesh ) {
				child.material = material;
				child.scale.x = 40;
				child.scale.y = 40;
				child.scale.z = 40;
				child.rotateY( - Math.PI / 2 );
				sceneGL.add( child );
				//console.log("OBJ: "+objToString(child.material.map));
			}
		} );
	}, onProgress, onError );				
	

	loader.load( 'objects/homeBounds.obj', function ( object ) {
		object.traverse( function ( child ) {
			if ( child instanceof THREE.Mesh ) {
				child.material = wallBoundsmaterial;//.map = texture2;//.map = texture;
				
				//console.log(objToString(child.geometry));
				/*var colors = child.geometry.attributes.color.array;
				var newColor = new THREE.Color(0xff0000);
				for(var k = 0; k < colors.length; k++)
				{
					colors[k] = 0xff0000;// Math.abs(Math.sin(performance.now())) * 256; 
				}
				child.geometry.colorsNeedUpdate = true;*/
				//child.geometry = child.geometry.toGeometry();
				/*if(child.geometry.faces)
				{
					for(var k = 0; k < child.geometry.faces.length; k++)
					{
						for(var l = 0; l < child.geometry.faces[k].vertexColors.length; l++)
						{
							geometry.faces[k].vertexColors[l] = new THREE.Color( 0xff0000 );
						}
					}
				}
				else
				{
					console.log(JSON.stringify(child.geometry));
				}*/

				child.scale.x = 36;
				child.scale.y = 36;
				child.scale.z = 36;
				child.rotateY( - Math.PI / 2 );
				sceneGL.add( child );
			}
		});
	}, onProgress, onError );					
	

	sceneCSS = new THREE.Scene();
	sceneCSS.add( controls.getObject() );
	var urls = [
		//[ 'http://www.planetshipgame.com', 1, 1, -50, 0, 0, 0 ],
		//[ 'http://www.laserwzzrd.com', 60, 1, -50, 0, 0, 0 ],
		[ 'https://www.gridcrack.com/embed.html', 120, 1, -50, 0, 0, 0 ],
		//[ 'http://www.planetshipgame.com', 100, 100, 0, 0, 1.57, 0 ],
		//[ 'http://www.planetshipgame.com', 0, 100, -100, 0, 3.14, 0 ],
		//[ 'http://www.planetshipgame.com', - 100, 1000, 0, 0, 4.71, 0 ],
		//[ 'http://www.planetshipgame.com', 0, 100, 0, 4.71, 0, 0 ],
		//[ 'http://www.planetshipgame.com', 0, -100, 0, 1.57, 0, 0 ]
	];

	for ( var i = 0; i < urls.length; i ++ ) 
	{
		var element = document.createElement( 'iframe' );
		element.src = urls[ i ][ 0 ];
		element.style.width = '800px';
		element.style.height = '800px';
		element.style.border = '0px';

		var object = new THREE.CSS3DObject( element );
		object.position.x = urls[ i ][ 1 ];
		object.position.y = urls[ i ][ 2 ];
		object.position.z = urls[ i ][ 3 ];
		object.rotation.x = urls[ i ][ 4 ];
		object.rotation.y = urls[ i ][ 5 ];
		object.rotation.z = urls[ i ][ 6 ];
		object.scale.x = 0.06;
		object.scale.y = 0.06;
		sceneCSS.add( object );
	}
	
	rendererCSS = new THREE.CSS3DRenderer({alpha:true});
	rendererCSS.setClearColor(0x00ff00, 0.0);
	rendererCSS.setSize( window.innerWidth, window.innerHeight );
	rendererCSS.domElement.style.position = 'absolute';
	rendererCSS.domElement.style.top = 50;
	rendererCSS.domElement.style.zIndex = 10;
	
	rendererGL = new THREE.WebGLRenderer();
	rendererGL.setClearColor(0x00ff00, 0.0);
	rendererGL.domElement.style.zIndex = 5;
	rendererGL.setPixelRatio( window.devicePixelRatio );
	rendererGL.setSize( window.innerWidth, window.innerHeight );
	
	document.body.appendChild( rendererCSS.domElement );	
	document.body.appendChild( rendererGL.domElement );		

	
	setTimeout(function() { ws.send("N"+userid+userAgent); }, 500);
	/*if(ws)
	{
		console.log(userid);
		console.log(userAgent);
		console.log(ws);
		ws.send("N"+userid+userAgent);
	}
	else
	{
		console.log(userid);
		console.log(userAgent);
		setTimeout(function() { ws.send("N"+userid+userAgent); }, 1000);	
	}*/
	//renderer.domElement.addEventListener( 'mousemove', onDocumentMouseMove, false );
	window.addEventListener( "mousedown", onDocumentMouseDown );
	//rendererGL.domElement.addEventListener('click', onDocumentMouseDown, false);	
	window.addEventListener( 'resize', onWindowResize, false );
	//window.addEventListener( 'mousemove', onMouseMove );
	//window.addEventListener("gamepadconnected", gamepadconnected);		
	
/*window.addEventListener("gamepadconnected", function(e) {
  console.log("Gamepad connected at index %d: %s. %d buttons, %d axes.",
    e.gamepad.index, e.gamepad.id,
    e.gamepad.buttons.length, e.gamepad.axes.length);
});	*/
}

function onWindowResize() 
{
	camera.aspect = window.innerWidth / window.innerHeight;
	camera.updateProjectionMatrix();

	rendererGL.setSize( window.innerWidth, window.innerHeight );
	rendererCSS.setSize( window.innerWidth, window.innerHeight );
}	

function animate() 
{
	requestAnimationFrame( animate );

	var time = performance.now();
	var delta = ( time - prevTime ) / 1000;
	var lerpSpeed = 1.2;
	//console.log("LERP: "+(delta * lerpSpeed));
	
	if(gamepad)
	{
		//console.log("AXIS0:"+gamepad.axes[0].toFixed(4)+"AXIS1:"+gamepad.axes[1].toFixed(4)+"AXIS2:"+gamepad.axes[2].toFixed(4));
		if(Math.abs(gamepad.axes[0].toFixed(4)) > 0.2)
		{
			//console.log(gamepad.axes[2].toFixed(4))
			controls.getObject().translateX(gamepad.axes[0] * delta * 100 * datUI.walkSpeed);
		//controls.getObject().translateX( velocity.x * delta * datUI.walkSpeed);
		//controls.getObject().translateY( velocity.y * delta * datUI.walkSpeed );
		// velocity.z * delta * datUI.walkSpeed );			
		}
		
		if(Math.abs(gamepad.axes[1].toFixed(4)) > 0.2)
		{
			controls.getObject().translateZ(gamepad.axes[1] * delta * 100 * datUI.walkSpeed);
		}		
		
		if(Math.abs(gamepad.axes[2].toFixed(4)) > 0.2)
		{
			//console.log(gamepad.axes[2].toFixed(4))
			controls.getObject().rotation.y -= gamepad.axes[2] * delta * 3;
		}
		
		if(Math.abs(gamepad.axes[3].toFixed(4)) > 0.2)
		{
			controls.getPitchObject().rotation.x -= gamepad.axes[3].toFixed(4) * delta * 2;
		}
		
		if(buttonPressed(gamepad.buttons[3]))
		{
			//console.log("BUTTON 0 PRESSED: JUMP VAL:"+(1000 * delta));
			if ( canJump === true ) 
			{
				velocity.y += 1000 * delta;
			}
		}
		
		if(buttonPressed(gamepad.buttons[0]))
		{
			raycaster.setFromCamera(raycastCenterVec2, camera );  
			var intersects = raycaster.intersectObjects( siteSceneObjects ); //sceneGL.children, false ); 
			//console.log(sceneGL.children)
			//console.log("CLICK: X="+mouse.x+" Y="+mouse.y+" INTERSECTS="+intersects.length+" SCENECHILDCOUNT="+sceneGL.children.length);
			
			if ( intersects.length > 0 ) 
			{
				for(var i = 0; i < intersects.length; i++)
				{
					console.log("CLICKED "+intersects[0].name);
				}
				if(typeof(intersects[0].object.callback) == "function")
				{
					intersects[0].object.callback();
				}
			}
		}
	}
	else
	{
		//console.log("GAMEPAD NOT CONNECTED");
	}
	
	
	// update/lerp player position
	if(users)
	{
		for(var i = 0; i < users.length; i++)
		{
			if(users[i]['userid'] != userid)
			{
				users[i]['positionX'] = lerp(users[i]['positionX'], users[i]['tweenX'], delta * lerpSpeed);
				users[i]['positionY'] = lerp(users[i]['positionY'], users[i]['tweenY'], delta * lerpSpeed);
				users[i]['positionZ'] = lerp(users[i]['positionZ'], users[i]['tweenZ'], delta * lerpSpeed);
				
				if(users[i]['model'])
				{
					users[i]['model'].position.x = users[i]['positionX'];
					users[i]['model'].position.y = users[i]['positionY'];
					users[i]['model'].position.z = users[i]['positionZ'];
				
					users[i]['textMesh1'].position.x = users[i]['positionX'];
					users[i]['textMesh1'].position.y = 28;
					users[i]['textMesh1'].position.z = users[i]['positionZ'];
					
					users[i]['textMesh2'].position.x = users[i]['positionX'];
					users[i]['textMesh2'].position.y = 26;
					users[i]['textMesh2'].position.z = users[i]['positionZ'];
					
					users[i]['textMesh3'].position.x = users[i]['positionX'];
					users[i]['textMesh3'].position.y = 24;
					users[i]['textMesh3'].position.z = users[i]['positionZ'];
				}
			}
		}
	}

	// update debug ui
	if(datUI)
	{
		datUI.positionX = controls.getObject().position.x;
		datUI.positionY = controls.getObject().position.y;
		datUI.positionZ = controls.getObject().position.z;
		//console.log("POSBBB X:"+controls.getObject().position.x+" Y: "+controls.getObject().position.y+" Z: "+controls.getObject().position.z);
	}
	else
	{
		console.log("POS X:"+camera.position.x+" Y: "+camera.position.y+" Z: "+camera.position.z);
	}
	
	if ( controlsEnabled ) 
	{
		raycaster2.ray.origin.copy( controls.getObject().position );
		raycaster2.ray.origin.y -= 10;

		var intersections = raycaster2.intersectObjects( objects );
		var isOnObject = intersections.length > 0;

		velocity.x -= velocity.x * 10.0 * delta;
		velocity.z -= velocity.z * 10.0 * delta;
		velocity.y -= 9.8 * 100.0 * delta; // 100.0 = mass

		if ( moveForward ) velocity.z -= 400.0 * delta;
		if ( moveBackward ) velocity.z += 400.0 * delta;

		if ( moveLeft ) velocity.x -= 400.0 * delta;
		if ( moveRight ) velocity.x += 400.0 * delta;

		if ( isOnObject === true ) {
			velocity.y = Math.max( 0, velocity.y );
			canJump = true;
		}

		controls.getObject().translateX( velocity.x * delta * datUI.walkSpeed);
		controls.getObject().translateY( velocity.y * delta * datUI.walkSpeed );
		controls.getObject().translateZ( velocity.z * delta * datUI.walkSpeed );
		
		if(controls.getObject().position.x > 300 || controls.getObject().position.x < -300)
		{
			controls.getObject().position.x = 0;
		}
		if(controls.getObject().position.z > 300 || controls.getObject().position.z < -300)
		{
			controls.getObject().position.z = 0;
		}

		if ( controls.getObject().position.y < 10 ) {

			velocity.y = 0;
			controls.getObject().position.y = 10;
			canJump = true;
		}
	}

	for(var i = 0; i < siteSceneObjects.length; i++)
	{
		siteSceneObjects[i].lookAt(controls.getObject().position);
	}
	
	prevTime = time;
		
	rendererGL.render(sceneGL, camera);
	rendererCSS.render(sceneCSS, camera);
}

finishedInitializing = true;