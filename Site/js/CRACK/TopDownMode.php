loadMap("http://www.gridcrack.com");

var playerPosition = new THREE.Vector3(0,0,0);
var lookOffset = new THREE.Vector3(0,0,0);
var cameraLook = new THREE.Vector3(0,0,0);
var cameraPosLerp = new THREE.Vector3(0,0,0);
var raycaster = new THREE.Raycaster();
var mouse = new THREE.Vector2();
var camera, sceneGL, sceneCSS, rendererGL, rendererCSS, composer, sphere, aimSphere;
var movementX, movementY, time, delta;
var originVec = new THREE.Vector3(0, 0, 0);
var prevTime = performance.now();
var velocity = new THREE.Vector3();
var objects = [];
var moveForward = false;
var moveBackward = false;
var moveLeft = false;
var moveRight = false;
var usingGamepadInput = false;
var windowHasFocus;
var linkURLs, dungeonTiles;

Start();
Update();

function buildMapChunk(x, y, w, h)
{
	//console.log("buildMapChunk("+x+", "+y+", "+w+", "+h+")");
	var canvas = document.getElementById("mapCanvas");
	var ctx = canvas.getContext("2d");	
	var imgData=ctx.getImageData(x, y, w, h);
	var cubeColor = new THREE.Color(0xffffff);
	var newIndex = 0;
	
	var levelGeometry = new THREE.Geometry();
	levelGeometry.dynamic = true;
	var level = new THREE.Mesh(levelGeometry, new THREE.MeshBasicMaterial( { vertexColors: THREE.VertexColors }  ));

	for (var i=0; i < imgData.data.length; i += 4)
	{
		//if(i == 0) { console.log("COLOR VAL: "+imgData.data[i]); }
	
		var geometry = new THREE.BoxGeometry( 1, 1, 1 );
		cubeColor.setRGB(imgData.data[i] / 256, imgData.data[i+1] / 256, imgData.data[i+2] / 256);
		for ( var j = 0, l = geometry.faces.length; j < l; j ++ ) 
		{
			var face = geometry.faces[ j ];
			face.vertexColors[ 0 ] = cubeColor; 
			face.vertexColors[ 1 ] = cubeColor;
			face.vertexColors[ 2 ] = cubeColor;
		}	
		
		material = new THREE.MeshBasicMaterial( { color: cubeColor, wireframe: false } );
		cube = new THREE.Mesh( geometry, material );
		cube.position.x = ((i/4) % w) + x;
		cube.position.y = imgData.data[i] / 30;
		cube.position.z = (Math.floor((i/4) / h)) + y;
		cube.updateMatrix();
		levelGeometry.merge(cube.geometry, cube.matrix);
	}	

	sceneGL.add( level );	
}

function doBuildMapChunk(i, j)
{
	setTimeout(function() { buildMapChunk(20*i, 20*j, 20, 20); }, (500*i)+(333*j));
}

function loadDungeon()
{
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
					
					console.log(linkURLs.length+" LINKS FOUND");
					
					for(var i = 0; i < linkURLs.length; i++)
					{
						var positionX = (Math.random()-0.5) * 600; 
						var positionY = ((Math.random()) * 20) + 10; 
						var positionZ = (Math.random()-0.5) * 600; 
						
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

}



function Start() 
{
	camera = new THREE.PerspectiveCamera( 60, window.innerWidth / window.innerHeight, 0.01, 10000 );
	camera.position.x = 100;
	camera.position.y = 40;
	camera.position.z = 100;
	camera.lookAt(originVec);
	
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
	
	for(var i = 0; i < 10; i++)
	{
		for(var j = 0; j < 6; j++)
		{
			doBuildMapChunk(i, j);
		}
	}
	
	// Add player
	var geometry = new THREE.SphereGeometry( 2, 16, 16 );
	var material = new THREE.MeshBasicMaterial( {color: 0xffff00} );
	sphere = new THREE.Mesh( geometry, material );
	sceneGL.add( sphere );	
	
	geometry = new THREE.SphereGeometry( 1, 8, 8 );
	material = new THREE.MeshBasicMaterial( {color: 0xff0000} );
	aimSphere = new THREE.Mesh( geometry, material );
	sceneGL.add( aimSphere );		
	
	rendererGL = new THREE.WebGLRenderer();
	rendererGL.setClearColor(0x00ff00, 0.0);
	rendererGL.domElement.style.zIndex = 5;
	rendererGL.setPixelRatio( window.devicePixelRatio );
	rendererGL.setSize( window.innerWidth, window.innerHeight );	

	/*composer = new THREE.EffectComposer( rendererGL );
	var effectBloom = new THREE.BloomPass( 1.25 );
	composer.addPass( effectBloom );*/
	
	document.body.appendChild( rendererGL.domElement );	
}

// 
function Update() 
{
	requestAnimationFrame( Update );
	time = performance.now();
	delta = ( time - prevTime ) / 1000;
	
	// Gamepad input
	if(gamepad)
	{
		if(Math.abs(gamepad.axes[0].toFixed(4)) > 0.2)
		{
			moveForward = true;	
			usingGamepadInput = true;
		}
		else if(Math.abs(gamepad.axes[1].toFixed(4)) > 0.2)
		{
			moveBackward = true;	
			usingGamepadInput = true;
		}		
		else if(Math.abs(gamepad.axes[2].toFixed(4)) > 0.2)
		{
			moveLeft = true;	
			usingGamepadInput = true;
		}
		else if(Math.abs(gamepad.axes[3].toFixed(4)) > 0.2)
		{
			moveRight = true;	
			usingGamepadInput = true;
		}
		else
		{
			if(usingGamepadInput == true)
			{
				moveForward = false;	
				moveBackward = false;	
				moveLeft = false;	
				moveRight = false;	
			}
		}
	}
	
	// Player motion - test move against collision
	if(moveForward == true)
	{
		playerPosition.z -= delta * 10;
	}
	if(moveBackward == true)
	{
		playerPosition.z += delta * 10;
	}
	if(moveLeft == true)
	{
		playerPosition.x -= delta * 10;
	}
	if(moveRight == true)
	{
		playerPosition.x += delta * 10;
	}
	
	if(document.hasFocus() == false)
	{
		lookOffset.set(0,0,0);
	}
	
	cameraPosLerp.x = playerPosition.x + lookOffset.x;
	cameraPosLerp.z = playerPosition.z + lookOffset.z;
	cameraPosLerp.y = 0;
	
	cameraLook.lerp(cameraPosLerp, delta * 20);
	
	sphere.position.set(playerPosition.x, 10, playerPosition.z);
	sphere.__dirtyPosition = true;
	//lookOffset = lookOffset.lerp(playerPosition, delta);
	
	aimSphere.position.set(playerPosition.x+lookOffset.x, 10, playerPosition.z+lookOffset.z);
	aimSphere.__dirtyPosition = true;
	
	
	cameraPosLerp.z = playerPosition.z + 10 + lookOffset.z;
	cameraPosLerp.x = playerPosition.x - 1 +lookOffset.x;
	cameraPosLerp.y = 40;
	camera.position.lerp(cameraPosLerp, delta * 10);
	camera.lookAt(cameraLook);
	
	rendererGL.render(sceneGL, camera);
	
	
	prevTime = time;
}

function onWindowResize() 
{
	camera.aspect = window.innerWidth / window.innerHeight;
	camera.updateProjectionMatrix();
	rendererGL.setSize( window.innerWidth, window.innerHeight );
}	

function onMouseMove(event) 
{
	//movementX = event.movementX || event.mozMovementX || event.webkitMovementX || 0;
	//movementY = event.movementY || event.mozMovementY || event.webkitMovementY || 0;

	//console.log("SCREEN: "+window.innerWidth+"|"+window.innerHeight+"  MOUSE: "+event.clientX+"|"+event.clientY);
	lookOffset.x = ((event.clientX - (window.innerWidth/2)) / window.innerWidth) * 30; //((event.clientX - screen.width) / screen.width) * 10; //+= movementX * 0.02;
	lookOffset.z = ((event.clientY - (window.innerHeight/2))  / window.innerHeight) * 20; //((event.clientY - screen.height) / screen.height) * 10; // += movementY * 0.02;
	//yawObject.rotation.y -= movementX * 0.002;
	//pitchObject.rotation.x -= movementY * 0.002;
	//pitchObject.rotation.x = Math.max( - PI_2, Math.min( PI_2, pitchObject.rotation.x ) );
	//console.log("MOUSE MOVE");
}

function onKeyDown(event) 
{
	if(usingGamepadInput == true)
	{
		moveForward = false;	
		moveBackward = false;	
		moveLeft = false;	
		moveRight = false;	
	}	

	if(event.keyCode == 38 || event.keyCode == 87) 
	{
		moveForward = true;
	}
	if(event.keyCode == 37 || event.keyCode == 65) 
	{
		moveLeft = true;
	}
	if(event.keyCode == 40 || event.keyCode == 83) 
	{
		moveBackward = true;
	}
	if(event.keyCode == 39 || event.keyCode == 68) 
	{
		moveRight = true;
	}	

	usingGamepadInput = false;	
}

function onKeyUp(event) 
{
	if(event.keyCode == 38 || event.keyCode == 87) 
	{
		moveForward = false;
	}
	if(event.keyCode == 37 || event.keyCode == 65) 
	{
		moveLeft = false;
	}
	if(event.keyCode == 40 || event.keyCode == 83) 
	{
		moveBackward = false;
	}
	if(event.keyCode == 39 || event.keyCode == 68) 
	{
		moveRight = false;
	}	
	usingGamepadInput = false;	
}

window.addEventListener( 'resize', onWindowResize, false );
document.addEventListener( 'mousemove', onMouseMove, false );
document.addEventListener( 'keydown', onKeyDown, false );
document.addEventListener( 'keyup', onKeyUp, false );

