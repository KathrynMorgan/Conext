<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $meta['sitename'] ?><?= (!empty($page['title']) ? ' - '.$page['title'] : '') ?></title>
    <meta name="description" content="<?= $meta['description'] ?>">
    
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" integrity="sha256-eZrrJcwDc/3uDhsdt61sL2oOBY362qM3lon1gyExkL0=" crossorigin="anonymous" />
    <link href="//fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet">
    <!-- Bulma Version 0.6.2-->
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/bulma/0.6.2/css/bulma.min.css" integrity="sha256-2k1KVsNPRXxZOsXQ8aqcZ9GOOwmJTMoOB5o5Qp1d6/s=" crossorigin="anonymous" />
    <link rel="stylesheet" href="css/styles.css"/>
  </head>
  <body>
    <!-- START NAV -->
    <nav class="navbar">
      <div class="container">
        <div class="navbar-brand">
          <a class="navbar-item" href="../">
            <img src="//via.placeholder.com/650x150" alt="Logo">
          </a>
          <span class="navbar-burger burger" data-target="navbarMenu">
            <span></span>
            <span></span>
            <span></span>
          </span>
        </div>
        <div id="navbarMenu" class="navbar-menu">
          <div class="navbar-end">
            <a href="/" class="navbar-item <?= ($PATH == '/' ? ' is-active' : null) ?>" >Home</a>
          </div>
        </div>
      </div>
    </nav>
    <!-- END NAV -->
    <div class="container">
      <?= $f3->decode($page['body']) ?>
    </div>
    <script src="js/bulma.js"></script>
  </body>
</html>