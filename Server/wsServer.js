var fs = require('fs');
var path = require('path');
var url = require('url');
var process = require('child_process');
var mongodb = require("mongodb");
var http = require("http");
var WebSocket = require("ws");
var WebSocketServer = require("ws").Server;
var MongoClient = mongodb.MongoClient;
var dburl = 'mongodb://user:password@localhost:27111/gridCrawl';

// Websockets over SSL 
var ws_cfg = {
  ssl: true,
  port: 12345,
  ssl_key: 'gridCrawl.key',
  ssl_cert: 'gridCrawl.crt'
};

var processRequest = function(req, res) {
    console.log("Request received.")
};
var httpServ = require('https');
var app = null;

app = httpServ.createServer({
  key: fs.readFileSync(ws_cfg.ssl_key),
  cert: fs.readFileSync(ws_cfg.ssl_cert)
}, processRequest).listen(ws_cfg.port);
var wss = new WebSocketServer( {server: app});

// ----------------Main Loop--------------------
MongoClient.connect(dburl, function (err, db) {
  if (err) {
    console.log('Unable to connect to the mongoDB server. Error:', err);
  } else {
    //console.log('Connection established to', dburl);

	wss.on('connection', function connection(ws) 
	{
	  ws.on('message', function incoming(message) 
	  {
		//console.log('received: %s', message);
		
		// new player / connection
		if(message[0] == 'N')
		{
			var splitMessage = message.substr(1).split(" ");
			var query = {'userid': splitMessage[0]};
			var updata = {'userid': splitMessage[0], 'userAgentMD5': splitMessage[1]};
		
			if(db.collection('users').find(query).length == 0)
			{
			db.collection('users').update(query, updata, {upsert: true},
				function(err, data) {
				  if (err) {
					  console.log(err);
				  }
				  else {
					  //console.log("update succeeded");
				  }
				});
			}

			db.collection('users').find().toArray(function(err, results) {
					ws.send('N'+JSON.stringify(results));
			});		
		}
		// position/rotation update
		if(message[0] == 'P')
		{
			var splitMessage = message.substr(1).split(" ");
			var query = {'userid': splitMessage[0]};
			var updata = {$set: {'positionX': splitMessage[1], 'positionY': splitMessage[2], 'positionZ': splitMessage[3]}};
		
			db.collection('users').update(query, updata, {upsert: true},
				function(err, data) {
				  if (err) {
					  console.log(err);
				  }
				  else {
					  //console.log("update succeeded");
				  }
				});
						
						
			db.collection('users').find().toArray(function(err, results) {
				try { ws.send('P'+JSON.stringify(results)); }
				catch(err) { console.log("ERROR: "+err); }
				  });	
			
		}
		
		// status update
		if(message[0] == 'S')
		{
			var splitMessage = message.substr(1).split(" ");
			var query = {'userid': splitMessage[0]}; 
			var updata = {$set: {'name': splitMessage[1], 'URL': splitMessage[2], 'userAgent': splitMessage[3], 'message': splitMessage[4]}};
		
			db.collection('users').update(query, updata, {upsert: true},
				function(err, data) {
				  if (err) {
					  console.log(err);
				  }
				  else {
					  //console.log("update succeeded");
				  }
				});
			
		}
		
		// build site cache
		if(message[0] == "W")
		{
			var splitMessage = message.substr(1).split(" ");
			var query = {'userid': splitMessage[0]}; 
			var cacheHost = url.parse(splitMessage[1]).hostname
			console.log("CACHE REQUEST: %s", message);
			
			// check cache expiry date, if older or non-existent then build cache
			if (fs.existsSync("public/STAGING/siteCache/"+cacheHost) == false) 
			{
				fs.writeFile("public/STAGING/siteCacheQueue/"+cacheHost, "", function(err) {
					if(err) {
						return console.log(err);
					}
					console.log("siteCacheQueue file was saved: public/STAGING/siteCacheQueue/"+cacheHost);
				}); 
				
				// run single-instance python process, will exit if already running
				const spawn = require('child_process').spawn;
				const ls = spawn('python', ['cacheSite.py']);
				
				// every 4 secs check if cache is complete, send notification if so (HACKY, FIXME)
				setTimeout(function() 
				{
					if (fs.existsSync("public/STAGING/siteCache/"+cacheHost) == false)
					{
						setTimeout(function() 
						{
							if (fs.existsSync("public/STAGING/siteCache/"+cacheHost) == false)
							{
								setTimeout(function() 
								{
									if (fs.existsSync("public/STAGING/siteCache/"+cacheHost) == false)
									{

									}
									else
									{
										try { ws.send('WLOADED'); }
										catch(err) { console.log("ERROR: "+err); }	
									}
								}, 4000);	
							}
							else
							{
								try { ws.send('WLOADED'); }
								catch(err) { console.log("ERROR: "+err); }	
							}
						}, 4000);				  
					}
					else
					{
						try { ws.send('WLOADED'); }
						catch(err) { console.log("ERROR: "+err); }	
					}
				}, 4000);
				
				// DIRECT CACHING - reenable when we get a beefy server
				/*console.log("CACHING: "+url.parse(splitMessage[1]).hostname);
				const spawn = require('child_process').spawn;
				const ls = spawn('python', ['cacheSite.py', url.parse(splitMessage[1]).hostname]);

				ls.stdout.on('data', function (data) {
					var str = data.toString()
					//console.log("STDOUT: "+str);
					if(str.indexOf("ROOT SITE CACHED") != -1)
					{
						console.log("FINISHED CACHING: "+url.parse(splitMessage[1]).hostname)
					}
				});

				ls.stderr.on('data', function (data) {
					var str = data.toString()
					//console.log("STDERR: "+str);
				});

				ls.on('close', function (data) {
					var str = data.toString()
					// send all-clear message to load the site to the requester
					//console.log("cacheSite.py closing..."+str);
				});	*/
			} 
			else
			{
				// otherwise send the all-clear message to load the site
				try { ws.send('WLOADED'); }
				catch(err) { console.log("ERROR: "+err); }				
			}
		}
	  });
	});	
  }
});

    