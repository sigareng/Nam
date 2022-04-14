<?php
require '../nam.php';
use sigareng\Nam\Nam;

Nam::setbase('Example');

Nam::get('/', function() {
  Nam::render('./view/head.php');
  Nam::render('./view/body.php');
  Nam::render('./view/footer.php');
});

Nam::get('/(:num)', function($val) {
  $age['alice']=$val;
  Nam::render('./hi.php',$age);
  echo '<pre>' . print_r(get_defined_vars(), true) . '</pre>';
});

Nam::dispatch();

?>