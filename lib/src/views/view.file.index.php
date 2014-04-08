<?php
header("Cache-Control: public");
header(sprintf('Content-Type: %s', $mime));
#header("Content-Description: File Transfer");
#header("Content-Disposition: attachment; filename=$filename");
#header("Content-Transfer-Encoding: binary");

die($contents);