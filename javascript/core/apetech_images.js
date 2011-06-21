/*
  Apetech jQuery Forum Plugin

  @author Ben Bowtell

  @date 27-Feb-2011

  (c) 2011 by http://www.amplifycreative.net

  contact: ben@amplifycreative.net.net

  This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
(function( $ ){

    var settings = {
        'max-height' : 0,
        'max-width' : 0
    };
	
    var interval;
        
    var methods = {
        init : function( options ) {
            return this.each(function(){
                // something
                })
        },
        resize : function( options ) {
            return this.each(function(){
                if ( options ) { 
                    $.extend( settings, options );
                }

                var element = $(this);

                $(element).load(function() {
                    resizeImages(this);
                });
      
            })
        }
    };

    function resizeImages(element) {
        var width = $(element).width();
        var height = $(element).height();
        if (settings['max-width'] > 0) {
            if (width > settings['max-width']) {
                var ratio = width / settings['max-width'];
                $(element).removeAttr('height').removeAttr('width');
                $(element).attr('height', Math.round(height / ratio));
                $(element).attr('width', Math.round(width / ratio));
            }
        }

        width = $(element).width();
        height = $(element).height();
        if (settings['max-height'] > 0) {
            if (width > settings['max-height']) {
                var ratio = height / settings['max-height'];
                $(element).removeAttr('height').removeAttr('width');
                $(element).attr('height', Math.round(height / ratio));
                $(element).attr('width', Math.round(width / ratio));
            }
        }
    }

    $.fn.apetech_images = function( method ) {
        if ( methods[method] ) {
            return methods[method].apply( this, Array.prototype.slice.call( arguments, 1 ));
        } else if ( typeof method === 'object' || ! method ) {
            return methods.init.apply( this, arguments );
        } else {
            $.error( 'Method ' +  method + ' does not exist on jQuery.apetech_shoutbox' );
        }
    };
})( jQuery );