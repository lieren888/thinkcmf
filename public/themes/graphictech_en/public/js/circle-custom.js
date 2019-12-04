(function($) {
  'use strict';

  //++++++++++++++++
  // 性能指标
  //++++++++++++++++

  var numBox = $('.number-box');
  var numflag = 0;
  window.onscroll = function() {
    var clientH = document.documentElement.clientHeight;
    var nTop = numBox.offset().top - $(window).scrollTop();
    if (clientH - nTop - 100 > 0 && numflag < 1) {
      circleAnimation();
      numflag++;
    }
  }

  function circleAnimation() {
    var $chart_0 = $('.number-box-0').attr('data-to');
    var $chart_1 = $('.number-box-1').attr('data-to');
    var $chart_2 = $('.number-box-2').attr('data-to');
    var $chart_3 = $('.number-box-3').attr('data-to');
    $('.number-box').circleProgress({
      size: 160,
      thickness: 5,
      fill: {
        gradient: ["#efc321"]
      }
    });
    $('.number-box-0').circleProgress({
      value: $chart_0 / 100
    }).on('circle-animation-progress', function(event, progress) {
      $(this).find('strong').html(Math.round($chart_0 * progress) + '<i>%</i>');
    });
    $('.number-box-1').circleProgress({
      value: $chart_1 / 100
    }).on('circle-animation-progress', function(event, progress) {
      $(this).find('strong').html(Math.round($chart_1 * progress) + '<i>%</i>');
    });
    $('.number-box-2').circleProgress({
      value: $chart_2 / 100
    }).on('circle-animation-progress', function(event, progress) {
      $(this).find('strong').html(Math.round($chart_2 * progress) + '<i>%</i>');
    });
    $('.number-box-3').circleProgress({
      value: $chart_3 / 100
    }).on('circle-animation-progress', function(event, progress) {
      $(this).find('strong').html(Math.round($chart_3 * progress) + '<i>%</i>');
    });
  }

})(jQuery)
