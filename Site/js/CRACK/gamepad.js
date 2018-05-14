var gamepad;

function gamepadconnected(e) 
{
	console.log("Gamepad connected at index %d: %s. %d buttons, %d axes.",
    e.gamepad.index, e.gamepad.id,
    e.gamepad.buttons.length, e.gamepad.axes.length);
	
	gamepad = e.gamepad;
}

function buttonPressed(b) {
  if (typeof(b) == "object") {
    return b.pressed;
  }
  return b == 1.0;
}


window.addEventListener("gamepadconnected", gamepadconnected);		
		
