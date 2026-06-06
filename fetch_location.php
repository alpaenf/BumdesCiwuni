<?php
$h = get_headers('https://maps.app.goo.gl/B5KaEx8UPwSk3FBk9', 1);
$loc = isset($h['Location']) ? (is_array($h['Location']) ? end($h['Location']) : $h['Location']) : 'Failed';
echo $loc;
