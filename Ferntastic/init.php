<?php

use Ferntastic\Routing\Dispatcher as Dispatcher;
use Ferntastic\HTTP\Request as SiteRequest;

//Only for awesome stuff

$Dispatcher = new Ferntastic\Routing\Dispatcher();
$Dispatcher->dispatch(
	new SiteRequest(),
	null
);