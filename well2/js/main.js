
$(document).ready(function(){

	$("#account_drop").click(function(){
		$("#drop_menu").toggleClass("show");
	});

	$(document).on("click", function(event){
        if (!$(event.target).closest('#drop_menu').length) {
            // $("#drop_menu").removeClass("show");
        }
    });

    $(".hamburger").click(function(){
    	$("#inner_rim").toggleClass("open");
    });
});
