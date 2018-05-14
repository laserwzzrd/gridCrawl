<?php

print(ip2long($_SERVER["REMOTE_ADDR"]).md5($_SERVER["HTTP_USER_AGENT"]));

?>