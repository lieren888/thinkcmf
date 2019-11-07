(function($){
  $(".image-map-container .state_area").click(function (t) {
    var i = $(this).index();
    $(".image-map-container .state-area-selected").attr("class", "state_area"), $(this).attr("class", "state_area state-area-selected");
    var n = $(this).closest(".image-map-container");
    $(".image-map-section", n).hide();
    var o = $(".image-map-section:nth(" + i + ")", n);
    o.show()
  })
})(jQuery)
