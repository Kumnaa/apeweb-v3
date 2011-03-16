/*
  Apetech jQuery Shoutbox Plugin

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
        'date' : new Date()
    };
	
    var interval;
        
    var methods = {
        init : function( options ) {
            return this.each(function(){
                if ( options ) { 
                    $.extend( settings, options );
                }
                element = this;
                
                $(element).submit(function() {
                    alert('Handler for .submit() called.');
                    return false;
                });
                
                interval = setInterval(
                    function(){
                        $('#shoutbox').load('/shoutbox.php?action=html');
                    }, 30000
                );
            });
        }
   };
   
    $.fn.apetech_shoutbox = function( method ) {
        if ( methods[method] ) {
            return methods[method].apply( this, Array.prototype.slice.call( arguments, 1 ));
        } else if ( typeof method === 'object' || ! method ) {
            return methods.init.apply( this, arguments );
        } else {
            $.error( 'Method ' +  method + ' does not exist on jQuery.apetech_shoutbox' );
        }
    };
})( jQuery );