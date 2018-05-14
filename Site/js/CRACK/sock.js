var ws = new WebSocket("wss://www.yoursite.com:12345");

if(!ws)
{
	console.log('Unable to establish websocket');
}
else
{
	console.log("Websocket connection established");
}

ws.onopen = function()
{
	console.log("websocket: onOpen");
};

ws.onclose = function()
{ 
	console.log("Websocket has closed");
};	

var positionUpdateWSLoop = setInterval(positionUpdateWSLooper, 200);
function positionUpdateWSLooper() 
{
	if(ws && finishedInitializing == true)
	{
		ws.send("P"+userid+" "+controls.getObject().position.x+" "+controls.getObject().position.y+" "+controls.getObject().position.z);
	}
}

var statusUpdateWSLoop = setInterval(statusUpdateWSLooper, 5000);
function statusUpdateWSLooper() 
{
	if(ws && finishedInitializing == true)
	{
		ws.send("S"+userid+" "+datUI.user+" "+datUI.URL+" "+datUI.userAgent.replace(/ /g, "_")+" "+datUI.message.replace(/ /g, "_")+" "+datUI.playerColor);
	}
}					
