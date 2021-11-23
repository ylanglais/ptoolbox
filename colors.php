<?php
require_once("lib/style.php");
require_once("lib/dbg_tools.php");
#
# Color evaluations:
foreach (style::colors() as $k => $v) $$k = $v;
#
# $fontfamily = style::value("fontfamily");
#
