<!DOCTYPE html>
<?php
  define('CSSPATH', 'public_html/css/');
  $css_file =  basename($_SERVER["SCRIPT_FILENAME"], '.php') . '.css';
?>
<html>
<head>
  <title>COMP3013gp</title>
  <link rel="stylesheet" type="text/css" href="resources/library/bootstrap-3.3.6-dest/css/bootstrap.min.css">
  <link rel="stylesheet" type="text/css" href="<?php echo (CSSPATH . $css_file); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<?php
  require_once(realpath(dirname(__FILE__) . "/../config.php"));
?>
<nav class="navbar">
  <div class="container-fluid">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="#">comp3013gp-auction</a>
    </div>
    <?php
      //TODO only if user is logged in
    ?>
      <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
        <ul class="nav navbar-nav navbar-right">
          <li><a class="nav-item" href="#">MyItems</a></li><!-- TODO href to item list page -->
          <li><a class="nav-item" href="#">MyAccount</a></li><!-- TODO href to user page -->
          <li><a class="nav-item" href="#">SignOut</a></li><!-- TODO href to login page -->
        </ul>
      </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</nav>
<div class="body-container">
