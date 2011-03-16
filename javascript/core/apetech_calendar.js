/*
  Apetech jQuery Calendar Plugin

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
    var month=new Array(12);
    month[0]="Jan";
    month[1]="Feb";
    month[2]="Mar";
    month[3]="Apr";
    month[4]="May";
    month[5]="Jun";
    month[6]="Jul";
    month[7]="Aug";
    month[8]="Sep";
    month[9]="Oct";
    month[10]="Nov";
    month[11]="Dec";
	
    var settings = {
        'date' : new Date()
    };
	
    var calendar;
	
    var methods = {
        init : function( options ) {
            return this.each(function(){
                if ( options ) { 
                    $.extend( settings, options );
                }
                element = this;
                populateCalendar();
                $("#cal_year_back", element).click(function() {
                    settings['date'].setFullYear(settings['date'].getFullYear() - 1);
                    moveCalendar();
                });
				
                $("#cal_year_forward", element).click(function() {
                    settings['date'].setFullYear(settings['date'].getFullYear() + 1);
                    moveCalendar();
                });
				
                $("#cal_month_back", element).click(function() {
                    if (settings['date'].getMonth() == 0)
                    {
                        settings['date'].setMonth(11);
                        settings['date'].setFullYear(settings['date'].getFullYear() - 1);
                    }
                    else
                    {
                        settings['date'].setMonth(settings['date'].getMonth() - 1);
                    }
					
                    moveCalendar();
                });
				
                $("#cal_month_forward", element).click(function() {
                    if (settings['date'].getMonth() == 11)
                    {
                        settings['date'].setMonth(0);
                        settings['date'].setFullYear(settings['date'].getFullYear() + 1);
                    }
                    else
                    {
                        settings['date'].setMonth(settings['date'].getMonth() + 1);
                    }
					
                    moveCalendar();
                });
            });

        }
    };

    function moveCalendar() {
        $.ajax({
            url: '/calendar.php?action=data&month=' + settings['date'].getFullYear() + (settings['date'].getMonth() + 1),
            success: function(data) {
                populateCalendar(data);
            }
        });
    }
	
    function populateCalendar(xml) {
        $("#cal_year", element).html(settings['date'].getFullYear());
        $("#cal_month", element).html(month[settings['date'].getMonth()]);
        if (xml != null) {
            $(xml).find("day").each(function() {
                var day = $(this).attr("id");
                $("#cal_day_" + day).html($(this).attr("number"))
            });
        }
    }
	
    $.fn.apetech_calendar = function( method ) {
        if ( methods[method] ) {
            return methods[method].apply( this, Array.prototype.slice.call( arguments, 1 ));
        } else if ( typeof method === 'object' || ! method ) {
            return methods.init.apply( this, arguments );
        } else {
            $.error( 'Method ' +  method + ' does not exist on jQuery.apetech_calendar' );
        }
    };
})( jQuery );