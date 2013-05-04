$(document).ready(function() {
	//------------------------------------------------------//
	//                          INIT                        //
	//------------------------------------------------------//
	//Tooltip
	$('a').tooltip({
		animation : false
	})
	
	//Reports
	$(".recipient-click-export").tooltip("destroy");
	$(".recipient-click-export").tooltip({animation : false, placement: "left"});
	
	//Campaigns
	$(".delete-campaign").tooltip("destroy");
	$(".delete-campaign").tooltip({animation : false, placement: "left"});
	
	//Lists
	$(".delete-list").tooltip("destroy");
	$(".delete-list").tooltip({animation : false, placement: "left"});
	
	//Subscribers
	$(".delete-subscriber").tooltip("destroy");
	$(".delete-subscriber").tooltip({animation : false, placement: "left"});
	//------------------------------------------------------//
	//                        BUTTONS                       //
	//------------------------------------------------------//
	
	//------------------------------------------------------//
	//                      FUNCTIONS                       //
	//------------------------------------------------------//	
});