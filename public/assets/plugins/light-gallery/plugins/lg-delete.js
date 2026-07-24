// noinspection UnnecessaryLocalVariableJS,JSVoidFunctionReturnValueUsed

(function (global, factory) {
    typeof exports === 'object' && typeof module !== 'undefined' ? module.exports = factory() :
        typeof define === 'function' && define.amd ? define(factory) :
            (global.lgDelete = factory());
}(this, (function () {'use strict';


    /*! *****************************************************************************
    Copyright (c) Microsoft Corporation.

    Permission to use, copy, modify, and/or distribute this software for any
    purpose with or without fee is hereby granted.

    THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES WITH
    REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY
    AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY SPECIAL, DIRECT,
    INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER RESULTING FROM
    LOSS OF USE, DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR
    OTHER TORTIOUS ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR
    PERFORMANCE OF THIS SOFTWARE.
    ***************************************************************************** */

    var __assign = function() {
        __assign = Object.assign || function __assign(t) {
            for (var s, i = 1, n = arguments.length; i < n; i++) {
                s = arguments[i];
                for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p)) t[p] = s[p];
            }
            return t;
        };
        return __assign.apply(this, arguments);
    };


    /**
     * List of lightGallery events
     * All events should be documented here
     * Below interfaces are used to build the website documentations
     * */
    var lGEvents = {
        afterAppendSlide: 'lgAfterAppendSlide',
        init: 'lgInit',
        hasVideo: 'lgHasVideo',
        containerResize: 'lgContainerResize',
        updateSlides: 'lgUpdateSlides',
        afterAppendSubHtml: 'lgAfterAppendSubHtml',
        beforeOpen: 'lgBeforeOpen',
        afterOpen: 'lgAfterOpen',
        slideItemLoad: 'lgSlideItemLoad',
        beforeSlide: 'lgBeforeSlide',
        afterSlide: 'lgAfterSlide',
        posterClick: 'lgPosterClick',
        dragStart: 'lgDragStart',
        dragMove: 'lgDragMove',
        dragEnd: 'lgDragEnd',
        beforeNextSlide: 'lgBeforeNextSlide',
        beforePrevSlide: 'lgBeforePrevSlide',
        beforeClose: 'lgBeforeClose',
        afterClose: 'lgAfterClose',
    };
    let Delete = /** @class */ (function () {
        function Delete(instance, $LG) {
            // get lightGallery core plugin instance
            this.core = instance;
            this.$LG = $LG;
            return this;
        }

        Delete.prototype.init = function () {
            var zoomIcons = "<button id=\"" + this.core.getIdName('lg-delete-picture') + "\" type=\"button\" aria-label=\"Delete Picture\" class=\"lg-icon lg-delete-picture\"><i class='icon-trash'></i></button>";
           /* this.core.outer.addClass('lg-use-transition-for-zoom');*/
            this.core.$toolbar.first().append(zoomIcons);
            this.delete();
        };

        Delete.prototype.delete = function (index) {
            const that = this;
            $('.lg-delete-picture').on('click', function (event) {
                console.log(that.core.index);
                let element = that.core.getSlideItem(that.core.index);
                console.log(element);
                /*that.core.Thumbnail.destroy();*/
                that.core.outer.find('.lg-inner').find('.lg-current').remove();
                /*that.core.LGel.on(lGEvents.updateSlides + ".thumb", function () {
                    that.core.rebuildThumbnails();
                });*/
                if (elements.length > 0) {
                    event.detail.index--;
                    that.core.goToNextSlide();

                    if (that.core.s.counter)
                        $('#lg-counter-all').text(elements.length);

                    if (elements.length == 1)
                        that.core.outer.find('.lg-actions').remove();

                } else
                    that.core.destroy();

            });
        };

        /**
         * Destroy function must be defined.
         * lightgallery will automatically call your module destroy function
         * before destroying the gallery
         */
        Delete.prototype.destroy = function () {
            console.log('asfasgfasfgasfgasfgasfgasgfasgf');
            /*this.onAfterSlide();
            this.core.outer.find('.lg-share').remove();*/
        };
        Delete.prototype.onAfterSlide = function (event) {
            var _this = this;
            var index = event.detail.index;
            var currentItem = this.core.galleryItems[index];
            setTimeout(function () {
                _this.shareOptions.forEach(function (shareOption) {
                    var selector = shareOption.selector;
                    console.log(selector);
                    /*_this.core.outer
                        .find(selector)
                        .attr('href', shareOption.generateLink(currentItem));*/
                });
            }, 100);
        };
        return Delete;
    }());

    return Delete;
})));
