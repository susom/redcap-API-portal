<nav>
    <ul>
        <li class="<?php echo $navon["home"]; ?>"><a href="index.php?nav=home">Home</a></li>
        <li class="<?php echo $navon["reports"]; ?>"><a href="reports.php?nav=reports">Reports</a></li>
        <li class="<?php echo $navon["game"]; ?>"><a href="game.php?nav=game">Game</a></li>
        <li class="<?php echo $navon["resources"]; ?>"><a href="resources.php?nav=resources">Resources</a></li>

    </ul>
</nav>
<div class="header-container">
    <header class="wrapper clearfix">
        <h1 class="title">WELL for Life</h1>
        <a id="account_drop" href="#"><span></span> <?php  echo $loggedInUser->firstname . " " . $loggedInUser->lastname?> <b class="caret"></b></a>
        <ul id="drop_menu">
            <li><a href="profile.php">Profile</a></li>
            <li><a href="index.php?logout=1">Logout</a></li>
        </ul>
        <a href="#" class="hamburger"></a>
    </header>
</div>
<div class="splash-container">
    <div class="wrapper clearfix">
        <?php  
        if(isset($cats[1])){
        ?>
        <h2><?php echo $cats[1]["subject"]?></h2>
        <blockquote>
            <?php echo $cats[1]["content"]?>
        </blockquote>
        <style>
            .splash-container .wrapper:before {
                background: url(<?php echo $cats[1]["pic"] ?>) 50% no-repeat;
                background-size:cover;
            }
        </style>
        <?php 
        }
        ?>
    </div>
</div>