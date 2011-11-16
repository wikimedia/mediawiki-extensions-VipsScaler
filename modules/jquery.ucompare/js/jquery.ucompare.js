/*!
 * jQuery uCompare
 * http://www.userdot.net/#!/jquery
 *
 * Copyright 2011, UserDot www.userdot.net
 * Licensed under the GPL Version 3 license.
 * Version 1.0.0
 *
 */
(function ($) {
    $.fn.extend({
        ucompare: function (b) {
            var c = {
                defaultgap: 50,
                leftgap: 10,
                rightgap: 10,
                caption: false,
                reveal: .5
            };
            var b = $.extend(c, b);
            return this.each(function () {
                var c = b;
                var d = $(this);
                var e = d.children("img:eq(0)").attr("src");
                var f = d.children("img:eq(1)").attr("src");
                var g = d.children("img:eq(0)").attr("alt");
                var h = d.children("img:eq(0)").width();
                var i = d.children("img:eq(0)").height();
                d.children("img").hide();
                d.css({
                    overflow: "hidden",
                    position: "relative"
                });

                /**
				 * MediaWiki hack:
                 * Parent element height can still be 0px after hiding the images
                 * so we really want to update its dimensions.
                */
                d.width(h); d.height(i);

                d.append('<div class="uc-mask"></div>');
                d.append('<div class="uc-bg"></div>');
                d.append('<div class="uc-caption">' + g + "</div>");
                d.children(".uc-mask, .uc-bg").width(h);
                d.children(".uc-mask, .uc-bg").height(i);
                d.children(".uc-mask").animate({
                    width: h - c.defaultgap
                }, 1e3);
                d.children(".uc-mask").css("backgroundImage", "url(" + e + ")");
                d.children(".uc-bg").css("backgroundImage", "url(" + f + ")");
                if (c.caption) d.children(".uc-caption").show()
            }).mousemove(function (c) {
                var d = b;
                var e = $(this);
                pos_img = e.position()["left"];
                pos_mouse = c.pageX - e.children(".uc-mask").offset().left;
                new_width = pos_mouse - pos_img;
                img_width = e.width();
                img_cap_one = e.children("img:eq(0)").attr("alt");
                img_cap_two = e.children("img:eq(1)").attr("alt");
                if (new_width > d.leftgap && new_width < img_width - d.rightgap) {
                    e.children(".uc-mask").width(new_width)
                }
                if (new_width < img_width * d.reveal) {
                    e.children(".uc-caption").html(img_cap_two)
                } else {
                    e.children(".uc-caption").html(img_cap_one)
                }
            })
        }
    })
})(jQuery)
