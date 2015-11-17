</html>
<script>
$(document).ready(function(){
  $(".alert").addClass("showit");
});

$(".nav-item > a").click(function(){
  $(".nav-item").removeClass("hot");
  if($(this).parent("li").hasClass("hot")){
    $(this).parent("li").removeClass("hot");
  }else{
    $(this).parent("li").addClass("hot");
  }
  return false; 
});

$(document).on('click', function(event) {
  $(".alert").removeClass("showit");

  if (!$(event.target).closest('#brandbar nav').length) {
    $(".nav-item").removeClass("hot");
  }
});

function doRedirect(caller) {
  var redirectHash1   = caller.attr('redirect');
  var surveyHash    = caller.attr('hash');
  var link      = caller.attr('link');

  // console.log("THIS IS THE redirectHash" +redirectHash + " and surveyHash" +surveyHash +" and link is " +link);
  $("#__code").val(redirectHash);
  $("#resumeSurveyForm").attr("action", link);
  $("#resumeSurveyForm").submit();

  return;
}
</script>
<!-- <script src="https://fb.me/react-0.14.1.min.js"></script>
<script src="https://fb.me/react-dom-0.14.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/babel-core/5.8.23/browser.min.js"></script> -->
<script type="text/babel">
// REACT COMPONENTS HERE
	// var NavBar = React.createClass({
	// 	getInitialState : function(){
	// 		return ({
	// 			unLoggedIn: true
	// 		});
	// 	},

	// 	render: function() {
	// 		return (
	// 			<div className="container">
	// 				<a className="main_logo" href={this.props.baseurl}></a>
	// 				<div className="menu">
	// 					{(
	// 						true 
	// 						? <div><a href="login.php">Login</a> | <a href="register.php">Register</a></div>
	// 						: <div>
	// 							<a href="login.php">irvins@stanford.edu +</a> 
	// 							<ul>
	// 							<li>Account Status : Active</li>
	// 							<li>Update Password</li>
	// 							</ul>
	// 						 </div>
	// 					)}
	// 				</div>
	// 			</div>
	// 		);
	// 	}
	// });

	// ReactDOM.render(
	// 	<NavBar baseurl="http://webtools.irvins.local/portal/"/>,
	// 	document.getElementById('navbar')
	// );
</script>
<?php
// $end_time = microtime(true) - $start_time;
// print_r($end_time . " seconds");
// exit;
?>