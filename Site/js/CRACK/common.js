function lerp(a, b, u) 
{
	return (1 - u) * a + u * b;
}

function objToString (obj) 
{
	var str = '';
	for (var p in obj) 
	{
		if (obj.hasOwnProperty(p)) 
		{
			str += p + '::' + obj[p] + '\n';
		}
	}
	return str;
}	

function getRandomColor() {
    var letters = '0123456789ABCDEF'.split('');
    var color = '#';
    for (var i = 0; i < 6; i++ ) {
        color += letters[Math.floor(Math.random() * 16)];
    }
    return color;
}

function getRandomBlueColor(rg, base, random) {
    var letters = '0123456789ABCDEF'.split('');
    var color = '#';
    for (var i = 0; i < 6; i++ ) {
        color += letters[Math.floor((Math.random() * random)+base)];
    }
    return color;
}

function loadMap(url)
{
	// load map PNG
	var canvas = document.getElementById("mapCanvas");
    var img1 = new Image();
	var ctx = canvas.getContext("2d");

    //drawing of the test image - img1
    img1.onload = function () {
        //draw background image
        ctx.drawImage(img1, 0, 0);
        //draw a box over the top
       // ctx.fillStyle = "rgba(200, 0, 0, 0.5)";
       // ctx.fillRect(0, 0, 500, 500);
    };

    img1.src = "mapData/"+hex_md5(url)+".png";
	
	// ajax links & colors, determine X/Z position for link
	
}

