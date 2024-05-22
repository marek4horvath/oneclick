/**
 * @fileoverview Font Awesome
 * @autor Oliver Tomondy <oliver.tomondy@gmail.com>
 */

/**
 * Font awesome
 * @namespace
 * @global
 */
nge.ui.fontAwesome = nge.ui.fontAwesome || {};

    // options cache
    nge.ui.fontAwesome.options = {};

/**
 *  Function for Ckeditor integration
 * @memberOf nge.ui.fontAwesome
 */
nge.ui.fontAwesome.openDialog = function (icon, callback) {
    icon = icon || "";
    nge.ui.fontAwesome.initFontAwesome(icon,'advanced');
    
    nge.ui.fontAwesome.options.html.on('click', '#submitIcon', function () {
        icon = nge.ui.fontAwesome.initFontAwesome.fontAwesomeValue();

        if (callback) callback(icon)
    });
}

/**
 * Inits font awesome
 * @memberOf nge.ui.fontAwesome
 */
nge.ui.fontAwesome.initFontAwesome = function (selectedIcon, settingsLevel) {

    if (selectedIcon.includes("no-selection")) {
        selectedIcon = "";
    }
    if (selectedIcon == "icon-span") {
        selectedIcon = "";
    }

    fontAwesomeValue = function() {
        $iconClasses = $(".icon-base i").attr('class');
        $.fancybox.close();
        return $iconClasses;
    }
    //all icons sorted to categories
    var prefix = "fas",
        $html = $('<div class="font-awesome-content"></div>');

    //Assigning classes of the icon to selectboxes
    configureSelectBoxes = function (iconSetup) {
        var setupClasses = iconSetup.split(" ");

        for (var i = 0; i < setupClasses.length; i++) {
            $(".animation .withClearFunctions option").each(function (v) {
                if (this.value == setupClasses[i]) {
                    $(".animation .withClearFunctions").val(this.value);
                }
            });
            $(".hover .withClearFunctions option").each(function (v) {
                if (this.value == setupClasses[i]) {
                    $(".hover .withClearFunctions").val(this.value);
                }
            });
            $(".weight-spec .withClearFunctions option").each(function (v) {
                if (this.value == setupClasses[i]) {
                    $(".weight-spec .withClearFunctions").val(this.value);
                }
            });
            $(".size .withClearFunctions option").each(function (v) {
                if (this.value == setupClasses[i]) {
                    $(".size .withClearFunctions").val(this.value);
                }
            });
            $(".rotation .withClearFunctions option").each(function (v) {
                if (this.value == setupClasses[i]) {
                    $(".rotation .withClearFunctions").val(this.value);
                }
            });
            $(".style .withClearFunctions option").each(function (v) {
                if (this.value == setupClasses[i]) {
                    $(".style .withClearFunctions").val(this.value);
                }
            });
            $(".style-hover .withClearFunctions option").each(function (v) {
                if (this.value == setupClasses[i]) {
                    $(".style-hover .withClearFunctions").val(this.value);
                }
            });
        }
    }
    //If icon is selected, open configuration right away
    configureIcon = function (selectedIcon) {
        if (selectedIcon) {
            $('.icon-base').html("");
            $('.icon-base').html("<i class='" + selectedIcon + "'></i>");
            configureSelectBoxes(selectedIcon);
        }
        $(".icons-filter-div").hide(100);
        $(".loadallicons").hide();
        $(".icon-settings-div").show(100);
        $(".info-title").html('<b>Set up your icon</b>');
        $('.filter-button-group li, .target, .filter-header, .quicksearch ').hide();
        $('.back-all-icon').addClass('hidden');
    }

    //Configure icon or return classes based on parameter
    detailIcon = function ($ele) {
        var $icon;
        //advanced settings
        if (settingsLevel == "advanced") {
            $('select').not('.weight-spec .withClearFunctions, .target .withClearFunctions').val('');
            $(".icons-filter-div").hide(100);
            $(".icon-settings-div").show(100);
            $(".icon-base").html($(".icon-div", $ele).html()); //important
            $(".info-title").html('<b>Set up your icon</b>');
            $('.back-all-icon').addClass('hidden');
            //Hide category filters
            $('.filter-button-group li, .target, .filter-header, .quicksearch ').hide();
        } else {
            //basic settigns
            fontAwesomeValue();
        }
        $icon = $ele.find('.icon-div i').attr('class');
       return $icon;
    }

    //Back to overview
    backToOverview = function (category) {
        $(".back-to-overview").click(function () {
            $('select').not('.weight-spec .withClearFunctions, .target .withClearFunctions').val('');
            $(".icon-settings-div").hide(500);
            $(".icons-filter-div").show(500);
            $(".info-title").html('<b>Icons: <span class="current-category-icons">' + category + '</span></b>');
            $('.fa-category-name, .target, .filter-header, .quicksearch ').show();
            $('.back-all-icon').removeClass('hidden');

        });
    }

    //Return configured icon
    iconSubmit = function () {
        $(".icon-submit").click(function () {
            fontAwesomeValue();
            $('.back-all-icon').addClass('hidden');
        });
    }

    //Change classes
    iconWeigh = function () {
        $(".weight-spec .withClearFunctions").change(function () {
            var newVal = "";
            $(".weight-spec .withClearFunctions option:selected").each(function () {
                newVal = $(this).val();
            });
            //For specific icon
            $('.icon-base i').not(".fab").removeClass("far fal fas fad fat").addClass(newVal);

        }).change();
    }

    iconRotation = function () {
        $(".rotation .withClearFunctions").change(function () {
            var newVal = "";
            $(".rotation .withClearFunctions option:selected").each(function () {
                newVal = $(this).val();
            });
            $('.icon-base i').removeClass('fa-rotate-90 fa-rotate-180 fa-rotate-270 fa-flip-horizontal fa-flip-vertical').addClass(newVal);
        }).change();
    }

    iconSize = function () {
        $(".size .withClearFunctions").change(function () {
            var newVal = "";
            $(".size .withClearFunctions option:selected").each(function () {
                newVal = $(this).val();
            });
            $('.icon-base i').removeClass('fa-sm fa-lg fa-2x fa-3x fa-5x fa-7x fa-10x').addClass(newVal);
        }).change();
    }

    iconAnimation = function () {
        $(".animation .withClearFunctions").change(function () {
            var newVal = "";
            $(".animation .withClearFunctions option:selected").each(function () {
                newVal = $(this).val();
            });
            $('.icon-base i').removeClass('fa-pulse fa-spin').addClass(newVal);
        }).change();
    }

    iconTarget = function () {
        $(".target .withClearFunctions").change(function () {
            var newVal = "";
            $(".target .withClearFunctions option:selected").each(function () {
                newVal = $(this).val();
            });
            if (newVal == 'fat') $('.icon-div i').not(".fab").removeClass("far fad fal fat").removeClass(prefix).addClass(newVal).css('font-size', '50px');
            else $('.icon-div i').not(".fab").removeClass("far fad fal fat").removeClass(prefix).addClass(newVal);
        }).change();
    }

    iconStyle = function () {
        $(".style").change(function () {
            var newVal = "";
            $(".style option:selected").each(function () {
                newVal = $(this).val();
            });

            $('.icon-base i').removeClass('fa-icon-style-1 fa-icon-style-2 fa-icon-style-3 fa-icon-style-4 fa-icon-style-5').addClass(newVal);
        }).change();
    }

    iconHover = function () {
        $(".style-hover").change(function () {
            var newVal = "";
            $(".style-hover option:selected").each(function () {
                newVal = $(this).val();
            });
            $('.icon-base i').removeClass('fa-icon-hover-1 fa-icon-hover-2 fa-icon-hover-3 fa-icon-hover-4').addClass(newVal);
        }).change();
    }

    // debounce so filtering doesn't happen every millisecond
    debounce = function (fn, threshold) {
        var timeout;
        threshold = threshold || 500;
        return function debounced() {
            clearTimeout(timeout);
            var args = arguments;
            var _this = this;
            function delayed() {
                fn.apply(_this, args);
            }
            timeout = setTimeout(delayed, threshold);
        };
    }

    listingByCategoryIcon = function () {
         var category = nge.ui.fontAwesome.options.category == undefined ? 'accessibility' : nge.ui.fontAwesome.options.category,
            filter = (nge.ui.fontAwesome.options.filter != '' && nge.ui.fontAwesome.options.filter != undefined) ? nge.ui.fontAwesome.options.filter : '';
            requestListing = {
                'module': 'Fontawesome',
                'func': 'getByCategory',
                'args': [ category, filter ],
                'props': {
                    'template': 'listing',
                }
            };

        $('.fancybox-inner').css('overflow', 'hidden');

        nge.io.Rmi.get(requestListing).done(function (retval, html, location) {
            var iconClass

            $html.empty();
            $html.append(html);
            $html.find('.current-category-icons').append(category);
            $html.find('.quicksearch').val(filter);

            if (filter != undefined && filter != '') {
                $html.find('.quicksearch').focus();
                $html.find('.current-category-icons').empty();
                $html.find('.current-category-icons').append(filter);
            }

            $html.find('.icons-filter-div').on('click','.icon-box-fa', function () {
                iconClass = detailIcon($(this));
                configureIcon(iconClass);
            });

            if (selectedIcon != "") {
                configureIcon(selectedIcon);
            }

            iconWeigh();
            iconRotation();
            iconSize();
            iconAnimation();
            iconTarget();
            iconStyle();
            iconHover();

            backToOverview(category);
            iconSubmit();

            $html.find('ul').on('click', 'li', function () {
                buttonFilter = $(this).attr('data-filter');
                nge.ui.fontAwesome.options.filter = '';
                nge.ui.fontAwesome.options.category = buttonFilter.replace(' + ', '-');
                nge.ui.fontAwesome.options.activeCategory = buttonFilter;

                listingByCategoryIcon();

                $(".current-category-icons").text(buttonFilter);
            });

            // use value of search field to filter
            var $quicksearch = $html.find('.quicksearch').keyup(debounce(function () {
                nge.ui.fontAwesome.options.filter = $quicksearch.val();
                listingByCategoryIcon();

            }));

            if (nge.ui.fontAwesome.options.activeCategory != undefined) {
                var activeCategory = nge.ui.fontAwesome.options.activeCategory;

                $html.find(`ul li[data-filter='${activeCategory}']`).removeClass('active');
                $html.find(`ul li[data-filter='${activeCategory}']`).addClass('active');
            }

        }).fail(function (message, code) {
            nge.ui.error(message);
        });
    }

    //Fancybox
    $.fancybox({
        'type': 'html',
        'content': $html,
        'title': '<h3>Font Awesome</h3>',
        'parent': 'body',
        'width': '90%',
        'height': '100%',
        'maxHeight': '100%',
        'wrapCSS': 'fancybox-system-overlay',
        'autoSize': false,
        'fitToView': true,
        'margin': [20, 20, 20, 20],
        'padding': 0,
        'overlayShow': true,
        'transitionIn': 'elastic',
        'transitionOut': 'elastic',
        'easingIn': 'easeOutBack',
        'easingOut': 'easeInBack',
        'speedIn': 500,
        'speedOut': 200,
        afterLoad: function () {
            $('.fancybox-overlay').css('z-index', '900000');
        },
        helpers: {
            overlay: {
                showEarly: true,
                closeClick: false,
                locked: true
            },
            title: {
                type: 'outside',
                position: 'top'
            }
        }
    });

    listingByCategoryIcon();

    nge.ui.fontAwesome.options.html = $html;

    nge.ui.fontAwesome.initFontAwesome.fontAwesomeValue = fontAwesomeValue;
};