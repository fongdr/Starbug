<?php
include(BASE_DIR."/vendor/autoload.php");
include(BASE_DIR."/core/global_functions.php");
include(BASE_DIR."/core/src/Settings.php");
if (defined('SB_CLI')) {
  include(BASE_DIR."/util/cli.php");
}
include(BASE_DIR."/etc/modules.php");

?>
