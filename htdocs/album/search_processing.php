<?php

require_once('../config.inc');

header('Location: '.URL.'/album/?' . $_GET['nick']);
exit;
