<?php
require_once("../models/config.php");

$pg_title 		= "Dashboard : $websiteName";
$body_classes 	= "dashboard";
include("inc/gl_head.php");
?>
  <section class="vbox">
    <?php 
    	include("inc/gl_header.php"); 
    ?>
    <section>
      <section class="hbox stretch">
        <?php 
        	include("inc/gl_sidenav.php"); 
        ?>

        <section id="content">
          <section class="hbox stretch">
            <section>
              <section class="vbox">
                <section class="scrollable padder">              
                  <section class="row m-b-md">
                    <div class="col-sm-6">
                      <h3 class="m-b-xs text-black">Dashboard</h3>
                      <small>Welcome back, John Smith, <i class="fa fa-map-marker fa-lg text-primary"></i> New York City</small>
                    </div>
                    <div class="col-sm-6 text-right text-left-xs m-t-md">
                      <div class="btn-group">
                        <a class="btn btn-rounded btn-default b-2x dropdown-toggle" data-toggle="dropdown">Widgets <span class="caret"></span></a>
                        <ul class="dropdown-menu text-left pull-right">
                          <li><a href="#">Notification</a></li>
                          <li><a href="#">Messages</a></li>
                          <li><a href="#">Analysis</a></li>
                          <li class="divider"></li>
                          <li><a href="#">More settings</a></li>
                        </ul>
                      </div>
                      <a href="#" class="btn btn-icon b-2x btn-default btn-rounded hover"><i class="i i-bars3 hover-rotate"></i></a>
                      <a href="#nav, #sidebar" class="btn btn-icon b-2x btn-info btn-rounded" data-toggle="class:nav-xs, show"><i class="fa fa-bars"></i></a>
                    </div>
                  </section>
                  <div class="row">
                    <div class="col-sm-6">
                      <div class="panel b-a">
                        <div class="row m-n">
                          <div class="col-md-6 b-b b-r">
                            <a href="#" class="block padder-v hover">
                              <span class="i-s i-s-2x pull-left m-r-sm">
                                <i class="i i-hexagon2 i-s-base text-danger hover-rotate"></i>
                                <i class="i i-plus2 i-1x text-white"></i>
                              </span>
                              <span class="clear">
                                <span class="h3 block m-t-xs text-danger">2,000</span>
                                <small class="text-muted text-u-c">New Visits</small>
                              </span>
                            </a>
                          </div>
                          <div class="col-md-6 b-b">
                            <a href="#" class="block padder-v hover">
                              <span class="i-s i-s-2x pull-left m-r-sm">
                                <i class="i i-hexagon2 i-s-base text-success-lt hover-rotate"></i>
                                <i class="i i-users2 i-sm text-white"></i>
                              </span>
                              <span class="clear">
                                <span class="h3 block m-t-xs text-success">75%</span>
                                <small class="text-muted text-u-c">Bounce rate</small>
                              </span>
                            </a>
                          </div>
                          <div class="col-md-6 b-b b-r">
                            <a href="#" class="block padder-v hover">
                              <span class="i-s i-s-2x pull-left m-r-sm">
                                <i class="i i-hexagon2 i-s-base text-info hover-rotate"></i>
                                <i class="i i-location i-sm text-white"></i>
                              </span>
                              <span class="clear">
                                <span class="h3 block m-t-xs text-info">25 <span class="text-sm">m</span></span>
                                <small class="text-muted text-u-c">location</small>
                              </span>
                            </a>
                          </div>
                          <div class="col-md-6 b-b">
                            <a href="#" class="block padder-v hover">
                              <span class="i-s i-s-2x pull-left m-r-sm">
                                <i class="i i-hexagon2 i-s-base text-primary hover-rotate"></i>
                                <i class="i i-alarm i-sm text-white"></i>
                              </span>
                              <span class="clear">
                                <span class="h3 block m-t-xs text-primary">9:30</span>
                                <small class="text-muted text-u-c">Meeting</small>
                              </span>
                            </a>
                          </div>
                        </div>
                      </div>
                    </div>
                    
                    <div class="col-sm-3 hide">
                      <section class="panel b-a">
                        <header class="panel-heading b-b b-light">
                          <ul class="nav nav-pills pull-right">
                            <li>
                              <a href="ajax.pie.html" class="text-muted" data-bjax data-target="#b-c">
                                <i class="i i-cycle"></i>
                              </a>
                            </li>
                            <li>
                              <a href="#" class="panel-toggle text-muted">
                                <i class="i i-plus text-active"></i>
                                <i class="i i-minus text"></i>
                              </a>
                            </li>
                          </ul>
                          Connection
                        </header>
                        <div class="panel-body text-center bg-light lter" id="b-c">
                          <div class="easypiechart inline m-b m-t" data-percent="60" data-line-width="4" data-bar-Color="#23aa8c" data-track-Color="#c5d1da" data-color="#2a3844" data-scale-Color="false" data-size="120" data-line-cap='butt' data-animate="2000">
                            <div>
                              <span class="h2 m-l-sm step"></span>%
                              <div class="text text-xs">completed</div>
                            </div>
                          </div>
                        </div>
                      </section>                      
                    </div>
                  </div>           
                  <div class="row bg-light dk m-b">
                    <div class="col-md-6 dker">
                      <section>
                        <header class="font-bold padder-v">
                          <div class="pull-right">
                            <div class="btn-group">
                              <button data-toggle="dropdown" class="btn btn-sm btn-rounded btn-default dropdown-toggle">
                                <span class="dropdown-label">Week</span> 
                                <span class="caret"></span>
                              </button>
                              <ul class="dropdown-menu dropdown-select">
                                  <li><a href="#"><input type="radio" name="b">Month</a></li>
                                  <li><a href="#"><input type="radio" name="b">Week</a></li>
                                  <li><a href="#"><input type="radio" name="b">Day</a></li>
                              </ul>
                            </div>
                            <a href="#" class="btn btn-default btn-icon btn-rounded btn-sm">Go</a>
                          </div>
                          Statistics
                        </header>
                        <div class="panel-body">
                          <div id="flot-sp1ine" style="height:210px"></div>
                        </div>
                        <div class="row text-center no-gutter">
                          <div class="col-xs-3">
                            <span class="h4 font-bold m-t block">5,860</span>
                            <small class="text-muted m-b block">Orders</small>
                          </div>
                          <div class="col-xs-3">
                            <span class="h4 font-bold m-t block">10,450</span>
                            <small class="text-muted m-b block">Sellings</small>
                          </div>
                          <div class="col-xs-3">
                            <span class="h4 font-bold m-t block">21,230</span>
                            <small class="text-muted m-b block">Items</small>
                          </div>
                          <div class="col-xs-3">
                            <span class="h4 font-bold m-t block">7,230</span>
                            <small class="text-muted m-b block">Customers</small>                        
                          </div>
                        </div>
                      </section>
                    </div>
                    <div class="col-md-6">
                      <section>
                        <header class="font-bold padder-v">
                          <div class="btn-group pull-right">
                            <button data-toggle="dropdown" class="btn btn-sm btn-rounded btn-default dropdown-toggle">
                              <span class="dropdown-label">Last 24 Hours</span> 
                              <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu dropdown-select">
                                <li><a href="#"><input type="radio" name="a">Today</a></li>
                                <li><a href="#"><input type="radio" name="a">Yesterday</a></li>
                                <li><a href="#"><input type="radio" name="a">Last 24 Hours</a></li>
                                <li><a href="#"><input type="radio" name="a">Last 7 Days</a></li>
                                <li><a href="#"><input type="radio" name="a">Last 30 days</a></li>
                                <li><a href="#"><input type="radio" name="a">Last Month</a></li>
                                <li><a href="#"><input type="radio" name="a">All Time</a></li>
                            </ul>
                          </div>
                          Analysis
                        </header>
                        <div class="panel-body flot-legend">
                          <div id="flot-pie-donut"  style="height:240px"></div>
                        </div>
                      </section>
                    </div>
                  </div>
                </section>
              </section>
            </section>
            
            <?php
            	include("inc/gl_slideout.php");
            ?>
          </section>
          <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen,open" data-target="#nav,html"></a>
        </section>
      </section>
    </section>
  </section>
<?php
include("inc/gl_foot.php");
?>
